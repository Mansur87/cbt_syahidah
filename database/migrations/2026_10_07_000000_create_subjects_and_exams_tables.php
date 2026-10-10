<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->unsignedTinyInteger('grade_level')->index();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('title', 160);
            $table->text('instructions')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedTinyInteger('passing_score')->default(75);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_score')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
        Schema::dropIfExists('subjects');
    }
};
