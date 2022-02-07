<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Enum;

/**
 * Class TransactionType
 * @package TotalCRM\OzonApi\Enum
 */
final class TransactionType
{
    public const ALL = 'ALL';
    public const ORDERS = 'ORDERS';
    public const RETURNS = 'RETURNS';
    public const SERVICES = 'SERVICES';
    public const OTHER = 'OTHER';
    public const DEPOSIT = 'DEPOSIT';
}
