<?php
namespace Dropcat\tests;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Created by SOPA
 */
class CacheRecreateCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock        = $this->getMockBuilder('\Dropcat\Command\CacheRecreateCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testCacheRecreate()
    {
        // We mock the Process so that we can test commands without
        // actually running them. And test if the command was succesfull
        // or not.
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        // We mock the method "isSuccessful" to return true
        // faking that it worked, in other words.
        $process_mock->method('isSuccessful')
            ->willReturn(true);

        // We then mock the runProcess method so we can make sure
        // that we return or mock of process, above.
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        // Here we set up an assertion that
        // + runProcess once
        // + That when run, the parameter is 'drush @mysite cim myconfig -q -y
        // + and that we return the mocked process, above.
        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @sitetorecreatecachefor cr'))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'configimport',
                '-d'      => 'sitetorecreatecachefor'
            ),
            $options
        );
    }
    public function testCacheRecreateFailed()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');
        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('drush @sitetorecreatecachefor cr'))
            ->willReturn($process_mock);

        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');

        $this->application->add($command_mock);

        $this->tester = new CommandTester($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'configimport',
                '-d'      => 'sitetorecreatecachefor'
            ),
            $options
        );
    }
}
