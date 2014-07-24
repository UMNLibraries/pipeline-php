<?php

namespace UmnLib\Core\Pipeline\Processor;

class DrupalCiteLoader extends \UmnLib\Core\Pipeline\Processor
{
  protected $loader;
  public function loader()
  {
    return $this->loader;
  }
  // TODO: Should the loader param have a class constraint? If so, what?
  protected function setLoader($loader)
  {
    $this->loader = $loader;
  }

  // TODO: Should the loader param have a class constraint? If so, what?
  public function __construct($loader)
  {
    $this->setLoader($loader);
  }

  public function process($key, $current)
  {
    // key should be the record id:
    $recordId = $key;
    // Current should be a citation array:
    $citation = $current;
    // TODO: Make the loader return the created node object or array!
    $this->loader()->load($citation);
    return array($recordId, $citation); // TODO: Should be ($node_id, $node)
  }

  public function context()
  {
    return array(
      'record id' => $this->key(),
    );
  }
}
