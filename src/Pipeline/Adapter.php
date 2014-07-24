<?php

namespace UmnLib\Core\Pipeline;

class Adapter extends \UmnLib\Core\Pipeline\Processor
{
    protected $container;
    public function container()
    {
        return $this->container;
    }
    protected function setContainer($container)
    {
        $this->container = $container;
    }

    protected $function;
    public function function()
    {
        return $this->function;
    }
    protected function setFunction($function)
    {
        $this->function = $function;
    }

    public function __construct($container)
    {
        $this->setContainer( $container );
    }

    public function process($key, $current)
    {
        // key should be the record id:
        $recordId = $key;
        // Current should be a citation array:
        $citation = $current;
        // TODO: Make the loader return the created node object or array!
        $this->loader()->load($citation);
        return array($recordId, $citation); // TODO: Should be ($node_id, $node)

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
