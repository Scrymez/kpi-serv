<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olympiads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->enum('level', ['school', 'municipal', 'regional', 'federal', 'international']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('result_deadline')->nullable();
            $table->string('source_url')->nullable();
            $table->enum('source_type', ['manual', 'auto'])->default('manual');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('olympiad_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('olympiad_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('registered_by')->nullable();
            // если два учителя — первичный и вторичный
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->unsignedBigInteger('teacher2_id')->nullable();
            $table->enum('status', ['registered', 'participated', 'absent'])->default('registered');
            $table->timestamps();

            $table->foreign('olympiad_id')->references('id')->on('olympiads')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('registered_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('teacher2_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['olympiad_id', 'student_id']);
        });

        Schema::create('olympiad_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->unsignedTinyInteger('place')->nullable(); // 1, 2, 3
            $table->boolean('is_winner')->default(false);
            $table->string('document_path')->nullable();
            $table->string('document_original_name')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->enum('status', ['pending_upload', 'pending_verification', 'approved', 'rejected'])
                  ->default('pending_upload');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->foreign('registration_id')->references('id')->on('olympiad_registrations')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olympiad_results');
        Schema::dropIfExists('olympiad_registrations');
        Schema::dropIfExists('olympiads');
    }
};
