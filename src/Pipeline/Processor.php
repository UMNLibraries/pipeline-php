<?php

namespace UmnLib\Core\Pipeline;

abstract class Processor implements \OuterIterator // Similar to FilterIterator
{
  protected $innerIterator;
  public function getInnerIterator()
  {
    return $this->innerIterator();
  }
  public function innerIterator()
  {
    return $this->innerIterator;
  }
  public function setInnerIterator(\Iterator $iterator)
  {
    $this->innerIterator = $iterator;
  }

  public function context()
  {
    return array(
      'key' => $this->key(),
      'current' => $this->current(),
    );
  }

  public function formatPipelineContext($pipelineContext)
  {
    // TODO: Come up with a more generic solution that doesn't assume
    // a certain context array structure?

    $formattedPipelineContext = '';
    foreach ($pipelineContext as $classContext) {
      foreach ($classContext as $class => $context) {
        $formattedPipelineContext .= "    $class:\n";
        foreach ($context as $k => $v) {
          $formattedPipelineContext .= "        $k: $v\n";
        }
      }
    }
    return $formattedPipelineContext;
  }

  public function pipelineContext()
  {
    $iterator = $this;
    $context = array();
    while(1) {
      $class = get_class($iterator);
      $rc = new \ReflectionClass($class);

      $classContext = array();

      if ($rc->hasMethod('context')) {
        $classContext += $iterator->context();
      } else {
        // Include only the key in this case, because anything
        // else may be too awkward to include in error messages:
        $classContext['key'] = $iterator->key();
      }

      // To keep the iterators in order, use default numerical
      // indexes on the context array:
      $context[] = array($class => $classContext);

      if ($rc->hasMethod('getInnerIterator')) {
        $iterator = $iterator->getInnerIterator();
        continue;
      }
      break;
    }
    return $context;
  }

  abstract public function process($key, $current);

  protected function handleException(\Exception $e)
  {
    $message = $e->getMessage();
    $message .= $this->formatPipelineContext($this->pipelineContext());
    error_log($message);
  }

  protected function fetch()
  {
    $this->setValid($this->innerIterator()->valid());

    while ($this->innerIterator()->valid()) {

      $innerKey = $this->innerIterator()->key();
      $this->setInnerKey($innerKey);
      $innerCurrent = $this->innerIterator()->current();
      $this->setInnerCurrent($innerCurrent);

      $this->pre();

      unset ($key, $current);
      try {
        list($key, $current) = $this->process($innerKey, $innerCurrent);
        //echo "process results: $key => $current\n";
      } catch (\Exception $e) {
        $this->handleException($e);
        $this->innerIterator->next();
        continue;
      }

      // This allows for FilterIterator-like functionality: If no values are
      // returned from process() and no exception was thrown, assume that
      // process() meant to filter out this $key, $current pair:
      if (!isset($key, $current)) {
        $this->innerIterator->next();
        continue;
      }

      $this->setKey($key);
      $this->setCurrent($current);

      //$this->post();

      return;
    }
    $this->setValid(false);
  }

  // Inherited methods:

  public function next()
  {
    $this->post();
    $this->innerIterator()->next();
    $this->fetch();
  }

  public function rewind()
  {
    $this->innerIterator()->rewind();
    $this->fetch();
  }

  // Pre- and post-iteration hooks:

  public function pre()
  {
    $class = get_class($this);
    //echo "$class: pre(): Should be executed only once for each item processed.\n";
  }

  public function post()
  {
    $class = get_class($this);
    //echo "$class: post(): Should be executed only once for each item processed.\n";
  }

  // Back-references to the inner iterator's state:

  protected $innerCurrent;
  public function innerCurrent()
  {
    return $this->innerCurrent;
  }
  public function setInnerCurrent($innerCurrent)
  {
    //echo "setting innerCurrent = $innerCurrent\n";
    $this->innerCurrent = $innerCurrent;
  }

  protected $innerKey;
  public function innerKey()
  {
    return $this->innerKey;
  }
  public function setInnerKey($innerKey)
  {
    $this->innerKey = $innerKey;
  }
  // Mix of inherited methods and custom properties & methods.

  protected $current;
  public function current()
  {
    //return $this->process( $this->innerIterator()->current() );
    //echo "current = {$this->current}\n";
    return $this->current;
  }
  public function setCurrent($current)
  {
    //echo "setting current = $current\n";
    $this->current = $current;
  }

  protected $key;
  public function key()
  {
    //return $this->innerIterator()->key();
    return $this->key;
  }
  public function setKey($key)
  {
    $this->key = $key;
  }

  protected $valid;
  public function valid()
  {
    //return $this->innerIterator()->valid();
    return $this->valid;
  }
  public function setValid($boolean)
  {
    $this->valid = $boolean;
  }
}
