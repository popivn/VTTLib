<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookItem;
use App\Models\BibliographicRecord;
use App\Models\LoanTransaction;
use App\Models\Branch;
use App\Models\ReadingRoomTransaction;
use App\Models\Reservation;
use App\Models\PatronDetail;
use App\Models\Fine;
use App\Models\PatronGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CirculationController extends Controller
{
    /**
     * Display circulation policies page
     */
    public function index()
    {
        $patronGroups = PatronGroup::with('circulationPolicies')->orderBy('order')->get();
        $policies = CirculationPolicy::with('patronGroup')->get();
        
        return view('admin.circulation.index', compact('patronGroups', 'policies'));
    }

    // ==================== PATRON GROUPS ====================

    public function storePatronGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:patron_groups',
            'description' => 'nullable|string|max:500',
            'order' => 'nullable|integer'
        ]);

        PatronGroup::create($validated);
        return back()->with('success', __('Patron group created successfully.'));
    }

    public function updatePatronGroup(Request $request, PatronGroup $patronGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:patron_groups,code,' . $patronGroup->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'order' => 'nullable|integer'
        ]);

        $patronGroup->update($validated);
        return back()->with('success', __('Patron group updated successfully.'));
    }

    public function deletePatronGroup(PatronGroup $patronGroup)
    {
        // Check if has patrons
        if ($patronGroup->patrons()->exists()) {
            return back()->with('error', __('Cannot delete patron group that has associated patrons.'));
        }

        $patronGroup->delete();
        return back()->with('success', __('Patron group deleted successfully.'));
    }

    // ==================== LOAN OPERATIONS ====================

    /**
     * Loan desk page
     */
    public function loanDesk()
    {
        $recentLoans = LoanTransaction::with(['patron.user', 'bookItem.bibliographicRecord'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $overdueLoans = LoanTransaction::with(['patron.user', 'bookItem.bibliographicRecord', 'bookItem.storageLocation', 'loanedByUser', 'policy'])
            ->overdue()
            ->orderBy('due_date')
            ->get();
            
        // Get all lock history with patron relationships
        $allLockHistory = \App\Models\PatronLockHistory::with(['patron.user', 'lockedBy', 'unlockedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all loan transactions for stats calculation
        $allLoanTransactions = LoanTransaction::with(['patron', 'bookItem.bibliographicRecord', 'bookItem.storageLocation', 'loanedByUser', 'policy'])
            ->get();
        
        // Get all active loans for the "Currently Borrowed" tab
        $activeLoans = LoanTransaction::with(['patron.user', 'bookItem.bibliographicRecord', 'bookItem.storageLocation', 'loanedByUser', 'policy'])
            ->active()
            ->orderBy('loan_date', 'desc')
            ->get();

        // Get loan requests (reservations) for the "Loan Requests" tab
        $loanRequests = Reservation::with(['patron.user', 'bibliographicRecord.fields.subfields', 'bookItem'])
            ->whereIn('status', ['pending', 'ready'])
            ->latest()
            ->get();
            
        return view('admin.circulation.loan-desk', compact('recentLoans', 'overdueLoans', 'allLockHistory', 'allLoanTransactions', 'activeLoans', 'loanRequests'));
    }

    /**
     * Process checkout (loan)
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'patron_code' => 'required|string',
            'barcode' => 'required|string',
            'loan_branch_id' => 'nullable|exists:branches,id'
        ]);

        try {
            DB::beginTransaction();

            // Find patron
            $patron = PatronDetail::where('patron_code', $validated['patron_code'])->first();
            if (!$patron) {
                DB::rollBack();
                return back()->with('warning', __('Không tìm thấy bạn đọc với mã đã nhập.'));
            }
            
            // Find book item
            $bookItem = BookItem::where('barcode', $validated['barcode'])->first();
            if (!$bookItem) {
                DB::rollBack();
                return back()->with('warning', __('Không tìm thấy tài liệu với mã vạch đã nhập.'));
            }

            // Check if patron can borrow
            if (!$patron->canBorrow()) {
                DB::rollBack();
                return back()->with('warning', __('Bạn đọc không thể mượn sách. Vui lòng kiểm tra lại giới hạn mượn, hạn thẻ hoặc tiền phạt tồn đọng.'));
            }

            // Check if book is available
            if ($bookItem->status !== 'available') {
                DB::rollBack();
                return back()->with('warning', __('Tài liệu hiện không ở trạng thái sẵn sàng để mượn.'));
            }

            // Get policy
            $policy = $patron->patronGroup?->activePolicy;
            if (!$policy) {
                DB::rollBack();
                return back()->with('warning', __('Chưa có quy định lưu thông đang hoạt động cho nhóm bạn đọc này.'));
            }

            // Create loan transaction
            $loan = LoanTransaction::create([
                'patron_detail_id' => $patron->id,
                'book_item_id' => $bookItem->id,
                'circulation_policy_id' => $policy->id,
                'loan_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays($policy->max_loan_days),
                'status' => 'borrowed',
                'loaned_by' => auth()->id(),
                'loan_branch_id' => $validated['loan_branch_id'] ?? null
            ]);

            // Update book item status
            $bookItem->update(['status' => 'on_loan']);

            DB::commit();

            return back()->with('success', __('Book checked out successfully. Due date: :date', [
                'date' => $loan->due_date->format('d/m/Y')
            ]));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Process checkin (return)
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required_without:loan_id|string',
            'loan_id' => 'required_without:barcode|exists:loan_transactions,id',
            'return_branch_id' => 'nullable|exists:branches,id',
            'forgive' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            if ($request->filled('loan_id')) {
                $loan = LoanTransaction::with('bookItem')->find($validated['loan_id']);
                if (!$loan) {
                    throw new \Exception(__('Không tìm thấy thông tin lượt mượn.'));
                }
                $bookItem = $loan->bookItem;
            } else {
                // Find book item
                $bookItem = BookItem::where('barcode', $validated['barcode'])->first();
                if (!$bookItem) {
                    throw new \Exception(__('Không tìm thấy tài liệu với mã vạch đã nhập.'));
                }

                // Find active loan
                $loan = LoanTransaction::where('book_item_id', $bookItem->id)
                    ->where('status', 'borrowed')
                    ->first();
                if (!$loan) {
                    throw new \Exception(__('Tài liệu này hiện không ở trạng thái đang được mượn.'));
                }
            }

            // Calculate fine if overdue and not forgiven
            $fine = null;
            if ($loan->isOverdue() && $loan->policy && !$request->get('forgive')) {
                $overdueDays = $loan->getOverdueDays();
                $fineAmount = $loan->policy->calculateFine($overdueDays);

                if ($fineAmount > 0) {
                    $fine = Fine::create([
                        'patron_detail_id' => $loan->patron_detail_id,
                        'loan_transaction_id' => $loan->id,
                        'fine_type' => 'overdue',
                        'amount' => $fineAmount,
                        'status' => 'pending',
                        'description' => __('Overdue fine for :days days', ['days' => $overdueDays])
                    ]);
                }
            }

            // Update loan
            $loan->update([
                'return_date' => Carbon::now(),
                'status' => 'returned',
                'returned_to' => auth()->id(),
                'return_branch_id' => $validated['return_branch_id'] ?? null,
                'notes' => $request->get('forgive') ? ($loan->notes . ' [Overdue Forgiven]') : $loan->notes
            ]);

            // Update book item status
            $bookItem->update(['status' => 'available']);

            // Tự động xử lý hàng chờ đặt giữ nếu có yêu cầu đang chờ
            Reservation::processQueue($bookItem->bibliographic_record_id, $bookItem->id);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Book returned successfully.') . ($fine ? ' ' . __('Fine applied: :amount VND', ['amount' => number_format($fine->amount)]) : '')
                ]);
            }

            $message = __('Book returned successfully.');
            if ($fine) {
                $message .= ' ' . __('Fine applied: :amount VND', ['amount' => number_format($fine->amount)]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function renew(Request $request, LoanTransaction $loan)
    {
        try {
            if (!$loan->canRenew()) {
                throw new \Exception(__('This loan cannot be renewed.'));
            }

            $loan->update([
                'due_date' => Carbon::parse($loan->due_date)->addDays($loan->policy->renewal_days),
                'renewal_count' => $loan->renewal_count + 1,
                'last_renewal_date' => Carbon::now()
            ]);

            $message = __('Loan renewed successfully. New due date: :date', [
                'date' => $loan->due_date->format('d/m/Y')
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'due_date' => $loan->due_date->format('d/m/Y'),
                    'renewal_count' => $loan->renewal_count
                ]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== FINES ====================

    /**
     * Fines management page
     */
    public function fines()
    {
        $unpaidFines = Fine::with(['patron.user', 'loanTransaction.bookItem'])
            ->unpaid()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.circulation.fines', compact('unpaidFines'));
    }

    /**
     * Pay fine
     */
    public function payFine(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $fine->balance,
            'payment_method' => 'required|in:cash,transfer,card'
        ]);

        $fine->paid_amount += $validated['amount'];
        
        if ($fine->balance <= 0) {
            $fine->status = 'paid';
            $fine->paid_date = Carbon::now();
        } else {
            $fine->status = 'partial';
        }

        $fine->collected_by = auth()->id();
        $fine->payment_method = $validated['payment_method'];
        $fine->save();

        return back()->with('success', __('Payment recorded successfully.'));
    }

    /**
     * Waive fine
     */
    public function waiveFine(Request $request, Fine $fine)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $fine->balance,
            'notes' => 'nullable|string|max:500'
        ]);

        $fine->waived_amount += $validated['amount'];
        
        if ($fine->balance <= 0) {
            $fine->status = 'waived';
        }

        $fine->notes = $validated['notes'];
        $fine->save();

        return back()->with('success', __('Fine waived successfully.'));
    }

    /**
     * Book distribution page - View all book items
     */
    public function distribution(Request $request)
    {
        $query = BookItem::with(['bibliographicRecord', 'branch', 'currentLoan.patron.user']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        // Search by barcode or title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode', 'like', "%{$search}%")
                  ->orWhereHas('bibliographicRecord', function($subQ) use ($search) {
                      $subQ->where('title', 'like', "%{$search}%");
                  });
            });
        }
        
        // Get statistics
        $stats = [
            'total' => BookItem::count(),
            'available' => BookItem::where('status', 'available')->count(),
            'on_loan' => BookItem::where('status', 'on_loan')->count(),
            'lost' => BookItem::where('status', 'lost')->count(),
            'damaged' => BookItem::where('status', 'damaged')->count(),
            'in_processing' => BookItem::where('status', 'in_processing')->count(),
        ];
        
        // Get branches for filter
        $branches = \App\Models\Branch::pluck('name', 'id');
        
        // Paginate results
        $bookItems = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return view('admin.circulation.distribution', compact('bookItems', 'stats', 'branches'));
    }

    /**
     * Get book item history
     */
    public function getBookItemHistory(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'history_type' => 'nullable|in:all,loan,reservation,reading_room'
        ]);

        try {
            $bookItem = BookItem::where('barcode', $validated['barcode'])->firstOrFail();
            $historyType = $validated['history_type'] ?? 'all';

            // Prepare book info
            $bookInfo = [
                'title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                'author' => $bookItem->bibliographicRecord?->author ?? 'N/A',
                'barcode' => $bookItem->barcode,
                'status' => $this->getBookStatusDisplay($bookItem->status),
                'location' => $bookItem->location ?? 'N/A',
                'branch' => $bookItem->branch?->name ?? 'N/A'
            ];

            $history = [];

            // Get loan history
            if ($historyType === 'all' || $historyType === 'loan') {
                $loans = LoanTransaction::with(['patron.user', 'loanedBy'])
                    ->where('book_item_id', $bookItem->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($loans as $loan) {
                    $history[] = [
                        'date' => $loan->loan_date->format('d/m/Y H:i'),
                        'type' => 'loan',
                        'type_display' => __('Mượn'),
                        'patron_name' => $loan->patron->display_name ?? $loan->patron->user->name,
                        'patron_code' => $loan->patron->patron_code,
                        'details' => __('Mượn đến') . ': ' . ($loan->due_date->format('d/m/Y')),
                        'staff_name' => $loan->loanedBy?->name
                    ];

                    if ($loan->return_date) {
                        $history[] = [
                            'date' => $loan->return_date->format('d/m/Y H:i'),
                            'type' => 'return',
                            'type_display' => __('Trả'),
                            'patron_name' => $loan->patron->display_name ?? $loan->patron->user->name,
                            'patron_code' => $loan->patron->patron_code,
                            'details' => __('Trả sách'),
                            'staff_name' => $loan->returnedBy?->name
                        ];
                    }
                }
            }

            // Get reservation history
            if ($historyType === 'all' || $historyType === 'reservation') {
                $reservations = Reservation::with(['patron.user'])
                    ->where('bibliographic_record_id', $bookItem->bibliographic_record_id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($reservations as $reservation) {
                    $statusDisplay = match($reservation->status) {
                        'pending' => __('Trong danh sách chờ'),
                        'ready' => __('Sẵn sàng nhận sách'),
                        'fulfilled' => __('Đã cho mượn'),
                        'cancelled' => __('Đã hủy'),
                        'expired' => __('Hết hạn'),
                        default => $reservation->status
                    };

                    $history[] = [
                        'date' => $reservation->reservation_date->format('d/m/Y H:i'),
                        'type' => 'reservation',
                        'type_display' => __('Reservation'),
                        'patron_name' => $reservation->patron->display_name ?? $reservation->patron->user->name,
                        'patron_code' => $reservation->patron->patron_code,
                        'details' => $statusDisplay . ($reservation->pickupBranch ? ' - ' . $reservation->pickupBranch->name : ''),
                        'staff_name' => null
                    ];
                }
            }

            // Get reading room history
            if ($historyType === 'all' || $historyType === 'reading_room') {
                $readingTransactions = ReadingRoomTransaction::with(['patron.user', 'staff'])
                    ->where('book_item_id', $bookItem->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($readingTransactions as $transaction) {
                    $history[] = [
                        'date' => $transaction->checkout_time->format('d/m/Y H:i'),
                        'type' => 'reading_room',
                        'type_display' => __('Mượn đọc'),
                        'patron_name' => $transaction->patron->display_name ?? $transaction->patron->user->name,
                        'patron_code' => $transaction->patron->patron_code,
                        'details' => __('Mượn đọc tại chỗ') . ' - ' . __('Hạn trả') . ': ' . $transaction->due_time->format('H:i'),
                        'staff_name' => $transaction->staff?->name
                    ];

                    if ($transaction->checkin_time) {
                        $history[] = [
                            'date' => $transaction->checkin_time->format('d/m/Y H:i'),
                            'type' => 'reading_room_return',
                            'type_display' => __('Trả mượn đọc'),
                            'patron_name' => $transaction->patron->display_name ?? $transaction->patron->user->name,
                            'patron_code' => $transaction->patron->patron_code,
                            'details' => __('Thời gian mượn') . ': ' . $transaction->getDuration(),
                            'staff_name' => $transaction->staff?->name
                        ];
                    }
                }
            }

            // Sort by date
            usort($history, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json([
                'success' => true,
                'book_info' => $bookInfo,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get transaction status display info
     */
    private function getTransactionStatusInfo($transaction)
    {
        $statusMap = [
            'borrowed' => [
                'display' => __('Đã mượn'),
                'color' => 'blue'
            ],
            'returned' => [
                'display' => __('Đã trả'),
                'color' => 'green'
            ],
            'renewed' => [
                'display' => __('Đã gia hạn'),
                'color' => 'yellow'
            ],
            'recalled' => [
                'display' => __('Đã triệu hồi'),
                'color' => 'yellow'
            ],
            'lost' => [
                'display' => __('Đã khai báo mất'),
                'color' => 'red'
            ],
            'overdue' => [
                'display' => __('Quá hạn'),
                'color' => 'red'
            ]
        ];

        return $statusMap[$transaction->status] ?? [
            'display' => ucfirst($transaction->status),
            'color' => 'gray'
        ];
    }

    /**
     * Get book status display text
     */
    private function getBookStatusDisplay($status)
    {
        return match($status) {
            'available' => __('Sẵn có'),
            'on_loan' => __('Đang mượn'),
            'reserved' => __('Để dành'),
            'in_reading_room' => __('Mượn đọc tại chỗ'),
            'lost' => __('Đã mất'),
            'damaged' => __('Hư hỏng'),
            'maintenance' => __('Bảo trì'),
            default => $status
        };
    }

    // ==================== SEARCH METHODS ====================

    /**
     * Search patron by code (AJAX)
     */
    public function searchPatron(Request $request)
    {
        $code = $request->get('code');
        $startTime = microtime(true);
        
        // Log search attempt
        \Log::info('Patron search initiated', [
            'code' => $code,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);
        
        if (!$code || strlen($code) < 2) {
            \Log::warning('Invalid patron code search attempt', [
                'code' => $code,
                'length' => strlen($code ?? ''),
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['success' => false, 'message' => __('Invalid patron code')]);
        }

        try {
            $patron = PatronDetail::with(['user', 'patronGroup.circulationPolicies', 'activeLoans.bookItem.bibliographicRecord'])
                ->where('patron_code', $code)
                ->first();

            if (!$patron) {
                \Log::info('Patron not found', [
                    'search_code' => $code,
                    'user_id' => auth()->id(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]);
                
                return response()->json(['success' => false, 'message' => __('Patron not found')]);
            }

            // Get current loans count
            $currentLoans = LoanTransaction::where('patron_detail_id', $patron->id)
                ->where('status', 'borrowed')
                ->count();

            // Calculate total outstanding fines
            $outstandingFine = Fine::whereHas('loanTransaction', function($query) use ($patron) {
                $query->where('patron_detail_id', $patron->id);
            })
            ->where('status', 'unpaid')
            ->selectRaw('SUM(amount - paid_amount - waived_amount) as outstanding')
            ->value('outstanding') ?? 0;

            // Check if patron can borrow
            $maxLoans = $patron->patronGroup?->activePolicy?->max_items ?? 5;
            $canBorrow = $patron->canBorrow() && $currentLoans < $maxLoans;

            // Get transaction statistics from loan_transactions
            $totalCheckouts = LoanTransaction::where('patron_detail_id', $patron->id)->count();
            $totalCheckins = LoanTransaction::where('patron_detail_id', $patron->id)
                ->whereNotNull('return_date')->count();
            $totalRenewals = LoanTransaction::where('patron_detail_id', $patron->id)
                ->sum('renewal_count');

            $result = [
                'success' => true,
                'data' => [
                    'id' => $patron->id,
                    'patron_code' => $patron->patron_code,
                    'display_name' => $patron->display_name,
                    'profile_image' => $patron->profile_image,
                    'user' => [
                        'name' => $patron->user?->name
                    ],
                    'patron_group' => [
                        'name' => $patron->patronGroup?->name
                    ],
                    'current_loans' => $currentLoans,
                    'active_loans' => $patron->activeLoans()->with(['bookItem.bibliographicRecord', 'bookItem.storageLocation', 'loanedByUser', 'policy'])->get()->map(function($loan) {
                        $now = \Carbon\Carbon::now();
                        $dueDate = $loan->due_date;
                        $remainingDays = $dueDate ? ceil($now->diffInDays($dueDate, false)) : 0;
                        return [
                            'id' => $loan->id,
                            'loan_date' => $loan->loan_date ? $loan->loan_date->toISOString() : null,
                            'due_date' => $dueDate ? $dueDate->toISOString() : null,
                            'due_date_iso' => $dueDate ? $dueDate->toISOString() : null,
                            'status' => $loan->status,
                            'renewal_count' => $loan->renewal_count ?? 0,
                            'max_renewals' => $loan->policy?->max_renewals ?? 2,
                            'remaining_days' => $remainingDays,
                            'is_overdue' => $dueDate ? $now->greaterThan($dueDate) : false,
                            'loaned_by' => $loan->loanedByUser?->name ?? $loan->loanedByUser?->username ?? 'staff',
                            'notes' => $loan->notes ?? 'Sách đang mượn',
                            'book_item' => [
                                'barcode' => $loan->bookItem?->barcode ?? 'N/A',
                                'price' => number_format($loan->bookItem?->price ?? 0),
                                'location' => $loan->bookItem?->storageLocation?->name ?? $loan->bookItem?->location ?? 'Kho',
                                'material_type' => $loan->bookItem?->storage_type ?? 'Giáo trình',
                                'bibliographic_record' => [
                                    'title' => $loan->bookItem?->bibliographicRecord?->title ?? 'N/A',
                                    'author' => $loan->bookItem?->bibliographicRecord?->author ?? '',
                                    'publisher' => $loan->bookItem?->bibliographicRecord?->publisher ?? '',
                                    'publish_year' => $loan->bookItem?->bibliographicRecord?->publish_year ?? '',
                                ]
                            ]
                        ];
                    }),
                    'max_loans' => $maxLoans,
                    'outstanding_fine' => $outstandingFine,
                    'can_borrow' => $canBorrow,
                    'status' => $patron->status,
                    'transaction_stats' => [
                        'total_checkouts' => $totalCheckouts,
                        'total_checkins' => $totalCheckins,
                        'total_renewals' => $totalRenewals,
                        'total_transactions' => $totalCheckouts + $totalCheckins + $totalRenewals
                    ]
                ]
            ];

            \Log::info('Patron search completed successfully', [
                'patron_id' => $patron->id,
                'patron_code' => $patron->patron_code,
                'can_borrow' => $canBorrow,
                'current_loans' => $currentLoans,
                'outstanding_fine' => $outstandingFine,
                'user_id' => auth()->id(),
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Patron search error', [
                'search_code' => $code,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
            return response()->json(['success' => false, 'message' => __('Search error: ') . $e->getMessage()]);
        }
    }

    /**
     * Search book by barcode (AJAX)
     */
    public function searchBook(Request $request)
    {
        $barcode = $request->get('barcode');
        $startTime = microtime(true);
        
        // Log search attempt
        \Log::info('Book search initiated', [
            'barcode' => $barcode,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);
        
        if (!$barcode || strlen($barcode) < 2) {
            \Log::warning('Invalid barcode search attempt', [
                'barcode' => $barcode,
                'length' => strlen($barcode ?? ''),
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['success' => false, 'message' => __('Invalid barcode')]);
        }

        try {
            $bookItem = BookItem::with(['bibliographicRecord', 'currentLoan.patron.user'])
                ->where('barcode', 'like', $barcode . '%')
                ->first();

            if (!$bookItem) {
                \Log::info('Book not found', [
                    'search_barcode' => $barcode,
                    'user_id' => auth()->id(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]);
                
                return response()->json(['success' => false, 'message' => __('Book not found')]);
            }

            // Check if the MARC record is approved
            if ($bookItem->bibliographicRecord && $bookItem->bibliographicRecord->status !== BibliographicRecord::STATUS_APPROVED) {
                return response()->json([
                    'success' => false, 
                    'message' => __('This book is currently pending cataloging approval and cannot be borrowed.')
                ]);
            }

            // Debug bibliographic record data
            \Log::info('Book item found', [
                'book_item_id' => $bookItem->id,
                'barcode' => $bookItem->barcode,
                'bibliographic_record_id' => $bookItem->bibliographic_record_id,
                'bibliographic_record_exists' => $bookItem->bibliographicRecord ? true : false,
                'bibliographic_record_title' => $bookItem->bibliographicRecord?->title,
                'status' => $bookItem->status
            ]);

            // Get current loan information
            $currentLoan = null;
            if ($bookItem->status === 'on_loan' && $bookItem->currentLoan) {
                $currentLoan = [
                    'patron_name' => $bookItem->currentLoan->patron->display_name ?? $bookItem->currentLoan->patron->user->name,
                    'patron_code' => $bookItem->currentLoan->patron->patron_code,
                    'due_date' => $bookItem->currentLoan->due_date->format('d/m/Y'),
                    'is_overdue' => $bookItem->currentLoan->due_date->isPast(),
                    'overdue_days' => $bookItem->currentLoan->due_date->isPast() ? $bookItem->currentLoan->due_date->diffInDays(now()) : 0
                ];
            }

            $result = [
                'success' => true,
                'data' => [
                    'id' => $bookItem->id,
                    'barcode' => $bookItem->barcode,
                    'title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                    'author' => $bookItem->bibliographicRecord?->author ?? 'N/A',
                    'call_number' => $bookItem->bibliographicRecord?->call_number ?? 'N/A',
                    'cover_image' => $bookItem->bibliographicRecord?->cover_image ? asset('storage/' . $bookItem->bibliographicRecord->cover_image) : null,
                    'status' => $bookItem->status,
                    'current_loan' => $currentLoan
                ]
            ];

            \Log::info('Book search completed successfully', [
                'book_item_id' => $bookItem->id,
                'barcode' => $bookItem->barcode,
                'title' => $bookItem->bibliographicRecord?->title,
                'status' => $bookItem->status,
                'has_current_loan' => !is_null($currentLoan),
                'user_id' => auth()->id(),
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error('Book search error', [
                'search_barcode' => $barcode,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]);
            
            return response()->json(['success' => false, 'message' => __('Search error: ') . $e->getMessage()]);
        }
    }

    /**
     * Display loan requests (reservations)
     */
    public function loanRequests(Request $request)
    {
        $query = Reservation::with(['patron.user', 'bibliographicRecord.fields.subfields', 'bookItem']);

        // Lọc theo trạng thái
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->latest()->paginate(20);

        return view('admin.circulation.requests', compact('requests', 'status'));
    }

    /**
     * Approve a loan request
     */
    public function approveRequest(Request $request, Reservation $reservation)
    {
        try {
            DB::beginTransaction();

            // Tìm một ấn phẩm còn trống cho bản ghi này
            $bookItem = BookItem::where('bibliographic_record_id', $reservation->bibliographic_record_id)
                ->where('status', 'available')
                ->first();

            if (!$bookItem) {
                throw new \Exception(__('Không có ấn phẩm nào sẵn sàng cho tài liệu này.'));
            }

            // Lấy chính sách lưu thông của độc giả
            $policy = $reservation->patron->patronGroup?->circulationPolicies()
                ->where('is_active', true)
                ->first();
            
            $holdDays = $policy ? $policy->reservation_hold_days : 3;

            // Cập nhật trạng thái yêu cầu
            $reservation->update([
                'status' => 'ready',
                'book_item_id' => $bookItem->id,
                'approved_at' => now(),
                'expiry_date' => now()->addDays($holdDays), // Sử dụng expiry_date từ DB
            ]);

            // Cập nhật trạng thái ấn phẩm thành 'reserved' (đang chờ lấy)
            $bookItem->update(['status' => 'reserved']);

            DB::commit();
            return back()->with('success', __('Yêu cầu đã được phê duyệt. Ấn phẩm đã được giữ cho độc giả.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a loan request
     */
    public function rejectRequest(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'status' => 'cancelled',
            'notes' => $request->get('reason', 'Yêu cầu bị từ chối bởi thủ thư.')
        ]);

        return back()->with('success', __('Đã từ chối yêu cầu mượn sách.'));
    }

    /**
     * Process recall - Create new record only
     */
    public function recall(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'reason' => 'nullable|string'
        ]);
        try {
            DB::beginTransaction();
            $bookItem = BookItem::where('barcode', $validated['barcode'])->firstOrFail();
            $activeLoan = LoanTransaction::where('book_item_id', $bookItem->id)
                ->where('status', 'borrowed')
                ->firstOrFail();
            
            // Check if book is overdue and set appropriate due date
            $now = Carbon::now();
            $originalDueDate = $activeLoan->due_date;
            
            if ($originalDueDate->greaterThan($now)) {
                // Book is NOT overdue → set due date to recall date
                $newDueDate = $now;
            } else {
                // Book IS overdue → keep original due date
                $newDueDate = $originalDueDate;
            }
            
            $recallTransaction = LoanTransaction::create([
                'patron_detail_id' => $activeLoan->patron_detail_id,
                'book_item_id' => $bookItem->id,
                'circulation_policy_id' => $activeLoan->circulation_policy_id,
                'loan_date' => $now,
                'due_date' => $newDueDate,
                'status' => 'recalled',
                'loaned_by' => auth()->id(),
                'loan_branch_id' => $activeLoan->loan_branch_id,
                'notes' => 'Triệu hồi: ' . ($validated['reason'] ?? 'Không có lý do')
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ghi nhận hành động triệu hồi thành công',
                'data' => [
                    'loan_id' => $recallTransaction->id,
                    'book_title' => $bookItem->bibliographicRecord->title ?? 'N/A',
                    'patron_name' => $activeLoan->patron->display_name ?? $activeLoan->patron->user->name ?? 'N/A',
                    'original_due_date' => $originalDueDate->format('d/m/Y'),
                    'new_due_date' => $newDueDate->format('d/m/Y'),
                    'is_overdue' => !$originalDueDate->greaterThan($now)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi ghi nhận triệu hồi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process declare lost - Create new record only
     */
    public function declareLost(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Find book item
            $bookItem = BookItem::where('barcode', $validated['barcode'])->firstOrFail();
            
            // Find active loan transaction
            $activeLoan = LoanTransaction::where('book_item_id', $bookItem->id)
                ->where('status', 'borrowed')
                ->firstOrFail();

            // Create NEW loan transaction record for declare lost action
            $lostTransaction = LoanTransaction::create([
                'patron_detail_id' => $activeLoan->patron_detail_id,
                'book_item_id' => $bookItem->id,
                'circulation_policy_id' => $activeLoan->circulation_policy_id,
                'loan_date' => Carbon::now(), // Current time as action date
                'due_date' => $activeLoan->due_date, // Keep original due date
                'status' => 'lost', // New status for this record
                'loaned_by' => auth()->id(),
                'loan_branch_id' => $activeLoan->loan_branch_id,
                'notes' => 'Khai báo mất: ' . ($validated['notes'] ?? 'Không có ghi chú')
            ]);

            // DO NOT update the original loan transaction
            // DO NOT update book item status

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ghi nhận hành động khai báo mất thành công',
                'data' => [
                    'loan_id' => $lostTransaction->id,
                    'book_title' => $bookItem->bibliographicRecord->title ?? 'N/A',
                    'patron_name' => $activeLoan->patron->display_name ?? $activeLoan->patron->user->name ?? 'N/A'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi ghi nhận khai báo mất: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== READING ROOM OPERATIONS ====================

    /**
     * Reading room checkout
     */
    public function readingRoomCheckout(Request $request)
    {
        $validated = $request->validate([
            'patron_code' => 'required|string',
            'barcode' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Find patron
            $patron = PatronDetail::where('patron_code', $validated['patron_code'])->first();
            if (!$patron) {
                throw new \Exception(__('Không tìm thấy bạn đọc với mã đã nhập.'));
            }
            
            // Find book item
            $bookItem = BookItem::where('barcode', $validated['barcode'])->first();
            if (!$bookItem) {
                throw new \Exception(__('Không tìm thấy tài liệu với mã vạch đã nhập.'));
            }

            // Check if book is available for reading room
            if (!in_array($bookItem->status, ['available', 'in_processing'])) {
                throw new \Exception(__('Tài liệu không sẵn có cho mượn đọc tại chỗ.'));
            }

            // Check patron's reading room policy
            $policy = $patron->patronGroup?->activePolicy;
            if (!$policy || !$policy->canUseReadingRoom()) {
                throw new \Exception(__('Bạn đọc không được phép sử dụng mượn đọc tại chỗ.'));
            }

            // Check if patron already has active reading room transactions
            $activeReadingCount = ReadingRoomTransaction::where('patron_detail_id', $patron->id)
                ->active()
                ->count();

            if ($activeReadingCount >= $policy->max_reading_room_items) {
                throw new \Exception(__('Bạn đọc đã mượn đọc tối đa :max tài liệu. Vui lòng trả một số tài liệu trước khi mượn thêm.', ['max' => $policy->max_reading_room_items]));
            }

            // Create reading room transaction
            $transaction = ReadingRoomTransaction::create([
                'patron_detail_id' => $patron->id,
                'book_item_id' => $bookItem->id,
                'checkout_time' => Carbon::now(),
                'due_time' => $policy->getReadingRoomDueTime(),
                'status' => ReadingRoomTransaction::STATUS_ACTIVE,
                'staff_id' => auth()->id(),
                'checkout_branch_id' => $patron->branch_id ?? null,
                'notes' => $validated['notes'] ?? null
            ]);

            // Update book item status
            $bookItem->update(['status' => 'in_reading_room']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Mượn đọc tại chỗ thành công'),
                'data' => [
                    'transaction_id' => $transaction->id,
                    'patron_name' => $patron->display_name ?? $patron->user->name,
                    'book_title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                    'due_time' => $transaction->due_time->format('H:i d/m/Y'),
                    'checkout_time' => $transaction->checkout_time->format('H:i d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reading room checkin
     */
    public function readingRoomCheckin(Request $request)
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'integer|exists:reading_room_transactions,id'
        ]);

        try {
            DB::beginTransaction();

            $checkedInItems = [];
            $now = Carbon::now();

            foreach ($validated['transaction_ids'] as $transactionId) {
                $transaction = ReadingRoomTransaction::findOrFail($transactionId);

                // Only check in active transactions
                if ($transaction->status !== ReadingRoomTransaction::STATUS_ACTIVE) {
                    continue;
                }

                // Mark as returned
                $transaction->markAsReturned(auth()->id());

                // Update book item status back to available
                $transaction->bookItem->update(['status' => 'available']);

                $checkedInItems[] = [
                    'book_title' => $transaction->bookItem->bibliographicRecord?->title ?? 'N/A',
                    'barcode' => $transaction->bookItem->barcode,
                    'duration' => $transaction->getFormattedDuration()
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Trả mượn đọc thành công') . ' (' . count($checkedInItems) . ' ' . __('tài liệu') . ')',
                'data' => [
                    'checked_in_items' => $checkedInItems,
                    'checkin_time' => $now->format('H:i d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getActiveReadingRoomTransactions(Request $request)
    {
        try {
            $transactions = ReadingRoomTransaction::with(['patron.user', 'bookItem.bibliographicRecord'])
                ->active()
                ->orderBy('checkout_time', 'desc')
                ->get();

            // Update overdue status
            foreach ($transactions as $transaction) {
                $transaction->updateOverdueStatus();
            }

            $formattedTransactions = $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'patron_name' => $transaction->patron->display_name ?? $transaction->patron->user->name,
                    'patron_code' => $transaction->patron->patron_code,
                    'book_title' => $transaction->bookItem->bibliographicRecord?->title ?? 'N/A',
                    'author' => $transaction->bookItem->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $transaction->bookItem->barcode,
                    'checkout_time' => $transaction->checkout_time->format('H:i d/m/Y'),
                    'due_time' => $transaction->due_time->format('H:i d/m/Y'),
                    'duration' => $transaction->getFormattedDuration(),
                    'status' => $transaction->status,
                    'status_display' => $transaction->status === 'overdue' ? 'Quá hạn' : 'Đang mượn',
                    'is_overdue' => $transaction->isOverdue(),
                    'notes' => $transaction->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $formattedTransactions,
                    'total_count' => $formattedTransactions->count(),
                    'overdue_count' => $formattedTransactions->where('is_overdue', true)->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== HOLD/RESERVE OPERATIONS ====================

    /**
     * Place a hold/reserve on a book
     */
    public function placeHold(Request $request)
    {
        $validated = $request->validate([
            'patron_code' => 'required|string',
            'barcode' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Find patron
            $patron = PatronDetail::where('patron_code', $validated['patron_code'])->first();
            if (!$patron) {
                throw new \Exception(__('Không tìm thấy bạn đọc với mã đã nhập.'));
            }
            
            // Find book item
            $bookItem = BookItem::where('barcode', $validated['barcode'])->first();
            if (!$bookItem) {
                throw new \Exception(__('Không tìm thấy tài liệu với mã vạch đã nhập.'));
            }

            // Chặn đặt giữ sách đang ở trạng thái không thể lưu thông
            if (in_array($bookItem->status, ['lost', 'damaged', 'maintenance'])) {
                throw new \Exception(__('Tài liệu này đang ở trạng thái không thể đặt giữ (Đã mất, Hư hỏng hoặc Đang bảo trì).'));
            }

            // Chặn đặt giữ nếu bạn đọc đang trực tiếp mượn cuốn sách này
            $activeLoan = LoanTransaction::where('book_item_id', $bookItem->id)
                ->where('patron_detail_id', $patron->id)
                ->where('status', 'borrowed')
                ->first();

            if ($activeLoan) {
                throw new \Exception(__('Bạn đọc đang mượn tài liệu này, không thể tự đặt giữ lại.'));
            }

            // Check if patron already has active reservation for this book
            $existingReservation = Reservation::where('patron_detail_id', $patron->id)
                ->where('bibliographic_record_id', $bookItem->bibliographic_record_id)
                ->whereIn('status', ['pending', 'ready'])
                ->first();

            if ($existingReservation) {
                throw new \Exception(__('Bạn đọc đã có reservation đang hoạt động cho tài liệu này.'));
            }

            // Check patron's hold policy
            $policy = $patron->patronGroup?->activePolicy;
            if (!$policy || !$policy->canPlaceHolds()) {
                throw new \Exception(__('Bạn đọc không được phép đặt giữ lại sách.'));
            }

            // Check patron's hold limit
            $activeHolds = Reservation::where('patron_detail_id', $patron->id)
                ->whereIn('status', ['pending', 'ready'])
                ->count();

            if ($activeHolds >= $policy->max_holds) {
                throw new \Exception(__('Bạn đọc đã đạt giới hạn giữ lại tối đa (:max)', ['max' => $policy->max_holds]));
            }

            // Check if book is available
            $reservationStatus = 'pending';
            $assignedBookItemId = $bookItem->id; // Gán book_item_id từ mã vạch đã quét

            if ($bookItem->status === 'available') {
                $reservationStatus = 'ready';
                
                // Update book item status
                $bookItem->update(['status' => 'reserved']);
            }

            // Create reservation
            $reservation = Reservation::create([
                'patron_detail_id' => $patron->id,
                'bibliographic_record_id' => $bookItem->bibliographic_record_id,
                'book_item_id' => $assignedBookItemId,
                'reservation_date' => Carbon::now(),
                'expiry_date' => $policy->getHoldExpiryDate(),
                'pickup_branch_id' => $validated['pickup_branch_id'] ?? $patron->branch_id,
                'status' => $reservationStatus,
                'notified' => false,
                'notes' => $validated['notes'] ?? null
            ]);

            DB::commit();

            $statusText = $reservationStatus === 'ready' ? __('Sẵn sàng nhận sách') : __('Trong danh sách chờ');

            return response()->json([
                'success' => true,
                'message' => __('Giữ lại sách thành công') . ' - ' . $statusText,
                'data' => [
                    'reservation_id' => $reservation->id,
                    'patron_name' => $patron->display_name ?? $patron->user->name,
                    'book_title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                    'status' => $reservationStatus,
                    'status_display' => $statusText,
                    'expiry_date' => $reservation->expiry_date->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cancel a hold/reserve
     */
    public function cancelHold(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|integer|exists:reservations,id'
        ]);

        try {
            DB::beginTransaction();

            $reservation = Reservation::findOrFail($validated['reservation_id']);

            // Only allow cancel pending or ready reservations
            if (!in_array($reservation->status, ['pending', 'ready'])) {
                throw new \Exception(__('Chỉ có thể hủy reservation đang chờ hoặc sẵn sàng.'));
            }

            // Update book item status if assigned
            if ($reservation->bookItem) {
                $reservation->bookItem->update(['status' => 'available']);
            }

            // Mark reservation as cancelled
            $reservation->update([
                'status' => 'cancelled',
                'notes' => ($reservation->notes ?? '') . "\n" . __('Đã hủy bởi thủ thư lúc :time', ['time' => Carbon::now()->format('H:i d/m/Y')])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Hủy giữ lại sách thành công'),
                'data' => [
                    'book_title' => $reservation->bibliographicRecord?->title ?? 'N/A',
                    'cancelled_time' => Carbon::now()->format('H:i d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get active reservations for patron
     */
    public function getPatronReservations(Request $request)
    {
        $validated = $request->validate([
            'patron_code' => 'required|string'
        ]);

        try {
            $patron = PatronDetail::where('patron_code', $validated['patron_code'])->firstOrFail();

            $reservations = Reservation::with(['bibliographicRecord', 'bookItem'])
                ->where('patron_detail_id', $patron->id)
                ->whereIn('status', ['pending', 'ready'])
                ->orderBy('reservation_date', 'desc')
                ->get();

            $formattedReservations = $reservations->map(function($reservation) {
                $statusDisplay = $reservation->status === 'ready' ? __('Sẵn sàng nhận sách') : __('Trong danh sách chờ');
                $statusColor = $reservation->status === 'ready' ? 'text-green-400' : 'text-yellow-400';
                
                return [
                    'id' => $reservation->id,
                    'book_title' => $reservation->bibliographicRecord?->title ?? 'N/A',
                    'author' => $reservation->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $reservation->bookItem?->barcode ?? __('Chưa gán'),
                    'reservation_date' => $reservation->reservation_date->format('H:i d/m/Y'),
                    'expiry_date' => $reservation->expiry_date->format('d/m/Y'),
                    'status' => $reservation->status,
                    'status_display' => $statusDisplay,
                    'status_color' => $statusColor,
                    'pickup_branch' => $reservation->pickupBranch?->name ?? 'N/A',
                    'position' => $reservation->getPositionInQueue(),
                    'notes' => $reservation->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'patron' => [
                        'name' => $patron->display_name ?? $patron->user->name,
                        'code' => $patron->patron_code
                    ],
                    'reservations' => $formattedReservations,
                    'total_count' => $formattedReservations->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all active reservations (for staff view)
     */
    public function getAllActiveReservations(Request $request)
    {
        try {
            $reservations = Reservation::with(['patron.user', 'bibliographicRecord', 'bookItem', 'pickupBranch'])
                ->whereIn('status', ['pending', 'ready'])
                ->orderBy('reservation_date', 'desc')
                ->get();

            // Update expired reservations
            foreach ($reservations as $reservation) {
                if ($reservation->isExpired()) {
                    $reservation->update(['status' => 'expired']);
                    if ($reservation->bookItem) {
                        $reservation->bookItem->update(['status' => 'available']);
                    }
                }
            }

            // Refresh after updates
            $reservations = Reservation::with(['patron.user', 'bibliographicRecord', 'bookItem', 'pickupBranch'])
                ->whereIn('status', ['pending', 'ready'])
                ->orderBy('reservation_date', 'desc')
                ->get();

            $formattedReservations = $reservations->map(function($reservation) {
                $statusDisplay = $reservation->status === 'ready' ? __('Sẵn sàng nhận sách') : __('Trong danh sách chờ');
                $statusColor = $reservation->status === 'ready' ? 'text-green-400' : 'text-yellow-400';
                
                return [
                    'id' => $reservation->id,
                    'patron_name' => $reservation->patron->display_name ?? $reservation->patron->user->name,
                    'patron_code' => $reservation->patron->patron_code,
                    'book_title' => $reservation->bibliographicRecord?->title ?? 'N/A',
                    'author' => $reservation->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $reservation->bookItem?->barcode ?? __('Chưa gán'),
                    'reservation_date' => $reservation->reservation_date->format('H:i d/m/Y'),
                    'expiry_date' => $reservation->expiry_date->format('d/m/Y'),
                    'status' => $reservation->status,
                    'status_display' => $statusDisplay,
                    'status_color' => $statusColor,
                    'pickup_branch' => $reservation->pickupBranch?->name ?? 'N/A',
                    'position' => $reservation->getPositionInQueue(),
                    'is_expired' => $reservation->isExpired(),
                    'notes' => $reservation->notes
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'reservations' => $formattedReservations,
                    'total_count' => $formattedReservations->count(),
                    'ready_count' => $formattedReservations->where('status', 'ready')->count(),
                    'pending_count' => $formattedReservations->where('status', 'pending')->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fulfill reservation (convert to loan)
     */
    public function fulfillReservation(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|integer|exists:reservations,id'
        ]);

        try {
            DB::beginTransaction();

            $reservation = Reservation::findOrFail($validated['reservation_id']);

            if ($reservation->status !== 'ready') {
                throw new \Exception(__('Chỉ có thể thực hiện reservation sẵn sàng.'));
            }

            if (!$reservation->bookItem) {
                throw new \Exception(__('Reservation chưa được gán tài liệu.'));
            }

            $patron = $reservation->patron;
            $bookItem = $reservation->bookItem;

            // Check if patron can borrow
            if (!$patron->canBorrow()) {
                throw new \Exception(__('Bạn đọc không thể mượn sách. Vui lòng kiểm tra lại giới hạn mượn, hạn thẻ hoặc tiền phạt tồn đọng.'));
            }

            // Get policy
            $policy = $patron->patronGroup?->activePolicy;
            if (!$policy) {
                throw new \Exception(__('Chưa có quy định lưu thông đang hoạt động cho nhóm bạn đọc này.'));
            }

            // Create loan transaction
            $loan = LoanTransaction::create([
                'patron_detail_id' => $patron->id,
                'book_item_id' => $bookItem->id,
                'circulation_policy_id' => $policy->id,
                'loan_date' => Carbon::now(),
                'due_date' => Carbon::now()->addDays($policy->max_loan_days),
                'status' => 'borrowed',
                'loaned_by' => auth()->id(),
                'loan_branch_id' => $patron->branch_id ?? null
            ]);

            // Update book item status
            $bookItem->update(['status' => 'on_loan']);

            // Mark reservation as fulfilled
            $reservation->update([
                'status' => 'fulfilled',
                'pickup_date' => Carbon::now(),
                'notes' => ($reservation->notes ?? '') . "\n" . __('Đã cho mượn lúc :time', ['time' => Carbon::now()->format('H:i d/m/Y')])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Thực hiện reservation thành công - Đã cho mượn sách'),
                'data' => [
                    'loan_id' => $loan->id,
                    'patron_name' => $patron->display_name ?? $patron->user->name,
                    'book_title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                    'due_date' => $loan->due_date->format('d/m/Y'),
                    'loan_date' => $loan->loan_date->format('H:i d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display circulation tools page
     */
    public function tools()
    {
        return view('admin.circulation.tools');
    }

    /**
     * Get patron history
     */
    public function getPatronHistory(Request $request)
    {
        $validated = $request->validate([
            'patron_code' => 'required|string',
            'period' => 'nullable|in:all,30,90,365'
        ]);

        try {
            $patron = PatronDetail::where('patron_code', $validated['patron_code'])->firstOrFail();
            $period = $validated['period'] ?? 'all';

            // Prepare patron info
            $patronInfo = [
                'name' => $patron->display_name ?? $patron->user->name,
                'code' => $patron->patron_code,
                'group' => $patron->patronGroup?->name ?? 'N/A',
                'email' => $patron->user?->email,
                'phone' => $patron->phone ?? 'N/A',
                'status' => $patron->status,
                'registration_date' => $patron->created_at->format('d/m/Y')
            ];

            $history = [];
            $dateFilter = null;

            if ($period !== 'all') {
                $dateFilter = Carbon::now()->subDays($period);
            }

            // Get loan history
            $loansQuery = LoanTransaction::with(['bookItem.bibliographicRecord', 'loanedBy', 'returnedBy'])
                ->where('patron_detail_id', $patron->id);

            if ($dateFilter) {
                $loansQuery->where('created_at', '>=', $dateFilter);
            }

            $loans = $loansQuery->orderBy('created_at', 'desc')->get();

            foreach ($loans as $loan) {
                $history[] = [
                    'date' => $loan->loan_date->format('d/m/Y H:i'),
                    'type' => 'loan',
                    'type_display' => __('Mượn'),
                    'book_title' => $loan->bookItem->bibliographicRecord?->title ?? 'N/A',
                    'book_author' => $loan->bookItem->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $loan->bookItem->barcode,
                    'details' => __('Mượn đến') . ': ' . ($loan->due_date->format('d/m/Y')),
                    'staff_name' => $loan->loanedBy?->name,
                    'status' => $loan->status,
                    'status_display' => $this->getLoanStatusDisplay($loan->status)
                ];

                if ($loan->return_date) {
                    $history[] = [
                        'date' => $loan->return_date->format('d/m/Y H:i'),
                        'type' => 'return',
                        'type_display' => __('Trả'),
                        'book_title' => $loan->bookItem->bibliographicRecord?->title ?? 'N/A',
                        'book_author' => $loan->bookItem->bibliographicRecord?->author ?? 'N/A',
                        'barcode' => $loan->bookItem->barcode,
                        'details' => __('Trả sách'),
                        'staff_name' => $loan->returnedBy?->name,
                        'status' => 'returned',
                        'status_display' => __('Đã trả')
                    ];
                }
            }

            // Get reservation history
            $reservationsQuery = Reservation::with(['bibliographicRecord', 'bookItem'])
                ->where('patron_detail_id', $patron->id);

            if ($dateFilter) {
                $reservationsQuery->where('created_at', '>=', $dateFilter);
            }

            $reservations = $reservationsQuery->orderBy('created_at', 'desc')->get();

            foreach ($reservations as $reservation) {
                $statusDisplay = match($reservation->status) {
                    'pending' => __('Trong danh sách chờ'),
                    'ready' => __('Sẵn sàng nhận sách'),
                    'fulfilled' => __('Đã cho mượn'),
                    'cancelled' => __('Đã hủy'),
                    'expired' => __('Hết hạn'),
                    default => $reservation->status
                };

                $history[] = [
                    'date' => $reservation->reservation_date->format('d/m/Y H:i'),
                    'type' => 'reservation',
                    'type_display' => __('Reservation'),
                    'book_title' => $reservation->bibliographicRecord?->title ?? 'N/A',
                    'book_author' => $reservation->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $reservation->bookItem?->barcode ?? __('Chưa gán'),
                    'details' => $statusDisplay . ($reservation->pickupBranch ? ' - ' . $reservation->pickupBranch->name : ''),
                    'staff_name' => null,
                    'status' => $reservation->status,
                    'status_display' => $statusDisplay
                ];
            }

            // Get reading room history
            $readingQuery = ReadingRoomTransaction::with(['bookItem.bibliographicRecord', 'staff'])
                ->where('patron_detail_id', $patron->id);

            if ($dateFilter) {
                $readingQuery->where('created_at', '>=', $dateFilter);
            }

            $readingTransactions = $readingQuery->orderBy('created_at', 'desc')->get();

            foreach ($readingTransactions as $transaction) {
                $history[] = [
                    'date' => $transaction->checkout_time->format('d/m/Y H:i'),
                    'type' => 'reading_room',
                    'type_display' => __('Mượn đọc'),
                    'book_title' => $transaction->bookItem->bibliographicRecord?->title ?? 'N/A',
                    'book_author' => $transaction->bookItem->bibliographicRecord?->author ?? 'N/A',
                    'barcode' => $transaction->bookItem->barcode,
                    'details' => __('Mượn đọc tại chỗ') . ' - ' . __('Hạn trả') . ': ' . $transaction->due_time->format('H:i'),
                    'staff_name' => $transaction->staff?->name,
                    'status' => $transaction->status,
                    'status_display' => $transaction->status === 'overdue' ? __('Quá hạn') : __('Đã mượn')
                ];

                if ($transaction->checkin_time) {
                    $history[] = [
                        'date' => $transaction->checkin_time->format('d/m/Y H:i'),
                        'type' => 'reading_room_return',
                        'type_display' => __('Trả mượn đọc'),
                        'book_title' => $transaction->bookItem->bibliographicRecord?->title ?? 'N/A',
                        'book_author' => $transaction->bookItem->bibliographicRecord?->author ?? 'N/A',
                        'barcode' => $transaction->bookItem->barcode,
                        'details' => __('Thời gian mượn') . ': ' . $transaction->getDuration(),
                        'staff_name' => $transaction->staff?->name,
                        'status' => 'returned',
                        'status_display' => __('Đã trả')
                    ];
                }
            }

            // Sort by date
            usort($history, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            // Calculate statistics
            $stats = [
                'total_loans' => $loans->count(),
                'active_loans' => $loans->where('status', 'borrowed')->count(),
                'total_reservations' => $reservations->count(),
                'active_reservations' => $reservations->whereIn('status', ['pending', 'ready'])->count(),
                'total_reading_room' => $readingTransactions->count(),
                'active_reading_room' => $readingTransactions->where('status', 'active')->count()
            ];

            return response()->json([
                'success' => true,
                'patron_info' => $patronInfo,
                'history' => $history,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get loan status display text
     */
    private function getLoanStatusDisplay($status)
    {
        return match($status) {
            'borrowed' => __('Đã mượn'),
            'returned' => __('Đã trả'),
            'overdue' => __('Quá hạn'),
            'lost' => __('Đã mất'),
            'renewed' => __('Đã gia hạn'),
            default => $status
        };
    }

    /**
     * Advanced book search
     */
    public function advancedBookSearch(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:50',
            'status' => 'nullable|in:available,on_loan,reserved,in_reading_room,lost,damaged,maintenance',
            'publisher' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'branch_id' => 'nullable|exists:branches,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);

        try {
            $limit = $validated['limit'] ?? 20;
            $page = $validated['page'] ?? 1;

            // Build query
            $query = BookItem::with(['bibliographicRecord', 'branch', 'currentLoan.patron.user']);

            // Search by title
            if (!empty($validated['title'])) {
                $query->whereHas('bibliographicRecord', function($q) use ($validated) {
                    $q->where('title', 'like', '%' . $validated['title'] . '%');
                });
            }

            // Search by author
            if (!empty($validated['author'])) {
                $query->whereHas('bibliographicRecord', function($q) use ($validated) {
                    $q->where('author', 'like', '%' . $validated['author'] . '%');
                });
            }

            // Search by barcode
            if (!empty($validated['barcode'])) {
                $query->where('barcode', 'like', '%' . $validated['barcode'] . '%');
            }

            // Filter by status
            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            // Search by publisher
            if (!empty($validated['publisher'])) {
                $query->whereHas('bibliographicRecord', function($q) use ($validated) {
                    $q->where('publisher', 'like', '%' . $validated['publisher'] . '%');
                });
            }

            // Search by ISBN
            if (!empty($validated['isbn'])) {
                $query->whereHas('bibliographicRecord', function($q) use ($validated) {
                    $q->where('isbn', 'like', '%' . $validated['isbn'] . '%');
                });
            }

            // Filter by publication year
            if (!empty($validated['publication_year'])) {
                $query->whereHas('bibliographicRecord', function($q) use ($validated) {
                    $q->where('publication_year', $validated['publication_year']);
                });
            }

            // Filter by branch
            if (!empty($validated['branch_id'])) {
                $query->where('branch_id', $validated['branch_id']);
            }

            // Get total count for pagination
            $total = $query->count();

            // Get results with pagination
            $bookItems = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $limit)
                ->take($limit)
                ->get();

            // Format results
            $formattedBooks = $bookItems->map(function($bookItem) {
                return [
                    'id' => $bookItem->id,
                    'barcode' => $bookItem->barcode,
                    'title' => $bookItem->bibliographicRecord?->title ?? 'N/A',
                    'author' => $bookItem->bibliographicRecord?->author ?? 'N/A',
                    'publisher' => $bookItem->bibliographicRecord?->publisher ?? 'N/A',
                    'publication_year' => $bookItem->bibliographicRecord?->publication_year ?? 'N/A',
                    'isbn' => $bookItem->bibliographicRecord?->isbn ?? 'N/A',
                    'status' => $bookItem->status,
                    'status_display' => $this->getBookStatusDisplay($bookItem->status),
                    'status_color' => $this->getBookStatusColor($bookItem->status),
                    'branch' => $bookItem->branch?->name ?? 'N/A',
                    'location' => $bookItem->location ?? 'N/A',
                    'price' => $bookItem->bibliographicRecord?->price ?? 0,
                    'current_loan' => $bookItem->currentLoan ? [
                        'patron_name' => $bookItem->currentLoan->patron->display_name ?? $bookItem->currentLoan->patron->user->name,
                        'patron_code' => $bookItem->currentLoan->patron->patron_code,
                        'due_date' => $bookItem->currentLoan->due_date->format('d/m/Y'),
                        'is_overdue' => $bookItem->currentLoan->isOverdue()
                    ] : null,
                    'created_at' => $bookItem->created_at->format('d/m/Y')
                ];
            });

            // Get search statistics
            $stats = [
                'total_results' => $total,
                'current_page' => $page,
                'per_page' => $limit,
                'total_pages' => ceil($total / $limit),
                'has_next' => $page * $limit < $total,
                'has_prev' => $page > 1
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'books' => $formattedBooks,
                    'stats' => $stats,
                    'search_criteria' => array_filter($validated, function($value) {
                        return $value !== null && $value !== '';
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tìm kiếm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get book status color
     */
    private function getBookStatusColor($status)
    {
        return match($status) {
            'available' => 'green',
            'on_loan' => 'blue',
            'reserved' => 'yellow',
            'in_reading_room' => 'purple',
            'lost' => 'red',
            'damaged' => 'orange',
            'maintenance' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get reading room transactions for a patron
     */
    public function getReadingRoomTransactions(Request $request)
    {
        $patronCode = $request->get('patron_code');
        
        if (!$patronCode) {
            return response()->json(['success' => false, 'message' => 'Patron code is required']);
        }

        try {
            // Find patron
            $patron = PatronDetail::where('patron_code', $patronCode)->first();
            
            if (!$patron) {
                return response()->json(['success' => false, 'message' => 'Patron not found']);
            }

            // Get active reading room transactions
            $transactions = LoanTransaction::with(['bookItem.bibliographicRecord'])
                ->where('patron_detail_id', $patron->id)
                ->where('status', 'in_reading_room')
                ->orderBy('loan_date', 'desc')
                ->get();

            $formattedTransactions = $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'barcode' => $transaction->bookItem->barcode,
                    'title' => $transaction->bookItem->bibliographicRecord?->title ?? 'N/A',
                    'author' => $transaction->bookItem->bibliographicRecord?->author ?? 'N/A',
                    'loan_date' => $transaction->loan_date->format('d/m/Y H:i'),
                    'due_time' => $transaction->due_date->format('H:i'),
                    'is_overdue' => $transaction->due_date->lt(now()),
                ];
            });

            return response()->json([
                'success' => true,
                'patron' => [
                    'id' => $patron->id,
                    'patron_code' => $patron->patron_code,
                    'display_name' => $patron->display_name,
                ],
                'transactions' => $formattedTransactions,
                'total_count' => $formattedTransactions->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting reading room transactions', [
                'patron_code' => $patronCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Error loading transactions: ' . $e->getMessage()
            ], 500);
        }
    }
}
