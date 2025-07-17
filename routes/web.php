<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiPostController;

// Route to display the main view with the form and list of posts.

Route::get('/', [AiPostController::class, 'index'])->name('ai-post.index');

// Route to process the natural language command from the form.

Route::post('/ai-post/process', [AiPostController::class, 'processCommand'])->name('ai.posts.process');
