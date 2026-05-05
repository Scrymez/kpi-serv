<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OlympiadRegistration;
use App\Models\OlympiadResult;
use App\Models\KpiSetting;
use App\Services\KpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = OlympiadResult::with(['registration.olympiad', 'registration.student', 'verifiedBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status));

        return response()->json($query->latest()->paginate(30));
    }

    public function pending()
    {
        $results = OlympiadResult::with(['registration.olympiad', 'registration.student', 'uploadedBy'])
            ->where('status', 'pending_verification')
            ->latest()
            ->get();

        return response()->json($results);
    }

    public function upload(Request $request, OlympiadRegistration $registration)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'place' => 'nullable|integer|min:1|max:3',
            'is_winner' => 'boolean',
        ]);

        // Проверяем дедлайн
        $deadlineDays = (int) KpiSetting::get('result_deadline_days', 14);
        $olympiad = $registration->olympiad;
        $deadline = $olympiad->end_date->addDays($deadlineDays);

        if (now()->gt($deadline)) {
            return response()->json(['message' => "Дедлайн загрузки результатов истёк ({$deadline->format('d.m.Y')})."], 422);
        }

        $file = $request->file('document');
        $path = $file->store("results/{$registration->id}", 'private');

        $result = OlympiadResult::updateOrCreate(
            ['registration_id' => $registration->id],
            [
                'place' => $request->place,
                'is_winner' => $request->boolean('is_winner'),
                'document_path' => $path,
                'document_original_name' => $file->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'status' => 'pending_verification',
            ]
        );

        return response()->json($result, 201);
    }

    public function verify(Request $request, OlympiadResult $result)
    {
        if (!auth()->user()->canVerify()) {
            return response()->json(['message' => 'Доступ запрещён.'], 403);
        }

        $result->update([
            'status' => 'approved',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'reject_reason' => null,
        ]);

        KpiService::awardForResult($result);

        return response()->json(['message' => 'Результат подтверждён.', 'result' => $result]);
    }

    public function reject(Request $request, OlympiadResult $result)
    {
        if (!auth()->user()->canVerify()) {
            return response()->json(['message' => 'Доступ запрещён.'], 403);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $result->update([
            'status' => 'rejected',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
            'reject_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Результат отклонён.']);
    }
}
