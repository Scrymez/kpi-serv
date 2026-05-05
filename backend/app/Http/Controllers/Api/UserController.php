<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Exports\UsersExport;
use App\Exports\CredentialsExport;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['schoolClass', 'subject'])
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('last_name', 'like', "%{$request->search}%")
                   ->orWhere('first_name', 'like', "%{$request->search}%")
                   ->orWhere('login', 'like', "%{$request->search}%");
            }))
            ->orderBy('last_name');

        return response()->json($query->paginate(50));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'role' => 'required|in:admin,director,deputy_events,deputy_edu,deputy_science,teacher,student',
            'class_id' => 'nullable|exists:school_classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'age' => 'nullable|integer|min:5|max:80',
            'is_class_teacher' => 'boolean',
            'class_teacher_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $plainPassword = UserService::generatePassword();
        $data['login'] = UserService::generateLogin($data['last_name'], $data['first_name']);
        $data['password'] = Hash::make($plainPassword);

        $user = User::create($data);

        return response()->json([
            'user' => $user->load(['schoolClass', 'subject']),
            'plain_password' => $plainPassword,
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['schoolClass', 'subject', 'classTeacherClass']));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'last_name' => 'sometimes|string|max:100',
            'first_name' => 'sometimes|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'class_id' => 'nullable|exists:school_classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'age' => 'nullable|integer|min:5|max:80',
            'is_class_teacher' => 'boolean',
            'class_teacher_class_id' => 'nullable|exists:school_classes,id',
            'is_active' => 'boolean',
        ]);

        $user->update($data);
        return response()->json($user->load(['schoolClass', 'subject']));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Нельзя удалить себя.'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'Пользователь удалён.']);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'role' => 'required|in:teacher,student,staff',
        ]);

        $import = new UsersImport($request->role);
        Excel::import($import, $request->file('file'));

        return response()->json([
            'imported' => $import->getImportedCount(),
            'errors' => $import->getErrors(),
            'credentials' => $import->getCredentials(),
        ]);
    }

    public function exportTemplate(Request $request)
    {
        $role = $request->get('role', 'student');
        return Excel::download(new \App\Exports\ImportTemplateExport($role), "шаблон_{$role}.xlsx");
    }

    public function exportCredentials(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        return Excel::download(new CredentialsExport($request->ids), 'логины_пароли.xlsx');
    }
}
