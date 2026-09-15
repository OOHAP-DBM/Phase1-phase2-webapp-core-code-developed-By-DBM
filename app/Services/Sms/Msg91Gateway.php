<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class Msg91Gateway implements SmsGatewayInterface
{
    public function send($mobile, $message)
    {
        $authKey = Setting::get('sms_msg91_auth_key');
        $sender  = Setting::get('sms_msg91_sender_id');
        $route   = Setting::get('sms_msg91_route', 4);
        $country = Setting::get('sms_msg91_country', 91);
        $apiUrl  = Setting::get('sms_msg91_base_url', 'https://api.msg91.com/api/v2/sendsms');
        $templateId = Setting::get('sms_msg91_template_id', '');

        $payload = [
            'sender' => $sender,
            'route' => $route,
            'country' => $country,
            'sms' => [
                [
                    'message' => $message,
                    'to' => [$mobile]
                ]
            ]
        ];

        // If a template id is configured, include it (some MSG91 endpoints use template/flow ids)
        if (!empty($templateId)) {
            $payload['template_id'] = $templateId;
        }

        $response = Http::withHeaders([
            'authkey' => $authKey,
            'content-type' => 'application/json',
        ])->post($apiUrl, $payload);

        return $response->json();
    }
}
