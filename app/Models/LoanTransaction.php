<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoanTransaction extends Model
{
    protected $fillable = [
        'patron_detail_id',
        'book_item_id',
        'circulation_policy_id',
        'loan_date',
        'due_date',
        'return_date',
        'renewal_count',
        'last_renewal_date',
        'status',
        'loaned_by',
        'returned_to',
        'loan_branch_id',
        'return_branch_id',
        'notes'
    ];

    protected $casts = [
        'loan_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
        'last_renewal_date' => 'datetime',
    ];

    public function patron()
    {
        return $this->belongsTo(PatronDetail::class, 'patron_detail_id');
    }

    public function bookItem()
    {
        return $this->belongsTo(BookItem::class);
    }

    public function policy()
    {
        return $this->belongsTo(CirculationPolicy::class, 'circulation_policy_id');
    }

    public function loanedByUser()
    {
        return $this->belongsTo(User::class, 'loaned_by');
    }

    public function returnedToUser()
    {
        return $this->belongsTo(User::class, 'returned_to');
    }

    public function loanBranch()
    {
        return $this->belongsTo(Branch::class, 'loan_branch_id');
    }

    public function returnBranch()
    {
        return $this->belongsTo(Branch::class, 'return_branch_id');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'returned') {
            return false;
        }
        return Carbon::now()->gt($this->due_date);
    }

    /**
     * Get overdue days
     */
    public function getOverdueDays(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return (int) ceil(Carbon::now()->diffInDays($this->due_date));
    }

    /**
     * Check if can renew
     */
    public function canRenew(): bool
    {
        if ($this->status !== 'borrowed') {
            return false;
        }

        if ($this->isOverdue()) {
            return false;
        }

        if (!$this->policy) {
            return false;
        }

        return $this->renewal_count < $this->policy->max_renewals;
    }

    /**
     * Scope for active loans
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'borrowed');
    }

    /**
     * Scope for overdue loans
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
                     ->where('due_date', '<', Carbon::now());
    }
}
