<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Payments;
use App\Jobs\GenerateReceiptJob;

class TestController extends Controller
{
     protected string $baseUrl;
    protected string $apiKey;
    protected string $userId;
    protected string $subscriptionKey;
    protected string $environment;

    public function __construct()
    {
        $this->baseUrl = config('services.momo.base_url');
        $this->apiKey = config('services.momo.api_key');
        $this->userId = config('services.momo.user_id');
        $this->subscriptionKey = config('services.momo.subscription_key');
        $this->environment = config('services.momo.env');

    }
    public function getAccessToken(): string
    {
        /** @var string|null $token */
        $token = cache()->remember('momo_token', 3500, function () {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->withBasicAuth($this->userId, $this->apiKey)
                ->post($this->baseUrl . '/collection/token/');

            if ($response->failed()) {
                Log::error('MoMo Token Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('Failed to retrieve MoMo access token: ' . $response->body());
            }

            $accessToken = $response->json('access_token');

            if (!is_string($accessToken) || $accessToken === '') {
                throw new \RuntimeException('Access token missing in MoMo response');
            }

            return $accessToken;
        });

        if (!is_string($token) || $token === '') {
            throw new \RuntimeException('Cached access token is invalid');
        }

        return $token;
        Log::info('MoMo Access Token Retrieved' . $token);
    }

     public function requestPayment(Payments $payment): bool
    {
        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'X-Target-Environment' => $this->environment,
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
                'X-Reference-Id' => $payment->internal_reference,
            ])->post($this->baseUrl . '/collection/v1_0/requesttopay', [
                'amount' => '20000',
                'currency' => 'EUR',
                'externalId' => 'String',
                'payer' => [
                    'partyIdType' => 'MSISDN',
                    'partyId' => '0760693131',
                ],
                'payerMessage' => 'Description of the payment',
                'payeeNote' => 'System Generated Payment',
            ]);

            if ($response->failed()) {
                Log::error('MoMo Initiation Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            // MoMo returns 202 Accepted for request-to-pay; require 202 specifically
            if ($response->status() === 202) {
              /*   $externalId = $response->header('X-Reference-Id') ?? $response->json('id') ?? $payment->internal_reference; */

                Log::info('MoMo Payment Initiated', [
                    'payment_id' => "Payment ID Test",
                    /* 'external_id' => "$externalId", */
                ]);
               /*  $payment->update([
                    
                    'status' => 'PROCESSING',
                ]);
 */
                return true;
            }

            Log::error('MoMo Initiation Unexpected Response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('MoMo Service Error: ' . $e->getMessage());
            return false;
        }
    }
}
