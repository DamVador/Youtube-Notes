<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->integer('timestamp')->nullable(); // en secondes, pour lier à un moment de la vidéo
            $table->timestamps();

            $table->index(['user_id', 'video_id']);
            $table->fullText('content'); // pour la recherche full-text
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};