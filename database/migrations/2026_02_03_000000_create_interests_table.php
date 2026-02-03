<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catégories d'intérêts prédéfinies
        Schema::create('interest_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // emoji ou classe d'icône
            $table->string('color')->default('#3B82F6');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Intérêts de l'utilisateur (catégories sélectionnées + mots-clés perso)
        Schema::create('user_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('interest_category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('custom_keyword')->nullable(); // Pour les mots-clés personnalisés
            $table->timestamps();

            $table->unique(['user_id', 'interest_category_id']);
            $table->index(['user_id', 'custom_keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interests');
        Schema::dropIfExists('interest_categories');
    }
};