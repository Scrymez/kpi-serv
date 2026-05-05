<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\KpiSetting;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Учебный год
        AcademicYear::create([
            'name' => '2025-2026',
            'starts_at' => '2025-09-01',
            'ends_at' => '2026-05-31',
            'is_current' => true,
        ]);

        // Предметы
        $subjects = [
            ['name' => 'Математика', 'coefficient' => 1.0],
            ['name' => 'Физика', 'coefficient' => 1.2],
            ['name' => 'Химия', 'coefficient' => 1.2],
            ['name' => 'Биология', 'coefficient' => 1.1],
            ['name' => 'Информатика', 'coefficient' => 1.3],
            ['name' => 'История', 'coefficient' => 1.0],
            ['name' => 'География', 'coefficient' => 1.0],
            ['name' => 'Русский язык', 'coefficient' => 1.0],
            ['name' => 'Литература', 'coefficient' => 1.0],
            ['name' => 'Английский язык', 'coefficient' => 1.1],
            ['name' => 'Обществознание', 'coefficient' => 1.0],
            ['name' => 'Технология', 'coefficient' => 1.5],
            ['name' => 'Изобразительное искусство', 'coefficient' => 1.8],
            ['name' => 'Музыка', 'coefficient' => 1.8],
            ['name' => 'Физическая культура', 'coefficient' => 1.2],
        ];
        foreach ($subjects as $s) {
            Subject::create($s);
        }

        // Классы 1А-11В
        for ($g = 1; $g <= 11; $g++) {
            $level = $g <= 4 ? 'primary' : ($g <= 9 ? 'basic' : 'secondary');
            foreach (['А', 'Б', 'В'] as $letter) {
                SchoolClass::create([
                    'name' => "{$g}{$letter}",
                    'grade_number' => $g,
                    'education_level' => $level,
                ]);
            }
        }

        // Администратор системы
        User::create([
            'last_name' => 'Администратор',
            'first_name' => 'Системный',
            'middle_name' => null,
            'login' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        // KPI настройки по умолчанию
        $settings = [
            ['key' => 'points_registration', 'value' => '2', 'description' => 'Баллы за регистрацию ученика'],
            ['key' => 'points_participation', 'value' => '5', 'description' => 'Баллы за участие ученика'],
            ['key' => 'points_place_1', 'value' => '40', 'description' => 'Баллы за 1 место'],
            ['key' => 'points_place_2', 'value' => '25', 'description' => 'Баллы за 2 место'],
            ['key' => 'points_place_3', 'value' => '15', 'description' => 'Баллы за 3 место'],
            ['key' => 'points_personal_participation', 'value' => '10', 'description' => 'Баллы за личное участие учителя'],
            ['key' => 'points_add_olympiad', 'value' => '3', 'description' => 'Баллы за добавление олимпиады'],
            ['key' => 'points_class_teacher_bonus', 'value' => '5', 'description' => 'Бонус классного руководителя'],
            ['key' => 'result_deadline_days', 'value' => '14', 'description' => 'Дней на загрузку результатов'],
            ['key' => 'min_kpi_threshold', 'value' => '20', 'description' => 'Минимальный порог KPI за год'],
        ];
        foreach ($settings as $s) {
            KpiSetting::create($s);
        }

    }
}
