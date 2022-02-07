<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V2\Posting;

use TotalCRM\OzonApi\Enum\PostingScheme;
use TotalCRM\OzonApi\Enum\SortDirection;
use TotalCRM\OzonApi\Enum\Status;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Service\GetOrderInterface;
use TotalCRM\OzonApi\Service\HasOrdersInterface;
use TotalCRM\OzonApi\Service\HasUnfulfilledOrdersInterface;
use TotalCRM\OzonApi\TypeCaster;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use TotalCRM\OzonApi\Utils\WithResolver;
use DateTimeInterface;
use Throwable;

/**
 * Class FbsService
 * @package TotalCRM\OzonApi\Service\V2\Posting
 */
class FbsService extends AbstractService implements HasOrdersInterface, HasUnfulfilledOrdersInterface, GetOrderInterface
{
    private $path = '/v2/posting/fbs';

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_list
     *
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
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_unfulfilled_list
     * @param array $requestData
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function unfulfilledList(array $requestData = []): array
    {
        $default = [
            'with'    => WithResolver::resolve($requestData, 2, PostingScheme::FBS, __FUNCTION__),
            'status'  => Status::getList(),
            'sort_by' => 'updated_at',
            'dir'     => SortDirection::ASC,
            'offset'  => 0,
            'limit'   => 10,
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
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_get
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
            'with'           => WithResolver::resolve($options, 2, PostingScheme::FBS),
        ]);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_ship
     *
     * @param array $packages
     * @param string $postingNumber
     * @return array list of postings IDs
     * @throws OzonApiException
     * @throws Throwable
     */
    public function ship(array $packages, string $postingNumber): array
    {
        foreach ($packages as &$package) {
            $package = ArrayHelper::pick($package, ['items']);
        }

        $body = [
            'packages'       => $packages,
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', "{$this->path}/ship", $body);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_package_label
     *
     * @param array|string $postingNumber
     * @return string
     * @throws OzonApiException
     * @throws Throwable
     */
    public function packageLabel($postingNumber): string
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        return $this->request('POST', "{$this->path}/package-label", ['posting_number' => $postingNumber], false);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_arbitration_title
     *
     * @param array|string $postingNumber
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function arbitration($postingNumber): bool
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        $result = $this->request('POST', "{$this->path}/arbitration", ['posting_number' => $postingNumber]);

        return 'true' === $result;
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbs_cancel_title
     * @param string $postingNumber
     * @param int $cancelReasonId
     * @param string|null $cancelReasonMessage
     * @return bool
     * @throws OzonApiException
     * @throws Throwable
     */
    public function cancel(string $postingNumber, int $cancelReasonId, string $cancelReasonMessage = null): bool
    {
        $body = [
            'posting_number'        => $postingNumber,
            'cancel_reason_id'      => $cancelReasonId,
            'cancel_reason_message' => $cancelReasonMessage,
        ];
        $result = $this->request('POST', "{$this->path}/cancel", $body);

        return 'true' === $result;
    }

    /**
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function cancelReasons(): array
    {
        return $this->request('POST', "{$this->path}/cancel-reason/list", '{}'); //todo свериться с исправленной документацией
    }

    /**
     * @param string|array $postingNumber
     *
     * @return array|string
     *
     * @throws OzonApiException
     * @throws Throwable
     * @todo return true
     */
    public function awaitingDelivery($postingNumber)
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        $body = [
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', "{$this->path}/awaiting-delivery", $body);
    }

    public function getByBarcode(string $barcode): array
    {
        return $this->request('POST', "{$this->path}/get-by-barcode", ['barcode' => $barcode]);
    }

    //<editor-fold desc="/act">

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFBSActCreate
     *
     * @param array $params [containers_count, delivery_method_id]
     * @return int
     * @throws OzonApiException
     * @throws Throwable
     */
    public function actCreate(array $params): int
    {
        $config = [
            'containers_count'   => 'int',
            'delivery_method_id' => 'int',
        ];

        $params = ArrayHelper::pick($params, array_keys($config));
        $params = TypeCaster::castArr($params, $config);
        $result = $this->request('POST', "{$this->path}/act/create", $params);

        return $result['id'];
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-section_postings_fbs_act_check_title
     * @param int $id
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function actCheckStatus(int $id): array
    {
        return $this->request('POST', "{$this->path}/act/check-status", ['id' => $id]);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-section_postings_fbs_act_get_title
     * @param int $id
     * @return string
     * @throws OzonApiException
     * @throws Throwable
     */
    public function actGetPdf(int $id): string
    {
        return $this->request('POST', "{$this->path}/act/get-pdf", ['id' => $id], false);
    }

    /**
     * @param int $id
     * @return string
     * @throws OzonApiException
     * @throws Throwable
     */
    public function actGetContainerLabels(int $id): string
    {
        return $this->request('POST', "{$this->path}/act/get-container-labels", ['id' => $id], false);
    }

    /**
     * @param array|string $postingNumber
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function delivered($postingNumber): array
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        $body = [
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', '/v2/fbs/posting/delivered', $body);
    }

    /**
     * @param array|string $postingNumber
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function delivering($postingNumber): array
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        $body = [
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', '/v2/fbs/posting/delivering', $body);
    }

    /**
     * @param array|string $postingNumber
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function lastMile($postingNumber): array
    {
        if (is_string($postingNumber)) {
            $postingNumber = [$postingNumber];
        }

        $body = [
            'posting_number' => $postingNumber,
        ];

        return $this->request('POST', '/v2/fbs/posting/last-mile', $body);
    }

    public function setTrackingNumber(array $trackingNumbers): array
    {
        if (isset($trackingNumbers['posting_number']) || isset($trackingNumbers['tracking_number'])) {
            $trackingNumbers = [$trackingNumbers];
        }

        foreach ($trackingNumbers as &$tn) {
            $tn = ArrayHelper::pick($tn, ['posting_number', 'tracking_number']);
        }

        $body = [
            'tracking_numbers' => $trackingNumbers,
        ];

        return $this->request('POST', '/v2/fbs/posting/tracking-number/set', $body);
    }
}
