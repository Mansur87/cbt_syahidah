<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->longText('prompt');
            $table->json('options');
            $table->string('correct_option', 1);
            $table->unsignedSmallInteger('points')->default(1);
            $table->timestamps();
            $table->unique(['exam_id', 'position']);
        });

        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->timestamp('started_at');
            $table->timestamp('deadline_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status', 20)->default('in_progress')->index();
            $table->timestamps();
            $table->unique(['exam_id', 'user_id', 'attempt_number']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('attempts');
        Schema::dropIfExists('questions');
    }
};
