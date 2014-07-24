<?php

require_once 'Pipeline/Processor.php';

class Pipeline
{
    protected $iterator;
    public function iterator()
    {
        return $this->iterator;
    }
    public function set_iterator( $iterator )
    {
        $this->iterator = $iterator;
    }

    public function __construct()
    {
        $processors = func_get_args();
        $iterator = array_shift($processors);

        if (!($iterator instanceof Iterator)) {
            throw new Exception("The first iterator argument must be an instance of Iterator");
        }

        $previous_iterator = $iterator;

        // Each processor is really just a special iterator.
        foreach ($processors as $processor) {
            if (!($processor instanceof Pipeline_Processor)) {
                throw new Exception("Each processor must be an instance of Pipeline_Processor");
            }
            $processor->set_inner_iterator( $previous_iterator );
            $previous_iterator = $processor;
        }

        // Set the pipeline iterator to the last processor, which is a chain of all the iterators:
        $this->set_iterator( $processor );
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

} // end class Pipeline
