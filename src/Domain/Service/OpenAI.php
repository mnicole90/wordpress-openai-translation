<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use Exception;

final class OpenAI
{
    public const API_URL = "https://api.openai.com";
    public const API_VERSION = "v1";
    public const OPEN_AI_URL = self::API_URL . '/' . self::API_VERSION;
    private string $model = "text-davinci-002";
    private string $chatModel = "gpt-3.5-turbo";
    private array $contentTypes;
    private array $headers;
    private int $timeout = 0;
    private object $stream_method;
    private string $customUrl = "";
    private string $proxy = "";
    private array $curlInfo = [];

    public function __construct(
        private readonly string $apiKey
    ) {
        $this->contentTypes = [
            "application/json" => "Content-Type: application/json",
            "multipart/form-data" => "Content-Type: multipart/form-data",
        ];

        $this->headers = [
            $this->contentTypes["application/json"],
            sprintf("Authorization: Bearer %s", $this->apiKey),
        ];
    }

    public function getCURLInfo(): array
    {
        return $this->curlInfo;
    }

    /**
     * @throws Exception
     */
    public function completion($opts, $stream = null): bool|string
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (!$opts['stream']) {
                throw new Exception(
                    'Please provide a stream function.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->model;
        $url = self::completionsURL();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**
     * @throws Exception
     */
    public function chat($opts, $stream = null): bool|string
    {
        if ($stream != null && array_key_exists('stream', $opts)) {
            if (!$opts['stream']) {
                throw new Exception(
                    'Please provide a stream function.'
                );
            }

            $this->stream_method = $stream;
        }

        $opts['model'] = $opts['model'] ?? $this->chatModel;
        $url = self::chatUrl();
        $this->baseUrl($url);

        return $this->sendRequest($url, 'POST', $opts);
    }

    /**************/
    /*   UTILS    */
    /**************/

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setProxy(string $proxy): void
    {
        if ($proxy && !str_contains($proxy, '://')) {
            $proxy = 'https://' . $proxy;
        }
        $this->proxy = $proxy;
    }

    public function setBaseURL(string $customUrl): void
    {
        if (!empty($customUrl)) {
            $this->customUrl = $customUrl;
        }
    }

    public function setHeader(array $header): void
    {
        if ($header) {
            foreach ($header as $key => $value) {
                $this->headers[$key] = $value;
            }
        }
    }

    public function setORG(string $org): void
    {
        if (!empty($org)) {
            $this->headers[] = "OpenAI-Organization: $org";
        }
    }

    private function sendRequest(string $url, string $method, array $opts = []): bool|string
    {
        $post_fields = json_encode($opts);

        if (array_key_exists('file', $opts) || array_key_exists('image', $opts)) {
            $this->headers[0] = $this->contentTypes["multipart/form-data"];
            $post_fields = $opts;
        } else {
            $this->headers[0] = $this->contentTypes["application/json"];
        }

        $this->headers = apply_filters('openai_translation_http_request_headers', $this->headers, $url);
        $post_fields = apply_filters('openai_translation_http_request_fields', $post_fields, $url, $this->headers);

        $curl_info = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $post_fields,
            CURLOPT_HTTPHEADER => $this->headers,
        ];

        if ($opts == []) {
            unset($curl_info[CURLOPT_POSTFIELDS]);
        }

        if (!empty($this->proxy)) {
            $curl_info[CURLOPT_PROXY] = $this->proxy;
        }

        if (array_key_exists('stream', $opts) && $opts['stream']) {
            $curl_info[CURLOPT_WRITEFUNCTION] = $this->stream_method;
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curl_info);
        $response = curl_exec($curl);

        $info = curl_getinfo($curl);
        $this->curlInfo = $info;

        curl_close($curl);

        do_action('openai_translation_http_response', $response, $url, $this->headers, $post_fields);

        return $response;
    }

    private function baseUrl(string &$url): void
    {
        if (!empty($this->customUrl)) {
            $url = str_replace(self::API_URL, $this->customUrl, $url);
        }
    }

    /**************/
    /*    URLS    */
    /**************/

    public static function completionsURL(): string
    {
        return self::OPEN_AI_URL . "/completions";
    }

    public static function chatUrl(): string
    {
        return self::OPEN_AI_URL . "/chat/completions";
    }
}
