<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_nominations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('teacher_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nomination_id')->constrained('vote_nominations')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['nomination_id', 'voter_id']);
            $table->index(['nomination_id', 'teacher_id']);
        });

        DB::table('vote_nominations')->insert([
            [
                'title' => 'Лучший наставник',
                'description' => 'Учитель, который особенно помогает ученикам расти и не бросать сложные задачи.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Лучший классный руководитель',
                'description' => 'Учитель, который создает сильную, спокойную и поддерживающую атмосферу в классе.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Олимпиадный вклад',
                'description' => 'Учитель, чей вклад в олимпиадное движение школы особенно заметен.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Самый вдохновляющий учитель',
                'description' => 'Учитель, который умеет заинтересовать предметом и поддержать мотивацию.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_votes');
        Schema::dropIfExists('vote_nominations');
    }
};
