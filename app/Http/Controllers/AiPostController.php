<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use OpenAI;
use Exception;

class AiPostController extends Controller
{
    /**
     * @var \OpenAI\Client The OpenAI client instance.
     */
    protected $client;

    /**
     * Constructor to initialize the OpenAI client.
     */
    public function __construct()
    {
        // Initialize the OpenAI client with the API key from the .env file.
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /**
     * Display the main view with all posts.
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return view('ai-posts', ['posts' => $posts]);
    }

    /**
     * Process the natural language command from the user.
     */
    public function processCommand(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:500']);

        // This prompt now instructs the AI to extract a 'topic' for the 'create' action.
        $systemPrompt = "You are a helpful assistant that processes user requests for a blog post management system. Your only output must be a single, valid JSON object. Do not include any other text, explanations, or markdown. Based on the user's prompt, determine the action and extract the necessary data.
        - For 'create', extract the blog post 'topic'. The JSON format is: {\"action\": \"create\", \"data\": {\"topic\": \"the blog topic\"}}.
        - For 'edit', identify the post 'id' and the fields to change. The JSON format is: {\"action\": \"edit\", \"data\": {\"id\": 1, \"title\": \"new title\", \"body\": \"new body\"}}.
        - For 'delete', identify the post 'id'. The JSON format is: {\"action\": \"delete\", \"data\": {\"id\": 2}}.
        ";

        try {
            // 1. Generate AI Response (Command Parsing)
            $result = $this->client->chat()->create([
                'model' => 'gpt-4o',
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $request->input('prompt')],
                ],
            ]);

            // 2. Parse the JSON response
            $responseContent = $result->choices[0]->message->content;
            $command = json_decode($responseContent, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($command['action'])) {
                throw new Exception("AI returned an invalid response format.");
            }

            // 3. Execute the CRUD operation based on the parsed action
            $message = '';
            switch ($command['action']) {
                case 'create':
                    // The handleCreate method now performs the second AI call for content generation.
                    $message = $this->handleCreate($command['data']);
                    break;
                case 'edit':
                    $message = $this->handleUpdate($command['data']);
                    break;
                case 'delete':
                    $message = $this->handleDelete($command['data']);
                    break;
                default:
                    return back()->with('error', 'AI detected an unknown action.');
            }

            return back()->with('success', $message);

        } catch (Exception $e) {
            return back()->with('error', 'Error processing command: ' . $e->getMessage());
        }
    }

    /**
     * Handles the creation of a new post by first generating content from a topic.
     * @param array $data Must contain a 'topic' key.
     * @return string Success message.
     */
    private function handleCreate(array $data): string
    {
        if (empty($data['topic'])) {
            throw new Exception("AI did not provide a topic for creation.");
        }

        $topic = $data['topic'];

        // This prompt asks the AI to act as a blog writer.
        $writerPrompt = "You are an AI-based article generator. Generate a high-quality, informative article on the following topic. Your response must be a single, valid JSON object with two keys: 'title' and 'body'. The 'body' should be clear, well-organized, and may contain multiple paragraphs for readability.";

        // Make the second API call to generate the actual blog content.
        $contentResult = $this->client->chat()->create([
            'model' => 'gpt-4o', // You can use a powerful model for better content
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $writerPrompt],
                ['role' => 'user', 'content' => "Topic: {$topic}"],
            ],
        ]);

        $blogContent = json_decode($contentResult->choices[0]->message->content, true);

        // Create the post in the database with the AI-generated content.
        Post::create([
            'title' => $blogContent['title'] ?? 'AI Generated Post',
            'body' => $blogContent['body'] ?? 'Content could not be generated.',
        ]);

        return 'Blog post on "' . $topic . '" created successfully!';
    }

    /**
     * Handles updating an existing post. (No changes needed here)
     */
    private function handleUpdate(array $data): string
    {
        if (empty($data['id'])) {
             throw new Exception("Post ID is missing for update action.");
        }
        $post = Post::find($data['id']);
        if (!$post) {
            throw new Exception("Post with ID {$data['id']} not found.");
        }
        $post->update(array_filter([
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
        ]));
        return "Post #{$data['id']} successfully updated!";
    }

    /**
     * Handles deleting a post. (No changes needed here)
     */
    private function handleDelete(array $data): string
    {
        if (empty($data['id'])) {
             throw new Exception("Post ID is missing for delete action.");
        }
        $post = Post::find($data['id']);
        if (!$post) {
            throw new Exception("Post with ID {$data['id']} not found.");
        }
        $post->delete();
        return "Post #{$data['id']} successfully deleted!";
    }
}