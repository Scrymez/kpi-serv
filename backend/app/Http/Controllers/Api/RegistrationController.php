<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Olympiad;
use App\Models\OlympiadRegistration;
use App\Models\OlympiadResult;
use App\Services\KpiService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = OlympiadRegistration::with(['olympiad.subject', 'student.schoolClass', 'teacher', 'result']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->id);
        } elseif ($user->isTeacher()) {
            $query->where(fn($q) => $q->where('teacher_id', $user->id)->orWhere('teacher2_id', $user->id));
        }

        $query->when($request->olympiad_id, fn($q) => $q->where('olympiad_id', $request->olympiad_id))
              ->when($request->student_id, fn($q) => $q->where('student_id', $request->student_id));

        return response()->json($query->latest()->paginate(30));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'olympiad_id' => 'required|exists:olympiads,id',
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'nullable|exists:users,id',
            'teacher2_id' => 'nullable|exists:users,id',
        ]);

        $olympiad = Olympiad::findOrFail($data['olympiad_id']);

        // Проверка на конфликт дат
        if ($olympiad->hasDateConflict($data['student_id'])) {
            return response()->json([
                'message' => 'Ученик уже зарегистрирован на другую олимпиаду в это же время.',
            ], 422);
        }

        // Проверка дублирования
        $exists = OlympiadRegistration::where('olympiad_id', $data['olympiad_id'])
            ->where('student_id', $data['student_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ученик уже зарегистрирован на эту олимпиаду.'], 422);
        }

        $data['registered_by'] = auth()->id();

        // Если учитель регистрирует сам — он первый учитель
        if (auth()->user()->isTeacher() && !$data['teacher_id']) {
            $data['teacher_id'] = auth()->id();
        }

        $reg = OlympiadRegistration::create($data);

        // Создаём запись результата
        OlympiadResult::create([
            'registration_id' => $reg->id,
            'status' => 'pending_upload',
        ]);

        KpiService::awardForRegistration($reg);

        return response()->json($reg->load(['olympiad', 'student', 'teacher']), 201);
    }

    public function destroy(OlympiadRegistration $registration)
    {
        $user = auth()->user();
        if (!$user->canVerify() && $registration->registered_by !== $user->id) {
            return response()->json(['message' => 'Доступ запрещён.'], 403);
        }
        $registration->delete();
        return response()->json(['message' => 'Регистрация отменена.']);
    }

    public function updateStatus(Request $request, OlympiadRegistration $registration)
    {
        $request->validate(['status' => 'required|in:registered,participated,absent']);
        $registration->update(['status' => $request->status]);
        return response()->json($registration);
    }
}
