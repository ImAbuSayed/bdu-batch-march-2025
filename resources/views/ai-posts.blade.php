<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-Powered CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">🤖 AI-Powered Post Manager</h1>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Enter a Command</h5>
                <form action="{{ route('ai.posts.process') }}" method="POST">
                    @csrf <div class="mb-3">
                        <textarea name="prompt" class="form-control" rows="3" placeholder="e.g., Create a post titled 'My AI Journey' with the content 'It was a great experience!'"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Process Command</button>
                </form>
            </div>
             <div class="card-footer">
                <p class="mb-1"><strong>Example Commands:</strong></p>
                <ul class="list-unstyled mb-0 small">
                    <li><small><code>Create a post about Laravel with the body 'Laravel is a PHP framework.'</code></small></li>
                    <li><small><code>Change the title of post 1 to 'Updated Title'</code></small></li>
                    <li><small><code>Delete post with id 2</code></small></li>
                </ul>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <h2 class="mt-5">Existing Posts</h2>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Body</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>{{ $post->id }}</td>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->body }}</td>
                        <td>{{ $post->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No posts found. Try creating one!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>