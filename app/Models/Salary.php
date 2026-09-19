<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency_id',
        'currency_code',
        'amount',
        'payment_day',
        'description',
        'employer',
        'salary_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_day' => 'integer',
        'salary_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The currency this salary is paid in.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
