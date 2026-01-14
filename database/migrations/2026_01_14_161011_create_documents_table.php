<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->longText('content')->nullable();
            $table->json('content_json')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'video_id']);
        });

        // Fulltext uniquement pour MySQL
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('documents', function (Blueprint $table) {
                $table->fullText('content');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};