<?php

// TODO: Load this class automatically from Pipeline_Processor is current is an iterator?

abstract class Pipeline_IteratorProcessor extends Pipeline_Processor
{
    protected function fetch()
    {
        $this->set_valid( $this->inner_iterator()->valid() );

        while ($this->inner_iterator()->valid()) {

            if ($this->inner_current_exhausted()) {
                $inner_key = $this->inner_iterator()->key();
                $this->set_inner_key( $inner_key );
                $inner_current = $this->inner_iterator()->current();
                $this->set_inner_current( $inner_current );
                $this->set_inner_current_exhausted( false );
            }

            // Note: inner_current may not be an instance of Pipeline_Processor.
            while ($this->inner_current()->valid()) {
                $inner_current_key = $this->inner_current()->key();
                $inner_current_current = $this->inner_current()->current();

                $this->pre();

                try {
                    list($key, $current) = $this->process( $inner_current_key, $inner_current_current );
                    //echo "process results: $key => $current\n";
                } catch (Exception $e) {
                    error_log( $e->getMessage() );
                    $this->inner_current()->next();
                    continue;
                }
                $this->set_key( $key );
                $this->set_current( $current );

                $this->post();

                $this->inner_current()->next();
                return;
            }
            
            $this->set_inner_current_exhausted( true );
            $this->inner_iterator->next();
            continue;
        }
        $this->set_valid( false );
    }

    // Inherited methods:

    public function next()
    {
        // For an IteratorProcessor, we don't want to call this on every iteration!
        //$this->inner_iterator()->next();

        $this->fetch();
    }

    public function rewind()
    {
        $this->inner_iterator()->rewind();

        // Initialize this to true so that we'll get a new iterator on the first iteration:
        $this->set_inner_current_exhausted( true );

        $this->fetch();
    }

    // Mix of inherited methods and custom properties & methods.

    protected $inner_current_exhausted;
    public function inner_current_exhausted()
    {
        return $this->inner_current_exhausted;
    }
    public function set_inner_current_exhausted( $boolean )
    {
        //echo "setting inner_current_exhausted = $boolean\n";
        $this->inner_current_exhausted = $boolean;
    }
} // end class Pipeline_IteratorProcessor
