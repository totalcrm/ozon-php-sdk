<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V2\Posting;

use TotalCRM\OzonApi\Enum\SortDirection;
use TotalCRM\OzonApi\Enum\Status;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Service\GetOrderInterface;
use TotalCRM\OzonApi\Service\HasOrdersInterface;
use TotalCRM\OzonApi\Service\HasUnfulfilledOrdersInterface;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use DateTimeInterface;
use Throwable;

/**
 * Class CrossborderService
 * @package TotalCRM\OzonApi\Service\V2\Posting
 */
class CrossborderService extends AbstractService implements HasOrdersInterface, HasUnfulfilledOrdersInterface, GetOrderInterface
{
    private $path = '/v2/posting/crossborder';

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-cb_list
     * @param array $requestData
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'filter' => [],
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $filter = ArrayHelper::pick($requestData['filter'], ['since', 'to', 'status']);
        foreach (['since', 'to'] as $key) {
            if (isset($filter[$key]) && $filter[$key] instanceof DateTimeInterface) {
                $filter[$key] = $filter[$key]->format(DATE_RFC3339);
            }
        }
        $requestData['filter'] = $filter;

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * @see  https://cb-api.ozonru.me/apiref/en/#t-cb_unfulfilled_list
     *
     * @param array $requestData
     * @return array|string
     *
     * @throws Throwable
     * @throws OzonApiException
     * @todo fix {"error":{"code":"BAD_REQUEST","message":"Invalid request payload","data":[{"name":"status","code":"TOO_FEW_ELEMENTS","value":"[]","message":""}]}}
     */
    public function unfulfilledList(array $requestData = []): array
    {
        $default = [
            'status' => Status::getList(),
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        if (is_string($requestData['status'])) {
            $requestData['status'] = [$requestData['status']];
        }

        return $this->request('POST', "{$this->path}/unfulfilled/list", $requestData);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-cb_get
     * @param string $postingNumber
     * @param array $options
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function get(string $postingNumber, array $options = []): array
    {
        return $this->request('POST', "{$this->path}/get", ['posting_number' => $postingNumber]);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-cb_approve
     * @param string $postingNumber
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function approve(string $postingNumber): bool
    {
        return $this->request('POST', "{$this->path}/approve", ['posting_number' => $postingNumber]);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-cb_cancel
     *
     * @param string $postingNumber
     * @param array|string $sku
     * @param int $cancelReasonId
     * @param string $cancelReasonMessage
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function cancel(string $postingNumber, $sku, int $cancelReasonId, string $cancelReasonMessage = ''): bool
    {
        if (is_string($sku)) {
            $sku = [$sku];
        }
        $body = [
            'posting_number'        => $postingNumber,
            'sku'                   => $sku,
            'cancel_reason_id'      => $cancelReasonId,
            'cancel_reason_message' => $cancelReasonMessage,
        ];

        return $this->request('POST', "{$this->path}/cancel", $body);
    }

    public function cancelReasons(): array
    {
        return $this->request('POST', "{$this->path}/cancel-reason/list", '{}');
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_ship
     *
     * @param string $postingNumber
     * @param string $track
     * @param int $shippingProviderId
     * @param array $items
     * @return array list of postings IDs
     * @throws OzonApiException
     * @throws Throwable
     */
    public function ship(string $postingNumber, string $track, int $shippingProviderId, array $items): array
    {
        foreach ($items as &$item) {
            $item = ArrayHelper::pick($item, ['quantity', 'sku']);
        }

        $body = [
            'posting_number'       => $postingNumber,
            'tracking_number'      => $track,
            'shipping_provider_id' => $shippingProviderId,
            'items'                => $items,
        ];

        return $this->request('POST', "{$this->path}/ship", $body);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-cb_shipping_provider_list
     */
    public function shippingProviders(): array
    {
        return $this->request('POST', "{$this->path}/shipping-provider/list", '{}');
    }
}
