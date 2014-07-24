<?php

abstract class Pipeline_Processor implements OuterIterator // Similar to FilterIterator
{
    protected $inner_iterator;
    public function getInnerIterator()
    {
        return $this->inner_iterator();
    }
    public function inner_iterator()
    {
        return $this->inner_iterator;
    }
    public function set_inner_iterator(Iterator $iterator)
    {
        $this->inner_iterator = $iterator;
    }

    public function context()
    {
        return array(
            'key' => $this->key(),
            'current' => $this->current(),
        );
    }

    public function format_pipeline_context( $pipeline_context )
    {
        // TODO: Come up with a more generic solution that doesn't assume
        // a certain context array structure?

        $formatted_pipeline_context = '';
        foreach ($pipeline_context as $class_context) {
            foreach ($class_context as $class => $context) {
                $formatted_pipeline_context .= "    $class:\n";
                foreach ($context as $k => $v) {
                    $formatted_pipeline_context .= "        $k: $v\n";
                }
            }
        }
        return $formatted_pipeline_context;
    }

    public function pipeline_context()
    {
        $iterator = $this;
        $context = array();
        while(1) {
            $class = get_class( $iterator );
            $rc = new ReflectionClass( $class );

            $class_context = array();

            if ($rc->hasMethod('context')) {
                $class_context += $iterator->context();
            } else {
                // Include only the key in this case, because anything
                // else may be too awkward to include in error messages:
                $class_context['key'] = $iterator->key();
            }

            // To keep the iterators in order, use default numerical
            // indexes on the context array:
            $context[] = array($class => $class_context);

            if ($rc->hasMethod('getInnerIterator')) {
                $iterator = $iterator->getInnerIterator();
                continue;
            }
            break;
        }
        return $context;
    }

    abstract public function process( $key, $current );

    protected function handle_exception( $e )
    {
        $message = $e->getMessage();
        $message .= $this->format_pipeline_context( $this->pipeline_context() );
        error_log( $message );
    }

    protected function fetch()
    {
        $this->set_valid( $this->inner_iterator()->valid() );

        while ($this->inner_iterator()->valid()) {

            $inner_key = $this->inner_iterator()->key();
            $this->set_inner_key( $inner_key );
            $inner_current = $this->inner_iterator()->current();
            $this->set_inner_current( $inner_current );

            $this->pre();

            unset ($key, $current);
            try {
                list($key, $current) = $this->process( $inner_key, $inner_current );
                //echo "process results: $key => $current\n";
            } catch (Exception $e) {
                $this->handle_exception( $e );
                $this->inner_iterator->next();
                continue;
            }
            
            // This allows for FilterIterator-like functionality: If no values are
            // returned from process() and no exception was thrown, assume that
            // process() meant to filter out this $key, $current pair:
            if (!isset($key, $current)) {
                $this->inner_iterator->next();
                continue;
            }

            $this->set_key( $key );
            $this->set_current( $current );

            //$this->post();

            return;
        }
        $this->set_valid( false );
    }

    // Inherited methods:

    public function next()
    {
        $this->post();
        $this->inner_iterator()->next();
        $this->fetch();
    }

    public function rewind()
    {
        $this->inner_iterator()->rewind();
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

    protected $inner_current;
    public function inner_current()
    {
        return $this->inner_current;
    }
    public function set_inner_current( $inner_current )
    {
        //echo "setting inner_current = $inner_current\n";
        $this->inner_current = $inner_current;
    }

    protected $inner_key;
    public function inner_key()
    {
        return $this->inner_key;
    }
    public function set_inner_key( $inner_key )
    {
        $this->inner_key = $inner_key;
    }
    // Mix of inherited methods and custom properties & methods.

    protected $current;
    public function current()
    {
        //return $this->process( $this->inner_iterator()->current() );
        //echo "current = {$this->current}\n";
        return $this->current;
    }
    public function set_current( $current )
    {
        //echo "setting current = $current\n";
        $this->current = $current;
    }

    protected $key;
    public function key()
    {
        //return $this->inner_iterator()->key();
        return $this->key;
    }
    public function set_key( $key )
    {
        $this->key = $key;
    }

    protected $valid;
    public function valid()
    {
        //return $this->inner_iterator()->valid();
        return $this->valid;
    }
    public function set_valid( $boolean )
    {
        $this->valid = $boolean;
    }

} // end class Pipeline_Processor
