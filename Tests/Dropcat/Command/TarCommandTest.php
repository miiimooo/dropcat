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
        $application->add(new TarCommand());
        $command = $application->find('dropcat:tar');
        $this->tester =  new CommandTester($command);
    }

    function testTarAppFolder()
    {
        $this->tester->execute(
          array(
            'command' => 'dropcat:tar',
            '-f'      => realpath(__DIR__)
          )
        );
        $this->assertEquals($this->tester->getDisplay(),
          'Task: dropcat:tar finished' . "\n");
        $this->conf = new Configuration();

        $this->assertFileExists($this->conf->pathToTarFileInTemp());

        $tar_library = new Archive_Tar($this->conf->pathToTarFileInTemp());
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
            'command' => 'dropcat:tar',
            '-f'      => $t
          )
        );
    }
}
