<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('app_notifications')) {
            Schema::create('app_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('type', 60)->default('system');
                $table->string('title');
                $table->text('message');
                $table->string('action_url', 500)->nullable();
                $table->string('icon', 50)->default('bell');
                $table->string('color', 30)->default('brand');
                $table->boolean('is_read')->default(false)->index();
                $table->timestamp('read_at')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_read']);
                $table->index(['campus_id', 'is_read']);
                $table->index(['role_id', 'is_read']);
                $table->index(['created_at', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};

