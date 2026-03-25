<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AfricasTalkingService
{
    private $sms;
    private $username;
    private $apiKey;
    private $realSend;

    public function __construct()
    {
        $this->username = config('services.africastalking.username');
        $this->apiKey = config('services.africastalking.api_key');
        $this->realSend = config('services.africastalking.real_send', false);
        
        Log::info("AT Service [v2] initialized. RealSend: " . ($this->realSend ? 'YES' : 'NO') . ", Username: {$this->username}");

        $at = new AfricasTalking($this->username, $this->apiKey);
        $this->sms = $at->sms();
    }

    /**
     * Internal method to send SMS with SSL bypass for sandbox/dev
     */
    private function executeSms(array $data): bool
    {
        if (!$this->realSend) {
            Log::info("[AFRICA_TALKING_MOCK] Simulacre d'envoi SMS : " . json_encode($data));
            return true;
        }

        try {
            if ($this->username === 'sandbox') {
                Log::info("[V2-BYPASS] AT Sandbox: Using manual Guzzle call (HTTPS) with SSL bypass and IPv4");
                $httpClient = new Client([
                    'base_uri' => 'https://api.sandbox.africastalking.com/version1/',
                    'verify' => false, 
                    'timeout' => 20,
                    'curl' => [
                        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, // Force TLS 1.2
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,       // Force IPv4
                    ],
                    'headers' => [
                        'apikey' => $this->apiKey,
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json'
                    ]
                ]);

                // Prepare data for manual POST
                $postData = [
                    'username' => 'sandbox',
                    'to' => $data['to'],
                    'message' => $data['message'],
                ];
                if (isset($data['from'])) $postData['from'] = $data['from'];

                Log::info("[V2-BYPASS] Sending POST to AT Sandbox: " . json_encode($postData));

                $responseBody = $httpClient->post('messaging', [
                    'form_params' => $postData
                ])->getBody()->getContents();

                $response = json_decode($responseBody);
                Log::info("[V2-BYPASS] AT Sandbox Response RAW: " . $responseBody);

                if (isset($response->SMSMessageData)) {
                    foreach ($response->SMSMessageData->Recipients as $recipient) {
                        if (in_array(strtolower($recipient->status), ['success', 'sent'])) {
                            return true;
                        }
                    }
                }
                return false;
            }

            // Normal production flow using SDK
            $response = $this->sms->send($data);
            Log::info("AT Response: " . print_r($response, true));

            if (isset($response['data']) && isset($response['data']->SMSMessageData)) {
                $recipients = $response['data']->SMSMessageData->Recipients;
                foreach ($recipients as $recipient) {
                    if (in_array(strtolower($recipient->status), ['success', 'sent'])) {
                        return true;
                    }
                }
            }

            return (isset($response['status']) && strtolower($response['status']) === 'success');

        } catch (\Exception $e) {
            Log::error("AT Execution Error: " . $e->getMessage());
            return false;
        }
    }

    public function sendOtp(string $phone, string $code): bool
    {
        Log::info("AT Service: Starting sendOtp for {$phone}");
        $message = "IvoirePay : Votre code OTP est {$code}. Valide 5 minutes.";
        
        $data = [
            'to' => $phone,
            'message' => $message,
        ];

        if ($this->username !== 'sandbox') {
            $data['from'] = 'IvoirePay';
        }

        return $this->executeSms($data);
    }

    public function sendKycResult(string $phone, string $status, ?string $reason = null): bool
    {
        $msg = $status === 'approved'
            ? 'Felicitations ! Votre compte IvoirePay est approuve.'
            : "Votre dossier KYC a ete rejete. Motif : {$reason}";
            
        return $this->executeSms([
            'to'      => $phone, 
            'message' => $msg
        ]);
    }

    public function sendMessage(string $phone, string $message): bool
    {
        return $this->executeSms([
            'to'      => $phone,
            'message' => $message,
        ]);
    }
}
