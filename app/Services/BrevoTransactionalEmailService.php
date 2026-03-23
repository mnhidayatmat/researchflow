<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrevoTransactionalEmailService
{
    public function send(array $payload): void
    {
        $apiKey = config('services.brevo.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('Brevo API key is not configured.');
        }

        $response = Http::timeout(30)
            ->withOptions($this->httpOptions())
            ->withHeaders([
                'accept' => 'application/json',
                'api-key' => $apiKey,
                'content-type' => 'application/json',
            ])
            ->post(rtrim((string) config('services.brevo.base_url'), '/') . '/smtp/email', $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Brevo email request failed: ' . $response->status() . ' ' . $response->body());
        }
    }

    public function sendEmailVerification(string $recipientEmail, string $recipientName, string $verificationUrl): void
    {
        $this->send([
            'sender' => [
                'name' => config('services.brevo.sender_name'),
                'email' => config('services.brevo.sender_email'),
            ],
            'to' => [[
                'email' => $recipientEmail,
                'name' => $recipientName,
            ]],
            'subject' => 'Verify your email for ResearchFlow',
            'htmlContent' => view('emails.auth.verify-email', [
                'recipientName' => $recipientName,
                'verificationUrl' => $verificationUrl,
            ])->render(),
        ]);
    }

    protected function httpOptions(): array
    {
        $caBundle = env('AI_CA_BUNDLE');
        $verifySsl = filter_var(env('AI_SSL_VERIFY', true), FILTER_VALIDATE_BOOL);

        if (is_string($caBundle) && $caBundle !== '') {
            return ['verify' => $caBundle];
        }

        if (!$verifySsl || app()->environment('local')) {
            return ['verify' => false];
        }

        return [];
    }
}
