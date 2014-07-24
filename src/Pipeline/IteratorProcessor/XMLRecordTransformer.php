<?php

namespace UmnLib\Core\Pipeline\IteratorProcessor;

class XMLRecordTransformer extends \UmnLib\Core\Pipeline\IteratorProcessor
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
    $this->setTransformer($transformer);
  }

  // TODO: Do I really want to do this type-checking here? Or should I have the transformer class handle it?
  public function process($key, \UmnLib\Core\XMLRecord $current)
  {
    $recordArray = $this->transformer()->transform($current);
    return array($current->primaryId(), $recordArray);
  }

  public function context()
  {
    return array('record id' => $this->key());
  }
}
