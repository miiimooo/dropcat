<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 11:22
 */

namespace Dropcat\Tests;

use Dropcat\Command\SiteInstallCommand;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class SiteInstallCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->mock = $this->getMockBuilder('Dropcat\Command\SiteInstallCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    function testSiteInstall()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('drush @drush_site_alias si profile_name --account-name=admin_user_name --account-pass=admin_pass_value -y some-install-options'))
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
                'command' => 'site-install',
                '-d' => 'drush_site_alias',
                '-p' => 'profile_name',
                '-to' => 'timeout-value',
                '-ap' => 'admin_pass_value',
                '-au' => 'admin_user_name',
                '-io' => 'some-install-options',
            ),
            $options
        );

        $this->expectOutputString('Task: configimport finished
');
        echo $this->tester->getDisplay();
    }
    function testSiteInstallFail()
    {
        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $this->expectException('\\Exception');
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->method('runProcess')
            ->with($this->equalTo('drush @drush_site_alias si profile_name --account-name=admin_user_name --account-pass=admin_pass_value -y some-install-options'))
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
                'command' => 'site-install',
                '-d' => 'drush_site_alias',
                '-p' => 'profile_name',
                '-to' => 'timeout-value',
                '-ap' => 'admin_pass_value',
                '-au' => 'admin_user_name',
                '-io' => 'some-install-options',
            ),
            $options
        );
    }
}
