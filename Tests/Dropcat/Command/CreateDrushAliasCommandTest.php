<?php
namespace Dropcat\tests;

use Dropcat\Command\CreateDrushAliasCommand;
use Dropcat\Lib\CreateDrushAlias;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Created by SOPA
 */
class CreateDrushAliasCommandTest extends \PHPUnit_Framework_TestCase
{

    private $conf;
    private $application;
    private $commandMock;

    private $container;
    private $mock;

    public function setUp()
    {

        putenv("HOME=/tmp");
        putenv("DROPCAT_ENV=stage");

        // building the container!
        $this->container = new ContainerBuilder();

        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);
        $CDA = new CreateDrushAlias();
        $this->container->set('createDrushAlias', $CDA);

        // Mock filesystem
        $this->filesystem_mock = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')
            ->getMock();

        $this->conf = $this->getMockBuilder('Dropcat\Services\Configuration')
            ->getMock();


        $this->conf->method('remoteEnvironmentServerName')->willReturn('servername');
        $this->conf->method('remoteEnvironmentSshUser')->willReturn('sshuser');
        $this->conf->method('remoteEnvironmentWebRoot')->willReturn('webroot');
        $this->conf->method('remoteEnvironmentAlias')->willReturn('envAlias');
        $this->conf->method('siteEnvironmentUrl')->willReturn('envUrl');
        $this->conf->method('remoteEnvironmentSshPort')->willReturn('sshPort');
        $this->conf->method('remoteEnvironmentLocalSshPort')->willReturn('sshPort');
        $this->conf->method('remoteEnvironmentLocalServerName')->willReturn('servername');
        $this->conf->method('remoteEnvironmentLocalSshUser')->willReturn('sshuser');

        $this->application = new Application();

    }

    public function testAlias()
    {
        $this->conf->method('siteEnvironmentName')->willReturn('something');
        // Expected generated command:
        $expected_drush_command = <<<EOF
<?php
  \$aliases["something"] = array (
    "remote-host" => "servername",
    "remote-user" => "sshuser",
    "root" => "webroot/envAlias/web",
    "uri"  => "envUrl",
    "ssh-options" => "-o LogLevel=Error -q -p sshPort",);
EOF;

        $this->filesystem_mock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo('/tmp/.drush/.aliases.drushrc.php'), $this->equalTo($expected_drush_command))
            ->willReturn(true);

        $this->container->set('filesystem', $this->filesystem_mock);

        $command = new CreateDrushAliasCommand($this->container, $this->conf);
        $this->application->add($command);

        // Initiate the tester.
        $this->tester = new CommandTester($command);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'create-drush-alias',
                '-l' => 'true'
            ),
            array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            )
        );
    }

    public function testAliasErrorWriteFile()
    {
        $this->conf->method('siteEnvironmentName')->willReturn('something');
        $this->filesystem_mock->expects($this->once())
            ->method('dumpFile')
            ->will($this->throwException(new \Symfony\Component\Filesystem\Exception\IOException('')));

        $this->expectOutputString('An error occurred while creating your file at ');
        $this->container->set('filesystem', $this->filesystem_mock);

        $command = new CreateDrushAliasCommand($this->container, $this->conf);
        $this->application->add($command);

        // Initiate the tester.
        $this->tester = new CommandTester($command);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'create-drush-alias'
            )
        );
    }
    public function testAliasErrorConfig()
    {
        $this->conf->method('siteEnvironmentName')->willReturn('');

        $this->expectOutputString('I cannot create any alias, please check your --env parameter');
        $this->container->set('filesystem', $this->filesystem_mock);

        $command = new CreateDrushAliasCommand($this->container, $this->conf);
        $this->application->add($command);

        // Initiate the tester.
        $this->tester = new CommandTester($command);

        // Execute the test, with our mocked stuff.
        $this->tester->execute(
            array(
                'command' => 'create-drush-alias'
            )
        );
    }
}
