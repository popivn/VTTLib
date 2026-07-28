<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailLibraryService
{
    /**
     * Send email using mailthuvien@vttu.edu.vn via SMTP mail_thu_vien mailer.
     */
    public function send(
        string $receivers,
        string $subject,
        string $html,
        string $name = 'Thư viện Đại học Võ Trường Toản',
        string $bcc = ''
    ) {
        try {
            $toEmails = array_map('trim', explode(',', $receivers));
            $ccEmails = !empty($bcc) ? array_map('trim', explode(',', $bcc)) : [];

            Mail::mailer('mail_thu_vien')->html($html, function ($message) use ($toEmails, $ccEmails, $subject, $name) {
                $message->to($toEmails)
                    ->subject($subject)
                    ->from('mailthuvien@vttu.edu.vn', $name);
                
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
            });

            return [
                'success' => true,
                'message' => 'Gửi email thành công qua Mail Thư Viện.'
            ];
        } catch (\Exception $e) {
            Log::error('MailLibraryService send error: ' . $e->getMessage());
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }
}
