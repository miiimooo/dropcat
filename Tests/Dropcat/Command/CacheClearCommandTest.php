<?php
namespace Dropcat\tests;

use Dropcat\Command\TarCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 26/02/16
 * Time: 10:33
 */
class CacheClearCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var Configuration */
    private $conf;
    /** @var  CommandTester */
    private $tester;
    private $mock;
    private $application;
    private $container;

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
        $this->mock = $this->getMockBuilder('\Dropcat\Command\CacheClearCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testCacheClear()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');
        $process_mock->method('isSuccessful')
            ->willReturn(true);
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @mysite cc all'))
            ->willReturn($process_mock);

        $this->expectOutputString("using drush alias: mysite\n");

        $this->application->add($command_mock);

        $this->tester = new CommandTester($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'cacheclear',
                '-d'      => 'mysite'
            ),
            $options
        );
    }
    public function testCacheClearFail()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');
        $process_mock->method('isSuccessful')
            ->willReturn(false);
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @mysite cc all'))
            ->willReturn($process_mock);

        $this->expectOutputString("using drush alias: mysite\n");
        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');

        $this->application->add($command_mock);

        $this->tester = new CommandTester($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'cacheclear',
                '-d'      => 'mysite'
            ),
            $options
        );
    }
}
