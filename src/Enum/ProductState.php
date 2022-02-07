<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Enum;

/**
 * Class ProductState
 * @package TotalCRM\OzonApi\Enum
 */
final class ProductState
{
    public const PROCESSED = 'processed';
    public const PROCESSING = 'processing';
    public const MODERATING = 'moderating';
    public const FAILED_MODERATION = 'failed_moderation';
    public const FAILED_VALIDATION = 'failed_validation';
    public const FAILED = 'failed';
}
