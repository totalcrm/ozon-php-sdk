<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Utils;

use TotalCRM\OzonApi\Enum\PostingScheme;

/**
 * Class WithResolver
 * @package TotalCRM\OzonApi\Utils
 */
final class WithResolver
{
    /**
     * @param int $version
     * @param string $postingScheme
     * @param string|null $method
     * @return array
     */
    public static function getKeys(int $version = 2, string $postingScheme = 'fbs', ?string $method = ''): array
    {
        $arr = array_filter([$version, $postingScheme, $method]);
        switch ($arr) {
            case [2, PostingScheme::FBS, 'unfulfilledList']:
                return ['barcodes'];
            case [2, PostingScheme::FBO]:
                return ['analytics_data', 'financial_data'];
            default:
                return ['analytics_data', 'barcodes', 'financial_data'];
        }
    }

    /**
     * @param int $version
     * @param string $postingScheme
     * @param string|null $method
     * @return array
     */
    public static function getDefaults(int $version = 2, string $postingScheme = 'fbs', ?string $method = ''): array
    {
        $keys = self::getKeys($version, $postingScheme, $method);

        return array_combine($keys, array_pad([], count($keys), false));
    }

    /**
     * @param array $with
     * @param int $version
     * @param string $postingScheme
     * @param string|null $method
     * @return array
     */
    public static function resolve(array $with = [], int $version = 2, string $postingScheme = PostingScheme::FBS, ?string $method = ''): array
    {
        if (isset($with['with'])) {
            $with = $with['with'];
        }

        return array_merge(
            self::getDefaults($version, $postingScheme, $method),
            ArrayHelper::pick($with, self::getKeys($version, $postingScheme))
        );
    }
}
