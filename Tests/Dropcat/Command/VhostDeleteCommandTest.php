<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 16:06
 */

namespace Dropcat\Tests;

use Dropcat\Command\VhostDeleteCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class VhostDeleteCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock = $this->getMockBuilder('Dropcat\Command\VhostDeleteCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testDeleteVhost()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess','readIdentityFile'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('ssh -o LogLevel=Error server-user@server-host -p ssh-port "rm vhost-target-folder/vhost_file_name"'))
            ->willReturn($process_mock);

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('/path/to/identity/file'))
            ->willReturn('Contents of id file');

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'vhost:create',
                '-t' => 'vhost-target-folder',
                '-f' => 'vhost_file_name',
                '-s' => 'server-host',
                '-u' => 'server-user',
                '-p' => 'ssh-port',
                '-i' => '/path/to/identity/file',
                '-skp' => 'ssh-key_password',
            ),
            $options
        );
    }

    function testDeleteVhostFailed()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);
        $this->expectException('\\Exception');

        $command_mock = $this->mock->setMethods(['runProcess','readIdentityFile'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('ssh -o LogLevel=Error server-user@server-host -p ssh-port "rm vhost-target-folder/vhost_file_name"'))
            ->willReturn($process_mock);

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('/path/to/identity/file'))
            ->willReturn('Contents of id file');

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'vhost:create',
                '-t' => 'vhost-target-folder',
                '-f' => 'vhost_file_name',
                '-s' => 'server-host',
                '-u' => 'server-user',
                '-p' => 'ssh-port',
                '-i' => '/path/to/identity/file',
                '-skp' => 'ssh-key_password',
            ),
            $options
        );
    }
}
