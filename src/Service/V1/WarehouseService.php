<?php

declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V1;

use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use Throwable;

/**
 * Class WarehouseService
 * @package TotalCRM\OzonApi\Service\V1
 */
class WarehouseService extends AbstractService
{
    private $path = '/v1/warehouse';

    /**
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     */
    public function list(): array
    {
        return $this->request('POST', "{$this->path}/list");
    }
}
