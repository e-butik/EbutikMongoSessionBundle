<?php

namespace Ebutik\MongoSessionBundle\Escaper;

interface EscaperInterface
{
  /**
   * @param $string
   *
   * @return string
   */
  function escape($string);

  /**
   * @param $string
   * 
   * @return void
   */
  function unescape($string);
}
