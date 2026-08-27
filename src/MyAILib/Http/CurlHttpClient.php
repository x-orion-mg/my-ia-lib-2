<?php


declare(strict_types=1);

namespace MyAILib\Http;

use RuntimeException;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private readonly int $timeout = 120
    )
    {
    }

    public function post(
        string $url,
        array  $headers = [],
        array  $data = []
    ): HttpResponse
    {
        $ch = curl_init($url);

        if ($ch === false) {
            throw new RuntimeException(
                'Unable to initialize cURL.'
            );
        }

        $httpHeaders = [];

        foreach ($headers as $name => $value) {
            $httpHeaders[] = $name . ': ' . $value;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_POSTFIELDS => json_encode(
                $data,
                JSON_THROW_ON_ERROR
            ),
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);

            curl_close($ch);

            throw new RuntimeException(
                'HTTP request failed: ' . $error
            );
        }

        $statusCode = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        return new HttpResponse(
            statusCode: $statusCode,
            body: $body
        );
    }
}
