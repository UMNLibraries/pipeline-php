<?php

namespace UmnLib\Core\Pipeline\Processor;

class DrupalCiteUpdater extends \UmnLib\Core\Pipeline\Processor
{
  protected $updater;
  public function updater()
  {
    return $this->updater;
  }
  // TODO: Should the updater param have a class constraint? If so, what?
  protected function setUpdater($updater)
  {
    $this->updater = $updater;
  }

  // TODO: Should the updater param have a class constraint? If so, what?
  public function __construct($updater)
  {
    $this->setUpdater($updater);
  }

  public function process($key, $current)
  {
    // key should be the record id:
    $recordId = $key;
    // Current should be a citation array:
    $citation = $current;
    // TODO: Make the updater return the created node object or array!
    $this->updater()->update($citation);
    return array($recordId, $citation); // TODO: Should be ($node_id, $node)
  }

  public function context()
  {
    return array(
      'record id' => $this->key(),
    );
  }
}
