<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Utils;

/**
 * Class ArrayHelper
 * @package TotalCRM\OzonApi\Utils
 */
class ArrayHelper
{
    /**
     * Filters unexpected array keys.
     * @param array $query
     * @param array $whitelist
     * @return array
     */
    public static function pick(array $query, array $whitelist): array
    {
        return array_intersect_key($query, array_flip($whitelist));
    }
}
