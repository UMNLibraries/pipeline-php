<?php

require_once 'Pipeline/Processor.php';

class Pipeline_Processor_MySQLiRowTransformer extends Pipeline_Processor
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
        // Current should be an array representing a row in a MySQLi_Result:
        $row = $current;
        $transformed_row = $this->transformer()->transform( $row );
        return array($key, $transformed_row);
    }

    public function context()
    {
        return array('row number' => $this->key());
    }
}
