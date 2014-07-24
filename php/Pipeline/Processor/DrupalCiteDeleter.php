<?php

class Pipeline_Processor_DrupalCiteDeleter extends Pipeline_Processor
{
    protected $deleter;
    public function deleter()
    {
        return $this->deleter;
    }
    // TODO: Should the deleter param have a class constraint? If so, what?
    protected function set_deleter($deleter)
    {
        $this->deleter = $deleter;
    }

    // TODO: Should the deleter param have a class constraint? If so, what?
    public function __construct($deleter)
    {
        $this->set_deleter( $deleter );
    }

    public function process($key, $current)
    {
        // key should be the record id:
        $record_id = $key;
        // Current should be a citation array:
        $citation = $current;
        // TODO: Make the deleter return the deleted node object or array?
        $this->deleter()->delete( $citation );
        return array($record_id, $citation); // TODO: Should be ($node_id, $node)
    }

    public function context()
    {
        return array(
            'record id' => $this->key(),
        );
    }
}
