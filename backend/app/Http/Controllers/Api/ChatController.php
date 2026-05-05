<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $messages = ChatMessage::with('user:id,last_name,first_name,middle_name,role,avatar_path')
            ->latest()
            ->limit((int) $request->get('limit', 50))
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $body = trim($data['body']);
        if ($body === '') {
            return response()->json(['message' => 'Сообщение не может быть пустым.'], 422);
        }

        $message = ChatMessage::create([
            'user_id' => $request->user()->id,
            'body' => $body,
        ]);

        return response()->json($message->load('user:id,last_name,first_name,middle_name,role,avatar_path'), 201);
    }

    public function stream(Request $request)
    {
        $accessToken = PersonalAccessToken::findToken((string) $request->query('token'));
        if (!$accessToken || !$accessToken->tokenable || !$accessToken->tokenable->is_active) {
            abort(401);
        }

        $lastId = (int) $request->query('last_id', 0);

        return response()->stream(function () use ($lastId) {
            $currentLastId = $lastId;
            $startedAt = time();

            while (time() - $startedAt < 60) {
                $messages = ChatMessage::with('user:id,last_name,first_name,middle_name,role,avatar_path')
                    ->where('id', '>', $currentLastId)
                    ->orderBy('id')
                    ->get();

                foreach ($messages as $message) {
                    $currentLastId = $message->id;
                    echo "event: message\n";
                    echo 'data: ' . $message->toJson(JSON_UNESCAPED_UNICODE) . "\n\n";
                }

                echo ": ping\n\n";

                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                sleep(1);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
