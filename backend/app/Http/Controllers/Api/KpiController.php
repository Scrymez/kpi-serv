<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\KpiAppeal;
use App\Models\KpiScore;
use App\Models\User;
use App\Services\KpiService;
use Illuminate\Http\Request;

class KpiController extends Controller
{
    public function my(Request $request)
    {
        $user = auth()->user();
        $year = AcademicYear::current();

        $scores = KpiScore::where('user_id', $user->id)
            ->when($year, fn($q) => $q->where('academic_year_id', $year->id))
            ->latest()
            ->get();

        return response()->json([
            'total' => KpiService::getTotalForUser($user->id),
            'scores' => $scores,
            'year' => $year,
        ]);
    }

    public function forUser(User $user)
    {
        $year = AcademicYear::current();

        $scores = KpiScore::where('user_id', $user->id)
            ->when($year, fn($q) => $q->where('academic_year_id', $year->id))
            ->latest()
            ->get();

        return response()->json([
            'user' => $user,
            'total' => KpiService::getTotalForUser($user->id),
            'scores' => $scores,
        ]);
    }

    public function storeAppeal(Request $request)
    {
        $data = $request->validate([
            'kpi_score_id' => 'required|exists:kpi_scores,id',
            'reason' => 'required|string|max:1000',
        ]);

        $score = KpiScore::findOrFail($data['kpi_score_id']);
        if ($score->user_id !== auth()->id()) {
            return response()->json(['message' => 'Это не ваш балл.'], 403);
        }

        $existing = KpiAppeal::where('kpi_score_id', $score->id)->where('status', 'pending')->exists();
        if ($existing) {
            return response()->json(['message' => 'Апелляция уже подана.'], 422);
        }

        $appeal = KpiAppeal::create([
            'user_id' => auth()->id(),
            'kpi_score_id' => $score->id,
            'reason' => $data['reason'],
        ]);

        return response()->json($appeal, 201);
    }

    public function appeals()
    {
        $appeals = KpiAppeal::with(['user', 'kpiScore', 'resolvedBy'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json($appeals);
    }

    public function resolveAppeal(Request $request, KpiAppeal $appeal)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'resolution_note' => 'nullable|string|max:500',
        ]);

        $appeal->update([
            'status' => $request->status,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_note' => $request->resolution_note,
        ]);

        return response()->json(['message' => 'Апелляция обработана.', 'appeal' => $appeal]);
    }
}
