<?php declare(strict_types=1);

namespace TotalCRM\OzonApi;

use LogicException;
use TotalCRM\OzonApi\Exception\ProductValidatorException;

/**
 * Class ProductValidator
 * @package TotalCRM\OzonApi
 */
class ProductValidator
{
    private const MAX_IMAGES_COUNT = 10;

    /** @var array */
    private $config;
    /** @var array */
    private $requiredKeys;
    /** @var array */
    private $optProps;
    /** @var array */
    private $typeCast;

    /**
     * ProductValidator constructor.
     * @param string $mode
     * @param int $version
     */
    public function __construct(string $mode = 'create', int $version = 1)
    {
        if (!in_array($mode, ['create', 'update'])) {
            throw new LogicException('Mode must be in [create, update]');
        }

        if (!in_array($version, [1, 2], true)) {
            throw new LogicException('Version must be in [1, 2]');
        }

        if (!file_exists($configFile = __DIR__."/config/product_validator_v{$version}.php")) {
            throw new LogicException("No config found for version $version");
        }

        $this->config = include $configFile;

        $this->requiredKeys = array_keys(array_filter(array_map(static function ($arr) use ($mode) {
            return $arr['required'.ucfirst($mode)] ?? false;
        }, $this->config)));

        $this->optProps = array_filter(array_map(static function ($arr) {
            return $arr['options'] ?? null;
        }, $this->config));

        $this->typeCast = array_map(static function ($arr) {
            return $arr['type'];
        }, $this->config);
    }

    /**
     * @param array $item
     * @return array
     * @throws ProductValidatorException
     */
    public function validateItem(array $item): array
    {
        if ($extraKeys = array_diff(array_keys($item), array_keys($this->config))) {
            foreach ($extraKeys as $key) {
                @trigger_error("ProductValidator noticed unexpected item key '$key'");
                unset($item[$key]);
            }
        }

        foreach ($this->requiredKeys as $key) {
            if (!array_key_exists($key, $item)) {
                throw new ProductValidatorException("Required property not defined: $key", 0, $item);
            }
            if ('' === $item[$key] && 'string' === TypeCaster::normalizeType($this->config[$key]['type'])) {
                throw new ProductValidatorException("Empty value for property: $key", 0, $item);
            }
        }

        foreach ($this->optProps as $key => $options) {
            if (isset($item[$key]) && !in_array($item[$key], $options, true)) {
                throw new ProductValidatorException("Incorrect property value '{$item[$key]}' for `$key` key");
            }
        }

        if (isset($item['images']) && count($item['images']) > self::MAX_IMAGES_COUNT) {
            array_splice($item['images'], self::MAX_IMAGES_COUNT);
        }

        return TypeCaster::castArr($item, $this->typeCast, false);
    }
}
