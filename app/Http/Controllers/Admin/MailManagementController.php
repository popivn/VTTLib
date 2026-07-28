<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\MailQueueService;

class MailManagementController extends Controller
{
    /**
     * Display a listing of sent emails.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'list');
        
        $stats = [
            'total' => DB::table('mail_logs')->count(),
            'sent' => DB::table('mail_logs')->where('status', 'sent')->count(),
            'failed' => DB::table('mail_logs')->where('status', 'failed')->count(),
            'pending' => DB::table('mail_logs')->where('status', 'pending')->count(),
        ];

        if ($tab === 'list') {
            $query = DB::table('mail_logs')->orderBy('created_at', 'desc');

            // Filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('recipient')) {
                $query->where('recipient', 'LIKE', "%{$request->recipient}%");
            }

            if ($request->filled('subject')) {
                $query->where('subject', 'LIKE', "%{$request->subject}%");
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $mails = $query->paginate(20);
            return view('admin.mails.index', compact('tab', 'mails', 'stats'));
        }

        if ($tab === 'send') {
            $overdueStudents = DB::table('loan_transactions')
                ->join('patron_details', 'loan_transactions.patron_detail_id', '=', 'patron_details.id')
                ->join('users', 'patron_details.user_id', '=', 'users.id')
                ->where('loan_transactions.status', 'borrowed')
                ->where('loan_transactions.due_date', '<', now())
                ->select(
                    'patron_details.id as patron_detail_id',
                    'users.name as student_name',
                    'users.email as student_email',
                    'patron_details.mssv as student_mssv',
                    DB::raw('COUNT(loan_transactions.id) as overdue_books_count'),
                    DB::raw('MIN(loan_transactions.due_date) as oldest_due_date')
                )
                ->groupBy('patron_details.id', 'users.name', 'users.email', 'patron_details.mssv')
                ->get();

            return view('admin.mails.index', compact('tab', 'stats', 'overdueStudents'));
        }

        $template = DB::table('system_settings')
            ->where('key', 'mail_template_overdue')
            ->first();
        $templateData = $template ? json_decode($template->value, true) : null;

        return view('admin.mails.index', compact('tab', 'stats', 'templateData'));
    }

    /**
     * Show the form for creating a new email.
     */
    public function create()
    {
        return view('admin.mails.create');
    }

    /**
     * Show email preview before sending.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        return view('admin.mails.preview', compact('validated'));
    }

    /**
     * Show email details.
     */
    public function show($id)
    {
        $mail = DB::table('mail_logs')->where('id', $id)->first();

        if (!$mail) {
            return redirect()->route('admin.mails.index')->with('error', 'Email không tồn tại!');
        }

        return view('admin.mails.show', compact('mail'));
    }

