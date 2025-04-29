<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingAddress extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'country_id',
        'email',
        'phone_number',
        'state_id',
        'full_address',
        'remark'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * User Model Linked.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Country Model Linked.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

     /**
     * State Model Linked.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
