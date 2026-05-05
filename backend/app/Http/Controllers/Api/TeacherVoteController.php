<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeacherVote;
use App\Models\User;
use App\Models\VoteNomination;
use Illuminate\Http\Request;

class TeacherVoteController extends Controller
{
    public function index(Request $request)
    {
        $nominations = VoteNomination::where('is_active', true)
            ->orderBy('title')
            ->get()
            ->map(function ($nomination) use ($request) {
                $myVote = TeacherVote::where('nomination_id', $nomination->id)
                    ->where('voter_id', $request->user()->id)
                    ->first();

                return [
                    'id' => $nomination->id,
                    'title' => $nomination->title,
                    'description' => $nomination->description,
                    'my_teacher_id' => $myVote?->teacher_id,
                    'votes_count' => TeacherVote::where('nomination_id', $nomination->id)->count(),
                ];
            });

        $teachers = User::with('subject')
            ->where('role', 'teacher')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get()
            ->map(fn($teacher) => [
                'id' => $teacher->id,
                'full_name' => $teacher->full_name,
                'subject' => $teacher->subject?->name,
                'avatar_url' => $teacher->avatar_url,
            ]);

        return response()->json([
            'nominations' => $nominations,
            'teachers' => $teachers,
        ]);
    }

    public function vote(Request $request)
    {
        if (!$request->user()->isParent()) {
            return response()->json(['message' => 'Голосовать за учителей могут только родители.'], 403);
        }

        $data = $request->validate([
            'nomination_id' => ['required', 'exists:vote_nominations,id'],
            'teacher_id' => ['required', 'exists:users,id'],
        ]);

        $nomination = VoteNomination::where('is_active', true)->findOrFail($data['nomination_id']);
        $teacher = User::where('role', 'teacher')->where('is_active', true)->findOrFail($data['teacher_id']);

        $vote = TeacherVote::updateOrCreate(
            ['nomination_id' => $nomination->id, 'voter_id' => $request->user()->id],
            ['teacher_id' => $teacher->id]
        );

        return response()->json([
            'message' => 'Голос учтен.',
            'vote' => $vote,
        ]);
    }
}

