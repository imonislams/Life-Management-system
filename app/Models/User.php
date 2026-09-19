<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'salary',
        'profile_photo_path',
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
            'salary' => 'decimal:2',
        ];
    }

    public function incomeRecords(): HasMany
    {
        return $this->hasMany(IncomeRecord::class);
    }

    public function expenseRecords(): HasMany
    {
        return $this->hasMany(ExpenseRecord::class);
    }

    /**
     * The user's primary savings goal (kept for backward compatibility with the
     * existing Money Management screens).
     */
    public function savingsGoal(): HasOne
    {
        return $this->hasOne(SavingsGoal::class)->latestOfMany();
    }

    /**
     * All savings goals/accounts owned by the user.
     */
    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoal::class);
    }

    public function savingsTransactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }

    public function habitActivities(): HasMany
    {
        return $this->hasMany(HabitActivity::class);
    }

    public function routineOccurrences(): HasMany
    {
        return $this->hasMany(RoutineOccurrence::class);
    }

    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * The user's settings row, created with defaults on first access.
     */
    public function settings(): Setting
    {
        return Setting::forUser($this->id);
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class);
    }

    public function habitCompletions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    public function currencies(): HasMany
    {
        return $this->hasMany(Currency::class);
    }

    /**
     * The user's single default currency (falls back to the first active one).
     */
    public function defaultCurrency(): ?Currency
    {
        return Currency::defaultFor($this->id);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(RoutineItem::class);
    }

    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class);
    }

    public function goalProgressUpdates(): HasMany
    {
        return $this->hasMany(GoalProgressUpdate::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    // ------------------------------------------------------------------
    // AI layer
    // ------------------------------------------------------------------

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class)->orderByDesc('last_message_at')->orderByDesc('id');
    }

    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }

    /**
     * The user's derived semantic-index rows (rebuildable, never authoritative).
     */
    public function aiEmbeddings(): HasMany
    {
        return $this->hasMany(AiEmbedding::class);
    }

    public function aiSetting(): HasOne
    {
        return $this->hasOne(AiSetting::class);
    }

    /**
     * The user's AI settings row, created with defaults on first access.
     */
    public function aiSettings(): AiSetting
    {
        return AiSetting::forUser($this->id);
    }

    public function aiTrainingSamples(): HasMany
    {
        return $this->hasMany(AiTrainingSample::class);
    }
}
