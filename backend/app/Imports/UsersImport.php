<?php

namespace App\Imports;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\UserService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    private string $role;
    private int $importedCount = 0;
    private array $errors = [];
    private array $credentials = [];

    // Маппинг русских названий должностей на коды ролей
    private array $roleMap = [
        'учитель'                               => 'teacher',
        'teacher'                               => 'teacher',
        'директор'                              => 'director',
        'director'                              => 'director',
        'зам. по мероприятиям'                 => 'deputy_events',
        'заместитель директора по мероприятиям' => 'deputy_events',
        'deputy_events'                         => 'deputy_events',
        'зам. по увр'                           => 'deputy_edu',
        'зам. по учебно-воспитательной работе'  => 'deputy_edu',
        'заместитель директора по увр'          => 'deputy_edu',
        'deputy_edu'                            => 'deputy_edu',
        'зам. по нтр'                           => 'deputy_science',
        'зам. по научно-технической работе'    => 'deputy_science',
        'заместитель директора по нтр'         => 'deputy_science',
        'deputy_science'                        => 'deputy_science',
        'администратор'                         => 'admin',
        'admin'                                 => 'admin',
        'родитель'                              => 'parent',
        'parent'                                => 'parent',
    ];

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $lastName  = trim($row['familiya']  ?? $row['last_name']   ?? '');
                $firstName = trim($row['imya']       ?? $row['first_name']  ?? '');
                $middleName= trim($row['otchestvo']  ?? $row['middle_name'] ?? '');
                $age       = $row['vozrast'] ?? $row['age'] ?? null;
                $className = trim($row['klass'] ?? $row['class'] ?? '');

                if (!$lastName || !$firstName) {
                    $this->errors[] = "Строка {$rowNum}: не заполнены ФИО";
                    continue;
                }

                // Определяем роль
                $role = $this->role;
                if ($this->role === 'staff') {
                    $positionRaw = strtolower(trim($row['dolzhnost'] ?? $row['position'] ?? $row['rol'] ?? ''));
                    $role = $this->roleMap[$positionRaw] ?? null;
                    if (!$role) {
                        $this->errors[] = "Строка {$rowNum}: неизвестная должность '{$positionRaw}'";
                        continue;
                    }
                }

                // Класс — только для учеников
                $classId = null;
                if ($role === 'student' && $className) {
                    $class = SchoolClass::where('name', $className)->first();
                    if (!$class) {
                        $this->errors[] = "Строка {$rowNum}: класс '{$className}' не найден в системе";
                        continue;
                    }
                    $classId = $class->id;
                }

                // Предмет — для учителей (необязательно)
                $subjectId = null;
                $subjectName = trim($row['predmet'] ?? $row['subject'] ?? '');
                if ($subjectName && $role === 'teacher') {
                    $subject = Subject::where('name', 'like', $subjectName)->first();
                    $subjectId = $subject?->id;
                }

                $plainPassword = UserService::generatePassword();
                $login = UserService::generateLogin($lastName, $firstName);

                $user = User::create([
                    'last_name'   => $lastName,
                    'first_name'  => $firstName,
                    'middle_name' => $middleName ?: null,
                    'age'         => $age ? (int)$age : null,
                    'class_id'    => $classId,
                    'subject_id'  => $subjectId,
                    'role'        => $role,
                    'login'       => $login,
                    'password'    => Hash::make($plainPassword),
                    'must_change_password' => true,
                ]);

                $this->credentials[] = [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'role'      => $role,
                    'class'     => $className,
                    'login'     => $login,
                    'password'  => $plainPassword,
                ];
                $this->importedCount++;

            } catch (\Throwable $e) {
                $this->errors[] = "Строка {$rowNum}: {$e->getMessage()}";
            }
        }
    }

    public function getImportedCount(): int { return $this->importedCount; }
    public function getErrors(): array       { return $this->errors; }
    public function getCredentials(): array  { return $this->credentials; }
}
