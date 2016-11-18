<?php


namespace Dropcat\tests;

use Consolidation\AnnotatedCommand\PassThroughArgsInput;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InitCommandTest extends \PHPUnit_Framework_TestCase
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

        $this->filesystem_mock = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();


        // We mock the command so that we later on can test Process.
        $this->mock = $this->getMockBuilder('\Dropcat\Command\InitCommand')
            ->setConstructorArgs(array($this->container, $this->conf));


    }



    public function testInitNoProfileNameCommand()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();
        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->expectException('\Exception');
        $this->expectExceptionMessage('You need to specify a profile name.');
        $this->tester->execute(
            array(
                'command' => 'init'
            )
        );
    }

    public function testInitWrongProfileNameCommand()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();
        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Profiles must use a-z i names.');
        $this->tester->execute(
            array(
                'command' => 'init',
                '-p'      => 'profile-name',
            )
        );
    }

    public function testInitProfileContainsSpacesCommand()
    {
        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        /*$command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo("drush @mysite entup -y"))
            ->willReturn($process_mock);
           */
        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Profile name can not have spaces.');
        $this->tester->execute(
            array(
                'command' => 'init',
                '-p'      => 'profile name',
            )
        );
    }

    public function testInitCommand()
    {
        $my_profile = 'profilename';

        $this->container->setParameter(
            'factory.libs.symfonystyle',
            "Symfony\\Component\\Console\\Style\\SymfonyStyle"
        );
        $this->container->setParameter(
            'factory.libs.splfileobject',
            "\\SplFileObject"
        );
        $input = new PassThroughArgsInput([]);
        $output = new NullOutput();

        $symfonystyle_mock_placeholder = $this->getMockBuilder($this->container->getParameter('factory.libs.symfonystyle'))
            ->setConstructorArgs([$input, $output]);

        $symfonystyle_mock = $symfonystyle_mock_placeholder->setMethods(
            [
                'confirm',
                'note',
                'newLine',
                'success',
            ]
        )->getMock();

        $symfonystyle_mock->expects($this->at(0))->method('confirm')
            ->with($this->equalTo('This will add files for setting up a drupal site in current folder, continue?'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(1))
            ->method('note')
            ->with($this->equalTo('Wk Drupal Template cloned to web_init/web'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(2))
            ->method('note')
            ->with($this->equalTo('Renaming of functions and files finished'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(3))
            ->method('note')
            ->with($this->equalTo('Move web folder in place, removed web_init folder'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(4))
            ->method('newLine')
            ->with($this->equalTo(2))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(5))
            ->method('success')
            ->with($this->equalTo('Site is setup'))
            ->willReturn(true);

        $factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');

        $factories_mock->method('symfonystyle')
            ->willReturn($symfonystyle_mock);

        $splFileObjectMock = $this->getMockBuilder(__NAMESPACE__.'\\SplFileObject')->getMock();

        $splFileObjectMock->expects($this->at(1))
            ->method('fread')
            ->with($this->equalTo(null))
            ->willReturn("wk-standard_install Install, update and uninstall functions for the wk-standard installation profile. web/profiles/wk-standard/ ('system.site')->set('uuid', '-')->save(TRUE);");

        $splFileObjectMock->expects($this->at(3))
            ->method('fwrite')
            ->with($this->equalTo(
                "profilename_install Install, update and uninstall functions for profilename installation profile. web/profiles/profilename/ ('system.site')->set('uuid', '-')->save(TRUE);"
            )
            );

        $splFileObjectMock->expects($this->at(5))
            ->method('fread')
            ->with($this->equalTo(null))
            ->willReturn('web/profiles/wk-standard/');

        $splFileObjectMock->expects($this->at(7))
            ->method('fwrite')
            ->with($this->equalTo("web/profiles/wk-standard/"));


        $factories_mock->method('SplFileObject')
            ->willReturn($splFileObjectMock);


        $this->container->set('dropcat.factory', $factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(true);

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo("git clone git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git web_init"))
            ->willReturn($process_mock);

        $command_mock->expects($this->at(1))
            ->method('runProcess')
            ->with($this->equalTo("mv web_init/* . && rm -rf web_init"))
            ->willReturn($process_mock);

        $this->filesystem_mock->expects($this->at(0))
            ->method('rename')
            ->with($this->equalTo('web_init/web/profiles/wk-standard'), $this->equalTo( 'web_init/web/profiles/profilename'))
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(1))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.profile'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.profile')
            )
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(2))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.install'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install')
            )
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(3))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.info.yml'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.info.yml')
            )
            ->willReturn(true);


        $this->container->set('filesystem', $this->filesystem_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'init',
                '-p'      => $my_profile,
            )
        );
    }

    public function testInitWithErrorCommand()
    {
        $my_profile = 'profilename';

        $this->container->setParameter(
            'factory.libs.symfonystyle',
            "Symfony\\Component\\Console\\Style\\SymfonyStyle"
        );
        $this->container->setParameter(
            'factory.libs.splfileobject',
            "\\SplFileObject"
        );
        $input = new PassThroughArgsInput([]);
        $output = new NullOutput();

        $symfonystyle_mock_placeholder = $this->getMockBuilder($this->container->getParameter('factory.libs.symfonystyle'))
            ->setConstructorArgs([$input, $output]);

        $symfonystyle_mock = $symfonystyle_mock_placeholder->setMethods(
            [
                'confirm',
                'note',
                'newLine',
                'success',
            ]
        )->getMock();

        $symfonystyle_mock->expects($this->at(0))->method('confirm')
            ->with($this->equalTo('This will add files for setting up a drupal site in current folder, continue?'))
            ->willReturn(true);

        $factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');

        $factories_mock->method('symfonystyle')
            ->willReturn($symfonystyle_mock);

        $splFileObjectMock = $this->getMockBuilder(__NAMESPACE__.'\\SplFileObject')->getMock();


        $this->container->set('dropcat.factory', $factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('isSuccessful')
            ->willReturn(false);

        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');
        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo("git clone git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git web_init"))
            ->willReturn($process_mock);

        $this->container->set('filesystem', $this->filesystem_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'init',
                '-p'      => $my_profile,
            )
        );
    }

    public function testInitErrorSecondRunCommand()
    {
        $my_profile = 'profilename';

        $this->container->setParameter(
            'factory.libs.symfonystyle',
            "Symfony\\Component\\Console\\Style\\SymfonyStyle"
        );
        $this->container->setParameter(
            'factory.libs.splfileobject',
            "\\SplFileObject"
        );
        $input = new PassThroughArgsInput([]);
        $output = new NullOutput();

        $symfonystyle_mock_placeholder = $this->getMockBuilder($this->container->getParameter('factory.libs.symfonystyle'))
            ->setConstructorArgs([$input, $output]);

        $symfonystyle_mock = $symfonystyle_mock_placeholder->setMethods(
            [
                'confirm',
                'note',
                'newLine',
                'success',
            ]
        )->getMock();

        $symfonystyle_mock->expects($this->at(0))->method('confirm')
            ->with($this->equalTo('This will add files for setting up a drupal site in current folder, continue?'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(1))
            ->method('note')
            ->with($this->equalTo('Wk Drupal Template cloned to web_init/web'))
            ->willReturn(true);

        $symfonystyle_mock->expects($this->at(2))
            ->method('note')
            ->with($this->equalTo('Renaming of functions and files finished'))
            ->willReturn(true);

        $factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');

        $factories_mock->method('symfonystyle')
            ->willReturn($symfonystyle_mock);

        $splFileObjectMock = $this->getMockBuilder(__NAMESPACE__.'\\SplFileObject')->getMock();

        $splFileObjectMock->expects($this->at(1))
            ->method('fread')
            ->with($this->equalTo(null))
            ->willReturn("wk-standard_install Install, update and uninstall functions for the wk-standard installation profile. web/profiles/wk-standard/ ('system.site')->set('uuid', '-')->save(TRUE);");

        $splFileObjectMock->expects($this->at(3))
            ->method('fwrite')
            ->with($this->equalTo(
                "profilename_install Install, update and uninstall functions for profilename installation profile. web/profiles/profilename/ ('system.site')->set('uuid', '-')->save(TRUE);"
            )
            );

        $splFileObjectMock->expects($this->at(5))
            ->method('fread')
            ->with($this->equalTo(null))
            ->willReturn('web/profiles/wk-standard/');

        $splFileObjectMock->expects($this->at(7))
            ->method('fwrite')
            ->with($this->equalTo("web/profiles/wk-standard/"));


        $factories_mock->method('SplFileObject')
            ->willReturn($splFileObjectMock);


        $this->container->set('dropcat.factory', $factories_mock);

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->expects($this->at(1))
            ->method('isSuccessful')
            ->willReturn(true);

        $process_mock->expects($this->at(5))
            ->method('isSuccessful')
            ->with()
            ->willReturn(false);

        $this->expectException('Symfony\Component\Process\Exception\ProcessFailedException');

        $command_mock = $this->mock->setMethods(['runProcess'])
            ->getMock();

        $command_mock->expects($this->at(0))
            ->method('runProcess')
            ->with($this->equalTo("git clone git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git web_init"))
            ->willReturn($process_mock);

        $command_mock->expects($this->at(1))
            ->method('runProcess')
            ->with($this->equalTo("mv web_init/* . && rm -rf web_init"))
            ->willReturn($process_mock);

        $this->filesystem_mock->expects($this->at(0))
            ->method('rename')
            ->with($this->equalTo('web_init/web/profiles/wk-standard'), $this->equalTo( 'web_init/web/profiles/profilename'))
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(1))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.profile'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.profile')
            )
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(2))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.install'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install')
            )
            ->willReturn(true);

        $this->filesystem_mock->expects($this->at(3))
            ->method('rename')
            ->with(
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/wk-standard.info.yml'),
                $this->equalTo('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.info.yml')
            )
            ->willReturn(true);


        $this->container->set('filesystem', $this->filesystem_mock);

        // Add our mocked command from above.
        $this->application->add($command_mock);

        // Initiate the tester.
        $this->tester = new CommandTester($command_mock);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'init',
                '-p'      => $my_profile,
            )
        );
    }
}

class SplFileObject{
    function __construct(){}
    function getSize() {}
    function fread(){}
    function getPathname(){}
    function fwrite(){}
}

