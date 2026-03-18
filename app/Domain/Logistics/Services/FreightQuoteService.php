<?php

namespace App\Domain\Logistics\Services;

use App\Domain\Logistics\DataTransferObjects\FreightQuoteRequestData;
use App\Domain\Logistics\DataTransferObjects\FreightQuoteResultData;

class FreightQuoteService
{
    public function __construct(
        private readonly LogisticsService $logisticsService,
    ) {
    }

    public function quote(FreightQuoteRequestData $request): FreightQuoteResultData
    {
        return $this->logisticsService->quoteShipping($request);
    }
}

