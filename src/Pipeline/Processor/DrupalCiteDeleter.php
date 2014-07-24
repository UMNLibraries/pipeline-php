<?php

namespace UmnLib\Core\Pipeline\Processor;

class DrupalCiteDeleter extends \UmnLib\Core\Pipeline\Processor
{
  protected $deleter;
  public function deleter()
  {
    return $this->deleter;
  }
  // TODO: Should the deleter param have a class constraint? If so, what?
  protected function setDeleter($deleter)
  {
    $this->deleter = $deleter;
  }

  // TODO: Should the deleter param have a class constraint? If so, what?
  public function __construct($deleter)
  {
    $this->setDeleter($deleter);
  }

  public function process($key, $current)
  {
    // key should be the record id:
    $recordId = $key;
    // Current should be a citation array:
    $citation = $current;
    // TODO: Make the deleter return the deleted node object or array?
    $this->deleter()->delete($citation);
    return array($recordId, $citation); // TODO: Should be ($node_id, $node)
  }

  public function context()
  {
    return array(
      'record id' => $this->key(),
    );
  }
}
