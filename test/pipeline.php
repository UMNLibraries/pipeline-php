#!/usr/bin/php -q
<?php

require_once 'simpletest/autorun.php';
SimpleTest :: prefer(new TextReporter());
set_include_path('../php' . PATH_SEPARATOR . get_include_path());
require_once 'Pipeline.php';
require_once 'Pipeline/Processor.php';
require_once 'Pipeline/IteratorProcessor.php';
require_once 'File/Find/Rule.php';

ini_set('memory_limit', '2G');

//error_reporting( E_STRICT );
error_reporting( E_ALL );

class PipelineTest extends UnitTestCase
{
    public function __construct()
    {
    }

    public function test_basic()
    {
        $input_array = array(1, 2, 3, 4, 5);
        $pipeline = new Pipeline(
            new ArrayIterator($input_array),
            new NoOpProcessor()
        );
        $this->assertIsA($pipeline, 'Pipeline');

        $output_array = array();
        $iterator = $pipeline->iterator();
        $iterator->rewind();
        while ($iterator->valid()) {
            $output_array[] = $iterator->current();
            $iterator->next();
        }

        $this->assertEqual($output_array, $input_array);
    }

    public function test_pipeline_run()
    {
        $sum_processor = new SumProcessor();
        $pipeline = new Pipeline(
            new ArrayIterator(array(1, 2, 3, 4, 5)),
            new Times2Processor(),
            $sum_processor
        );
        $pipeline->run();
        $this->assertEqual($sum_processor->sum(), 30);
    }

    public function test_context()
    {
        $pipeline = new Pipeline(
            new PipelineArrayIterator(array(1, 2, 3, 4, 5)),
            new Times2Processor(),
            new SumProcessor()
        );

        $context_by_iteration = array(
            array(
                array(
                    'SumProcessor' => array(
                        'sum' => 2,
                    )
                ),
                array(
                    'Times2Processor' => array(
                        'input' => 1,
                        'output' => 2,
                    ),
                ),
                array(
                    'PipelineArrayIterator' => array(
                        'key' => 0,
                        'current' => 1,
                    ),
                ),
            ),
            array(
                array(
                    'SumProcessor' => array(
                        'sum' => 6,
                    )
                ),
                array(
                    'Times2Processor' => array(
                        'input' => 2,
                        'output' => 4,
                    ),
                ),
                array(
                    'PipelineArrayIterator' => array(
                        'key' => 1,
                        'current' => 2,
                    ),
                ),
            ),
            array(
                array(
                    'SumProcessor' => array(
                        'sum' => 12,
                    )
                ),
                array(
                    'Times2Processor' => array(
                        'input' => 3,
                        'output' => 6,
                    ),
                ),
                array(
                    'PipelineArrayIterator' => array(
                        'key' => 2,
                        'current' => 3,
                    ),
                ),
            ),
            array(
                array(
                    'SumProcessor' => array(
                        'sum' => 20,
                    )
                ),
                array(
                    'Times2Processor' => array(
                        'input' => 4,
                        'output' => 8,
                    ),
                ),
                array(
                    'PipelineArrayIterator' => array(
                        'key' => 3,
                        'current' => 4,
                    ),
                ),
            ),
            array(
                array(
                    'SumProcessor' => array(
                        'sum' => 30,
                    )
                ),
                array(
                    'Times2Processor' => array(
                        'input' => 5,
                        'output' => 10,
                    ),
                ),
                array(
                    'PipelineArrayIterator' => array(
                        'key' => 4,
                        'current' => 5,
                    ),
                ),
            ),
        );

        $iterator = $pipeline->iterator();
        $iterator->rewind();
        $iteration = 0;
        while ($iterator->valid()) {

            $pipeline_context = $iterator->pipeline_context();
            //echo "pipeline_context = "; print_r( $pipeline_context ); echo "\n";
            $this->assertEqual(
                $pipeline_context,
                $context_by_iteration[$iteration]
            );

            /* Not so concerned about testing this...
            $formatted_pipeline_context =
                $iterator->format_pipeline_context( $pipeline_context );
            echo "formatted_pipeline_context =\n$formatted_pipeline_context\n";
            */

            $iterator->next();
            $iteration++;
        }
    }

    public function test_filtering_processors()
    {
        $odds_filter = new OddsFilterProcessor();
        $pipeline = new Pipeline(
            new ArrayIterator(array(1, 2, 3, 4, 5)),
            $odds_filter
        );
        $pipeline->run();
        $this->assertEqual($odds_filter->evens(), array(2,4));
    }

    public function test_exception_handling()
    {
        $exception_processor = new NonIntExceptionProcessor();
        $pipeline = new Pipeline(
            // From the Simpsons episode "Rednecks and Broomsticks":
            new ArrayIterator(array(
                1,
                2,
                'backwards E',
                'one-legged triangle',
                'banana hot dog',
                'double banana hot dog',
                'sixty corncob two',
            )),
            $exception_processor
        );
        $pipeline->run();

        $this->assertEqual($exception_processor->integers(), array(1,2));
        $this->assertEqual(
            $exception_processor->exceptions(),
            array(
                "'backwards E' is not an integer.",
                "'one-legged triangle' is not an integer.",
                "'banana hot dog' is not an integer.",
                "'double banana hot dog' is not an integer.",
                "'sixty corncob two' is not an integer.",
           )
        );
    }

