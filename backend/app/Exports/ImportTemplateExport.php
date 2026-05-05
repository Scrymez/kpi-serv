<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function array(): array
    {
        if ($this->role === 'student') {
            return [
                ['Иванов',  'Иван',    'Иванович',  '14', '9А'],
                ['Петрова', 'Мария',   'Алексеевна','13', '8Б'],
                ['Сидоров', 'Алексей', 'Петрович',  '15', '10В'],
            ];
        }

        if ($this->role === 'parent') {
            return [
                ['Иванова', 'Ольга', 'Павловна', '39'],
                ['Петров', 'Андрей', 'Сергеевич', '42'],
            ];
        }

        // Сотрудники
        return [
            ['Иванова',  'Анна',    'Петровна',   '38', 'учитель',              'Математика'],
            ['Петров',   'Сергей',  'Иванович',   '45', 'учитель',              'Физика'],
            ['Сидорова', 'Мария',   'Алексеевна', '52', 'зам. по мероприятиям', ''],
            ['Козлов',   'Дмитрий', 'Сергеевич',  '48', 'зам. по УВР',          ''],
            ['Новикова', 'Елена',   'Викторовна', '55', 'директор',             ''],
        ];
    }

    public function headings(): array
    {
        if ($this->role === 'student') {
            return ['familiya', 'imya', 'otchestvo', 'vozrast', 'klass'];
        }
        if ($this->role === 'parent') {
            return ['familiya', 'imya', 'otchestvo', 'vozrast'];
        }
        return ['familiya', 'imya', 'otchestvo', 'vozrast', 'dolzhnost', 'predmet'];
    }

    public function columnWidths(): array
    {
        if ($this->role === 'student') {
            return ['A' => 20, 'B' => 15, 'C' => 20, 'D' => 10, 'E' => 8];
        }
        if ($this->role === 'parent') {
            return ['A' => 20, 'B' => 15, 'C' => 20, 'D' => 10];
        }
        return ['A' => 20, 'B' => 15, 'C' => 20, 'D' => 10, 'E' => 35, 'F' => 20];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9EAD3']],
            ],
        ];
    }

    public function title(): string
    {
        return match ($this->role) {
            'student' => 'Ученики',
            'parent' => 'Родители',
            default => 'Сотрудники',
        };
    }
}
