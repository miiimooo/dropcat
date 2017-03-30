<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-30
 * Time: 16:22
 */

namespace Dropcat\Tests;

use Dropcat\Command\RsyncFromCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class RsyncFromCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock = $this->getMockBuilder('Dropcat\Command\RsyncFromCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testRsync()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('rsync -chavzP --stats -e \'ssh -i id_file -p sshPort\' dasUser@server:fromPlace toPlace'))
            ->willReturn($process_mock);


        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester->execute(
            array(
                'command' => 'rsync:from',
                '-f' => 'fromPlace',
                '-t' => 'toPlace',
                '-s' => 'server',
                '-u' => 'dasUser',
                '-p' => 'sshPort',
                '-skp' => 'Pass4Key',
                '-tp' => '/file/should/go/here',
                '-i' => 'id_file',
                '-to' => 'tiomeout',
            ),
            $options
        );
    }
    function testRsyncError()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('rsync -chavzP --stats -e \'ssh -i id_file -p sshPort\' dasUser@server:fromPlace toPlace'))
            ->willReturn($process_mock);


        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectException('\\Exception');
        $this->tester->execute(
            array(
                'command' => 'rsync:from',
                '-f' => 'fromPlace',
                '-t' => 'toPlace',
                '-s' => 'server',
                '-u' => 'dasUser',
                '-p' => 'sshPort',
                '-skp' => 'Pass4Key',
                '-tp' => '/file/should/go/here',
                '-i' => 'id_file',
                '-to' => 'tiomeout',
            ),
            $options
        );
    }
}
