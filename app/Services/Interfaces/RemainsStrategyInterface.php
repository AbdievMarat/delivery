<?php

namespace App\Services\Interfaces;

interface RemainsStrategyInterface
{
    /**
     * @param array $products
     * @param string $shopMobileBackendId
     * @return array
     */
    public function getRemains(array $products, string $shopMobileBackendId): array;
}
