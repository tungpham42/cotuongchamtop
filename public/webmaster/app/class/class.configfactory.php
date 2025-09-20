<?php
class ConfigFactory {
	private static $config = array();

    /**
     * @param $key
     * @return Config
     */
	public static function load($key) {
		if(!isset(self::$config[$key])) {
		    $cfg_dir = APP."config".DS;
		    $local_path = $cfg_dir."{$key}_local.php";
            $cfg_path = $cfg_dir."{$key}.php";
			$config = file_exists($local_path) ? require $local_path : require $cfg_path;
			self::$config[$key] = new Config($config);
		}
		return self::$config[$key];
	}
}

class Config implements ArrayAccess, Iterator, Countable {
	private $config;

	private $internal_pointer = 0;

	public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    public function offsetGet($offset)
    {
        return isset($this->config[$offset]) ? $this->config[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function __serialize()
    {
        return [
            'config'=>$this->config,
            'internal_pointer_pos'=>$this->internal_pointer,
        ];
    }

    public function __unserialize(array $data)
    {
        $this->config = $data['config'];
        $i = $data['internal_pointer'];
        while($i > 0 AND (false !== $this->next())) {
            $i--;
        }
    }

    public function __clone()
    {
        return unserialize(serialize($this));
    }

    public function current()
    {
        return current($this->config);
    }

    public function next()
    {
        $this->internal_pointer++;
        return next($this->config);
    }

    public function key()
    {
        return key($this->config);
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        $this->internal_pointer = 0;
        reset($this->config);
    }

    public function firstValue()
    {
        reset($this->config);
        $value = $this->current();
        $this->restore_internal_pointer();
        return $value;
    }

    public function firstKey()
    {
        reset($this->config);
        $key = $this->key();
        $this->restore_internal_pointer();
        return $key;
    }

    public function lastValue()
    {
        $value = end($this->config);
        $this->restore_internal_pointer();
        return $value;
    }

    public function lastKey()
    {
        end($this->config);
        $key = $this->key();
        $this->restore_internal_pointer();
        return $key;
    }

    private function restore_internal_pointer()
    {
        reset($this->config);
        $i = $this->internal_pointer;
        while($i > 0) {
            next($this->config);
            $i--;
        }
    }

    public function count()
    {
        return count($this->config);
    }

    public function getIfEmpty($key, $default = null)
    {
        $value = $this->offsetGet($key);
        return !empty($value) ? $value : $default;
    }
}