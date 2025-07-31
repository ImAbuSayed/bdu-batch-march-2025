<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use OpenAI;
use Exception;

class AiPostController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /**
     * Show the form and recent posts
     */
    public function index()
    {
        $posts = Post::latest()->get(); // ✅ fetch all posts
        return view('ai_post_manager', ['posts' => $posts]);
    }

    /**
     * Process the AI command (create/edit/delete)
     */
    public function processCommand(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:500']);

        $systemPrompt = <<<PROMPT
You are a helpful assistant that processes user requests for a blog post management system.
Your only output must be a single, valid JSON object. Do not include any other text, explanations, or markdown.
Based on the user's prompt, determine the action and extract the necessary data.

- For 'create', extract the blog post 'topic'.
  JSON: {"action": "create", "data": {"topic": "the blog topic"}}

- For 'edit', identify the post 'id' and the fields to change.
  JSON: {"action": "edit", "data": {"id": 1, "title": "new title", "body": "new body"}}

- For 'delete', identify the post 'id'.
  JSON: {"action": "delete", "data": {"id": 2"}}
PROMPT;

        try {
            $result = $this->client->chat()->create([
                'model' => 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->input('prompt')],
                ],
            ]);

            $responseContent = $result->choices[0]->message->content;
            $command = json_decode($responseContent, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($command['action'])) {
                throw new Exception("AI returned an invalid response format.");
            }

            $message = '';
            switch ($command['action']) {
                case 'create':
                    $message = $this->handleCreate($command['data']);
                    break;
                case 'edit':
                    $message = $this->handleUpdate($command['data']);
                    break;
                case 'delete':
                    $message = $this->handleDelete($command['data']);
                    break;
                default:
                    throw new Exception("Unknown action detected by AI.");
            }

            return back()->with('success', $message);

        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function handleCreate(array $data): string
    {
        if (empty($data['topic'])) {
            throw new Exception("AI did not provide a topic for creation.");
        }

        $topic = $data['topic'];

        $writerPrompt = <<<PROMPT
You are a skilled blog writer. Write a high-quality, engaging blog post on the following topic.
Your response must be a single, valid JSON object with two keys: 'title' and 'body'.
The 'body' should be well-structured, possibly with multiple paragraphs.
PROMPT;

        $contentResult = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $writerPrompt],
                ['role' => 'user', 'content' => "Topic: {$topic}"],
            ],
        ]);

        $blogContent = json_decode($contentResult->choices[0]->message->content, true);

        // Fallback image logic - based on keyword in topic
        $imageMap = [
            'ai' => '/images/img1.jpg',
            'laravel' => '/images/img2.jpg',
            'technology' => '/images/img3.jpg',
        ];

        $image = '/images/default.jpg'; // Default
        foreach ($imageMap as $keyword => $path) {
            if (stripos($topic, $keyword) !== false) {
                $image = $path;
                break;
            }
        }

        Post::create([
            'title' => $blogContent['title'] ?? 'AI Generated Post',
            'body' => $blogContent['body'] ?? 'Content could not be generated.',
            'image' => $image,
        ]);

        return 'Blog post on "' . $topic . '" created successfully!';
    }

    private function handleUpdate(array $data): string
    {
        if (empty($data['id'])) {
            throw new Exception("Post ID is missing for update.");
        }

        $post = Post::find($data['id']);
        if (!$post) {
            throw new Exception("Post with ID {$data['id']} not found.");
        }

        $post->update(array_filter([
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
        ]));

        return "Post #{$data['id']} updated!";
    }

    private function handleDelete(array $data): string
    {
        if (empty($data['id'])) {
            throw new Exception("Post ID is missing for delete.");
        }

        $post = Post::find($data['id']);
        if (!$post) {
            throw new Exception("Post with ID {$data['id']} not found.");
        }

        $post->delete();
        return "Post #{$data['id']} deleted!";
    }
}
