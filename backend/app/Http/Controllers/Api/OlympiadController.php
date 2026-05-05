<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Olympiad;
use App\Services\KpiService;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class OlympiadController extends Controller
{
    public function index(Request $request)
    {
        $query = Olympiad::with(['subject', 'createdBy'])
            ->where('is_active', true)
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->level, fn($q) => $q->where('level', $request->level))
            ->when($request->search, fn($q) => $q->where('title', 'like', "%{$request->search}%"))
            ->when($request->upcoming, fn($q) => $q->where('end_date', '>=', now()))
            ->orderBy('start_date');

        return response()->json($query->paginate(20));
    }

    public function show(Olympiad $olympiad)
    {
        return response()->json($olympiad->load(['subject', 'createdBy', 'registrations.student']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'level' => 'required|in:school,municipal,regional,federal,international',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'result_deadline' => 'nullable|date|after_or_equal:end_date',
            'source_url' => 'nullable|url',
        ]);

        $data['created_by'] = auth()->id();
        $data['source_type'] = 'manual';

        $olympiad = Olympiad::create($data);

        KpiService::awardForAddingOlympiad(auth()->id());

        return response()->json($olympiad->load('subject'), 201);
    }

    public function update(Request $request, Olympiad $olympiad)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'subject_id' => 'nullable|exists:subjects,id',
            'level' => 'sometimes|in:school,municipal,regional,federal,international',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'result_deadline' => 'nullable|date',
            'source_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        $olympiad->update($data);
        return response()->json($olympiad->load('subject'));
    }

    public function destroy(Olympiad $olympiad)
    {
        $olympiad->update(['is_active' => false]);
        return response()->json(['message' => 'Олимпиада деактивирована.']);
    }

    public function searchAi(Request $request)
    {
        $results = GeminiService::searchOlympiads($request->get('query', ''));
        return response()->json($results);
    }
}
