<?php

require_once 'Pipeline/IteratorProcessor.php';

class Pipeline_IteratorProcessor_XMLRecordTransformer extends Pipeline_IteratorProcessor
{
    protected $transformer;
    public function transformer()
    {
        return $this->transformer;
    }
    protected function set_transformer($transformer)
    {
        $this->transformer = $transformer;
    }

    public function __construct($transformer)
    {
        $this->set_transformer( $transformer );
    }

    public function process($key, $current)
    {
        // Current should be an XML_Record.
        $xml_record = $current;
        // TODO: Do I really want to do this here? Or should I have the transformer class handle it?
        if (!($xml_record instanceof XML_Record)) {
            throw new Exception("2nd argument to process() must be an instance of XML_Record");
        }
        $citation_array = $this->transformer()->transform( $xml_record );
        return array($xml_record->primary_id(), $citation_array);
    }

    public function context()
    {
        return array('record id' => $this->key());
    }
}
