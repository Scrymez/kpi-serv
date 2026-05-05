<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\KpiSetting;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем AI настройки в kpi_settings
        $settings = [
            [
                'key' => 'gemini_api_key',
                'value' => '',
                'description' => 'Gemini API ключ (получить на aistudio.google.com)',
            ],
            [
                'key' => 'gemini_system_prompt',
                'value' => 'Ты помощник для поиска школьных олимпиад в России. Ищи актуальные олимпиады для учеников 1-11 классов на сайтах olimpiada.ru, vseross.ru, mos.ru, edu.gov.ru. Возвращай только JSON массив без пояснений.',
                'description' => 'Системный промпт для AI агента поиска олимпиад',
            ],
        ];

        foreach ($settings as $s) {
            KpiSetting::firstOrCreate(['key' => $s['key']], $s);
        }
    }

    public function down(): void
    {
        KpiSetting::whereIn('key', ['gemini_api_key', 'gemini_system_prompt'])->delete();
    }
};
