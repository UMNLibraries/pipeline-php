<?php

class Pipeline_Processor_DrupalCiteLoader extends Pipeline_Processor
{
    protected $loader;
    public function loader()
    {
        return $this->loader;
    }
    // TODO: Should the loader param have a class constraint? If so, what?
    protected function set_loader($loader)
    {
        $this->loader = $loader;
    }

    // TODO: Should the loader param have a class constraint? If so, what?
    public function __construct($loader)
    {
        $this->set_loader( $loader );
    }

    public function process($key, $current)
    {
        // key should be the record id:
        $record_id = $key;
        // Current should be a citation array:
        $citation = $current;
        // TODO: Make the loader return the created node object or array!
        $this->loader()->load( $citation );
        return array($record_id, $citation); // TODO: Should be ($node_id, $node)
    }

    public function context()
    {
        return array(
            'record id' => $this->key(),
        );
    }
}
