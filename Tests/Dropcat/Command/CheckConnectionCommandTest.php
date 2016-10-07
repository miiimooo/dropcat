<?php
namespace Dropcat\tests;

use Dropcat\Command\CheckConnectionCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;


/**
 * Created by SOPA
 */
class CheckConnectionCommandTest extends \PHPUnit_Framework_TestCase
{

    private $conf;
    private $application;

    private $container;
    private $mock;
    private $tester;

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
        $this->mock        = $this->getMockBuilder('\Dropcat\Command\CheckConnectionCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testCheckConnection()
    {

        $this->container->setParameter('factory.libs.ssh', "phpseclib\\Net\\SSH2");
        $sshmock_placeholder = $this->getMockBuilder($this->container->getParameter('factory.libs.ssh'))
            ->setConstructorArgs(['Cathost', '1234']);


        $sshMock = $sshmock_placeholder->setMethods(
            [
                'login',
                'exec',
                'disconnect',
            ]
        )->getMock();

        $sshMock->method('disconnect')
            ->willReturn(true);

        $sshMock->expects($this->at(0))->method('login')
            ->willReturn(true);

        $sshMock->expects($this->at(1))
            ->method('exec')
            ->with($this->equalTo('/usr/bin/whoami'))
            ->willReturn('Catuser');

        $sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo('/bin/hostname'))
            ->willReturn('Cathost');

        $factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');

        $factories_mock->method('ssh')
            ->willReturn($sshMock);

        $this->container->set('dropcat.factory', $factories_mock);

        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
            ]
        )->getMock();

        $rsaMock->expects($this->once())
            ->method('setPassword')
            ->willReturn(true);

        $rsaMock->expects($this->once())
            ->method('loadKey')
            ->willReturn(true);

        $this->container->set('rsa', $rsaMock);


        // Add our mocked command from above.
        $this->application->add(
            new CheckConnectionCommand(
                $this->container,
                $this->conf
            )
        );

        // Initiate the tester.
        $this->tester = new CommandTester($this->application->find('check-connection'));

        $this->expectOutputString("Successfully logged in to server as user Catuser on Cathost.\n");
        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'check-connection',
                '-s' => 'Cathost',
                '-u' => 'Catuser',
                '-p' => '667',
                '-skp' => 'file-passwd',
                '-if' => '/dev/null'

            ),
            $options
        );

        echo $this->tester->getDisplay();
    }
}
