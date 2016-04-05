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

        // set up to ignore files
        $dh = opendir($this->conf->localEnvironmentAppPath());
        $files_to_ignore = array();
        while (($file = readdir($dh)) !== false) {
            if ($file[0] !== '.' && basename(__FILE__) !== $file) {
                $files_to_ignore[] = $file;
            }
        }
        $this->conf->method('deployIgnoreFiles')->willReturn($files_to_ignore);


        $application = new Application();
        $application->add(new TarCommand($this->conf));
        $command      = $application->find('tar');
        $this->tester = new CommandTester($command);
    }

    public function testTarAppFolder()
    {

        $filename = $this->conf->localEnvironmentTmpPath() .
            $this->conf->localEnvironmentAppName() .
            $this->conf->localEnvironmentSeparator() .
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
        
        $this->assertEquals(\count($contents), 1);
        $this->assertEquals($contents[0]['filename'], basename(__FILE__));
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
