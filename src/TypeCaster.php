<?php declare(strict_types=1);

namespace TotalCRM\OzonApi;

use LogicException;

/**
 * Class TypeCaster
 * @package TotalCRM\OzonApi
 */
class TypeCaster
{
    /**
     * @param array $data Array with cast types
     * @param array $config ['float_key' => 'float', 'str_key' => 'string', 'int_key' => 'int']
     * @param bool $force
     * @return array Modified data
     */
    public static function castArr(array $data, array $config, bool $force = true): array
    {
        foreach ($data as $key => &$val) {
            if (array_key_exists($key, $config) && null !== $val) {
                switch (self::normalizeType($config[$key])) {
                    case 'boolean':
                        $val = (bool) $val;
                        break;
                    case 'string':
                        $val = (string) $val;
                        break;
                    case 'integer':
                        $val = (int) $val;
                        break;
                    case 'float':
                        $val = (float) $val;
                        break;
                    default:
                        if ($force) {
                            throw new LogicException("Unsupported type: {$config[$key]}");
                        }
                }
            }
        }

        return $data;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function normalizeType(string $type): string
    {
        switch ($type) {
            case 'arr':
            case 'array':
                return 'array';
            case 'bool':
            case 'boolean':
                return 'boolean';
            case 'str':
            case 'string':
                return 'string';
            case 'int':
            case 'integer':
                return 'integer';
            case 'float':
            case 'double':
                return 'float';
            default:
                throw new LogicException("Unsupported type: $type");
        }
    }
}
