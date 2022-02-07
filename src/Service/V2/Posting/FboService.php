<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V2\Posting;

use TotalCRM\OzonApi\Enum\PostingScheme;
use TotalCRM\OzonApi\Enum\SortDirection;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Service\GetOrderInterface;
use TotalCRM\OzonApi\Service\HasOrdersInterface;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use TotalCRM\OzonApi\Utils\WithResolver;
use Throwable;

/**
 * Class FboService
 * @package TotalCRM\OzonApi\Service\V2\Posting
 */
class FboService extends AbstractService implements HasOrdersInterface, GetOrderInterface
{
    private $path = '/v2/posting/fbo';

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbo_list
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
            'with'   => WithResolver::getDefaults(2, PostingScheme::FBO),
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $filter = ArrayHelper::pick($requestData['filter'], ['since', 'to', 'status']);
        foreach (['since', 'to'] as $key) {
            if (isset($filter[$key]) && $filter[$key] instanceof \DateTimeInterface) {
                $filter[$key] = $filter[$key]->format(DATE_RFC3339);
            }
        }
        $requestData['filter'] = $filter;

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * @see https://cb-api.ozonru.me/apiref/en/#t-fbo_get
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
            'with'           => WithResolver::resolve($options, 2, PostingScheme::FBO),
        ]);
    }
}
