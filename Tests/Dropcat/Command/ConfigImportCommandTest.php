<?php
namespace Dropcat\tests;

use Dropcat\Command\ConfigImportCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Created by SOPA
 */
class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
{

    private $conf;
    private $application;
    private $commandMock;

    private $container;
    private $mock;

    public function setUp()
    {
        // building the container!
        $this->container = new ContainerBuilder();
        $this->container->set('DropcatContainer', $this->container);

        $this->conf = $configuration = new Configuration();

        // new ConfigImportCommand($container, $this->conf);
        $this->application = new Application();
        $this->mock        = $this->getMockBuilder('\Dropcat\Command\ConfigImportCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testDrushCommand()
    {
        $p = $this->createMock('Symfony\Component\Process\Process');
        $p->method('isSuccessful')
            ->willReturn(true);

        $m = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $m->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @mysite cim myconfig -q -y'))
            ->willReturn($p);

        $this->application->add($m);

        $this->tester = new CommandTester($m);
        $this->tester->execute(
            array(
                'command' => 'configimport',
                '-d'      => 'mysite',
                '-c'      => 'myconfig'
            )
        );
    }

    public function testVerboseDrushCommand()
    {
        $p = $this->createMock('Symfony\Component\Process\Process');
        $p->method('isSuccessful')
            ->willReturn(true);

        $m = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $m->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @mysite cim myconfig -y'))
            ->willReturn($p);

        $this->application->add($m);

        $this->tester = new CommandTester($m);
        $this->tester->execute(
            array(
                'command' => 'configimport',
                '-d'      => 'mysite',
                '-c'      => 'myconfig',
            ),
            array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            )
        );
    }

    public function testCommandFail()
    {
        $p = $this->createMock('Symfony\Component\Process\Process');
        $p->method('isSuccessful')
            ->willReturn(false);

        $m = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $m->expects($this->once())
            ->method('runProcess')
            ->willReturn($p);

        $this->application->add($m);
        $this->expectException('\Symfony\Component\Process\Exception\ProcessFailedException');
        $this->tester = new CommandTester($m);
        $this->tester->execute(
            array(
                'command' => 'configimport',
                '-d'      => 'mysite',
                '-c'      => 'myconfig',
            )
        );
    }
}