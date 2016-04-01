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

    public function setUp()
    {
        $this->conf = $this->getMockBuilder('Dropcat\Services\Configuration')
            ->getMock();

        $this->conf->method('localEnvironmentTmpPath')->willReturn(realpath(__DIR__));
        $this->conf->method('localEnvironmentAppName')->willReturn('Tarcommandapptest');
        $this->conf->method('localEnvironmentAppPath')->willReturn(realpath(__DIR__));


        $application = new Application();
        $application->add(new TarCommand($this->conf));
        $command      = $application->find('tar');
        $this->tester = new CommandTester($command);
    }

    public function testTarAppFolder()
    {

        $filename = $this->conf->localEnvironmentTmpPath() .
            $this->conf->localEnvironmentAppName() .
            $this->conf->localEnvironmentSeperator() .
            $this->conf->localEnvironmentBuildId() . '.tar';
        $this->tester->execute(
            array(
                'command' => 'tar',
            )
        );

        $this->assertEquals(
            $this->tester->getDisplay(),
            'Task: tar finished' . "\n"
        );
        $this->assertFileExists($filename);

        $tar_library = new Archive_Tar($filename);

        $contents = $tar_library->listContent();
        // go through all files in this folder and count them
        $dh = opendir($this->conf->localEnvironmentAppPath());
        $counter = 0;
        $files = array();
        while (($file = readdir($dh)) !== false) {
            if ($file[0] !== '.') {
                $counter++;
                $files[] = $file;
            }
        }
        $this->assertEquals(\count($contents), $counter);

        foreach($files as $key => $file) {
            $this->assertEquals($contents[$key]['filename'], $file);
        }

    }

    /**
     * @expectedException \RuntimeException
     *
     */
    public function testTarError()
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
