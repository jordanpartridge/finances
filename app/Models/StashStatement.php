<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StashStatement extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'portfolio_id',
        'account_number',
        'statement_period_start',
        'statement_period_end',
        'file_path',
        'file_hash',
        'opening_cash',
        'closing_cash',
        'opening_securities_value',
        'closing_securities_value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statement_period_start' => 'date',
            'statement_period_end' => 'date',
            'opening_cash' => 'decimal:2',
            'closing_cash' => 'decimal:2',
            'opening_securities_value' => 'decimal:2',
            'closing_securities_value' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Portfolio, $this>
     */
    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
