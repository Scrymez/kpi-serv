<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('coefficient', 3, 2)->default(1.00);
            $table->timestamps();
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('grade_number');
            $table->enum('education_level', ['primary', 'basic', 'secondary']);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('login')->unique();
            $table->string('password');
            $table->enum('role', [
                'admin',
                'director',
                'deputy_events',
                'deputy_edu',
                'deputy_science',
                'teacher',
                'student',
                'parent',
            ]);
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('avatar_path')->nullable();
            $table->boolean('must_change_password')->default(true);
            $table->boolean('is_class_teacher')->default(false);
            $table->unsignedBigInteger('class_teacher_class_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->foreign('class_teacher_class_id')->references('id')->on('school_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_years');
    }
};
