<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\KpiScore;
use App\Models\KpiSetting;
use App\Models\OlympiadResult;
use App\Models\OlympiadRegistration;
use App\Models\User;

class KpiService
{
    public static function awardForRegistration(OlympiadRegistration $reg): void
    {
        $year = AcademicYear::current();
        if (!$year) return;

        $points = (float) KpiSetting::get('points_registration', 2);

        if ($reg->teacher_id) {
            self::addScore($reg->teacher_id, $points, 'Регистрация ученика на олимпиаду', $reg, $year->id);
        }

        if ($reg->teacher2_id) {
            self::addScore($reg->teacher2_id, $points / 2, 'Регистрация ученика на олимпиаду (совместно)', $reg, $year->id);
        }
    }

    public static function awardForResult(OlympiadResult $result): void
    {
        $year = AcademicYear::current();
        if (!$year) return;

        $reg = $result->registration()->with(['olympiad.subject', 'student.schoolClass'])->first();
        $olympiad = $reg->olympiad;

        $levelCoef = $olympiad->level_coefficient;
        $subjectCoef = $olympiad->subject?->coefficient ?? 1.0;

        $participationPoints = (float) KpiSetting::get('points_participation', 5);
        $placePoints = match($result->place) {
            1 => (float) KpiSetting::get('points_place_1', 40),
            2 => (float) KpiSetting::get('points_place_2', 25),
            3 => (float) KpiSetting::get('points_place_3', 15),
            default => 0,
        };

        $totalPoints = ($participationPoints + $placePoints) * $levelCoef * $subjectCoef;

        // Обновляем статус регистрации
        $reg->update(['status' => 'participated']);

        $teacherIds = array_filter([$reg->teacher_id, $reg->teacher2_id]);
        $hasTwoTeachers = count($teacherIds) === 2;

        foreach ($teacherIds as $teacherId) {
            $pts = $hasTwoTeachers ? $totalPoints / 2 : $totalPoints;
            self::addScore($teacherId, $pts, "Результат ученика: {$reg->student->full_name}, место: {$result->place}", $result, $year->id);
        }

        // Бонус классному руководителю
        $student = $reg->student;
        if ($student && $student->class_id) {
            $classTeacher = User::where('is_class_teacher', true)
                ->where('class_teacher_class_id', $student->class_id)
                ->where('id', '!=', $reg->teacher_id)
                ->where('id', '!=', $reg->teacher2_id)
                ->first();

            if ($classTeacher) {
                $bonusPoints = (float) KpiSetting::get('points_class_teacher_bonus', 5);
                self::addScore($classTeacher->id, $bonusPoints * $levelCoef, "Бонус классного руководителя за победу ученика", $result, $year->id);
            }
        }
    }

    public static function awardForAddingOlympiad(int $userId): void
    {
        $year = AcademicYear::current();
        if (!$year) return;

        $points = (float) KpiSetting::get('points_add_olympiad', 3);
        self::addScore($userId, $points, 'Добавил олимпиаду в систему', null, $year->id);
    }

    public static function awardPersonalParticipation(OlympiadResult $result, int $teacherId): void
    {
        $year = AcademicYear::current();
        if (!$year) return;

        $points = (float) KpiSetting::get('points_personal_participation', 10);
        self::addScore($teacherId, $points, 'Личное участие в соревновании', $result, $year->id);
    }

    public static function getTotalForUser(int $userId, ?int $yearId = null): float
    {
        $query = KpiScore::where('user_id', $userId);

        if ($yearId) {
            $query->where('academic_year_id', $yearId);
        } else {
            $year = AcademicYear::current();
            if ($year) $query->where('academic_year_id', $year->id);
        }

        return (float) $query->sum('points');
    }

    private static function addScore(int $userId, float $points, string $reason, $reference, int $yearId): void
    {
        KpiScore::create([
            'user_id' => $userId,
            'points' => round($points, 2),
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'academic_year_id' => $yearId,
        ]);
    }
}
