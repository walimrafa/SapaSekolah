<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('questionnaire_questions')) {
            Schema::create('questionnaire_questions', function (Blueprint $table) {
                $table->id();
                $table->string('section_title', 150);
                $table->string('question_key', 100)->unique();
                $table->text('question_text');
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('section_title');
                $table->index('order_index');
                $table->index('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};

