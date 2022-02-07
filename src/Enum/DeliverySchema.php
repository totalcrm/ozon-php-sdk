<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Enum;

/**
 * Class DeliverySchema
 * @package TotalCRM\OzonApi\Enum
 */
final class DeliverySchema
{
    /** @var string Fulfilled by Seller */
    public const FBS = 'fbs';

    /** @var string Fulfilled by Ozon */
    public const FBO = 'fbo';

    /** @var string */
    public const CROSSBORDER = 'crossborder';
}
