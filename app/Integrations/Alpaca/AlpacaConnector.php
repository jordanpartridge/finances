<?php

declare(strict_types=1);

namespace App\Integrations\Alpaca;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class AlpacaConnector extends Connector
{
    use AcceptsJson;

    /**
     * The base URL for the Alpaca Market Data API.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://data.alpaca.markets/v2';
    }

    /**
     * Default headers for all requests.
     */
    protected function defaultHeaders(): array
    {
        return [
            'APCA-API-KEY-ID' => config('services.alpaca.key'),
            'APCA-API-SECRET-KEY' => config('services.alpaca.secret'),
        ];
    }
}
