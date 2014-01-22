<?php

namespace Klein;

/**
 * GetParameters
 * 
 * @package     Klein
 */
class GetParameters {
  private $reflection;

  public function __construct($callable) {
    if( is_array($callable) ) {
      $this->reflection = new \ReflectionMethod($callable[0], $callable[1]);
    }else {
      $this->reflection = new \ReflectionFunction($callable);
    }
  }

  /**
   * Returns parameters from
   * function definition
   *
   * @returns array
   */
  public static function forMethod($callable) {
    $method = new self($callable);
    return $method->getParameters();
  }

  /**
   * Extracts parameters from function
   * definition and returns the names
   *
   * @returns array
   */
  private function getParameters() {
    $params = array_map(function($p) {
      return $p->getName();
    }, $this->reflection->getParameters());
    return $params;
  }

}
