<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V1;

use Throwable;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Utils\ArrayHelper;

/**
 * Class ActionsService
 * @package TotalCRM\OzonApi\Service\V1
 * @see    https://cb-api.ozonru.me/apiref/en/#t-title_action
 */
class ActionsService extends AbstractService
{
    private $path = '/v1/actions';

    /**
     * @return string
     */
    protected function getDefaultHost(): string
    {
        return 'https://seller-api.ozon.ru/';
    }

    /**
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_available
     */
    public function list(): array
    {
        return $this->request('GET', $this->path);
    }

    /**
     * @param int $actionId
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_available_products
     */
    public function candidates(int $actionId, int $offset = 0, int $limit = 10): array
    {
        $body = [
            'action_id' => $actionId,
            'offset'    => $offset,
            'limit'     => $limit,
        ];

        return $this->request('POST', "{$this->path}/candidates", $body);
    }

    /**
     * @param int $actionId
     * @param int $offset
     * @param int $limit
     * @return mixed
     * @throws OzonApiException
     * @throws Throwable
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_products
     */
    public function products(int $actionId, int $offset = 0, int $limit = 10)
    {
        $body = [
            'action_id' => $actionId,
            'offset'    => $offset,
            'limit'     => $limit,
        ];

        return $this->request('POST', "{$this->path}/products", $body);
    }

    /**
     * @param int $actionId
     * @param array $products
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_add_products
     */
    public function productsActivate(int $actionId, array $products): array
    {
        $products = $this->ensureCollection($products);
        foreach ($products as &$p) {
            $p = ArrayHelper::pick($p, ['product_id', 'action_price']);
        }
        unset($p);

        $body = [
            'action_id' => $actionId,
            'products'  => $products,
        ];

        return $this->request('POST', "{$this->path}/products/activate", $body);
    }

    /**
     * This method allows to delete products from the promotional offer.
     *
     * @param int $actionId
     * @param array $productIds
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_add_products
     */
    public function productsDeactivate(int $actionId, array $productIds): array
    {
        $body = [
            'action_id'   => $actionId,
            'product_ids' => $productIds,
        ];

        return $this->request('POST', "{$this->path}/products/deactivate", $body);
    }
}
