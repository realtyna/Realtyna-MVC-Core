<?php

namespace Realtyna\MvcCore;

class Config
{

    /**
     * Raw config array.
     * @var array
     * @since 0.0.1
     */
    protected array $raw;


    /**
     * Constructor.
     *
     * @param array $raw Raw config array.
     *
     * @since 0.0.1
     *
     */
    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    /**
     * @return array
     */
    public function getRaw(): array
    {
        return $this->raw;
    }

    /**
     * Returns value stored in given key.
     * Can access multidimensional array values with a DOT(.)
     * i.e. path.plugin_dir
     * You can pass custom array as config in second parameter
     *
     * @param string $key Key.
     * @param array|null $configs Child array
     *
     * @return mixed
     * @since 0.0.1
     */
    public function get(string $key, array $configs = null)
    {
        if (defined($key)
            && strpos($key, 'namespace') !== 0
            && strpos($key, 'type') !== 0
            && strpos($key, 'version') !== 0
            && strpos($key, 'author') !== 0
            && strpos($key, 'addons') !== 0
            && strpos($key, 'license') !== 0
        ) {
            return constant($key);
        }
        if (empty($configs)) {
            $configs = $this->raw;
        }
        $keys = explode('.', $key);
        if (empty($keys)) {
            return null;
        }

        if (array_key_exists($keys[0], $configs)) {
            if (count($keys) == 1) {
                return $configs[$keys[0]];
            } elseif (is_array($configs[$keys[0]])) {
                $configs = $configs[$keys[0]];
                unset($keys[0]);

                return $this->get(implode('.', $keys), $configs);
            }
        }

        return null;
    }
}