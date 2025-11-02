<?php

declare(strict_types=1);

namespace App\Integrations\Alpaca\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLatestQuote extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private string $symbol,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return "/stocks/{$this->symbol}/quotes/latest";
    }
}
