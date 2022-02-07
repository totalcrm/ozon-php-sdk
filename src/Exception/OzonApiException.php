<?php declare(strict_types=1);

namespace TotalCRM\OzonApi\Exception;

use Exception;

/**
 * Class OzonApiException
 * @package TotalCRM\OzonApi\Exception
 */
class OzonApiException extends Exception
{
    /** @var array|null */
    protected $details;

    /**
     * OzonApiException constructor.
     * @param string $messages
     * @param int $code
     * @param array $details
     */
    public function __construct(string $messages, int $code = 0, array $details = [])
    {
        parent::__construct($messages, $code);
        $this->details = $details;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return parent::__toString().PHP_EOL.'Data: '.json_encode($this->details);
    }

    /**
     * @deprecated use getDetails() method
     */
    public function getData(): ?array
    {
        return $this->details;
    }

    /**
     * @return array|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }
}
