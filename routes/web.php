<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiPostController;
use App\Http\Controllers\ImageGeneratorController;

use App\Livewire\AiAudioProcessor;


// Route to display the main view with the form and list of posts.

Route::get('/', [AiPostController::class, 'index'])->name('ai-post.index');

// Route to process the natural language command from the form.

Route::post('/ai-post/process', [AiPostController::class, 'processCommand'])->name('ai.posts.process');



Route::get('/image-generator', [ImageGeneratorController::class, 'index'])->name('image.index');
Route::post('/image-generator', [ImageGeneratorController::class, 'generate'])->name('image.generate');


Route::get('/audio-processor', AiAudioProcessor::class)->name('audio.processor'); 
