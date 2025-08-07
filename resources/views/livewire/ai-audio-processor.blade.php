<div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <!-- Text to Speech Section -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Text to Speech</h2>
        <div class="mb-4">
            <textarea 
                wire:model.defer="textToSpeechInput" 
                rows="4" 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                placeholder="Enter text to convert to speech..."
            ></textarea>
        </div>
        
        <button 
            wire:click="generateSpeech" 
            wire:loading.attr="disabled" 
            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="generateSpeech">
                Generate Audio
            </span>
            <span wire:loading wire:target="generateSpeech">
                <i class="fas fa-spinner fa-spin mr-2"></i> Generating...
            </span>
        </button>

        @if($generatedAudioUrl)
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium mb-2">Generated Audio:</h3>
                <audio controls class="w-full">
                    <source src="{{ asset($generatedAudioUrl) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <div class="mt-2 text-sm text-gray-600">
                    <p>Audio URL: {{ $generatedAudioUrl }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Speech to Text Section -->
    <div class="border-t pt-8">
        <h2 class="text-xl font-semibold mb-4">Speech to Text</h2>
        <div class="mb-4">
            <input 
                type="file" 
                wire:model="uploadedAudioFile" 
                accept=".mp3,.wav,.m4a,.webm"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100"
            >
        </div>

        @if($transcriptionResult)
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium mb-2">Transcription Result:</h3>
                <div class="p-3 bg-white border rounded">
                    {{ $transcriptionResult }}
                </div>
            </div>
        @endif
    </div>

    <!-- Error Messages -->
    @if($errorMessage)
        <div class="mt-6 p-4 bg-red-50 text-red-700 rounded-lg">
            <p>{{ $errorMessage }}</p>
        </div>
    @endif

    <!-- Debug Information -->
    @env('local')
    <div class="mt-8 p-4 bg-gray-100 rounded-lg text-xs text-gray-600">
        <h4 class="font-medium mb-2">Debug Info:</h4>
        <div class="space-y-1">
            <p>Generated Audio URL: {{ $generatedAudioUrl ?? 'null' }}</p>
            <p>Processing TTS: {{ $processingTTS ? 'true' : 'false' }}</p>
            <p>Processing Whisper: {{ $processingWisper ? 'true' : 'false' }}</p>
            @if($uploadedAudioFile)
                <p>Uploaded file: {{ $uploadedAudioFile->getClientOriginalName() }}</p>
                <p>File size: {{ number_format($uploadedAudioFile->getSize() / 1024, 2) }} KB</p>
            @endif
        </div>
    </div>
    @endenv
</div>

