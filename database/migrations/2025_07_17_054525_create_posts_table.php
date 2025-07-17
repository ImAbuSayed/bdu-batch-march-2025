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
        // Creates the 'posts' table with title and body fields.
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('title'); // For the post title
            $table->text('body'); // For the post content
            $table->timestamps(); // created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};