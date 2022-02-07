<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V3\Posting;

use TotalCRM\OzonApi\Enum\PostingScheme;
use TotalCRM\OzonApi\Enum\SortDirection;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Service\GetOrderInterface;
use TotalCRM\OzonApi\Service\HasOrdersInterface;
use TotalCRM\OzonApi\Service\HasUnfulfilledOrdersInterface;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use TotalCRM\OzonApi\Utils\WithResolver;
use DateTime;
use LogicException;
use Throwable;

/**
 * Class FbsService
 * @package TotalCRM\OzonApi\Service\V3\Posting
 */
class FbsService extends AbstractService implements HasOrdersInterface, HasUnfulfilledOrdersInterface, GetOrderInterface
{
    private $path = '/v3/posting/fbs';

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingList
     * @param array $requestData
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'with'   => WithResolver::getDefaults(3, PostingScheme::FBS),
            'filter' => [],
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['filter'] = ArrayHelper::pick($requestData['filter'], [
            'delivery_method_id',
            'order_id',
            'provider_id',
            'status',
            'since',
            'to',
            'warehouse_id',
        ]);

        //default filter parameters
        $requestData['filter'] = array_merge(
            [
                'since' => (new DateTime('now - 7 days'))->format(DATE_W3C),
                'to'    => (new DateTime('now'))->format(DATE_W3C),
            ],
            $requestData['filter']
        );

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * @param array $requestData
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function unfulfilledList(array $requestData = []): array
    {
        $default = [
            'with'   => WithResolver::getDefaults(3, PostingScheme::FBS),
            'filter' => [],
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['filter'] = ArrayHelper::pick($requestData['filter'], [
            'cutoff_from',
            'cutoff_to',
            'delivering_date_from',
            'delivering_date_to',
            'delivery_method_id',
            'provider_id',
            'status',
            'warehouse_id',
        ]);

        if (
            (empty($requestData['filter']['cutoff_from']) && empty($requestData['filter']['cutoff_to'])) &&
            (empty($requestData['filter']['delivering_date_from']) && empty($requestData['filter']['delivering_date_to']))
        ) {
            throw new LogicException('Not defined mandatory filter date ranges `cutoff` or `delivering_date`');
        }

        return $this->request('POST', "{$this->path}/unfulfilled/list", $requestData);
    }

    /**
     * @param string $postingNumber
     * @param array $options
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function get(string $postingNumber, array $options = []): array
    {
        return $this->request('POST', "{$this->path}/get", [
            'posting_number' => $postingNumber,
            'with'           => WithResolver::resolve($options, 3, PostingScheme::FBS),
        ]);
    }
}
