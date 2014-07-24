<?php

class Pipeline_Processor_XMLRecordIteratorFactory extends Pipeline_Processor
{
    protected $factory;
    public function factory()
    {
        return $this->factory;
    }
    protected function set_factory(XML_Record_Iterator_Factory $factory)
    {
        $this->factory = $factory;
    }

    public function __construct(XML_Record_Iterator_Factory $factory)
    {
        $this->set_factory( $factory );
    }

    public function process($key, $current)
    {
        // Current should be a filename.
        $file_name = $current;
        $iterator = $this->factory()->create( $file_name );
        return array($file_name, $iterator);
    }

    public function context()
    {
        return array('file' => $this->key());
    }
}
