<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class OpenAITranslator implements TranslatorInterface
{
    private OpenAI $openIA;

    public function __construct(private readonly string $apiKey)
    {
        $this->openIA = new OpenAI($this->apiKey);
    }

    public function translate(string $text, string $targetLanguage): string
    {
        $system = sprintf('You are a professional translator. You will have to answer just by giving only one translation in %s.', $targetLanguage);

        $response = $this->openIA->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ],
            'temperature' => 1.5,
            'max_tokens' => 1000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        if (!$response) {
            throw new RuntimeException('OpenAI API error');
        }

        $decodedResponse = json_decode($response);

        return $decodedResponse->choices[0]->message->content;
    }
}
