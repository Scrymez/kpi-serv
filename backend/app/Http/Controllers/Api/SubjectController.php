<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return response()->json(Subject::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:subjects',
            'coefficient' => 'required|numeric|min:1|max:5',
        ]);
        return response()->json(Subject::create($data), 201);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:100|unique:subjects,name,' . $subject->id,
            'coefficient' => 'sometimes|numeric|min:1|max:5',
        ]);
        $subject->update($data);
        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return response()->json(['message' => 'Предмет удалён.']);
    }
}
