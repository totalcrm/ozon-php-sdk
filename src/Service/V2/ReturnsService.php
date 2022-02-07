<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Service\V2;

use TotalCRM\OzonApi\Enum\PostingScheme;
use TotalCRM\OzonApi\Exception\OzonApiException;
use TotalCRM\OzonApi\Service\AbstractService;
use TotalCRM\OzonApi\Utils\ArrayHelper;
use LogicException;
use Throwable;

/**
 * Class ReturnsService
 * @package TotalCRM\OzonApi\Service\V2
 */
class ReturnsService extends AbstractService
{
    private $path = '/v2/returns';

    /**
     * @param string $postingScheme Value from ['fbo', 'fbs']
     * @param array $requestData ['filter' => array, 'offset' => int, 'limit' => int]
     * @return array
     * @throws Throwable
     * @throws OzonApiException
     */
    public function company(string $postingScheme, array $requestData): array
    {
        $postingScheme = strtolower($postingScheme);
        if (!in_array($postingScheme, [PostingScheme::FBO, PostingScheme::FBS], true)) {
            throw new LogicException("Unsupported posting scheme: $postingScheme");
        }

        $default = [
            'filter' => [],
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        return $this->request('POST', "{$this->path}/company/{$postingScheme}", $requestData);
    }
}
