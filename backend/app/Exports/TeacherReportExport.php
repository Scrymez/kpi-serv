<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\KpiScore;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $year = AcademicYear::current();

        return User::where('role', 'teacher')
            ->where('is_active', true)
            ->with(['subject'])
            ->get()
            ->map(function ($teacher) use ($year) {
                $total = KpiScore::where('user_id', $teacher->id)
                    ->when($year, fn($q) => $q->where('academic_year_id', $year->id))
                    ->sum('points');

                $registrations = \App\Models\OlympiadRegistration::where(function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id)->orWhere('teacher2_id', $teacher->id);
                })->count();

                $wins = \App\Models\OlympiadResult::whereHas('registration', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id)->orWhere('teacher2_id', $teacher->id);
                })->where('status', 'approved')->whereNotNull('place')->count();

                return [
                    $teacher->full_name,
                    $teacher->subject?->name ?? '—',
                    round((float)$total, 2),
                    $registrations,
                    $wins,
                    $total >= (float)\App\Models\KpiSetting::get('min_kpi_threshold', 20) ? 'Да' : 'Нет',
                ];
            })
            ->sortByDesc(fn($row) => $row[2])
            ->values();
    }

    public function headings(): array
    {
        return ['ФИО учителя', 'Предмет', 'KPI (баллы)', 'Регистраций', 'Побед учеников', 'Порог выполнен'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Рейтинг учителей';
    }
}
