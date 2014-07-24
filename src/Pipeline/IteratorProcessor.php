<?php

namespace UmnLib\Core\Pipeline;

// TODO: Load this class automatically from Pipeline\Processor is current is an iterator?

abstract class IteratorProcessor extends \UmnLib\Core\Pipeline\Processor
{
  protected function fetch()
  {
    $this->setValid($this->innerIterator()->valid());

    while ($this->innerIterator()->valid()) {

      if ($this->innerCurrentExhausted()) {
        $innerKey = $this->innerIterator()->key();
        $this->setInnerKey($innerKey);
        $innerCurrent = $this->innerIterator()->current();
        $this->setInnerCurrent($innerCurrent);
        $this->setInnerCurrentExhausted(false);
      }

      // Note: innerCurrent may not be an instance of Pipeline_Processor.
      while ($this->innerCurrent()->valid()) {
        $innerCurrentKey = $this->innerCurrent()->key();
        $innerCurrentCurrent = $this->innerCurrent()->current();

        $this->pre();

        try {
          list($key, $current) = $this->process($innerCurrentKey, $innerCurrentCurrent);
          //echo "process results: $key => $current\n";
        } catch (\Exception $e) {
          error_log($e->getMessage());
          $this->innerCurrent()->next();
          continue;
        }
        $this->setKey($key);
        $this->setCurrent($current);

        $this->post();

        $this->innerCurrent()->next();
        return;
      }

      $this->setInnerCurrentExhausted(true);
      $this->innerIterator->next();
      continue;
    }
    $this->setValid(false);
  }

  // Inherited methods:

  public function next()
  {
    // For an IteratorProcessor, we don't want to call this on every iteration!
    //$this->innerIterator()->next();

    $this->fetch();
  }

  public function rewind()
  {
    $this->innerIterator()->rewind();

    // Initialize this to true so that we'll get a new iterator on the first iteration:
    $this->setInnerCurrentExhausted(true);

    $this->fetch();
  }

  // Mix of inherited methods and custom properties & methods.

  protected $innerCurrentExhausted;
  public function innerCurrentExhausted()
  {
    return $this->innerCurrentExhausted;
  }
  public function setInnerCurrentExhausted($boolean)
  {
    //echo "setting innerCurrentExhausted = $boolean\n";
    $this->innerCurrentExhausted = $boolean;
  }
}
