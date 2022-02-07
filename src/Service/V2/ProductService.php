<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V2;

use InvalidArgumentException;
use Throwable;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Exception\ProductValidatorException;
use TotalCRM\OzonApi\ProductValidator;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\TypeCaster;
use TotalCRM\OzonApi\Utils\ArrayHelper;

/**
 * Class ProductService
 * @package TotalCRM\OzonApi\Service\V2
 */
class ProductService extends AbstractService
{
    private $path = '/v2/product';

    /**
     * Creates product page in our system.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_product_import
     *
     * @param array $income Single item structure or array of items
     *
     * @param bool $validateBeforeSend
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     * @throws ProductValidatorException
     */
    public function import(array $income, bool $validateBeforeSend = true): array
    {
        if (!array_key_exists('items', $income)) {
            $income = $this->ensureCollection($income);
            $income = ['items' => $income];
        }

        $income = ArrayHelper::pick($income, ['items']);

        if ($validateBeforeSend) {
            $pv = new ProductValidator('create', 2);
            foreach ($income['items'] as &$item) {
                $item = $pv->validateItem($item);
            }
        }

        return $this->request('POST', "{$this->path}/import", $income);
    }

    /**
     * Receive product info.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_products_info
     *
     * @param array $query ['product_id', 'sku', 'offer_id']
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function info(array $query): array
    {
        $query = ArrayHelper::pick($query, ['product_id', 'sku', 'offer_id']);
        $query = TypeCaster::castArr($query, ['product_id' => 'int', 'sku' => 'int', 'offer_id' => 'str']);

        return $this->request('POST', "{$this->path}/info", $query);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_products_info_attributes
     * @param array $filter
     * @param int $page
     * @param int $pageSize
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function infoAttributes(array $filter, int $page = 1, int $pageSize = 100): array
    {
        $keys = ['offer_id', 'product_id'];
        $filter = ArrayHelper::pick($filter, $keys);

        foreach ($keys as $k) {
            if (isset($filter[$k]) && !is_array($filter[$k])) {
                $filter[$k] = [$filter[$k]];
            }
        }

        if (isset($filter['offer_id'])) {
            $filter['offer_id'] = array_map('strval', $filter['offer_id']);
        }

        $query = [
            'filter'    => $filter,
            'page'      => $page,
            'page_size' => $pageSize,
        ];

        return $this->request('POST', "{$this->path}s/info/attributes", $query);
    }

    /**
     * Receive products stocks info.
     *
     * @param array $pagination ['page', 'page_size']
     *
     * @return array {items: array, total: int}
     *
     * @throws OzonApiException
     * @throws Throwable
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoPricesV2
     */
    public function infoStocks(array $pagination = []): array
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', "{$this->path}/info/stocks", $pagination);
    }

    /**
     * Receive products prices info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoListV2
     *
     * @param array $pagination [page, page_size]
     *
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function infoPrices(array $pagination = []): array
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', '/v1/product/info/prices', $pagination);
    }

    /**
     * Update product stocks.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductsStocksV2
     *
     * @param array $input
     *
     * @return array
     * @throws OzonApiException
     * @throws Throwable
     */
    public function importStocks(array $input): array
    {
        if (empty($input)) {
            throw new InvalidArgumentException('Empty stocks data');
        }

        if ($this->isAssoc($input) && !isset($input['stocks'])) {// if it one price
            $input = ['stocks' => [$input]];
        } else if (!$this->isAssoc($input)) {// if it plain array on prices
            $input = ['stocks' => $input];
        }

        if (!isset($input['stocks'])) {
            throw new InvalidArgumentException('');
        }

        foreach ($input['stocks'] as $i => &$s) {
            if (!$s = ArrayHelper::pick($s, ['product_id', 'offer_id', 'stock', 'warehouse_id'])) {
                throw new InvalidArgumentException('Invalid stock data at index '.$i);
            }

            $s = TypeCaster::castArr(
                $s,
                [
                    'product_id' => 'int',
                    'offer_id'   => 'str',
                    'stock'      => 'int',
                    'warehouse_id' => 'int'
                ]
            );
        }

        return $this->request('POST', "{$this->path}s/stocks", $input);
    }

    /**
     * @param array $input one of: <br>
     *                     {products:[{offer_id: "str"}, ...]}<br>
     *                     [{offer_id: "str"}, ...]<br>
     *                     {offer_id: "str"}<br>
     *
     * @return mixed
     * @throws OzonApiException
     * @throws Throwable
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_DeleteProducts
     */
    public function delete(array $input)
    {
        if ($this->isAssoc($input) && !isset($input['products'])) {// if it one price
            $input = ['products' => [$input]];
        } else if (!$this->isAssoc($input)) {// if it plain array on prices
            $input = ['products' => $input];
        }

        if (!isset($input['products'])) {
            throw new InvalidArgumentException('');
        }

        foreach ($input['products'] as $i => &$s) {
            if (!$s = ArrayHelper::pick($s, ['offer_id'])) {
                throw new InvalidArgumentException('Invalid stock data at index '.$i);
            }

            $s = TypeCaster::castArr(
                $s,
                [
                    'offer_id' => 'str',
                ]
            );
        }

        return $this->request('POST', "{$this->path}s/delete", $input);
    }
}
