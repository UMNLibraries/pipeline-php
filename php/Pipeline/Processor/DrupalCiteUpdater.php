<?php

class Pipeline_Processor_DrupalCiteUpdater extends Pipeline_Processor
{
    protected $updater;
    public function updater()
    {
        return $this->updater;
    }
    // TODO: Should the updater param have a class constraint? If so, what?
    protected function set_updater($updater)
    {
        $this->updater = $updater;
    }

    // TODO: Should the updater param have a class constraint? If so, what?
    public function __construct($updater)
    {
        $this->set_updater( $updater );
    }

    public function process($key, $current)
    {
        // key should be the record id:
        $record_id = $key;
        // Current should be a citation array:
        $citation = $current;
        // TODO: Make the updater return the created node object or array!
        $this->updater()->update( $citation );
        return array($record_id, $citation); // TODO: Should be ($node_id, $node)
    }

    public function context()
    {
        return array(
            'record id' => $this->key(),
        );
    }
}
