<?php

declare(strict_types=1);

namespace Services\Notifications;

final class CurlWebhookTransport implements WebhookTransport
{
    public function postJson(string $url, array $payload, ?string $caBundle = null): HttpResponse
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $curl = curl_init($url);

        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if ($caBundle !== null) {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        return new HttpResponse(
            $status,
            is_string($response) ? $response : null,
            $error !== '' ? $error : null,
        );
    }
}
