<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // L'auteur du commentaire
        $table->unsignedBigInteger('article_id'); // L'article lié
        $table->unsignedBigInteger('parent_id')->nullable(); // Commentaire parent pour les réponses
        $table->text('content'); // Le contenu du commentaire
        $table->integer('likes')->default(0); // Nombre de likes
        $table->timestamps();

        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