    public function test_iterator_processors()
    {
        $pipeline = new Pipeline(
            new ArrayIterator(array(
                'foo' => array(1, 2, 3),
                'bar' => array(4, 5,),
            )),
            new ArrayIteratorGenerator(),
            new SubArrayProcessor()
        );

        $context_by_iteration = array(
            array(
                array(
                    'SubArrayProcessor' => array(
                        'key' => 0,
                        'current' => 1,
                    )
                ),
                array(
                    'ArrayIteratorGenerator' => array(
                        'key' => 'foo',
                    ),
                ),
                array(
                    'ArrayIterator' => array(
                        'key' => 'foo',
                    ),
                ),
            ),
            array(
                array(
                    'SubArrayProcessor' => array(
                        'key' => 1,
                        'current' => 2,
                    )
                ),
                array(
                    'ArrayIteratorGenerator' => array(
                        'key' => 'foo',
                    ),
                ),
                array(
                    'ArrayIterator' => array(
                        'key' => 'foo',
                    ),
                ),
            ),
            array(
                array(
                    'SubArrayProcessor' => array(
                        'key' => 2,
                        'current' => 3,
                    )
                ),
                array(
                    'ArrayIteratorGenerator' => array(
                        'key' => 'foo',
                    ),
                ),
                array(
                    'ArrayIterator' => array(
                        'key' => 'foo',
                    ),
                ),
            ),
            array(
                array(
                    'SubArrayProcessor' => array(
                        'key' => 0,
                        'current' => 4,
                    )
                ),
                array(
                    'ArrayIteratorGenerator' => array(
                        'key' => 'bar',
                    ),
                ),
                array(
                    'ArrayIterator' => array(
                        'key' => 'bar',
                    ),
                ),
            ),
            array(
                array(
                    'SubArrayProcessor' => array(
                        'key' => 1,
                        'current' => 5,
                    )
                ),
                array(
                    'ArrayIteratorGenerator' => array(
                        'key' => 'bar',
                    ),
                ),
                array(
                    'ArrayIterator' => array(
                        'key' => 'bar',
                    ),
                ),
            ),
        );

        $output_array = array();
        $iterator = $pipeline->iterator();
        $iterator->rewind();
        $iteration = 0;
        while ($iterator->valid()) {
            $output_array[] = $iterator->current();

            $pipeline_context = $iterator->pipeline_context();
            //echo "context = "; print_r( $context ); echo "\n";
            $this->assertEqual( $pipeline_context, $context_by_iteration[$iteration] );

            $iterator->next();
            $iteration++;
        }
        $this->assertEqual($output_array, array(1, 2, 3, 4, 5));
    }

    public function test_pre_post_hooks()
    {
        $f = new File_Find_Rule();
        $directory = getcwd();
        $file_names = $f->name('*.txt')->in( $directory );

        $file_size_checker_processor = new FileSizeCheckerProcessor();

        $pipeline = new Pipeline(
            new FileNameIterator($file_names),
            new FileGetContentsThenGzipProcessor(),
            $file_size_checker_processor
            
        );

        $iterator = $pipeline->iterator();
        $iterator->rewind();
        while ($iterator->valid()) {
            $output_array[] = $iterator->current();

            //$pipeline_context = $iterator->pipeline_context();
            //echo "context = "; print_r( $pipeline_context ); echo "\n";

            // Note: This test is a bit contrived and magical. Because the
            // FileGetContentsThenGzipProcessor gzips each file in a post hook, this
            // test will succeed ONLY if the post hook executes after the 
            // FileSizeCheckerProcessor has had a chance to get the file size.
            // The reason this is contrived is because I needed this gzip-files
            // functionality for a specific project.
            $this->assertEqual(
                $file_size_checker_processor->file_size(),
                $file_size_checker_processor->file_contents_length()
            );

            $iterator->next();
        }

        // cleanup:
        foreach ($file_names as $file_name)
        {
            $file = fopen($file_name, 'w');
            $gzip_file_name = $file_name . '.gz';

            $lines = gzfile($gzip_file_name);
            foreach ($lines as $line) {
                fwrite($file, $line);
            }
            fclose($file);
            unlink($gzip_file_name);
        }
    }

} // end class PipelineTest

class NoOpProcessor extends Pipeline_Processor
{
    // The simplest possible processor:
    public function process ( $key, $current )
    {
        return array($key, $current);
    }
}

class PipelineArrayIterator extends ArrayIterator
{
    public function context()
    {
        return array(
            'key' => $this->key(),
            'current' => $this->current(),
        );
    }
}

class Times2Processor extends Pipeline_Processor
{
    public function process( $key, $current )
    {
        $this->set_input($current);
        $output = $current * 2;
        return array($key, $output);
    }

