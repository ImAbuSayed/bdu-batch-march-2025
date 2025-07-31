<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4">

    <div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
        <form method="POST" action="{{ route('image.generate') }}">
            @csrf

            <label for="prompt" class="block mb-2 font-semibold">Enter your image prompt:</label>
            <textarea name="prompt" id="prompt" rows="4" class="w-full p-2 border rounded" required>{{ old('prompt') }}</textarea>

            <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Generate Image
            </button>

            


        </form>

        @if ($errors->any())
            <div class="text-red-500 mt-4">
                @foreach ($errors->all() as $error)
                    <p>⚠️ {{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (isset($imageUrl))
            <div class="mt-6">
                <h2 class="text-lg font-semibold mb-2">Generated Image:</h2>
                <img src="{{ $imageUrl }}" alt="Generated Image" class="max-w-full rounded shadow">
            </div>
        @endif
    </div>

</body>
</html>
