<?php

namespace Ebutik\MongoSessionBundle\Escaper;

class Escaper implements EscaperInterface
{
  /**
   * @var array
   */
  protected $delimiter = array('', '');

  /**
   * @var array
   */
  protected $map = array('.' => '-'); // TODO remove the default value when the patch (https://github.com/symfony/symfony/pull/2427) will be applied
  /**
   * @var boolean
   */
  protected $is_map_dirty = true;

  /**
   * {@inheritdoc}
   */
  public function escape($string)
  {
    $map = $this->getEscapingMap();

    return str_replace(array_keys($map), array_values($map), $string);
  }

  /**
   * {@inheritdoc}
   */
  public function unescape($string)
  {
    $map = $this->getEscapingMap();

    return str_replace(array_values($map), array_keys($map), $string);
  }

  /**
   * @param string $start
   * @param string $end
   *
   * @return void
   */
  public function setDelimiter($start = '', $end = '')
  {
    $this->is_map_dirty = true;
    $this->delimiter = array($start, $end);
  }

  /**
   * @return array
   */
  public function getEscapingMap()
  {
    if($this->is_map_dirty)
    {
      $this->map = $this->wrapInDelimiter($this->map);
    }

    return $this->map;
  }

  /**
   * @param array $map
   * 
   * @return void
   */
  public function setEscapingMap(array $map)
  {
    $this->is_map_dirty = true;
    $this->map = $map;
  }

  /**
   * @param array $map
   * 
   * @return array
   */
  protected function wrapInDelimiter(array $map)
  {
    $prepared_map = array();

    foreach($map as $escape => $escaped)
    {
       $prepared_map[$escape] = $this->delimiter[0] . $escaped . $this->delimiter[1];
    }

    $this->is_map_dirty = false;

    return $prepared_map;
  }
}