<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2016-10-07
 * Time: 10:21
 */

namespace Dropcat\tests;


use Dropcat\Services\Configuration;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BackuppCommandTest  extends PHPUnit_Framework_TestCase
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
        $this->mock        = $this->getMockBuilder('\Dropcat\Command\BackupCommand')
            ->setConstructorArgs(array($this->container, $this->conf));
    }

    public function testBackup()
    {
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
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
            drush @mysite sql-dump > /dev/null/mysite/666.sql'))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'backup',
                '-d'      => 'mysite',
                '-t'      => '666',
                '-b'      => '/dev/null'
            )
        );
    }

    public function testBackupFail()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        // We mock the method "isSuccessful" to return true
        // faking that it worked, in other words.
        $process_mock->method('isSuccessful')
            ->willReturn(false);

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
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
            drush @mysite sql-dump > /dev/null/mysite/666.sql'))
            ->willReturn($process_mock);

        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');
        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'backup',
                '-d'      => 'mysite',
                '-t'      => '666',
                '-b'      => '/dev/null'
            )
        );
    }

    public function testBackupSite()
    {
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
        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
            drush @mysite sql-dump > /dev/null/mysite/666.sql'))
            ->willReturn($process_mock);

        $command_mock->expects($this->at(1))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
                drush -y rsync @mysite /dev/null/mysite/666/  --include-conf --include-vcs'))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'backup',
                '-d'      => 'mysite',
                '-t'      => '666',
                '-b'      => '/dev/null',
                '-bs'     => true,
            )
        );
    }
    public function testBackupSiteFailed()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        // We mock the method "isSuccessful" to return true
        // faking that it worked, in other words.
        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $process_mock_fails = $this->createMock('Symfony\Component\Process\Process');
        $process_mock_fails->method('isSuccessful')->willReturn(false);

        // We then mock the runProcess method so we can make sure
        // that we return or mock of process, above.
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        // Here we set up an assertion that
        // + runProcess once
        // + That when run, the parameter is 'drush @mysite cim myconfig -q -y
        // + and that we return the mocked process, above.
        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
            drush @mysite sql-dump > /dev/null/mysite/666.sql'))
            ->willReturn($process_mock);

        $command_mock->expects($this->at(1))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
                drush -y rsync @mysite /dev/null/mysite/666/  --include-conf --include-vcs'))
            ->willReturn($process_mock_fails);

        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'backup',
                '-d'      => 'mysite',
                '-t'      => '666',
                '-b'      => '/dev/null',
                '-bs'     => true,
            )
        );
    }

    public function testBackupSiteWithLinks()
    {
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
        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
            drush @mysite sql-dump > /dev/null/mysite/666.sql'))
            ->willReturn($process_mock);

        $command_mock->expects($this->at(1))
            ->method('runProcess')
            ->with($this->equalTo('mkdir -p /dev/null/mysite &&
                drush -y rsync @mysite /dev/null/mysite/666/ --links  --include-conf --include-vcs'))
            ->willReturn($process_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'backup',
                '-d'      => 'mysite',
                '-t'      => '666',
                '-b'      => '/dev/null',
                '-bs'     => true,
                '-l'      => true
            )
        );
    }
}