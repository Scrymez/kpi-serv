<?php

namespace App\Services;

use App\Models\KpiSetting;
use App\Models\Olympiad;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private static string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public static function getApiKey(): string
    {
        // Приоритет: БД → .env
        $fromDb = KpiSetting::get('gemini_api_key', '');
        return $fromDb ?: config('services.gemini.key', '');
    }

    public static function getSystemPrompt(): string
    {
        return KpiSetting::get('gemini_system_prompt',
            'Ты помощник для поиска школьных олимпиад в России. Ищи актуальные олимпиады для учеников 1-11 классов.'
        );
    }

    public static function searchOlympiads(string $query = ''): array
    {
        $apiKey = self::getApiKey();
        if (!$apiKey) {
            return ['error' => 'API ключ Gemini не настроен. Зайдите в Настройки → AI и добавьте ключ.'];
        }

        $prompt = self::buildSearchPrompt($query);

        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 4096],
        ]);

        $ch = curl_init(self::$apiUrl . '?key=' . $apiKey);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            Log::error('Gemini API error', ['code' => $httpCode]);
            return ['error' => "Ошибка Gemini API (код {$httpCode}). Проверьте ключ."];
        }

        return self::parseResponse($response);
    }

    private static function buildSearchPrompt(string $query): string
    {
        $date = now()->format('Y-m-d');
        $systemPrompt = self::getSystemPrompt();
        $queryPart = $query ? "Дополнительный фокус поиска: {$query}." : '';

        return <<<PROMPT
{$systemPrompt}

Сегодня {$date}. {$queryPart}

Верни ТОЛЬКО JSON массив (без пояснений, без markdown), каждый элемент:
{
  "title": "Название олимпиады",
  "description": "Краткое описание",
  "subject": "Предмет (Математика/Физика/Химия/История и т.д.)",
  "level": "school|municipal|regional|federal|international",
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD",
  "source_url": "https://..."
}

Верни 10-15 актуальных олимпиад. Если точных дат нет — поставь приблизительные на ближайшие 3 месяца.
PROMPT;
    }

    private static function parseResponse(string $response): array
    {
        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);

        $olympiads = json_decode($text, true);
        if (!is_array($olympiads)) {
            Log::error('Gemini: failed to parse JSON', ['text' => substr($text, 0, 500)]);
            return ['error' => 'AI вернул некорректный ответ. Попробуйте снова.'];
        }

        $saved = [];
        foreach ($olympiads as $item) {
            if (empty($item['title']) || empty($item['start_date']) || empty($item['end_date'])) {
                continue;
            }

            $exists = Olympiad::where('title', $item['title'])
                ->where('start_date', $item['start_date'])
                ->exists();

            if (!$exists) {
                $saved[] = Olympiad::create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'level' => in_array($item['level'], ['school', 'municipal', 'regional', 'federal', 'international'])
                        ? $item['level'] : 'federal',
                    'start_date' => $item['start_date'],
                    'end_date' => $item['end_date'],
                    'source_url' => $item['source_url'] ?? null,
                    'source_type' => 'auto',
                    'is_active' => true,
                ]);
            }
        }

        return $saved;
    }
}
