<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Enum;

final class Status
{
    public const AWAITING_APPROVE = 'awaiting_approve';
    public const AWAITING_PACKAGING = 'awaiting_packaging';
    public const AWAITING_DELIVER = 'awaiting_deliver';
    public const DELIVERING = 'delivering';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    /**
     * @return array
     */
    public static function getList(): array
    {
        return [
            self::AWAITING_APPROVE,
            self::AWAITING_PACKAGING,
            self::AWAITING_DELIVER,
            self::DELIVERING,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }
}
