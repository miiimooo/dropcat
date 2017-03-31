<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 11:38
 */

namespace Dropcat\Tests;

use Dropcat\Command\SymlinkCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class SymlinkCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock = $this->getMockBuilder('Dropcat\Command\SymlinkCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testSymlink()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['readIdentityFile'])
            ->getMock();

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('/path/to/the/id/file'))
            ->willReturn('filecontents of key is here');

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->method('setPassword')
            ->with($this->equalTo('ssh_key_password_is_this'))
            ->willReturn(true);

        $rsaMock->method('loadKey')
            ->with($this->equalTo('filecontents of key is here'))
            ->willReturn(true);

        $this->container->set('rsa', $rsaMock);


        $sshMock = $this->getMockBuilder("\\phpseclib\\Net\\SSH2")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['login','exec','getExitStatus'])
            ->getMock();

        $sshMock->method('login')
            ->with($this->equalTo('server_user'))
            ->willReturn(true);


        $sshMock->expects($this->at(1))
            ->method('exec')
            ->with($this->equalTo('rm /sym/link/value.backup'))
            ->willReturn(true);

        $sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo('mv -b /sym/link/value /sym/link/value.backup'))
            ->willReturn(true);

        $sshMock->expects($this->at(3))
            ->method('exec')
            ->with($this->equalTo('ls -l /orig/path'))
            ->willReturn(true);


        $sshMock->method('getExitStatus')
            ->willReturn(0);

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
                'command' => 'symlink',
                '-o' => '/orig/path',
                '-sl' => '/sym/link/value',
                '-s' => 'da_server',
                '-u' => 'server_user',
                '-p' => 'server_ssh_port',
                '-i' => '/path/to/the/id/file',
                '-skp' => 'ssh_key_password_is_this',
                '-w' => 'web/root/path',
                '-aa' => 'alias',
            ),
            $options
        );
    }


    function testSymlinkLoginErrro()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['readIdentityFile','exitCommand'])
            ->getMock();

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('/path/to/the/id/file'))
            ->willReturn('filecontents of key is here');

        $e = new \Exception();
        $command_mock->method('exitCommand')
            ->willThrowException($e);

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->method('setPassword')
            ->with($this->equalTo('ssh_key_password_is_this'))
            ->willReturn(true);

        $rsaMock->method('loadKey')
            ->with($this->equalTo('filecontents of key is here'))
            ->willReturn(true);

        $this->container->set('rsa', $rsaMock);


        $sshMock = $this->getMockBuilder("\\phpseclib\\Net\\SSH2")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['login','exec','getExitStatus'])
            ->getMock();

        $sshMock->method('login')
            ->with($this->equalTo('server_user'))
            ->willReturn(false);
        $this->expectException('\\Exception');
        $this->expectOutputString('Login Failed using /path/to/the/id/file and user server_user at da_server 
');

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
                'command' => 'symlink',
                '-o' => '/orig/path',
                '-sl' => '/sym/link/value',
                '-s' => 'da_server',
                '-u' => 'server_user',
                '-p' => 'server_ssh_port',
                '-i' => '/path/to/the/id/file',
                '-skp' => 'ssh_key_password_is_this',
                '-w' => 'web/root/path',
                '-aa' => 'alias',
            ),
            $options
        );
    }



    function testSymlinkErrorExitStatus()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['readIdentityFile','exitCommand'])
            ->getMock();

        $command_mock->method('readIdentityFile')
            ->with($this->equalTo('/path/to/the/id/file'))
            ->willReturn('filecontents of key is here');

        $e = new \Exception();
        $command_mock->method('exitCommand')
            ->willThrowException($e);


        $this->expectException('\\Exception');

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->method('setPassword')
            ->with($this->equalTo('ssh_key_password_is_this'))
            ->willReturn(true);

        $rsaMock->method('loadKey')
            ->with($this->equalTo('filecontents of key is here'))
            ->willReturn(true);

        $this->container->set('rsa', $rsaMock);


        $sshMock = $this->getMockBuilder("\\phpseclib\\Net\\SSH2")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['login','exec','getExitStatus'])
            ->getMock();

        $sshMock->method('login')
            ->with($this->equalTo('server_user'))
            ->willReturn(true);


        $sshMock->expects($this->at(1))
            ->method('exec')
            ->with($this->equalTo('rm /sym/link/value.backup'))
            ->willReturn(true);

        $sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo('mv -b /sym/link/value /sym/link/value.backup'))
            ->willReturn(true);

        $sshMock->expects($this->at(3))
            ->method('exec')
            ->with($this->equalTo('ls -l /orig/path'))
            ->willReturn(true);

        $sshMock->method('getExitStatus')
            ->willReturn(666);

        $this->factories_mock->method('ssh')->willReturn($sshMock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectOutputString('original folder does not exist, creating it
could not create orginal folder, /orig/path, you need to create it manually, error code 666
');
        $this->tester->execute(
            array(
                'command' => 'symlink',
                '-o' => '/orig/path',
                '-sl' => '/sym/link/value',
                '-s' => 'da_server',
                '-u' => 'server_user',
                '-p' => 'server_ssh_port',
                '-i' => '/path/to/the/id/file',
                '-skp' => 'ssh_key_password_is_this',
                '-w' => 'web/root/path',
                '-aa' => 'alias',
            ),
            $options
        );

        echo $this->tester->getDisplay();
    }
}
