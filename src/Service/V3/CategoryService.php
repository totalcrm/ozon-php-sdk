<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V3;

use TotalCRM\OzonApi\Enum\Language;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\TypeCaster;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class CategoryService
 * @package TotalCRM\OzonApi\Service\V3
 */
class CategoryService extends AbstractService
{
    private $path = '/v3/category';

    /**
     * @param array|int $categoryId
     * @param array $query [attribute_type, language]
     *
     * @return mixed|ResponseInterface
     * @throws Throwable
     * @throws OzonApiException
     */
    public function attribute($categoryId, array $query = []): array
    {
        $query = ArrayHelper::pick($query, ['attribute_type', 'language']);
        $query = TypeCaster::castArr($query, [
            'attribute_type' => 'str',
            'language'       => 'str',
        ]);
        $query = array_merge([
            'category_id' => (array) $categoryId,
            'language'    => Language::DEFAULT,
        ], $query);

        return $this->request('POST', "{$this->path}/attribute", $query);
    }
}
