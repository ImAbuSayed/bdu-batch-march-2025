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
            // Validate OpenAI API key
            $apiKey = env('OPENAI_API_KEY');
            if (empty($apiKey)) {
                throw new Exception('OpenAI API key is not configured.');
            }

            $client = OpenAI::client($apiKey);

            // Call the OpenAI Audio API for text-to-speech
            $response = $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $this->textToSpeechInput,
                'voice' => 'alloy',
                'response_format' => 'mp3',
            ]);

            // Create audio directory if it doesn't exist
            $directory = 'audio';
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Generate a unique filename
            $fileName = $directory . '/' . Str::random(40) . '.mp3';
            $filePath = Storage::disk('public')->path($fileName);
            
            // Handle the response based on its type
            if (is_string($response)) {
                // If it's already a string, use it directly
                $audioContent = $response;
            } elseif (is_object($response) && method_exists($response, 'getContents')) {
                // If it's a stream, get its contents
                $audioContent = $response->getContents();
            } else {
                // Try to cast to string as a fallback
                $audioContent = (string) $response;
            }
            
            // Verify we have audio content
            if (empty($audioContent)) {
                throw new Exception('Received empty audio content from API');
            }
            
            // Save the audio content to file
            $bytesWritten = file_put_contents($filePath, $audioContent);
            
            if ($bytesWritten === false) {
                throw new Exception('Failed to save audio file to storage');
            }

            // Verify the file was written
            if (!Storage::disk('public')->exists($fileName)) {
                throw new Exception('Audio file was not saved correctly');
            }

            // Set the public URL for the generated audio file
            $this->generatedAudioUrl = Storage::url($fileName);
            
            // Log successful generation
            \Log::info('Audio generated successfully', [
                'file' => $fileName,
                'size' => Storage::disk('public')->size($fileName),
                'url' => $this->generatedAudioUrl
            ]);

        } catch (\Exception $e) {
            // Log the full error for debugging
            \Log::error('Text-to-Speech generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Set a user-friendly error message
            $this->errorMessage = 'Failed to generate audio. ' . $e->getMessage();
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                $this->errorMessage = 'Request timed out. Please try again.';
            } elseif (str_contains($e->getMessage(), '401')) {
                $this->errorMessage = 'Authentication failed. Please check your OpenAI API key.';
            }
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
        try {
            $this->validate([
                'uploadedAudioFile' => 'required|file|mimes:mp3,mp4,mpeg,mpga,m4a,wav,webm|max:25600', // 25MB max for Whisper
            ]);

            // Reset state before processing
            $this->processingWisper = true;
            $this->transcriptionResult = null;
            $this->errorMessage = null;

            // Validate OpenAI API key
            $apiKey = env('OPENAI_API_KEY');
            if (empty($apiKey)) {
                throw new Exception('OpenAI API key is not configured.');
            }

            $client = OpenAI::client($apiKey);

            // Validate the uploaded file
            if (!$this->uploadedAudioFile->isValid()) {
                throw new Exception('Invalid file upload: ' . $this->uploadedAudioFile->getErrorMessage());
            }

            // Log file upload
            \Log::info('Processing audio file for transcription', [
                'original_name' => $this->uploadedAudioFile->getClientOriginalName(),
                'size' => $this->uploadedAudioFile->getSize(),
                'mime' => $this->uploadedAudioFile->getMimeType(),
                'path' => $this->uploadedAudioFile->getRealPath()
            ]);

            // Call the OpenAI Audio API to transcribe the uploaded file
            $response = $client->audio()->transcribe([
                'model' => 'whisper-1',
                'file'  => fopen($this->uploadedAudioFile->getRealPath(), 'r'),
                'response_format' => 'text',
                'language' => 'en', // Optional: specify language for better accuracy
            ]);

            // Store the transcription result
            $this->transcriptionResult = $response->text;
            
            // Log successful transcription
            \Log::info('Audio transcription successful', [
                'file' => $this->uploadedAudioFile->getClientOriginalName(),
                'result_length' => strlen($this->transcriptionResult)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $this->errorMessage = 'Validation error: ' . $e->getMessage();
            \Log::error('Audio upload validation failed', [
                'errors' => $e->errors(),
                'file' => $this->uploadedAudioFile ? $this->uploadedAudioFile->getClientOriginalName() : 'no file'
            ]);
        } catch (\Exception $e) {
            // Log the full error for debugging
            \Log::error('Audio transcription failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $this->uploadedAudioFile ? $this->uploadedAudioFile->getClientOriginalName() : 'no file'
            ]);
            
            // Set a user-friendly error message
            $this->errorMessage = 'Failed to transcribe audio. ';
            
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                $this->errorMessage .= 'The request timed out. Please try again with a shorter audio file.';
            } elseif (str_contains($e->getMessage(), '401')) {
                $this->errorMessage .= 'Authentication failed. Please check your OpenAI API key.';
            } elseif (str_contains($e->getMessage(), 'file format')) {
                $this->errorMessage .= 'Unsupported file format. Please upload a valid audio file (MP3, WAV, M4A, or WebM).';
            } else {
                $this->errorMessage .= $e->getMessage();
            }
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