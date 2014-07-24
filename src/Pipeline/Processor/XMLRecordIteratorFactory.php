<?php

namespace UmnLib\Core\Pipeline\Processor;

class XMLRecordIteratorFactory extends \UmnLib\Core\Pipeline\Processor
{
  protected $factory;
  public function factory()
  {
    return $this->factory;
  }
  protected function setFactory(\UmnLib\Core\XMLRecord\IteratorFactory $factory)
  {
    $this->factory = $factory;
  }

  public function __construct(\UmnLib\Core\XMLRecord\IteratorFactory $factory)
  {
    $this->setFactory($factory);
  }

  public function process($key, $current)
  {
    // Current should be a filename.
    $filename = $current;
    $iterator = $this->factory()->create($filename);
    return array($filename, $iterator);
  }

  public function context()
  {
    return array('file' => $this->key());
  }
}
