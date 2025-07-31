<?php

namespace App\Livewire;

use Livewire\Component;
use OpenAI;
use Exception;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiAudioProcessor extends Component
{
    use WithFileUploads;

    // Properties for Text-to-Speech
    public $textToSpeechInput = "";
    public $generatedAudioUrl = null;
    public $processingTTS = false;

    // Properties for Speech-to-Text (Transcription)
    public $uploadedAudioFile;
    public $transcriptionResult = null;
    public $processingWisper = false; // Note: "Whisper" is the model name

    // General error message property
    public $errorMessage = null;

    /**
     * Generate speech from the provided text input using OpenAI's TTS model.
     */
    public function generateSpeech()
    {
        $this->validate([
            'textToSpeechInput' => 'required|string|max:4096',
        ]);

        // Reset state before processing
        $this->processingTTS = true;
        $this->generatedAudioUrl = null;
        $this->errorMessage = null;

        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));

            // Call the OpenAI Audio API for text-to-speech
            $response = $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $this->textToSpeechInput,
                'voice' => 'alloy', // You can choose from 'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'
            ]);

            // Generate a unique filename and save the audio content
            $fileName = 'audio/' . Str::random(40) . '.mp3';
            Storage::disk('public')->put($fileName, $response);

            

            // Set the public URL for the generated audio file
            $this->generatedAudioUrl = Storage::url($fileName);

        } catch (Exception $e) {
            // Set an error message if the API call fails
            $this->errorMessage = 'Text-to-Speech generation failed: ' . $e->getMessage();
        } finally {
            // Ensure the processing flag is reset
            $this->processingTTS = false;
        }
    }

    /**
     * This is a Livewire lifecycle hook that runs when the 'uploadedAudioFile' property is updated.
     * It handles the file upload and initiates transcription using OpenAI's Whisper model.
     */
    public function updatedUploadedAudioFile()
    {
        $this->validate([
            'uploadedAudioFile' => 'required|file|mimes:mp3,mp4,mpeg,mpga,m4a,wav,webm|max:25600', // 25MB max for Whisper
        ]);

        // Reset state before processing
        $this->processingWisper = true;
        $this->transcriptionResult = null;
        $this->errorMessage = null;

        try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));

            // Call the OpenAI Audio API to transcribe the uploaded file
            $response = $client->audio()->transcribe([
                'model' => 'whisper-1',
                'file'  => fopen($this->uploadedAudioFile->getRealPath(), 'r'),
                'response_format' => 'text', // Or 'json', 'srt', 'verbose_json', 'vtt'
            ]);

            // Store the transcription result
            $this->transcriptionResult = $response;

        } catch (Exception $e) {
            // Set an error message if the transcription fails
            $this->errorMessage = 'Transcription failed: ' . $e->getMessage();
        } finally {
            // Ensure the processing flag is reset
            $this->processingWisper = false;
        }
    }

    /**
     * Render the component's view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.ai-audio-processor');
    }
}