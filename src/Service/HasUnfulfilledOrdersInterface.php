<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service;

/**
 * Interface HasUnfulfilledOrdersInterface
 * @package TotalCRM\OzonApi\Service
 */
interface HasUnfulfilledOrdersInterface
{
    public function unfulfilledList(array $requestData = []): array;
}
