<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatronDetail extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'patron_group_id',
        'patron_code',
        'id_card',
        'mssv',
        'phone_contact',
        'display_name',
        'card_status',
        'is_read_only',
        'is_waiting_for_print',
        'dob',
        'gender',
        'profile_image',
        'school_name',
        'batch',
        'department',
        'position_class',
        'phone',
        'fax',
        'branch',
        'classification',
        'card_fee',
        'deposit',
        'balance',
        'registration_date',
        'expiry_date',
        'creator_id',
        'notes',
        'is_reading_room_only',
        'add_to_print_queue'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(PatronAddress::class);
    }

    public function patronGroup()
    {
        return $this->belongsTo(PatronGroup::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function loanTransactions()
    {
        return $this->hasMany(LoanTransaction::class);
    }

    public function activeLoans()
    {
        return $this->hasMany(LoanTransaction::class)->where('status', 'borrowed');
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function unpaidFines()
    {
        return $this->hasMany(Fine::class)->whereIn('status', ['pending', 'partial']);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get active circulation policy
     */
    public function activePolicy()
    {
        return $this->belongsTo(CirculationPolicy::class, 'circulation_policy_id');
    }

    /**
     * Get total outstanding fine amount
     */
    public function getTotalOutstandingFineAttribute(): float
    {
        return $this->unpaidFines->sum(function ($fine) {
            return $fine->amount - $fine->paid_amount - $fine->waived_amount;
        });
    }

    /**
     * Check if patron can borrow items
     */
    public function canBorrow(): bool
    {
        // Check if card is locked
        if ($this->card_status !== 'normal') {
            return false;
        }

        // Check if expired
        if ($this->expiry_date && now()->greaterThan($this->expiry_date)) {
            return false;
        }

        // Get circulation policy
        $policy = $this->patronGroup->circulationPolicies()->where('is_active', true)->first();
        if (!$policy) {
            return false;
        }

        // Check max items
        if ($this->activeLoans()->count() >= $policy->max_items) {
            return false;
        }

        // Check outstanding fines
        if ($this->total_outstanding_fine > $policy->max_outstanding_fine) {
            return false;
        }

        return true;
    }

    // Relationships for new features
    public function transactions()
    {
        return $this->hasMany(PatronTransaction::class);
    }

    public function lockHistory()
    {
        return $this->hasMany(PatronLockHistory::class);
    }

    public function printQueue()
    {
        return $this->hasMany(PrintQueue::class);
    }

    /**
     * Get the display name for the patron
     * Priority: patron_details.display_name > users.name > patron_code
     */
    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name'] ?? $this->user?->name ?? $this->patron_code;
    }

    // Methods for patron management
    public function lock(string $reason, int $lockedBy): bool
    {
        $this->update(['card_status' => 'locked']);

        $this->lockHistory()->create([
            'action' => PatronLockHistory::ACTION_LOCK,
            'reason' => $reason,
            'locked_by' => $lockedBy,
            'locked_at' => now(),
        ]);

        ActivityLog::log('patron_locked', $this, [
            'reason' => $reason,
            'locked_by' => $lockedBy,
        ]);

        return true;
    }

    public function unlock(string $reason, int $unlockedBy, float $unlockFee = 0): bool
    {
        $this->update(['card_status' => 'normal']);

        $this->lockHistory()->create([
            'action' => PatronLockHistory::ACTION_UNLOCK,
            'reason' => $reason,
            'unlock_fee' => $unlockFee,
            'unlocked_by' => $unlockedBy,
            'unlocked_at' => now(),
        ]);

        // Deduct unlock fee if applicable
        if ($unlockFee > 0) {
            $this->addTransaction(
                PatronTransaction::TYPE_FEE,
                $unlockFee,
                'Phí mở khóa thẻ',
                "Phí mở khóa thẻ cho độc giả {$this->display_name}",
                null,
                $unlockedBy
            );
        }

        ActivityLog::log('patron_unlocked', $this, [
            'reason' => $reason,
            'unlocked_by' => $unlockedBy,
            'unlock_fee' => $unlockFee,
        ]);

        return true;
    }

    public function addTransaction(string $type, float $amount, string $description, string $notes = null, string $paymentMethod = null, int $createdBy = null): PatronTransaction
    {
        $balanceBefore = $this->balance;
        $balanceAfter = $balanceBefore;

        // Calculate new balance based on transaction type
        if ($type === PatronTransaction::TYPE_DEPOSIT) {
            $balanceAfter += $amount;
        } else {
            $balanceAfter -= $amount;
        }

        // Create transaction
        $transaction = $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'notes' => $notes,
            'payment_method' => $paymentMethod,
            'created_by' => $createdBy,
        ]);

        // Update patron balance
        $this->update(['balance' => $balanceAfter]);

        // Log activity
        ActivityLog::log('patron_transaction', $this, [
            'transaction_type' => $type,
            'amount' => $amount,
            'description' => $description,
        ]);

        return $transaction;
    }

    public function addToPrintQueue(int $addedBy, int $priority = 0, string $notes = null): PrintQueue
    {
        // Check if already in queue
        $existing = $this->printQueue()->pending()->first();
        if ($existing) {
            return $existing;
        }

        $queueItem = $this->printQueue()->create([
            'priority' => $priority,
            'notes' => $notes,
            'added_by' => $addedBy,
        ]);

        ActivityLog::log('patron_added_to_print_queue', $this, [
            'priority' => $priority,
            'added_by' => $addedBy,
        ]);

        return $queueItem;
    }

    public function removeFromPrintQueue(): bool
    {
        $queueItem = $this->printQueue()->pending()->first();
        if ($queueItem) {
            $queueItem->cancel();

            ActivityLog::log('patron_removed_from_print_queue', $this);

            return true;
        }

        return false;
    }

    public function isInPrintQueue(): bool
    {
        return $this->printQueue()->pending()->exists();
    }

    public function isLocked(): bool
    {
        return $this->card_status === 'locked';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && now()->greaterThan($this->expiry_date);
    }
}
