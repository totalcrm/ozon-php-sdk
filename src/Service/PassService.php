<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service;

use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\TypeCaster;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use Throwable;

/**
 * Class PassService
 * @package TotalCRM\OzonApi\Service
 */
class PassService extends AbstractService
{
    private const CONF = [
        'car_model'            => 'string',
        'car_number'           => 'string',
        'driver_name'          => 'string',
        'driver_patronymic'    => 'string',
        'driver_surname'       => 'string',
        'end_unloading_time'   => 'string',
        'is_regular_pass'      => 'boolean',
        'start_unloading_time' => 'string',
        'telephone'            => 'string',
        'trailer_number'       => 'string',
        'unload_date'          => 'string',
    ];

    /**
     * @param array $data
     * @return mixed
     * @throws Throwable
     * @throws OzonApiException
     */
    public function create(array $data)
    {
        ArrayHelper::pick($data, array_keys(self::CONF));
        TypeCaster::castArr($data, self::CONF);

        return $this->request('POST', '/pass/create', $data, true, false);
    }

    /**
     * @return mixed
     * @throws OzonApiException
     * @throws Throwable
     */
    public function getLast()
    {
        return $this->request('POST', '/pass/get/last', '{}', true, false);
    }

    /**
     * @param array $data
     * @return mixed
     * @throws OzonApiException
     * @throws Throwable
     */
    public function update(array $data)
    {
        ArrayHelper::pick($data, array_keys(self::CONF));
        TypeCaster::castArr($data, self::CONF);

        return $this->request('POST', '/pass/update', $data);
    }
}
