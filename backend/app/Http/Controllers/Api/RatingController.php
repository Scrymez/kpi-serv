<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\KpiScore;
use App\Models\User;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function teachers(Request $request)
    {
        $year = AcademicYear::current();

        $teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->with(['subject'])
            ->get()
            ->map(function ($teacher) use ($year) {
                $total = KpiScore::where('user_id', $teacher->id)
                    ->when($year, fn($q) => $q->where('academic_year_id', $year->id))
                    ->sum('points');

                return [
                    'id' => $teacher->id,
                    'full_name' => $teacher->full_name,
                    'subject' => $teacher->subject?->name,
                    'total_kpi' => round((float)$total, 2),
                ];
            })
            ->sortByDesc('total_kpi')
            ->values();

        return response()->json($teachers);
    }

    public function students(Request $request)
    {
        $educationLevel = $request->get('education_level'); // primary, basic, secondary

        $query = User::where('role', 'student')
            ->where('is_active', true)
            ->with(['schoolClass']);

        if ($educationLevel) {
            $query->whereHas('schoolClass', fn($q) => $q->where('education_level', $educationLevel));
        }

        $year = AcademicYear::current();

        $students = $query->get()->map(function ($student) use ($year) {
            $wins = \App\Models\OlympiadResult::whereHas('registration', fn($q) => $q->where('student_id', $student->id))
                ->where('status', 'approved')
                ->whereNotNull('place')
                ->count();

            $participations = \App\Models\OlympiadRegistration::where('student_id', $student->id)
                ->where('status', 'participated')
                ->count();

            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'class' => $student->schoolClass?->name,
                'education_level' => $student->schoolClass?->education_level,
                'participations' => $participations,
                'wins' => $wins,
            ];
        })
        ->sortByDesc('wins')
        ->values();

        return response()->json($students);
    }
}
