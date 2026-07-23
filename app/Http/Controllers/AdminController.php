<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatronDetail;
use App\Models\BibliographicRecord;
use App\Models\BookItem;
use App\Models\LoanTransaction;
use App\Models\Reservation;
use App\Models\Fine;
use App\Models\DigitalResource;
use App\Models\OpenEducationalResource;
use App\Models\News;
use App\Models\LibraryEntry;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        // ── STAT CARDS ──
        $totalPatrons      = PatronDetail::count();
        $activePatrons     = PatronDetail::where('card_status', 'normal')->count();
        $lockedPatrons     = PatronDetail::where('card_status', 'locked')->count();
        $totalRecords      = BibliographicRecord::count();
        $approvedRecords   = BibliographicRecord::where('status', 'approved')->count();
        $totalBookItems    = BookItem::count();
        $availableItems    = BookItem::where('status', 'available')->count();

        $activeLoans       = LoanTransaction::where('status', 'borrowed')->count();
        $overdueLoans      = LoanTransaction::where('status', 'borrowed')
                                ->where('due_date', '<', Carbon::now())->count();
        $returnedLoans     = LoanTransaction::where('status', 'returned')->count();
        $totalLoans        = LoanTransaction::count();

        $pendingReservations = Reservation::where('status', 'pending')->count();
        $totalReservations   = Reservation::count();

        $unpaidFines       = Fine::whereIn('status', ['pending', 'partial'])->count();
        $totalFineAmount   = Fine::whereIn('status', ['pending', 'partial'])->sum('amount');

        $totalDigitalRes   = DigitalResource::where('status', 'published')->count();
        $totalOER          = OpenEducationalResource::where('is_active', true)->count();
        $totalNews         = News::where('status', 'published')->count();

        // ── LOAN TREND (last 12 months) ──
        $loanTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $loanTrend[] = [
                'label'    => $month->format('m/Y'),
                'borrowed' => LoanTransaction::whereYear('loan_date', $month->year)
                                ->whereMonth('loan_date', $month->month)->count(),
                'returned' => LoanTransaction::where('status', 'returned')
                                ->whereYear('return_date', $month->year)
                                ->whereMonth('return_date', $month->month)->count(),
            ];
        }

        // ── TOP BORROWERS ──
        $topBorrowers = PatronDetail::withCount('loanTransactions')
            ->having('loan_transactions_count', '>', 0)
            ->orderByDesc('loan_transactions_count')
            ->with(['user', 'patronGroup'])
            ->take(10)
            ->get();

        // ── RECENT LOANS ──
        $recentLoans = LoanTransaction::with([
                'patron.user',
                'bookItem.bibliographicRecord.fields.subfields'
            ])
            ->latest('loan_date')
            ->take(8)
            ->get();

        // ── OVERDUE LIST ──
        $overdueList = LoanTransaction::with([
                'patron.user',
                'bookItem.bibliographicRecord.fields.subfields'
            ])
            ->where('status', 'borrowed')
            ->where('due_date', '<', Carbon::now())
            ->orderBy('due_date')
            ->take(10)
            ->get();

        // ── PATRON GROUP DISTRIBUTION ──
        $patronGroups = PatronDetail::selectRaw('patron_group_id, count(*) as total')
            ->groupBy('patron_group_id')
            ->with('patronGroup')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->patronGroup->name ?? 'Chưa phân nhóm',
                'count' => $item->total,
            ]);

        // ── BOOK STATUS DISTRIBUTION ──
        $bookStatusDist = BookItem::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── PENDING RESERVATIONS ──
        $pendingResList = Reservation::with([
                'patron.user',
                'bibliographicRecord.fields.subfields'
            ])
            ->where('status', 'pending')
            ->latest('reservation_date')
            ->take(5)
            ->get();

        // ── LATEST USERS ──
        $latestUsers = User::with('roles')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalPatrons', 'activePatrons', 'lockedPatrons',
            'totalRecords', 'approvedRecords', 'totalBookItems', 'availableItems',
            'activeLoans', 'overdueLoans', 'returnedLoans', 'totalLoans',
            'pendingReservations', 'totalReservations',
            'unpaidFines', 'totalFineAmount',
            'totalDigitalRes', 'totalOER', 'totalNews',
            'loanTrend', 'topBorrowers', 'recentLoans', 'overdueList',
            'patronGroups', 'bookStatusDist', 'pendingResList', 'latestUsers'
        ));
    }

    /**
     * Redirect to dashboard
     */
    public function redirect()
    {
        return redirect()->route('admin.dashboard');
    }
}
