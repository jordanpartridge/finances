<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $quoted_at
 */
class Price extends Model
{
    /** @use HasFactory<\Database\Factories\PriceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['ticker', 'bid', 'ask', 'last', 'quoted_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bid' => 'decimal:2',
            'ask' => 'decimal:2',
            'last' => 'decimal:2',
            'quoted_at' => 'datetime',
        ];
    }

    /**
     * Get the midpoint price between bid and ask.
     */
    public function midpoint(): string
    {
        $bid = (float) $this->bid;
        $ask = (float) $this->ask;

        return (string) (($bid + $ask) / 2);
    }
}
