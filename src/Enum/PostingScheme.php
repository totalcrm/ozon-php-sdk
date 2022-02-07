<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Enum;

/**
 * Class PostingScheme
 * @package TotalCRM\OzonApi\Enum
 */
final class PostingScheme
{
    public const CROSSBORDER = 'crossborder';
    public const FBO = 'fbo';
    public const FBS = 'fbs';

    /**
     * @return array
     */
    public static function all(): array
    {
        return [
            self::CROSSBORDER,
            self::FBO,
            self::FBS,
        ];
    }
}
