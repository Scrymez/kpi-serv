<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KpiSetting;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class KpiSettingController extends Controller
{
    private array $kpiDefaults = [
        'points_registration'          => ['value' => '2',  'description' => 'Баллы за регистрацию ученика'],
        'points_participation'          => ['value' => '5',  'description' => 'Баллы за участие ученика'],
        'points_place_1'                => ['value' => '40', 'description' => 'Баллы за 1 место'],
        'points_place_2'                => ['value' => '25', 'description' => 'Баллы за 2 место'],
        'points_place_3'                => ['value' => '15', 'description' => 'Баллы за 3 место'],
        'points_personal_participation' => ['value' => '10', 'description' => 'Баллы за личное участие учителя'],
        'points_add_olympiad'           => ['value' => '3',  'description' => 'Баллы за добавление олимпиады'],
        'points_class_teacher_bonus'    => ['value' => '5',  'description' => 'Бонус классного руководителя'],
        'result_deadline_days'          => ['value' => '14', 'description' => 'Дней на загрузку результатов после олимпиады'],
        'min_kpi_threshold'             => ['value' => '20', 'description' => 'Минимальный порог KPI за год'],
    ];

    // KPI настройки
    public function index()
    {
        $settings = [];
        foreach ($this->kpiDefaults as $key => $meta) {
            $settings[$key] = [
                'value' => KpiSetting::get($key, $meta['value']),
                'description' => $meta['description'],
            ];
        }
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $data = $request->validate(
            collect($this->kpiDefaults)->mapWithKeys(fn($v, $k) => [$k => 'nullable|numeric|min:0'])->toArray()
        );

        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                KpiSetting::set($key, $value);
            }
        }

        return response()->json(['message' => 'Настройки KPI сохранены.']);
    }

    // AI настройки
    public function getAiSettings()
    {
        return response()->json([
            'gemini_api_key' => [
                'value' => KpiSetting::get('gemini_api_key', ''),
                'description' => 'Gemini API ключ (aistudio.google.com)',
                'is_secret' => true,
            ],
            'gemini_system_prompt' => [
                'value' => KpiSetting::get('gemini_system_prompt',
                    'Ты помощник для поиска школьных олимпиад в России. Ищи актуальные олимпиады для учеников 1-11 классов на сайтах olimpiada.ru, vseross.ru, mos.ru, edu.gov.ru.'
                ),
                'description' => 'Системный промпт для AI агента',
                'is_secret' => false,
            ],
        ]);
    }

    public function updateAiSettings(Request $request)
    {
        $data = $request->validate([
            'gemini_api_key'      => 'nullable|string|max:200',
            'gemini_system_prompt' => 'nullable|string|max:2000',
        ]);

        foreach ($data as $key => $value) {
            if (!is_null($value)) {
                KpiSetting::set($key, $value);
            }
        }

        return response()->json(['message' => 'AI настройки сохранены.']);
    }

    // Тест API ключа
    public function testAiKey()
    {
        $key = GeminiService::getApiKey();
        if (!$key) {
            return response()->json(['ok' => false, 'message' => 'API ключ не задан.']);
        }

        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['contents' => [['parts' => [['text' => 'Hi']]]]]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            return response()->json(['ok' => true, 'message' => 'Ключ работает!']);
        }

        $error = json_decode($response, true)['error']['message'] ?? "HTTP {$code}";
        return response()->json(['ok' => false, 'message' => "Ошибка: {$error}"]);
    }
}