    protected $input;
    public function input()
    {
        return $this->input;
    }
    public function set_input( $input )
    {
        $this->input = $input;
    }

    public function context()
    {
        return array(
            'input' => $this->input(),
            'output' => $this->current(),
        );
    }
}

class SumProcessor extends Pipeline_Processor
{
    public function process( $key, $current )
    {
        $this->add_to_sum( $current );
        return array($key, $current);
    }

    protected $sum = 0;
    public function sum()
    {
        return $this->sum;
    }
    public function add_to_sum( $value )
    {
        $this->sum += $value;
    }

    public function context()
    {
        return array(
            'sum' => $this->sum(),
        );
    }
}

class OddsFilterProcessor extends Pipeline_Processor
{
    public function process ( $key, $current )
    {
        if ($current % 2) {
            // Returning an empty array results in "Undefined offset" E_NOTICE's
            // in the calling code, so we return nulls instead:
            return array(null, null);
        }
        $this->push_evens( $current );
        return array($key, $current);
    }

    protected $evens = array();
    public function evens()
    {
        return $this->evens;
    }
    public function push_evens( $value )
    {
        $this->evens[] = $value;
    }
}

class NonIntExceptionProcessor extends Pipeline_Processor
{
    public function process ( $key, $current )
    {
        if (!is_int($current)) {
            throw new Exception("'$current' is not an integer.");
        }
        $this->push_integers( $current );
        return array($key, $current);
    }

    protected function handle_exception( $e )
    {
        $this->push_exceptions( $e->getMessage() );
    }

    protected $integers = array();
    public function integers()
    {
        return $this->integers;
    }
    public function push_integers( $integer )
    {
        $this->integers[] = $integer;
    }
    
    protected $exceptions = array();
    public function exceptions()
    {
        return $this->exceptions;
    }
    public function push_exceptions( $exception )
    {
        $this->exceptions[] = $exception;
    }
}

class ArrayIteratorGenerator extends Pipeline_Processor
{
    public function process ($key, $current)
    {
        $array_iterator = new ArrayIterator($current);
        //$this->set_context_property('array_key', $key);
        return array($key, $array_iterator);
    }

    public function context()
    {
        return array(
            'key' => $this->key(),
        );
    }
}

class SubArrayProcessor extends Pipeline_IteratorProcessor
{
    public function process ($key, $current)
    {
        //$this->set_context_property('current', $current);
        return array($key, $current);
    }
}

class FileNameIterator extends ArrayIterator
{
    public function context()
    {
        return array(
            'file' => $this->current(),
        );
    }

}

class FileGetContentsThenGzipProcessor extends Pipeline_Processor
{
    public function process($key, $current)
    {
        $file_name = $current;
        $this->set_file_name( $file_name );
        $file_contents = file_get_contents( $current );
        $this->set_file_contents( $file_contents );

        return array($file_name, $file_contents);
    }

    protected $file_name;
    public function file_name()
    {
        return $this->file_name;
    }
    public function set_file_name( $file_name )
    {
        $this->file_name = $file_name;
    }

    protected $file_contents;
    public function file_contents()
    {
        return $this->file_contents;
    }
    public function set_file_contents( $file_contents )
    {
        $this->file_contents = $file_contents;
    }

    public function context()
    {
        return array(
            'file_name' => $this->file_name(),
            'file_contents' => $this->file_contents(),
        );
    }

    // gzip the file in a post hook, so that other processors later in the
    // pipeline can do things with the file before we gzip it. In other
    // words, this hook should get executed only after all other pipeline
    // processors later in the chain have executed.
    public function post()
    {
        $file_name = $this->file_name();
        $zip_file_name = "$file_name.gz";
        $zip_file_contents = $this->file_contents();
        $zip_file = gzopen($zip_file_name, "w9");
        gzwrite( $zip_file, $zip_file_contents );
        gzclose( $zip_file );
        unlink( $file_name );
    }
}

class FileSizeCheckerProcessor extends Pipeline_Processor
{
    public function process($key, $current)
    {
        $file_name = $key;
        $this->set_file_name( $file_name );

        $file_contents = $current;
        $file_contents_length = strlen( $file_contents );
        $this->set_file_contents_length( $file_contents_length );

        $file_size = filesize( $file_name );
        $this->set_file_size( $file_size );

        return array($file_name, $file_size);
    }

    protected $file_name;
    public function file_name()
    {
        return $this->file_name;
    }
    public function set_file_name( $file_name )
    {
        $this->file_name = $file_name;
    }

    protected $file_size;
    public function file_size()
    {
        return $this->file_size;
    }
    public function set_file_size( $file_size )
    {
        $this->file_size = $file_size;
    }

    protected $file_contents_length;
    public function file_contents_length()
    {
        return $this->file_contents_length;
    }
    public function set_file_contents_length( $file_contents_length )
    {
        $this->file_contents_length = $file_contents_length;
    }

    public function context()
    {
        return array(
            'file_name' => $this->file_name(),
            'file_size' => $this->file_size(),
            'file_contents_length' => $this->file_contents_length(),
        );
    }
}
