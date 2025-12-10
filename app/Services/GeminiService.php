<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private ?string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    private string $model = 'gemini-2.5-flash'; // Latest stable free model

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
        
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
                "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}",
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
                
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if (!$text) {
                    Log::error('Gemini returned empty text', ['data' => $data]);
                    return '⚠️ AI returned an empty response. Please try again.';
                }
                
                return $text;
            }

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            // Return the actual error message for debugging
            $errorData = $response->json();
            return '❌ Gemini API Error: ' . ($errorData['error']['message'] ?? 'Unknown error');

        } catch (\Exception $e) {
            Log::error('Gemini API Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '❌ Exception: ' . $e->getMessage();
        }
    }

    /**
     * Analyze sales data and provide insights
     */
    public function analyzeSalesData(array $salesData): ?string
    {
        $prompt = "Analyze this sales data and provide 3-5 actionable business insights:\n\n";
        $prompt .= json_encode($salesData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nFormat your response as:\n";
        $prompt .= "1. Use **bold** for key terms\n";
        $prompt .= "2. Number each insight (1., 2., 3., etc.)\n";
        $prompt .= "3. Include: **Trend**, **Opportunity**, and **Recommendation** for each insight\n";
        $prompt .= "4. Keep each insight concise and actionable\n";
        $prompt .= "5. Use clear paragraph breaks between insights";

        return $this->generateContent($prompt);
    }

    /**
     * Predict future sales trends
     */
    public function predictSalesTrend(array $historicalData): ?string
    {
        $prompt = "Based on this historical sales data, predict the trend for the next 30 days:\n\n";
        $prompt .= json_encode($historicalData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nFormat your response as:\n";
        $prompt .= "### Trend Prediction\n";
        $prompt .= "- **Expected Trend**: (up/down/stable)\n";
        $prompt .= "- **Confidence Level**: (percentage)\n";
        $prompt .= "- **Key Factors**: List the main factors influencing this prediction\n";
        $prompt .= "\nUse bullet points and bold for key terms.";

        return $this->generateContent($prompt);
    }

    /**
     * Suggest optimal reorder points
     */
    public function suggestReorderPoints(array $inventoryData): ?string
    {
        $prompt = "Analyze this inventory data and suggest optimal reorder points:\n\n";
        $prompt .= json_encode($inventoryData, JSON_PRETTY_PRINT);
        $prompt .= "\n\nFormat each product recommendation as:\n";
        $prompt .= "### Product Name\n";
        $prompt .= "- **Suggested Reorder Point**: (quantity)\n";
        $prompt .= "- **Reasoning**: Explanation\n";
        $prompt .= "- **Risk Level**: (Low/Medium/High)\n";
        $prompt .= "\nUse clear sections and bold for key information.";

        return $this->generateContent($prompt);
    }

    /**
     * Generate product recommendations
     */
    public function generateProductRecommendations(array $productPerformance): ?string
    {
        $prompt = "Based on this product performance data, recommend actions:\n\n";
        $prompt .= json_encode($productPerformance, JSON_PRETTY_PRINT);
        $prompt .= "\n\nOrganize recommendations by action category:\n";
        $prompt .= "### 🔥 Products to Promote\n";
        $prompt .= "### 💰 Products to Discount\n";
        $prompt .= "### 📦 Products to Restock\n";
        $prompt .= "### ⚠️ Products to Discontinue\n";
        $prompt .= "\nFor each product, explain the reasoning using bullet points and bold key terms.";

        return $this->generateContent($prompt);
    }
}
