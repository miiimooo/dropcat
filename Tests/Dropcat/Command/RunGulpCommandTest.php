<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-30
 * Time: 16:47
 */

namespace Dropcat\Tests;

use Dropcat\Command\RunGulpCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class RunGulpCommandTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // building the container!
        $this->container = new ContainerBuilder();

        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $this->conf = $configuration = new Configuration();

        $this->application = new Application();

        // We mock the command so that we later on can test Process.
        $this->mock = $this->getMockBuilder('Dropcat\Command\RunGulpCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testGulpWithAllOptions()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock
            ->setMethods([
                'runProcess',
                'NvmrcFileExists',
                ])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('bash -c \'source /the/nvm/folder/nvm.sh\' && . /the/nvm/folder/nvm.sh && nvm use && cd /the/gulp/dir && NODE_ENV=das-nvm-env gulp some-gulp-options'))
            ->willReturn($process_mock);

        $command_mock->method('NvmrcFileExists')
            ->with($this->equalTo('/the/path/to/nvmrc/file'))
            ->willReturn(true);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'node:gulp',
                '-gd' => '/the/gulp/dir',
                '-nd' => '/the/nvm/folder',
                '-nc' => '/the/path/to/nvmrc/file',
                '-go' => 'some-gulp-options',
                '-ne' => 'das-nvm-env',
            ),
            $options
        );
    }

    function testGulpExceptions()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $this->expectException('\\Exception');
        $command_mock = $this->mock
            ->setMethods([
                'runProcess',
                'NvmrcFileExists',
            ])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('bash -c \'source /the/nvm/folder/nvm.sh\' && . /the/nvm/folder/nvm.sh && nvm use && cd /the/gulp/dir && NODE_ENV=das-nvm-env gulp some-gulp-options'))
            ->willReturn($process_mock);

        $command_mock->method('NvmrcFileExists')
            ->with($this->equalTo('/the/path/to/nvmrc/file'))
            ->willReturn(true);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'node:gulp',
                '-gd' => '/the/gulp/dir',
                '-nd' => '/the/nvm/folder',
                '-nc' => '/the/path/to/nvmrc/file',
                '-go' => 'some-gulp-options',
                '-ne' => 'das-nvm-env',
            ),
            $options
        );
    }


    function testGulpWithOptionsMissing()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock
            ->setMethods([
                'runProcess',
                'NvmrcFileExists',
            ])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('bash -c \'source /the/nvm/folder/nvm.sh\' && . /the/nvm/folder/nvm.sh && nvm use && cd . && NODE_ENV=das-nvm-env gulp some-gulp-options'))
            ->willReturn($process_mock);

        $command_mock->method('NvmrcFileExists')
            ->with($this->equalTo(getcwd() . DIRECTORY_SEPARATOR . '.nvmrc'))
            ->willReturn(true);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'node:gulp',
                '-nd' => '/the/nvm/folder',
                '-go' => 'some-gulp-options',
                '-ne' => 'das-nvm-env',
            ),
            $options
        );
    }


    function testGulpWithOptionsMissingAndNvrmrcFileMissing()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock
            ->setMethods([
                'runProcess',
                'NvmrcFileExists',
            ])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('bash -c \'source /the/nvm/folder/nvm.sh\' && . /the/nvm/folder/nvm.sh && nvm use && cd . && NODE_ENV=das-nvm-env gulp some-gulp-options'))
            ->willReturn($process_mock);

        $command_mock->method('NvmrcFileExists')
            ->with($this->equalTo(getcwd() . DIRECTORY_SEPARATOR . '.nvmrc'))
            ->willReturn(false);
        $this->expectException('\\Exception');

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'node:gulp',
                '-nd' => '/the/nvm/folder',
                '-go' => 'some-gulp-options',
                '-ne' => 'das-nvm-env',
            ),
            $options
        );
    }
}
