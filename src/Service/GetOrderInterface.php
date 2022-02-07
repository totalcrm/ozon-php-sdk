<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service;

/**
 * Interface GetOrderInterface
 * @package TotalCRM\OzonApi\Service
 */
interface GetOrderInterface
{
    public function get(string $postingNumber, array $options = []): array;
}
