<?php
namespace base;

use exceptions\Exception;
use exceptions\InvalidArgumentException;
use exceptions\InvalidCallException;
use exceptions\UnknownPropertyException;
use exceptions\UnknownMethodException;

abstract class BaseObject
{
    public final static function className()
    {
        return
        	static::class;
    }

	public final function configure($config = [])
	{
		$rf = new \ReflectionClass(static::class);
		$publicProperties = $rf->getProperties(\ReflectionProperty::IS_PUBLIC);
		$publicProperties = array_column($publicProperties, 'name');

		foreach($config as $property => $value)
		{
			if(in_array($property, $publicProperties))
			{
				$this->{$property} = $value;
				continue;
			}

			$setter = 'set'. $property;
			$getter = 'get'. $property;

			if($rf->hasMethod($setter))
			{
                $rm = new \ReflectionMethod(static::class, $setter);
				if($rm->isPublic())
				{
					$this->$setter($value);
					continue;
				}
				throw new InvalidCallException('call non public method on ' . get_class($this) . '::' . $rm->getName() );
            }

			if($rf->hasMethod($getter) )
				throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $property);

			if($rf->hasProperty($property))
				throw new UnknownPropertyException('Setting non public property: ' . get_class($this) . '::' . $property);

			throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $property);
		}
	}

	public function init()
	{
	}

	public function __construct($config = [])
	{
		$this->configure($config);
		$this->init();
	}

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        }

        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    public function __call($name, $params)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    public final function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    public final function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    public final function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    public final function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    protected final static function loadAliases()
    {
        //@todo tmp antes de hacer la clase app que sobrescriba a slim en un singleton
        // voy a guardar los aliases no en un static::$aliases sino en un archivo de configuracion para quesea global..
        $coreAliases = [
            '@app'     => BASE_PATH,
            '@data'    => DATA_PATH,
            '@logs'    => LOG_PATH,
            '@runtime' => RUNTIME_PATH,
            '@cache'   => CACHE_PATH,
            '@params'  => PARAMS_PATH,
            '@web'     => '/',
        ];

        try{
            $aliases = file_get_contents(ALIASES_FILE);
            $aliases = json_decode($aliases, true);
        }
        catch(\Exception $e)
        {
            file_put_contents(ALIASES_FILE, json_encode( $coreAliases ));
        }

        return array_merge($aliases, $coreAliases);
    }

    protected final static function writeAliases(Array $values)
    {
        try{
            file_put_contents(ALIASES_FILE, json_encode($values));
        }
        catch(\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getAlias($alias, $throwException = true)
    {
        if(strncmp($alias, '@', 1))
            return $alias;

        $pos     = strpos($alias, '/');
        $root    = $pos === false ? $alias : substr($alias, 0, $pos);
        $aliases = static::loadAliases();

        if (isset($aliases[$root]))
        {
            if (is_string($aliases[$root]))
                return $pos === false ? $aliases[$root] : $aliases[$root] . substr($alias, $pos);

            foreach ($aliases[$root] as $name => $path)
            {
                if (strpos($alias . '/', $name . '/') === 0)
                    return $path . substr($alias, strlen($name));
            }
        }

        if($throwException)
            throw new InvalidArgumentException("Invalid path alias: $alias");

        return false;
    }

    public static function getRootAlias($alias)
    {
        $pos     = strpos($alias, '/');
        $root    = $pos === false ? $alias : substr($alias, 0, $pos);
        $aliases = static::loadAliases();

        if (isset($aliases[$root]))
        {
            if (is_string($aliases[$root]))
                return $root;

            foreach ($aliases[$root] as $name => $path)
            {
                if (strpos($alias . '/', $name . '/') === 0)
                    return $name;
            }
        }
        return false;
    }

    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1))
            $alias = '@' . $alias;

        $pos     = strpos($alias, '/');
        $root    = $pos === false ? $alias : substr($alias, 0, $pos);
        $aliases = static::loadAliases();

        if ($path !== null)
        {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);

            switch(true)
            {
                case (!isset($aliases[$root])):
                    $aliases[$root] = ($pos === false) ?  $path : [$alias => $path];
                    break;

                case (is_string($aliases[$root])):
                    $aliases[$root] = ($pos === false) ? $path : [$alias => $path , $root => $aliases[$root] ];
                    break;

                default:
                    $aliases[$root][$alias] = $path;
                    krsort($aliases[$root]);
                    break;
            }

            return static::writeAliases($aliases);
        }

        if(!isset($aliases[$root]))
            return;

        if (is_array($aliases[$root]))
            unset($aliases[$root][$alias]);

        else if($pos === false)
            unset($aliases[$root]);

        return static::writeAliases($aliases);
    }
}
