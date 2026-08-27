<?php


declare(strict_types=1);

namespace MyAILib\Http;

interface HttpClientInterface
{
    public function post(
        string $url,
        array  $headers = [],
        array  $data = []
    ): HttpResponse;
}
