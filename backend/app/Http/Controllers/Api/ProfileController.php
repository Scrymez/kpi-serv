<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['nullable', 'required_unless:force,true', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'force' => ['boolean'],
        ]);

        $user = $request->user();
        $isForcedChange = $request->boolean('force') && $user->must_change_password;

        if (!$isForcedChange && !$request->filled('current_password')) {
            return response()->json(['message' => 'Укажите текущий пароль.'], 422);
        }

        if (!$isForcedChange && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Текущий пароль указан неверно.'], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Пароль изменен.',
            'token' => $token,
            'user' => $user->fresh()->load(['schoolClass', 'subject']),
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);

        return response()->json([
            'message' => 'Аватар обновлен.',
            'user' => $user->fresh()->load(['schoolClass', 'subject']),
        ]);
    }
}
