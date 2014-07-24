<?php

class Pipeline_Adapter extends Pipeline_Processor
{
    protected $container;
    public function container()
    {
        return $this->container;
    }
    protected function set_container($container)
    {
        $this->container = $container;
    }

    protected $function;
    public function function()
    {
        return $this->function;
    }
    protected function set_function($function)
    {
        $this->function = $function;
    }

    public function __construct($container)
    {
        $this->set_container( $container );
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

        // Input:
        array(
            'container' => 'Foo',
            'function' => 'bar',
            'input' => array(
                ':key',
                ':current',
                $foo,
                $bar,
            ),
            'output' => array(
                ':key' => ':0'
                ':current' => ':1'
            ),
            'context' => array(

            ),
        );
    }

    public function context()
    {
        return array(
            'record id' => $this->key(),
        );
    }
}
