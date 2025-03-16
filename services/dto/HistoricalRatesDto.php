<?php

namespace app\services\dto;

class HistoricalRatesDto
{
    public string $base;
    public array $rates;
    public int $timestamp;

    public function __construct(string $base='RUB', array $rates = [], int $timestamp = 0)
    {
        $this->base = $base;
        $this->rates = $rates;
        $this->timestamp = $timestamp;
    }
}
?>