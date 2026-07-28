<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MailQueueService
{
    public function send(
        string $receivers,
        string $subject,
        string $html,
        string $name = 'Hệ Thống',
        string $bcc = ''
    ) {
        $time = time() . '_' . uniqid();
        $token = md5(date('Ym') . '#!!$@' . $time);

        $payload = [
            'time'      => $time,
            'token'     => $token,
            'name'      => $name,
            'subject'   => $subject,
            'body'      => $html,
            'cc'        => $bcc,
            'code'      => 'xmhp',
            'receivers' => $receivers,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://info.vttu.edu.vn/api/guest/mailer_service/add_queue.php');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Laravel-MailQueue/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error'   => $error
            ];
        }

        return [
            'success'  => $httpCode >= 200 && $httpCode < 300,
            'response' => $response,
            'httpCode' => $httpCode
        ];
    }
}

