<div>
    <textarea wire:model.defer="textToSpeechInput" rows="4" class="w-full p-2 border rounded" placeholder="Enter your audio generation prompt..."></textarea>
    <button wire:click="generateSpeech" wire:loading.attr="disabled" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
      <span wire:loading.remove wire:target="generateSpeech">Generate Audio</span>

        <span wire:loading wire:target="generateSpeech">Generating.....</span>
    </button>

</div>


