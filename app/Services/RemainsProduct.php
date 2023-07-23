<?php

namespace App\Services;

use App\Services\Interfaces\RemainsStrategyInterface;
use Exception;

class RemainsProduct
{
    private RemainsStrategyInterface $strategy;

    /**
     * @param RemainsStrategyInterface $strategy
     * @return void
     */
    public function setStrategy(RemainsStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @param array $products
     * @param string $shopMobileBackendId
     * @return array
     * @throws Exception
     */
    public function getRemains(array $products, string $shopMobileBackendId): array
    {
        return $this->strategy->getRemains($products, $shopMobileBackendId);
    }
}
