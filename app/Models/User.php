<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->using(RoleUser::class)
            ->withPivot('id');
    }

    public function getSidebarTabs()
    {
        $roleIds = $this->roles->pluck('id')->toArray();

        return Sidebar::whereNull('parent_id')
            ->where('is_active', true)
            ->where(function ($query) use ($roleIds) {
                // Return parent if it is directly assigned to the role
                $query->whereHas('roles', function ($q) use ($roleIds) {
                    $q->whereIn('role_id', $roleIds);
                })
                    // OR if any of its children are assigned to the role
                    ->orWhereHas('children', function ($q) use ($roleIds) {
                    $q->whereHas('roles', function ($sq) use ($roleIds) {
                        $sq->whereIn('role_id', $roleIds);
                    });
                });
            })
            ->orderBy('order')
            ->get();
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'full_name',
        'description',
        'job_title',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function patronDetail()
    {
        return $this->hasOne(PatronDetail::class);
    }
}
