<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private ?string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
        
        if (empty($this->apiKey)) {
            Log::warning('GEMINI_API_KEY is not set in environment variables');
        }
    }

    /**
     * Generate AI insights from text prompt
     */
    public function generateContent(string $prompt): ?string
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('Gemini API Key is empty');
                return null;
            }

            Log::info('Calling Gemini API', ['prompt_length' => strlen($prompt)]);

            $response = Http::timeout(30)->post(
                "{$this->baseUrl}/gemini-pro:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            );

            Log::info('Gemini API Response Status', ['status' => $response->status()]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Gemini API Success', ['has_candidates' => isset($data['candidates'])]);
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini API Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Analyze sales data and provide insights
     */
    public function analyzeSalesData(array $salesData): ?string
    {
        $prompt = "Analyze this sales data and provide 3-5 actionable business insights:\n\n";
        $prompt .= json_encode($salesData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide insights in bullet points covering: trends, opportunities, and recommendations.";

        return $this->generateContent($prompt);
    }

    /**
     * Predict future sales trends
     */
    public function predictSalesTrend(array $historicalData): ?string
    {
        $prompt = "Based on this historical sales data, predict the trend for the next 30 days:\n\n";
        $prompt .= json_encode($historicalData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nProvide: expected trend (up/down/stable), confidence level, and key factors.";

        return $this->generateContent($prompt);
    }

    /**
     * Suggest optimal reorder points
     */
    public function suggestReorderPoints(array $inventoryData): ?string
    {
        $prompt = "Analyze this inventory data and suggest optimal reorder points:\n\n";
        $prompt .= json_encode($inventoryData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nFor each product, provide: suggested reorder point, reasoning, and risk level.";

        return $this->generateContent($prompt);
    }

    /**
     * Generate product recommendations
     */
    public function generateProductRecommendations(array $productPerformance): ?string
    {
        $prompt = "Based on this product performance data, recommend actions:\n\n";
        $prompt .= json_encode($productPerformance, JSON_PRETTY_PRINT);
        $prompt .= "\n\nSuggest which products to: promote, discount, restock, or discontinue.";

        return $this->generateContent($prompt);
    }
}
