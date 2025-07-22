<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-Based Article Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .heading {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin: 2rem 0;
        }

        .card-title {
            font-weight: 600;
            color: #444;
        }

        .btn-primary {
            background-color: #27ae60;
            border-color: #27ae60;
        }

        .btn-primary:hover {
            background-color: #1e8449;
            border-color: #1e8449;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #27ae60;
        }

        .example-list code {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 2px 6px;
            border-radius: 5px;
            display: inline-block;
        }

        .footer {
            margin-top: 3rem;
            padding: 2rem 1rem;
            text-align: center;
            font-size: 1.4rem;
            color: #777;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>

    <div class="container py-4">
        <h1 class="heading">AI-Based Article Generator</h1>

        <!-- Article Command Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Enter a Command</h5>
                <form action="{{ route('ai.posts.process') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea name="prompt" class="form-control" rows="4" placeholder="e.g., Write an article on how AI is transforming the EdTech industry."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Article</button>
                </form>
            </div>
            <div class="card-footer bg-light">
                <p class="mb-1"><strong>Example Commands:</strong></p>
                <ul class="list-unstyled example-list small">
                    <li><code>Generate an article on the future of AI in education.</code></li>
                    <li><code>Update the title of article 1 to 'AI in the Classroom: A New Era'</code></li>
                    <li><code>Delete article with ID 2</code></li>
                </ul>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Article Table -->
        <div class="mt-5">
            <h2 class="mb-3">Existing Articles</h2>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary" style="text-align: center;">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Body of Article</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            <tr>
                                <td class="text-center">{{ $post->id }}</td>
                                <td>{{ $post->title }}</td>
                                <td style="text-align:justify">{{ $post->body }}</td>
                                <td class="text-nowrap text-center">{{ $post->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No articles found. Try creating one!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} AI Article Generator | Built with Laravel & OpenAI
        </div>
    </div>

</body>
</html>
