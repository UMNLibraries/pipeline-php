<?php

namespace UmnLib\Core\Pipeline\Processor;

class MySQLiRowTransformer extends \UmnLib\Core\Pipeline\Processor
{
  protected $transformer;
  public function transformer()
  {
    return $this->transformer;
  }
  protected function setTransformer($transformer)
  {
    $this->transformer = $transformer;
  }

  public function __construct($transformer)
  {
    $this->setTransformer( $transformer );
  }

  public function process($key, $current)
  {
    // Current should be an array representing a row in a MySQLi_Result:
    $row = $current;
    $transformedRow = $this->transformer()->transform($row);
    return array($key, $transformedRow);
  }

  public function context()
  {
    return array('row number' => $this->key());
  }
}
