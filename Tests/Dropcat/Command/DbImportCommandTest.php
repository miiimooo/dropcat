<?php
namespace Dropcat\tests;

use Dropcat\Command\DbImportCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;


/**
 * Created by SOPA
 */
class DbImportCommandTest extends \PHPUnit_Framework_TestCase
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

        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $this->conf = $configuration = new Configuration();

        $this->application = new Application();

        // We mock the command so that we later on can test Process.
        $this->mock        = $this->getMockBuilder('\Dropcat\Command\DbImportCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testDbImport()
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
        $expected_process_command = <<<EOF
drush @mysite sql-drop -y &&
            drush @mysite sql-cli < /dev/null
EOF;

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo($expected_process_command))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'db-import',
                '-d'      => 'mysite',
                '-i'      => '/dev/null'
            )
        );
    }

    public function testDbImportFail()
    {
        // We mock the Process so that we can test commands without
        // actually running them. And test if the command was succesfull
        // or not.
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        // We mock the method "isSuccessful" to return true
        // faking that it worked, in other words.
        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $this->expectException('\Symfony\Component\Process\Exception\ProcessFailedException');
        // We then mock the runProcess method so we can make sure
        // that we return or mock of process, above.
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        // Here we set up an assertion that
        // + runProcess once
        // + That when run, the parameter is 'drush @mysite cim myconfig -q -y
        // + and that we return the mocked process, above.
        $expected_process_command = <<<EOF
drush @mysite sql-drop -y &&
            drush @mysite sql-cli < /dev/null
EOF;

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo($expected_process_command))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'db-import',
                '-d'      => 'mysite',
                '-i'      => '/dev/null'
            )
        );
    }
}
