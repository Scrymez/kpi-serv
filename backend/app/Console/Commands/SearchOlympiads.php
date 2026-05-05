<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;

class SearchOlympiads extends Command
{
    protected $signature = 'olympiads:search {--query= : Дополнительный поисковый запрос}';
    protected $description = 'Поиск новых олимпиад через Gemini AI';

    public function handle(): int
    {
        $this->info('Запускаю поиск олимпиад через Gemini AI...');

        $results = GeminiService::searchOlympiads($this->option('query') ?? '');

        $this->info("Найдено и добавлено: " . count($results) . " олимпиад.");

        foreach ($results as $olympiad) {
            $this->line("  + {$olympiad->title} ({$olympiad->start_date->format('d.m.Y')})");
        }

        return Command::SUCCESS;
    }
}
