<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailQueueService;

class MailQueueController extends Controller
{
    protected $mailQueueService;

    public function __construct(MailQueueService $mailQueueService)
    {
        $this->mailQueueService = $mailQueueService;
    }

    /**
     * Gửi mail vào Queue
     */
    public function send(Request $request)
    {
        $request->validate([
            'receivers' => 'required|string',
            'subject'   => 'required|string',
            'body'      => 'required|string',
        ]);

        $result = $this->mailQueueService->sendToQueue([
            'receivers' => $request->receivers,
            'subject'   => $request->subject,
            'body'      => $request->body,
            'name'      => $request->input('name', 'Hệ Thống'),
            'bcc'       => $request->input('bcc', ''),
        ]);

        return response()->json($result);
    }
}