<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    public static function generateLogin(string $lastName, string $firstName): string
    {
        $base = Str::lower(
            self::transliterate($lastName) . '.' . Str::substr(self::transliterate($firstName), 0, 1)
        );
        $login = $base;
        $counter = 1;

        while (User::where('login', $login)->exists()) {
            $login = $base . $counter;
            $counter++;
        }

        return $login;
    }

    public static function generatePassword(int $length = 8): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyz23456789';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 0, $length);
    }

    private static function transliterate(string $text): string
    {
        $map = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
            'ж'=>'zh','з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m',
            'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
            'ф'=>'f','х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch',
            'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        ];
        return strtr(Str::lower($text), $map);
    }
}
