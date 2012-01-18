<?php

namespace Ebutik\MongoSessionBundle\Collection;

use Ebutik\MongoSessionBundle\Escaper\Escaper;

/**
* 
*/
abstract class FlatteningParameterBag
{
  protected $escaper;

  protected function getEscaper()
  {
    if (!$this->escaper)
    {
      $this->escaper = $this->createEscaper();
    }

    return $this->escaper;
  }

  public function all()
  {
    $flattened = $this->_read();
    return $this->unflatten($flattened);
  }

  public function keys()
  {
    return array_keys($this->all());
  }

  public function replace(array $parameters = array())
  {
    $this->_write(array());

    $this->add($parameters);
  }

  public function add(array $parameters = array())
  {
    $current_data = $this->_read();

    foreach ($parameters as $key => $value) 
    {
      $flat_kv_array = $this->flatten(array($key => $value));
      $current_data = array_merge($this->purgePrefix($current_data, $key), $flat_kv_array);
    }

    $this->_write($current_data);
  }

  public function get($key, $default = null)
  {
    $flattened = $this->readWithPrefix($key);
    $unflattened = $this->unflatten($flattened);

    return (isset($unflattened[$key]) ? $unflattened[$key] : $default);
  }

  public function set($key, $value)
  {
    $flat_kv_array = $this->flatten(array($key => $value));

    $current_data = $this->_read();

    $current_data = $this->purgePrefix($current_data, $key);

    $this->_write(array_merge($current_data, $flat_kv_array));
  }

  public function has($key)
  {
    $flattened = $this->readWithPrefix($key);

    return count($flattened) > 0;
  }

  public function remove($key)
  {
    $this->_write($this->purgePrefix($this->_read(), $key));
  }

  protected function createEscaper()
  {
    $escaper = new Escaper();
    $escaper->setDelimiter('', '>');
    $escaper->setEscapingMap(array('/' => '%'));

    return $escaper;
  }

  protected function keyMatchesPrefix($key, $prefix)
  {
    return $key === $this->getEscaper()->escape($prefix) || strpos($key, $this->getEscaper()->escape($prefix).'/') === 0;
  }

  protected function readWithPrefix($prefix)
  {
    $matches = array();
    foreach ($this->_read() as $key => $value)
    {
      if ($this->keyMatchesPrefix($key, $prefix))
      {
        $matches[$key] = $value;
      }
    }
    return $matches;
  }

  protected function unflatten(array $data)
  {
    $result = array();
    foreach ($data as $key => $value) 
    {
      $exploded_key = explode('/', $key);
      $current_node = &$result;
      $last_index = count($exploded_key)-1;

      foreach ($exploded_key as $index => $component) 
      {
        $component = $this->getEscaper()->unescape($component);
        if ($index < $last_index)
        {
          if (!isset($current_node[$component]))
          {
            $current_node[$component] = array();
          }
  
          if (!is_array($current_node[$component]))
          {
            throw new \RuntimeException(sprinf("Error while unflattening session data. Node %s of key %s already exists, but is not an array.", $component, $key));
          }
  
          $current_node = &$current_node[$component];          
        }
        else
        {
          $current_node[$component] = $value;
        }
      }
    }

    return $result;
  }

  protected function flatten(array $data)
  {
    $output = array();
    foreach ($data as $key => $subdata) 
    {
      if (is_array($subdata))
      {
        $processed_subdata = $this->flatten($subdata);
        foreach($processed_subdata as $subkey => $subsubdata)
        {
          $output[$this->getEscaper()->escape($key).'/'.$subkey] = $subsubdata;
        }
      }
      else
      {
        $output[$this->getEscaper()->escape($key)] = $subdata;
      }
    }

    return $output;
  }

  protected function purgePrefix($data, $prefix)
  {
    foreach ($data as $key => $value) 
    {
      if ($this->keyMatchesPrefix($key, $prefix))
      {
        unset($data[$key]);
      }
    }

    return $data;
  }

  abstract protected function _write(array $data);

  abstract protected function _read();
}