<?php

namespace UmnLib\Core\Tests;

use UmnLib\Core\Pipeline;
use Symfony\Component\Finder\Finder;

{
  class PipelineTest extends \PHPUnit_Framework_TestCase
  {
    public function testBasic()
    {
      $inputArray = array(1, 2, 3, 4, 5);
      $pipeline = new Pipeline(
        new \ArrayIterator($inputArray),
        new PipelineTest\NoOpProcessor()
      );
      $this->assertInstanceOf('\UmnLib\Core\Pipeline', $pipeline);

      $outputArray = array();
      $iterator = $pipeline->iterator();
      $iterator->rewind();
      while ($iterator->valid()) {
        $outputArray[] = $iterator->current();
        $iterator->next();
      }

      $this->assertEquals($inputArray, $outputArray);
    }

    public function testPipelineRun()
    {
      $sumProcessor = new PipelineTest\SumProcessor();
      $pipeline = new Pipeline(
        new \ArrayIterator(array(1, 2, 3, 4, 5)),
        new PipelineTest\Times2Processor(),
        $sumProcessor
      );
      $pipeline->run();
      $this->assertEquals(30, $sumProcessor->sum());
    }

    public function testContext()
    {
      $pipeline = new Pipeline(
        new PipelineTest\PipelineArrayIterator(array(1, 2, 3, 4, 5)),
        new PipelineTest\Times2Processor(),
        new PipelineTest\SumProcessor()
      );

      $contextByIteration = array(
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SumProcessor' => array(
              'sum' => 2,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\Times2Processor' => array(
              'input' => 1,
              'output' => 2,
            ),
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\PipelineArrayIterator' => array(
              'key' => 0,
              'current' => 1,
            ),
          ),
        ),
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SumProcessor' => array(
              'sum' => 6,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\Times2Processor' => array(
              'input' => 2,
              'output' => 4,
            ),
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\PipelineArrayIterator' => array(
              'key' => 1,
              'current' => 2,
            ),
          ),
        ),
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SumProcessor' => array(
              'sum' => 12,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\Times2Processor' => array(
              'input' => 3,
              'output' => 6,
            ),
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\PipelineArrayIterator' => array(
              'key' => 2,
              'current' => 3,
            ),
          ),
        ),
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SumProcessor' => array(
              'sum' => 20,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\Times2Processor' => array(
              'input' => 4,
              'output' => 8,
            ),
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\PipelineArrayIterator' => array(
              'key' => 3,
              'current' => 4,
            ),
          ),
        ),
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SumProcessor' => array(
              'sum' => 30,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\Times2Processor' => array(
              'input' => 5,
              'output' => 10,
            ),
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\PipelineArrayIterator' => array(
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

        $pipelineContext = $iterator->pipelineContext();
        //echo "pipelineContext = "; print_r( $pipelineContext ); echo "\n";
        $this->assertEquals(
          $contextByIteration[$iteration],
          $pipelineContext
        );

            /* Not so concerned about testing this...
            $formattedPipelineContext =
                $iterator->formatPipelineContext( $pipelineContext );
            echo "formattedPipelineContext =\n$formattedPipelineContext\n";
             */

        $iterator->next();
        $iteration++;
      }
    }

    public function testFilteringProcessors()
    {
      $oddsFilter = new PipelineTest\OddsFilterProcessor();
      $pipeline = new Pipeline(
        new \ArrayIterator(array(1, 2, 3, 4, 5)),
        $oddsFilter
      );
      $pipeline->run();
      $this->assertEquals(array(2,4), $oddsFilter->evens());
    }

    public function testExceptionHandling()
    {
      $exceptionProcessor = new PipelineTest\NonIntExceptionProcessor();
      $pipeline = new Pipeline(
        // From the Simpsons episode "Rednecks and Broomsticks":
        new \ArrayIterator(array(
          1,
          2,
          'backwards E',
          'one-legged triangle',
          'banana hot dog',
          'double banana hot dog',
          'sixty corncob two',
        )),
        $exceptionProcessor
      );
      $pipeline->run();

      $this->assertEquals(array(1,2), $exceptionProcessor->integers());
      $this->assertEquals(
        array(
          "'backwards E' is not an integer.",
          "'one-legged triangle' is not an integer.",
          "'banana hot dog' is not an integer.",
          "'double banana hot dog' is not an integer.",
          "'sixty corncob two' is not an integer.",
        ),
        $exceptionProcessor->exceptions()
      );
    }

    public function testIteratorProcessors()
    {
      $pipeline = new Pipeline(
        new \ArrayIterator(array(
          'foo' => array(1, 2, 3),
          'bar' => array(4, 5,),
        )),
        new PipelineTest\ArrayIteratorGenerator(),
        new PipelineTest\SubArrayProcessor()
      );

      $contextByIteration = array(
        array(
          array(
            'UmnLib\Core\Tests\PipelineTest\SubArrayProcessor' => array(
              'key' => 0,
              'current' => 1,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\ArrayIteratorGenerator' => array(
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
            'UmnLib\Core\Tests\PipelineTest\SubArrayProcessor' => array(
              'key' => 1,
              'current' => 2,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\ArrayIteratorGenerator' => array(
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
            'UmnLib\Core\Tests\PipelineTest\SubArrayProcessor' => array(
              'key' => 2,
              'current' => 3,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\ArrayIteratorGenerator' => array(
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
            'UmnLib\Core\Tests\PipelineTest\SubArrayProcessor' => array(
              'key' => 0,
              'current' => 4,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\ArrayIteratorGenerator' => array(
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
            'UmnLib\Core\Tests\PipelineTest\SubArrayProcessor' => array(
              'key' => 1,
              'current' => 5,
            )
          ),
          array(
            'UmnLib\Core\Tests\PipelineTest\ArrayIteratorGenerator' => array(
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

      $outputArray = array();
      $iterator = $pipeline->iterator();
      $iterator->rewind();
      $iteration = 0;
      while ($iterator->valid()) {
        $outputArray[] = $iterator->current();

        $pipelineContext = $iterator->pipelineContext();
        //echo "context = "; print_r($context); echo "\n";
        $this->assertEquals($contextByIteration[$iteration], $pipelineContext);

        $iterator->next();
        $iteration++;
      }
      $this->assertEquals(array(1, 2, 3, 4, 5), $outputArray);
    }

    public function testPrePostHooks()
    {
      $finder = new Finder();
      $files = $finder->name('*.xml')->in(dirname(__FILE__) . '/fixtures');
      $filenames = array();
      foreach($files as $file) {
        $filenames[] = $file->getRealPath();
      }

      $fileSizeCheckerProcessor = new PipelineTest\FileSizeCheckerProcessor();

      $pipeline = new Pipeline(
        new PipelineTest\FilenameIterator($filenames),
        new PipelineTest\FileGetContentsThenGzipProcessor(),
        $fileSizeCheckerProcessor

      );

      $iterator = $pipeline->iterator();
      $iterator->rewind();
      while ($iterator->valid()) {
        $outputArray[] = $iterator->current();

        //$pipelineContext = $iterator->pipelineContext();
        //echo "context = "; print_r($pipelineContext); echo "\n";

        // Note: This test is a bit contrived and magical. Because the
        // FileGetContentsThenGzipProcessor gzips each file in a post hook, this
        // test will succeed ONLY if the post hook executes after the 
        // FileSizeCheckerProcessor has had a chance to get the file size.
        // The reason this is contrived is because I needed this gzip-files
        // functionality for a specific project.
        $this->assertEquals(
          $fileSizeCheckerProcessor->fileContentsLength(),
          $fileSizeCheckerProcessor->fileSize()
        );

        $iterator->next();
      }

      // cleanup:
      foreach ($filenames as $filename)
      {
        $file = fopen($filename, 'w');
        $gzipFilename = $filename . '.gz';

        $lines = gzfile($gzipFilename);
        foreach ($lines as $line) {
          fwrite($file, $line);
        }
        fclose($file);
        unlink($gzipFilename);
      }
    }
  }
}

namespace UmnLib\Core\Tests\PipelineTest;

{
  class NoOpProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    // The simplest possible processor:
    public function process ($key, $current)
    {
      return array($key, $current);
    }
  }

  class PipelineArrayIterator extends \ArrayIterator
  {
    public function context()
    {
      return array(
        'key' => $this->key(),
        'current' => $this->current(),
      );
    }
  }

  class Times2Processor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process($key, $current) 
    {
      $this->setInput($current);
      $output = $current * 2;
      return array($key, $output);
    }

    protected $input;
    public function input()
    {
      return $this->input;
    }
    public function setInput($input)
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

  class SumProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process($key, $current)
    {
      $this->addToSum($current);
      return array($key, $current);
    }

    protected $sum = 0;
    public function sum()
    {
      return $this->sum;
    }
    public function addToSum($value)
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

  class OddsFilterProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process ($key, $current)
    {
      if ($current % 2) {
        // Returning an empty array results in "Undefined offset" E_NOTICE's
        // in the calling code, so we return nulls instead:
        return array(null, null);
      }
      $this->pushEvens($current);
      return array($key, $current);
    }

    protected $evens = array();
    public function evens()
    {
      return $this->evens;
    }
    public function pushEvens($value)
    {
      $this->evens[] = $value;
    }
  }

  class NonIntExceptionProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process ($key, $current)
    {
      if (!is_int($current)) {
        throw new \InvalidArgumentException("'$current' is not an integer.");
      }
      $this->pushIntegers($current);
      return array($key, $current);
    }

    protected function handleException(\Exception $e)
    {
      // TODO: This method seems badly named, since we are only pushing the messages!
      $this->pushExceptions($e->getMessage());
    }

    protected $integers = array();
    public function integers()
    {
      return $this->integers;
    }
    public function pushIntegers($integer)
    {
      $this->integers[] = $integer;
    }

    protected $exceptions = array();
    public function exceptions()
    {
      return $this->exceptions;
    }
    public function pushExceptions($exception)
    {
      $this->exceptions[] = $exception;
    }
  }

  class ArrayIteratorGenerator extends \UmnLib\Core\Pipeline\Processor
  {
    public function process ($key, $current)
    {
      $arrayIterator = new \ArrayIterator($current);
      //$this->setContextProperty('arrayKey', $key);
      return array($key, $arrayIterator);
    }

    public function context()
    {
      return array(
        'key' => $this->key(),
      );
    }
  }

  class SubArrayProcessor extends \UmnLib\Core\Pipeline\IteratorProcessor
  {
    public function process ($key, $current)
    {
      //$this->setContextProperty('current', $current);
      return array($key, $current);
    }
  }

  class FilenameIterator extends \ArrayIterator
  {
    public function context()
    {
      return array(
        'file' => $this->current(),
      );
    }
  }

  class FileGetContentsThenGzipProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process($key, $current)
    {
      $filename = $current;
      $this->setFilename( $filename );
      $fileContents = file_get_contents( $current );
      $this->setFileContents( $fileContents );

      return array($filename, $fileContents);
    }

    protected $filename;
    public function filename()
    {
      return $this->filename;
    }
    public function setFilename( $filename )
    {
      $this->filename = $filename;
    }

    protected $fileContents;
    public function fileContents()
    {
      return $this->fileContents;
    }
    public function setFileContents( $fileContents )
    {
      $this->fileContents = $fileContents;
    }

    public function context()
    {
      return array(
        'filename' => $this->filename(),
        'fileContents' => $this->fileContents(),
      );
    }

    // gzip the file in a post hook, so that other processors later in the
    // pipeline can do things with the file before we gzip it. In other
    // words, this hook should get executed only after all other pipeline
    // processors later in the chain have executed.
    public function post()
    {
      $filename = $this->filename();
      $zipFilename = "$filename.gz";
      $zipFileContents = $this->fileContents();
      $zipFile = gzopen($zipFilename, "w9");
      gzwrite( $zipFile, $zipFileContents );
      gzclose( $zipFile );
      unlink( $filename );
    }
  }

  class FileSizeCheckerProcessor extends \UmnLib\Core\Pipeline\Processor
  {
    public function process($key, $current)
    {
      $filename = $key;
      $this->setFilename( $filename );

      $fileContents = $current;
      $fileContentsLength = strlen( $fileContents );
      $this->setFileContentsLength( $fileContentsLength );

      $fileSize = filesize( $filename );
      $this->setFileSize( $fileSize );

      return array($filename, $fileSize);
    }

    protected $filename;
    public function filename()
    {
      return $this->filename;
    }
    public function setFilename( $filename )
    {
      $this->filename = $filename;
    }

    protected $fileSize;
    public function fileSize()
    {
      return $this->fileSize;
    }
    public function setFileSize( $fileSize )
    {
      $this->fileSize = $fileSize;
    }

    protected $fileContentsLength;
    public function fileContentsLength()
    {
      return $this->fileContentsLength;
    }
    public function setFileContentsLength( $fileContentsLength )
    {
      $this->fileContentsLength = $fileContentsLength;
    }

    public function context()
    {
      return array(
        'filename' => $this->filename(),
        'fileSize' => $this->fileSize(),
        'fileContentsLength' => $this->fileContentsLength(),
      );
    }
  }
}
