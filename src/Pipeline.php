<?php

namespace UmnLib\Core;

class Pipeline
{
  protected $iterator;
  public function iterator()
  {
    return $this->iterator;
  }
  public function setIterator( $iterator )
  {
    $this->iterator = $iterator;
  }

  public function __construct()
  {
    $processors = func_get_args();
    $iterator = array_shift($processors);

    if (!($iterator instanceof \Iterator)) {
      throw new \InvalidArgumentException("The first iterator argument must be an instance of Iterator");
    }

    $previousIterator = $iterator;

    // Each processor is really just a special iterator.
    foreach ($processors as $processor) {
      if (!($processor instanceof Pipeline\Processor)) {
        throw new \InvalidArgumentException("Each processor must be an instance of \UmnLib\Core\Pipeline\Processor");
      }
      $processor->setInnerIterator( $previousIterator );
      $previousIterator = $processor;
    }

    // Set the pipeline iterator to the last processor, which is a chain of all the iterators:
    $this->setIterator( $processor );
  }

  public function run()
  {
    $iterator = $this->iterator();
    $iterator->rewind();
    while ($iterator->valid()) {

      //$this->pre();

      $key = $iterator->key();
      $current = $iterator->current();
      //echo "$key => $current\n";

      //$this->post();
      $iterator->next();
    }
  }

  // TODO: Not sure that I need these...
  public function pre()
  {
    //echo "pre(): Should be executed only once for each item processed.\n";
  }
  public function post()
  {
    //echo "post(): Should be executed only once for each item processed.\n";
  }

/* Make a generic 'post' function out of this!
    public function compress_loaded_file( $file_name )
    {
        $zip_file_name = "$file_name.gz";
        $zip_file_contents = file_get_contents( $file_name );
        $zip_file = gzopen($zip_file_name, "w9");
        gzwrite( $zip_file, $zip_file_contents );
        gzclose( $zip_file );
        unlink( $file_name );
    }
 */

}
