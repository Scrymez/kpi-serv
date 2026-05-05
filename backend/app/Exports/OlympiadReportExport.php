<?php

namespace App\Exports;

use App\Models\Olympiad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OlympiadReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return Olympiad::with(['subject', 'registrations'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($o) => [
                $o->title,
                $o->subject?->name ?? 'Не указан',
                $o->level_label,
                $o->start_date->format('d.m.Y'),
                $o->end_date->format('d.m.Y'),
                $o->registrations->count(),
                $o->registrations->where('status', 'participated')->count(),
                $o->source_type === 'auto' ? 'AI' : 'Вручную',
            ]);
    }

    public function headings(): array
    {
        return ['Название', 'Предмет', 'Уровень', 'Начало', 'Конец', 'Зарегистрировано', 'Участвовало', 'Источник'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function title(): string
    {
        return 'Олимпиады';
    }
}
