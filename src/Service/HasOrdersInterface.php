<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service;

/**
 * Interface HasOrdersInterface
 * @package TotalCRM\OzonApi\Service
 */
interface HasOrdersInterface
{
    public function list(array $requestData): array;
}
