<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-30
 * Time: 13:51
 */

namespace Dropcat\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class JenkinsBuildCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var Configuration */
    private $conf;
    /** @var  CommandTester */
    private $tester;
    private $rsaMock;
    /** @var  ContainerBuilder */
    private $container;
    /** @var  DropcatFactories */
    private $factories_mock;
    /** @var  Application */
    private $application;
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
        $this->mock = $this->getMockBuilder('\Dropcat\Command\JenkinsBuildCommand')
            ->setConstructorArgs(array($this->container, $this->conf));

        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    private function _getFirstJob()
    {
        $jenkinsMock_placeholder = $this->getMockBuilder("\\JenkinsApi\\Jenkins")
            ->setConstructorArgs(['some.server'])
            ->setMethods(['getJob'])
            ->getMock();

        $jenkinsJob_0 = $this->getMockBuilder('\JenkinsApi\Item\Job')
            ->setConstructorArgs(array('job one', $jenkinsMock_placeholder))
            ->setMethods(['getLastSuccessfulBuild','refresh','launchAndWait','getLastBuild'])
            ->getMock();


        $jenkinsBuild_0 = $this->getMockBuilder('\JenkinsApi\Item\Build')
            ->setConstructorArgs([12, 'job one',$jenkinsMock_placeholder ])
            ->setMethods(['getEstimatedDuration','refresh',])
            ->getMock();

        $jenkinsBuild_0->method('getEstimatedDuration')->willReturn(61);

        $jenkinsBuild_1 = $this->getMockBuilder('\JenkinsApi\Item\Build')
            ->setConstructorArgs([12, 'job one',$jenkinsMock_placeholder ])
            ->setMethods(['getEstimatedDuration','refresh','getResult','getConsoleTextBuild'])
            ->getMock();

        $jenkinsBuild_1->method('getResult')->willReturn('Loving it');

        $jenkinsJob_0->method('getLastBuild')->willReturn($jenkinsBuild_1);
        $jenkinsJob_0->method('launchAndWait')->willReturn(true);
        $jenkinsJob_0->method('getLastSuccessfulBuild')->willReturn($jenkinsBuild_0);

        $jenkinsMock_placeholder->method('getJob')
            ->willReturn($jenkinsJob_0);
        return $jenkinsMock_placeholder;
    }

    public function testBuildSite()
    {
        # Add job.
        $this->factories_mock->expects($this->at(0))->method('jenkins')->willReturn($this->_getFirstJob());

        $this->container->set('dropcat.factory', $this->factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        $this->application->add($command_mock);
        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester = new CommandTester($command_mock);

        $this->tester->execute(
            array(
                'command' => 'jenkins-build'
            )
        );
    }
}










/*
*/
