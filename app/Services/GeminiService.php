<?php

namespace App\Services;

use GuzzleHttp\Client;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function evaluateAnswer($question, $studentAnswer)
    {
        $prompt = "Question: $question\nStudent Answer: $studentAnswer\n\nTask: 
        1. Correct any grammatical mistakes.
        2. Provide improvement suggestions.
        3. Evaluate and rate the answer as one of the following: BelowSatisfaction, Satisfactory, Good, Better, Excellent.";

        try {
            $response = $this->client->post($this->apiUrl, [
                'query' => ['key' => $this->apiKey],
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ]
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            // Extract AI response
            $geminiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';

            return $this->parseGeminiResponse($geminiResponse);

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    private function parseGeminiResponse($responseText)
    {
        // Try extracting based on structured bullet points or format
        $pattern = '/Corrected Answer:\s*(.*?)\nSuggestions:\s*(.*?)\nMarking:\s*(.*)/s';
        preg_match($pattern, $responseText, $matches);

        if (count($matches) >= 4) {
            return [
                'corrected_answer' => trim($matches[1]),
                'suggestions' => trim($matches[2]),
                'marking' => trim($matches[3])
            ];
        }

        // If the pattern doesn't match, return raw response
        return [
            'corrected_answer' => $responseText,
            'suggestions' => 'AI response format may have changed. Please verify.',
            'marking' => 'Not graded'
        ];
    }
}
