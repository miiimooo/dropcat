<?php

use Dropcat\Command\TarCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 26/02/16
 * Time: 10:33
 */
class TarCommandTest extends \PHPUnit_Framework_TestCase
{

    function setUp() {
        $this->conf = $configuration = new Configuration();
        $application = new Application();
        $application->add(new TarCommand($this->conf));
        $command = $application->find('tar');
        $this->tester =  new CommandTester($command);
    }

    function testTarAppFolder()
    {
        $this->tester->execute(
          array(
            'command' => 'tar',
            '-f'      => realpath(__DIR__)
          )
        );
        $this->assertEquals($this->tester->getDisplay(),
          'Task: tar finished' . "\n");
        $this->conf = new Configuration();

        $filename = $this->conf->localEnvironmentTmpPath() .
            $this->conf->localEnvironmentAppName() .
            $this->conf->localEnvironmentSeperator() .
            $this->conf->localEnvironmentBuildId() . '.tar';
            $this->assertFileExists($filename);

        $tar_library = new Archive_Tar($filename);

        $contents    = $tar_library->listContent();
        $this->assertEquals(\count($contents), 1);
        $this->assertEquals($contents[0]['filename'], 'TarCommandTest.php');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to tar folder, Error message:
    Invalid file list

     */
    function testTarError()
    {
        $t = new StdClass();
        $this->tester->execute(
          array(
            'command' => 'tar',
            '-f'      => $t
          )
        );
    }
}