    /**
     * Send a new email.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'reply_to' => 'nullable|email',
        ]);

        try {
            // Parse CC and BCC
            $cc = !empty($validated['cc']) ? array_map('trim', explode(',', $validated['cc'])) : [];
            $bcc = !empty($validated['bcc']) ? array_map('trim', explode(',', $validated['bcc'])) : [];

            // Send email
            Mail::html($validated['body'], function ($message) use ($validated, $cc, $bcc) {
                $message->to($validated['to'])
                    ->subject($validated['subject']);

                if (!empty($cc)) {
                    $message->cc($cc);
                }

                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }

                if (!empty($validated['reply_to'])) {
                    $message->replyTo($validated['reply_to']);
                }
            });

            // Log email
            DB::table('mail_logs')->insert([
                'recipient' => $validated['to'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'cc' => $validated['cc'] ?? null,
                'bcc' => $validated['bcc'] ?? null,
                'reply_to' => $validated['reply_to'] ?? null,
                'status' => 'sent',
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('admin.mails.index')
                ->with('success', 'Gửi email thành công!');
        } catch (\Exception $e) {
            // Log failed email
            DB::table('mail_logs')->insert([
                'recipient' => $validated['to'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'cc' => $validated['cc'] ?? null,
                'bcc' => $validated['bcc'] ?? null,
                'reply_to' => $validated['reply_to'] ?? null,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::error('Mail sending failed: ' . $e->getMessage());

            return redirect()
                ->route('admin.mails.index', ['tab' => 'create'])
                ->with('error', 'Gửi email thất bại: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send mass email to multiple recipients.
     */
    public function sendMass(Request $request)
    {
        $validated = $request->validate([
            'recipients' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'reply_to' => 'nullable|email',
        ]);

        try {
            // Parse recipients
            $recipients = array_map('trim', explode(',', $validated['recipients']));
            $recipients = array_filter($recipients, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            if (empty($recipients)) {
                return redirect()
                    ->route('admin.mails.index', ['tab' => 'create'])
                    ->with('error', 'Danh sách email không hợp lệ!')
                    ->withInput();
            }

            $failedCount = 0;
            $successCount = 0;

            foreach ($recipients as $recipient) {
                try {
                    Mail::html($validated['body'], function ($message) use ($recipient, $validated) {
                        $message->to($recipient)
                            ->subject($validated['subject']);

                        if (!empty($validated['reply_to'])) {
                            $message->replyTo($validated['reply_to']);
                        }
                    });

                    // Log successful email
                    DB::table('mail_logs')->insert([
                        'recipient' => $recipient,
                        'subject' => $validated['subject'],
                        'body' => $validated['body'],
                        'reply_to' => $validated['reply_to'] ?? null,
                        'status' => 'sent',
                        'sent_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    // Log failed email
                    DB::table('mail_logs')->insert([
                        'recipient' => $recipient,
                        'subject' => $validated['subject'],
                        'body' => $validated['body'],
                        'reply_to' => $validated['reply_to'] ?? null,
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $failedCount++;
                    Log::error("Mail sending failed for {$recipient}: " . $e->getMessage());
                }
            }

            $message = "Gửi {$successCount} email thành công";
            if ($failedCount > 0) {
                $message .= ", {$failedCount} email thất bại";
            }

            return redirect()
                ->route('admin.mails.index')
                ->with('success', $message . '!');
        } catch (\Exception $e) {
            Log::error('Mass mail sending failed: ' . $e->getMessage());

            return redirect()
                ->route('admin.mails.index', ['tab' => 'create'])
                ->with('error', 'Lỗi gửi mass email: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Resend an email.
     */
    public function resend($id)
    {
        $mail = DB::table('mail_logs')->where('id', $id)->first();

        if (!$mail) {
            return redirect()->route('admin.mails.index')->with('error', 'Email không tồn tại!');
        }

        try {
            Mail::html($mail->body, function ($message) use ($mail) {
                $message->to($mail->recipient)
                    ->subject($mail->subject);

                if (!empty($mail->cc)) {
                    $message->cc(explode(',', $mail->cc));
                }

                if (!empty($mail->bcc)) {
                    $message->bcc(explode(',', $mail->bcc));
                }

                if (!empty($mail->reply_to)) {
                    $message->replyTo($mail->reply_to);
                }
            });

            // Update mail log
            DB::table('mail_logs')
                ->where('id', $id)
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'updated_at' => now(),
                ]);

            return redirect()
                ->route('admin.mails.index')
                ->with('success', 'Gửi lại email thành công!');
        } catch (\Exception $e) {
            Log::error('Mail resending failed: ' . $e->getMessage());

            return redirect()
                ->route('admin.mails.index')
                ->with('error', 'Gửi lại email thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Delete an email from logs.
     */
    public function destroy($id)
    {
        DB::table('mail_logs')->where('id', $id)->delete();

        return redirect()
            ->route('admin.mails.index')
            ->with('success', 'Xóa email khỏi logs thành công!');
    }

    /**
     * Bulk actions on emails.
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids');

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một email'
            ]);
        }

        try {
            switch ($action) {
                case 'resend':
                    $mails = DB::table('mail_logs')->whereIn('id', $ids)->get();
                    foreach ($mails as $mail) {
                        try {
                            Mail::html($mail->body, function ($message) use ($mail) {
                                $message->to($mail->recipient)
                                    ->subject($mail->subject);
                            });

                            DB::table('mail_logs')
                                ->where('id', $mail->id)
                                ->update(['status' => 'sent', 'sent_at' => now()]);
                        } catch (\Exception $e) {
                            Log::error("Resend failed for mail {$mail->id}: " . $e->getMessage());
                        }
                    }
                    break;

                case 'delete':
                    DB::table('mail_logs')->whereIn('id', $ids)->delete();
                    break;

                case 'mark_as_sent':
                    DB::table('mail_logs')
                        ->whereIn('id', $ids)
                        ->update(['status' => 'sent', 'updated_at' => now()]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Thực hiện hành động thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk action failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => DB::table('mail_logs')->count(),
            'sent' => DB::table('mail_logs')->where('status', 'sent')->count(),
            'failed' => DB::table('mail_logs')->where('status', 'failed')->count(),
            'pending' => DB::table('mail_logs')->where('status', 'pending')->count(),
            'today' => DB::table('mail_logs')->whereDate('created_at', now())->count(),
            'this_week' => DB::table('mail_logs')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => DB::table('mail_logs')->whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.mails.statistics', compact('stats'));
    }

    /**
     * Export mail logs.
     */
    public function export(Request $request)
    {
        $query = DB::table('mail_logs');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $mails = $query->get();

        // Prepare CSV content
        $headers = ['ID', 'Người nhận', 'Chủ đề', 'Trạng thái', 'Thời gian gửi', 'Ngày tạo'];
        $data = [];

        foreach ($mails as $mail) {
            $data[] = [
                $mail->id,
                $mail->recipient,
                $mail->subject,
                $mail->status,
                $mail->sent_at,
                $mail->created_at,
            ];
        }

        $csvContent = implode(',', $headers) . "\n";
        foreach ($data as $row) {
            $csvContent .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="mail_logs_' . now()->format('Y-m-d_H-i-s') . '.csv"');
    }

    /**
     * Save the email template config to system_settings.
     */
    public function saveTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_key' => 'required|string',
            'config' => 'required|array',
        ]);

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'mail_template_' . $validated['template_key']],
            [
                'value' => json_encode($validated['config']),
                'group' => 'email',
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Lưu cấu hình template email thành công!'
        ]);
    }

    /**
     * Send overdue notifications to selected students via MailQueueService.
     */
    public function sendOverdueMails(Request $request)
    {
        $validated = $request->validate([
            'patron_ids' => 'required|array',
            'patron_ids.*' => 'integer',
        ]);

        $template = DB::table('system_settings')
            ->where('key', 'mail_template_overdue')
            ->first();
        $tpl = $template ? json_decode($template->value, true) : [];

        $libraryName = $tpl['library_name'] ?? 'Thư viện Đại học Võ Trường Toản';
        $subtitle = $tpl['subtitle'] ?? 'Thông báo hoàn trả tài liệu quá hạn';
        $greetingTpl = $tpl['greeting'] ?? 'Thân gửi sinh viên {name} (MSSV: {id}),';
        $introTpl = $tpl['intro'] ?? 'Hệ thống ghi nhận bạn đang mượn tài liệu tại Thư viện đã quá thời hạn trả quy định là {days} ngày. Để bảo đảm quyền lợi của bản thân cũng như phục vụ tài liệu cho các bạn khác, đề nghị bạn khẩn trương đến thư viện để thực hiện thủ tục trả sách và hoàn phí quá hạn (nếu có).';
        $noticeTitle = $tpl['notice_title'] ?? '⚠️ LƯU Ý QUAN TRỌNG:';
        $noticeContent = $tpl['notice_content'] ?? "Mức phạt quá hạn áp dụng theo quy định hiện hành: 5.000 đ/ngày/cuốn sách.\nTrường hợp không hoàn trả hoặc để quá hạn kéo dài, tài khoản thư viện của bạn sẽ bị tạm khóa và hệ thống có thể tạm đình chỉ quyền mượn sách.\nNếu bạn đã trả sách hoặc gia hạn tài liệu thành công trước thời gian nhận được email này, vui lòng bỏ qua thư thông báo này hoặc liên hệ phản hồi trực tiếp qua mail.";
        $signature = $tpl['signature'] ?? 'Ban Quản lý Thư viện Đại học Võ Trường Toản';
        $address = $tpl['address'] ?? 'Khu đô thị ĐH Võ Trường Toản, QL 1A, Châu Thành A, Hậu Giang';
        $footerNote = $tpl['footer_note'] ?? 'Email tự động, vui lòng không phản hồi thư này.';

        $mailQueueService = resolve(MailQueueService::class);
        $successCount = 0;
        $failCount = 0;

        foreach ($validated['patron_ids'] as $patronId) {
            $patron = DB::table('patron_details')
                ->join('users', 'patron_details.user_id', '=', 'users.id')
                ->where('patron_details.id', $patronId)
                ->select('users.name', 'users.email', 'patron_details.mssv')
                ->first();

            if (!$patron) continue;

            $loans = \App\Models\LoanTransaction::with(['bookItem.bibliographicRecord.fields.subfields'])
                ->where('patron_detail_id', $patronId)
                ->where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->get();

            if ($loans->isEmpty()) continue;

            $maxOverdueDays = 1;
            $booksRowsHtml = '';
            foreach ($loans as $index => $loan) {
                $overdueDays = max(1, (int) now()->diffInDays($loan->due_date));
                if ($overdueDays > $maxOverdueDays) {
                    $maxOverdueDays = $overdueDays;
                }
                $bookTitle = $loan->bookItem->bibliographicRecord->title ?? 'Tài liệu không tên';
                $fine = ($overdueDays * 5000);
                $fineFormatted = number_format($fine, 0, ',', '.') . ' đ';

                $booksRowsHtml .= '
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b; font-weight: 500;">' . ($index + 1) . '</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #1e293b;">' . htmlspecialchars($bookTitle) . '</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #ef4444; font-weight: 600; text-align: center;">' . $overdueDays . ' ngày</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #475569; text-align: right;">5.000 đ/ngày</td>
                        <td style="padding: 12px 10px; font-size: 13px; color: #e11d48; font-weight: bold; text-align: right;">' . $fineFormatted . '</td>
                    </tr>
                ';
            }

            $greeting = str_replace(['{name}', '{id}'], [$patron->name, $patron->mssv], $greetingTpl);
            $intro = str_replace('{days}', $maxOverdueDays, $introTpl);

            $noticeHtml = '';
            $noticeItems = explode("\n", $noticeContent);
            foreach ($noticeItems as $item) {
                $item = trim($item);
                if (empty($item)) continue;
                $cleanItem = preg_replace('/^[\s\-\*\•\d\.\)]+/', '', $item);
                $noticeHtml .= '<li style="margin-bottom: 6px;">' . htmlspecialchars($cleanItem) . '</li>';
            }

            $htmlBody = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; padding: 30px 10px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #680102 0%, #450001 100%); padding: 35px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 22px; font-weight: 800; text-transform: uppercase;">' . htmlspecialchars($libraryName) . '</h1>
                            <p style="color: #fca5a5; margin: 5px 0 0 0; font-size: 13px; font-weight: 500;">' . htmlspecialchars($subtitle) . '</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 40px 30px 40px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #334155; line-height: 24px; font-weight: 500;">' . htmlspecialchars($greeting) . '</p>
                            <p style="margin: 0 0 24px 0; font-size: 14px; color: #475569; line-height: 22px;">' . htmlspecialchars($intro) . '</p>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <thead>
                                    <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                        <th width="8%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">STT</th>
                                        <th width="42%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: left;">Tên sách</th>
                                        <th width="15%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: center;">Quá hạn</th>
                                        <th width="18%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: right;">Đơn giá</th>
                                        <th width="17%" style="padding: 10px; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #475569; text-align: right;">Tạm tính</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ' . $booksRowsHtml . '
                                </tbody>
                            </table>
                            ' . ($noticeHtml ? '
                            <div style="background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                                <h4 style="margin: 0 0 6px 0; color: #680102; font-size: 13px; font-weight: 750;">' . htmlspecialchars($noticeTitle) . '</h4>
                                <ul style="margin: 0; padding-left: 18px; color: #680102; font-size: 12px; line-height: 1.6;">
                                    ' . $noticeHtml . '
                                </ul>
                            </div>
                            ' : '') . '
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8fafc; padding: 25px 40px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: bold; color: #334155;">' . htmlspecialchars($signature) . '</p>
                            ' . ($address ? '<p style="margin: 0 0 6px 0; font-size: 11px; color: #64748b;">Địa chỉ: ' . htmlspecialchars($address) . '</p>' : '') . '
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">' . htmlspecialchars($footerNote) . '</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';

            $result = $mailQueueService->send(
                $patron->email,
                $subtitle,
                $htmlBody,
                'Thư viện Đại học Võ Trường Toản',
                'trunghieu3832@vttu.edu.vn,ptnguyen@vttu.edu.vn'
            );

            DB::table('mail_logs')->insert([
                'recipient' => $patron->email,
                'subject' => $subtitle,
                'body' => $htmlBody,
                'cc' => 'trunghieu3832@vttu.edu.vn,ptnguyen@vttu.edu.vn',
                'status' => (!empty($result['success'])) ? 'sent' : 'failed',
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($result['success'])) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã gửi thành công {$successCount} email, thất bại {$failCount} email."
        ]);
    }
}
