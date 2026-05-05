<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CredentialsExport implements FromCollection, WithHeadings, WithStyles
{
    private array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return User::whereIn('id', $this->ids)
            ->with('schoolClass')
            ->get()
            ->map(fn($u) => [
                $u->full_name,
                $u->schoolClass?->name ?? '—',
                $u->login,
                '(выдан при создании)',
                $u->role,
            ]);
    }

    public function headings(): array
    {
        return ['ФИО', 'Класс', 'Логин', 'Пароль', 'Роль'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
