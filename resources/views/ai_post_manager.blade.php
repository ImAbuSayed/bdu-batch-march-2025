<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Emon AI Chat - Post Manager</title>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; }
    h1, .logo-font { font-family: 'Pacifico', cursive; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-thumb { background-color: #3b82f6; border-radius: 3px; }
    ::-webkit-scrollbar-track { background: transparent; }
</style>
</head>

<body class="bg-white text-gray-800 min-h-screen relative overflow-x-hidden">

<div class="container mx-auto p-4 space-y-8">

    <!-- Header -->
    <h1 class="logo-font text-4xl sm:text-5xl text-blue-600 text-center drop-shadow">
        🤖 Emon AI Chat
    </h1>

    <!-- Session feedback -->
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded shadow max-w-xl mx-auto text-center">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded shadow max-w-xl mx-auto text-center">
            {{ session('error') }}
        </div>
    @endif

    <!-- Command Form -->
    <form method="POST" action="{{ route('ai.posts.process') }}" 
          class="bg-white p-6 rounded-2xl shadow-xl max-w-xl mx-auto space-y-4">
        @csrf
        <label for="prompt" class="block text-lg font-medium text-gray-700">
            Enter a command to manage posts
        </label>
        <textarea name="prompt" id="prompt" rows="3" required 
            placeholder="e.g. Create a blog post about AI in healthcare"
            class="w-full p-3 rounded-md border border-gray-300 focus:ring focus:ring-blue-400"></textarea>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl shadow-md">
            Submit Command
        </button>

        <div class="text-sm text-gray-600">
            <p><strong>Example Commands:</strong></p>
            <ul class="list-disc pl-5">
                <li>Create a blog post about Laravel with the body 'Laravel is a PHP framework.'</li>
                <li>Change the title of post 1 to 'Updated Title'</li>
                <li>Delete post with id 2</li>
            </ul>
        </div>
    </form>

    <!-- Existing Posts -->
    @if($posts->count())
    <div class="bg-white p-6 rounded-xl shadow-xl max-w-6xl mx-auto space-y-4">
        <h2 class="text-2xl font-semibold text-blue-600 mb-2">
            Existing Posts ({{ $posts->count() }})
        </h2>

        <div class="h-[70vh] overflow-y-auto space-y-4 pr-2">
            @foreach($posts as $post)
            <div class="relative flex flex-col sm:flex-row gap-4 bg-gray-50 rounded-lg shadow p-4">
                <!-- Post ID -->
                <div class="absolute top-2 right-2 text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded shadow">
                    ID: {{ $post->id }}
                </div>

                <!-- Post Image -->
                <div class="w-full sm:w-48 h-48">
                    @if ($post->image)
                        <img src="{{ $post->image }}" alt="Post Image"
                             class="w-full h-full object-cover rounded">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center rounded text-gray-400">
                            No Image
                        </div>
                    @endif
                </div>

                <!-- Post Content -->
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-800 truncate">{{ $post->title }}</h3>
                    <p class="text-gray-700 mt-2">
                        {{ \Illuminate\Support\Str::limit(strip_tags($post->body), 300) }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                        Posted {{ $post->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="text-center text-gray-500">
        No posts available yet.
    </div>
    @endif

</div>

</body>
</html>
