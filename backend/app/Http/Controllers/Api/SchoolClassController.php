<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        return response()->json(SchoolClass::with('classTeacher')->orderBy('grade_number')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:10|unique:school_classes',
            'grade_number' => 'required|integer|min:1|max:11',
            'education_level' => 'required|in:primary,basic,secondary',
        ]);
        return response()->json(SchoolClass::create($data), 201);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:10',
            'grade_number' => 'sometimes|integer|min:1|max:11',
            'education_level' => 'sometimes|in:primary,basic,secondary',
        ]);
        $class->update($data);
        return response()->json($class);
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return response()->json(['message' => 'Класс удалён.']);
    }
}
