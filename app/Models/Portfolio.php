<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    /** @use HasFactory<\Database\Factories\PortfolioFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'description', 'type'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Get all positions for this portfolio.
     *
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Calculate the total value of the portfolio based on positions.
     * Value = sum of all (position.shares * latest_price_from_price_table)
     */
    public function calculateValue(): float
    {
        return (float) $this->positions->sum(function (Position $position) {
            $latestPrice = Price::where('ticker', $position->ticker)
                ->orderByDesc('quoted_at')
                ->first();

            $currentPrice = $latestPrice ? (float) $latestPrice->midpoint() : 0;

            return (float) $position->shares * $currentPrice;
        });
    }
}
