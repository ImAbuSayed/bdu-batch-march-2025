<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use Exception;
class ImageGeneratorController extends Controller
{
    //
  public function index()
    {
    
        return view('image-generator');

    }

    public function generate(Request $request)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);

       try {
            $client = OpenAI::client(env('OPENAI_API_KEY'));
            $result = $client->images()->create([
                'model' => 'dall-e-3',
                'prompt' => $request->input('prompt'),
                'n' => 1,
                'size' => '1024x1024',
            ]);

            $imageUrl = $result->data[0]->url;

            return view('image-generator', ['imageUrl' => $imageUrl, 'prompt' => $request->input('prompt')]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Image generation failed: ' . $e->getMessage()], 500);
        }



    }



}
