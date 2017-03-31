<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 10:28
 */

namespace Dropcat\Tests;

use Dropcat\Command\RunRemoteCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class RunRemoteCommandTest extends \PHPUnit_Framework_TestCase
{

    private $factories_mock;

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
        $this->mock = $this->getMockBuilder('Dropcat\Command\RunRemoteCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testRunRemoteCommand()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['readIdentityFile'])
            ->getMock();

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('identifyFileValue'))
            ->willReturn('');

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->method('setPassword')
            ->with($this->equalTo('sshKeyPassword'))
            ->willReturn(true);

        $rsaMock->method('loadKey')
            ->with($this->equalTo(''))
            ->willReturn(true);

        $this->container->set('rsa',$rsaMock);


        $sshMock = $this->getMockBuilder("\\phpseclib\\Net\\SSH2")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['login','exec'])
            ->getMock();

        $sshMock->method('login')
            ->with($this->equalTo('userValue'))
            ->willReturn(true);

        $sshMock->method('exec')
            ->with($this->equalTo('command to run'))
            ->willReturn(true);

        $this->factories_mock->method('ssh')->willReturn($sshMock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'run-remote',
                '-i' => 'command to run',
                '-s' => 'serverValue',
                '-u' => 'userValue',
                '-p' => 'sshPort',
                '-skp' => 'sshKeyPassword',
                '-if' => 'identifyFileValue',
            ),
            $options
        );
    }


    function testRunRemoteCommandFailToLogin()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['readIdentityFile','exitCommand'])
            ->getMock();

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('identifyFileValue'))
            ->willReturn('');
        $command_mock->method('exitCommand')
            ->with($this->equalTo('Login Failed'));

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->method('setPassword')
            ->with($this->equalTo('sshKeyPassword'))
            ->willReturn(true);

        $rsaMock->method('loadKey')
            ->with($this->equalTo(''))
            ->willReturn(true);

        $this->container->set('rsa',$rsaMock);


        $sshMock = $this->getMockBuilder("\\phpseclib\\Net\\SSH2")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['login','exec'])
            ->getMock();

        $sshMock->method('login')
            ->with($this->equalTo('userValue'))
            ->willReturn(false);


        $sshMock->method('exec')
            ->with($this->equalTo('command to run'))
            ->willReturn(true);

        $this->factories_mock->method('ssh')->willReturn($sshMock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'run-remote',
                '-i' => 'command to run',
                '-s' => 'serverValue',
                '-u' => 'userValue',
                '-p' => 'sshPort',
                '-skp' => 'sshKeyPassword',
                '-if' => 'identifyFileValue',
            ),
            $options
        );
    }
}
