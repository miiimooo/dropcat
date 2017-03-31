<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 09:40
 */

namespace Dropcat\Tests;

use Dropcat\Command\RunNpmInstallCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class RunNpmInstallCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock = $this->getMockBuilder('Dropcat\Command\RunNpmInstallCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testNpmInstall()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess','nvmrcFileExists'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalto('bash -c \'source /the/nvm/directory/nvm.sh\' && . /the/nvm/directory/nvm.sh && nvm install && npm install'))
            ->willReturn($process_mock);

        $command_mock->method('nvmrcFileExists')
            ->with($this->equalTo('/path/to/the/nvmrc/file'))
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
                'command' => 'node:npm-install',
                '-nd' => '/the/nvm/directory',
                '-nc' => '/path/to/the/nvmrc/file',
            ),
            $options
        );
    }

    function testNpmInstallWithMissingNvmrc()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess','nvmrcFileExists'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalto('bash -c \'source /the/nvm/directory/nvm.sh\' && . /the/nvm/directory/nvm.sh && nvm install && npm install'))
            ->willReturn($process_mock);

        $command_mock->method('nvmrcFileExists')
            ->with($this->equalTo(getcwd() . DIRECTORY_SEPARATOR .'.nvmrc'))
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
                'command' => 'node:npm-install',
                '-nd' => '/the/nvm/directory',
            ),
            $options
        );
    }

    function testNpmInstallWithNoNvmDir()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess','nvmrcFileExists'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalto('bash -c \'source /the/nvm/directory/nvm.sh\' && . /the/nvm/directory/nvm.sh && nvm install && npm install'))
            ->willReturn($process_mock);

        $command_mock->method('nvmrcFileExists')
            ->with($this->equalTo(getcwd() . DIRECTORY_SEPARATOR .'.nvmrc'))
            ->willReturn(false);

        $this->expectException('\\Exception');
        $this->expectExceptionMessage('No nvm dir found in options.');

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'node:npm-install',
            ),
            $options
        );
    }
    function testNpmInstallWithProcessFailing()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);
        $this->expectException('\Symfony\Component\Process\Exception\ProcessFailedException');

        $command_mock = $this->mock->setMethods(['runProcess','nvmrcFileExists'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalto('bash -c \'source /the/nvm/directory/nvm.sh\' && . /the/nvm/directory/nvm.sh && nvm install && npm install'))
            ->willReturn($process_mock);

        $command_mock->method('nvmrcFileExists')
            ->with($this->equalTo(getcwd() . DIRECTORY_SEPARATOR .'.nvmrc'))
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
                'command' => 'node:npm-install',
                '-nd' => '/the/nvm/directory',
            ),
            $options
        );
    }
}
