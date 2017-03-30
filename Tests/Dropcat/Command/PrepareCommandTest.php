<?php
namespace Dropcat\tests;

use Dropcat\Lib\CreateDrushAlias;
use Dropcat\Lib\DropcatFactories;
use Dropcat\Services\Configuration;
use Dropcat\Command\UploadCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 26/02/16
 * Time: 10:33
 */
class PrepareCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var Configuration */
    private $conf;
    /** @var  CommandTester */
    private $tester;
    private $mysqlMock;
    /** @var  ContainerBuilder */
    private $container;
    /** @var  DropcatFactories */
    private $factories_mock;
    /** @var  Application */
    private $application;
    private $sshMock;
    private $fsMock;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $CDA = new CreateDrushAlias();
        $this->container->set('createDrushAlias', $CDA);

        $this->conf = $configuration = new Configuration();

        $this->application = new Application();

        // mysqli mock
        $mysqliMock_placeholder = $this->getMockBuilder("\\mysqli");

        $this->mysqlMock = $mysqliMock_placeholder->setMethods(
            [
                'select_db'
            ]
        )->getMock();

        // SFTP mock
        $sshMock_placeholder = $this->getMockBuilder("\\phpseclib\\Net\\SFTP")
            ->setConstructorArgs(['some.server']);

        $this->sshMock = $sshMock_placeholder->setMethods(
            [
                'setTimeout',
                'login',
                'put',
                'getLastError',
                'getExitStatus',
                'disconnect',
                'exec'
            ]
        )->getMock();


        // filesystem mock
        $filesystem_placeholder = $this->getMockBuilder('\Symfony\Component\Filesystem\Filesystem');

        $this->fsMock = $filesystem_placeholder->setMethods(
            [
                'dumpFile',

            ]
        )->getMock();
        // Factories mock
        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    public function testPrepareDumpFileError()
    {
        $this->factories_mock->method('mysqli')
            ->will($this->returnCallback(function ($code) {
                throw new IOException($code);
            }));

        $this->fsMock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo("drushFolder/siteAlias.aliases.drushrc.php"), $this->equalTo('<?php
  $aliases["NameOfSite"] = array (
    "remote-host" => "dasServer",
    "remote-user" => "Collinius",
    "root" => "/srv/www/webroot//someAlias/web",
    "uri"  => "some.url.com",
    "ssh-options" => "-o LogLevel=Error -q -p 7777",);'))
            ->will($this->returnCallback(function ($code) {
                throw new IOException($code);
            }));

        $this->container->set('filesystem', $this->fsMock);

        $command_mock = $this->getMockBuilder('Dropcat\\Command\\PrepareCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'runProcess',
                'exitCommand'
            ])
            ->getMock();
        /*$command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('HERE BE COMMAND'))
            ->willReturn(null);*/

        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));

        $this->application->add($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectOutputString('An error occurred while creating your file at ');
        $this->expectException('\\Exception');

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-df' => 'drushFolder',
                '-d' => 'siteAlias',
                '-s' => 'dasServer',
                '-u' => 'Collinius',
                '-p' => '7777',
                '-w' => '/srv/www/webroot/',
                '-a' => 'someAlias',
                '-url' => 'some.url.com',
                '-sn' => 'NameOfSite',
                '-mh' => 'mysqlHost',
                '-mp' => '4321',
                '-md' => 'nameOfSchema',
                '-mu' => 'dbUser',
                '-mpd' => 'dbUserPass',
                '-to' => '99',
            ),
            $options
        );
    }

    public function testPrepareDbConnectError()
    {

        $this->factories_mock->method('mysqli')
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));

        $this->fsMock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo("drushFolder/siteAlias.aliases.drushrc.php"), $this->equalTo('<?php
  $aliases["NameOfSite"] = array (
    "remote-host" => "dasServer",
    "remote-user" => "Collinius",
    "root" => "/srv/www/webroot//someAlias/web",
    "uri"  => "some.url.com",
    "ssh-options" => "-o LogLevel=Error -q -p 7777",);'))
            ->willReturn(true);

        $this->container->set('filesystem', $this->fsMock);
        $this->container->set('dropcat.factory', $this->factories_mock);


        $command_mock = $this->getMockBuilder('Dropcat\\Command\\PrepareCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'runProcess',
                'exitCommand'
            ])
            ->getMock();
        /*$command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('HERE BE COMMAND'))
            ->willReturn(null);*/

        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));

        $this->application->add($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectOutputString('mysqlHost'."\n");
        $this->expectException('\\Exception');

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-df' => 'drushFolder',
                '-d' => 'siteAlias',
                '-s' => 'dasServer',
                '-u' => 'Collinius',
                '-p' => '7777',
                '-w' => '/srv/www/webroot/',
                '-a' => 'someAlias',
                '-url' => 'some.url.com',
                '-sn' => 'NameOfSite',
                '-mh' => 'mysqlHost',
                '-mp' => '4321',
                '-md' => 'nameOfSchema',
                '-mu' => 'dbUser',
                '-mpd' => 'dbUserPass',
                '-to' => '99',
            ),
            $options
        );
    }
    public function testPrepareMysqlAdminFailsExist()
    {


        $this->mysqlMock->method('select_db')->willReturn(false);
        $this->factories_mock->method('mysqli')
            ->willReturn($this->mysqlMock);

        $this->fsMock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo("drushFolder/siteAlias.aliases.drushrc.php"), $this->equalTo('<?php
  $aliases["NameOfSite"] = array (
    "remote-host" => "dasServer",
    "remote-user" => "Collinius",
    "root" => "/srv/www/webroot//someAlias/web",
    "uri"  => "some.url.com",
    "ssh-options" => "-o LogLevel=Error -q -p 7777",);'))
            ->willReturn(true);

        $this->container->set('filesystem', $this->fsMock);
        $this->container->set('dropcat.factory', $this->factories_mock);


        $command_mock = $this->getMockBuilder('Dropcat\\Command\\PrepareCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'runProcess',
                'exitCommand'
            ])
            ->getMock();

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('setTimeout')->willReturn(true);
        $process_mock->method('run')->willReturn(false);
        $process_mock->method('isSuccessful')->willReturn(false);

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with('mysqladmin -u dbUser -pdbUserPass -h mysqlHost -P 4321 create nameOfSchema')
            ->willReturn($process_mock);

        $this->application->add($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectException('\\Exception');

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-df' => 'drushFolder',
                '-d' => 'siteAlias',
                '-s' => 'dasServer',
                '-u' => 'Collinius',
                '-p' => '7777',
                '-w' => '/srv/www/webroot/',
                '-a' => 'someAlias',
                '-url' => 'some.url.com',
                '-sn' => 'NameOfSite',
                '-mh' => 'mysqlHost',
                '-mp' => '4321',
                '-md' => 'nameOfSchema',
                '-mu' => 'dbUser',
                '-mpd' => 'dbUserPass',
                '-to' => '99',
            ),
            $options
        );
    }
    public function testPrepareSchemaDoesNoExist()
    {


        $this->mysqlMock->method('select_db')->willReturn(false);
        $this->factories_mock->method('mysqli')
            ->willReturn($this->mysqlMock);

        $this->fsMock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo("drushFolder/siteAlias.aliases.drushrc.php"), $this->equalTo('<?php
  $aliases["NameOfSite"] = array (
    "remote-host" => "dasServer",
    "remote-user" => "Collinius",
    "root" => "/srv/www/webroot//someAlias/web",
    "uri"  => "some.url.com",
    "ssh-options" => "-o LogLevel=Error -q -p 7777",);'))
            ->willReturn(true);

        $this->container->set('filesystem', $this->fsMock);
        $this->container->set('dropcat.factory', $this->factories_mock);


        $command_mock = $this->getMockBuilder('Dropcat\\Command\\PrepareCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'runProcess',
                'exitCommand'
            ])
            ->getMock();

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('setTimeout')->willReturn(true);
        $process_mock->method('run')->willReturn(false);
        $process_mock->method('isSuccessful')->willReturn(true);

        $command_mock->expects($this->once())
            ->method('runProcess')
            ->with($this->equalTo('mysqladmin -u dbUser -pdbUserPass -h mysqlHost -P 4321 create nameOfSchema'))
            ->willReturn($process_mock);


        $this->application->add($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectOutputString('Database created
Task: prepare finished'."\n");

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-df' => 'drushFolder',
                '-d' => 'siteAlias',
                '-s' => 'dasServer',
                '-u' => 'Collinius',
                '-p' => '7777',
                '-w' => '/srv/www/webroot/',
                '-a' => 'someAlias',
                '-url' => 'some.url.com',
                '-sn' => 'NameOfSite',
                '-mh' => 'mysqlHost',
                '-mp' => '4321',
                '-md' => 'nameOfSchema',
                '-mu' => 'dbUser',
                '-mpd' => 'dbUserPass',
                '-to' => '99',
            ),
            $options
        );
        echo $this->tester->getDisplay();
    }
    public function testPrepareSchemaExist()
    {


        $this->mysqlMock->method('select_db')->willReturn(true);
        $this->factories_mock->method('mysqli')
            ->willReturn($this->mysqlMock);

        $this->fsMock->expects($this->once())
            ->method('dumpFile')
            ->with($this->equalTo("drushFolder/siteAlias.aliases.drushrc.php"), $this->equalTo('<?php
  $aliases["NameOfSite"] = array (
    "remote-host" => "dasServer",
    "remote-user" => "Collinius",
    "root" => "/srv/www/webroot//someAlias/web",
    "uri"  => "some.url.com",
    "ssh-options" => "-o LogLevel=Error -q -p 7777",);'))
            ->willReturn(true);

        $this->container->set('filesystem', $this->fsMock);
        $this->container->set('dropcat.factory', $this->factories_mock);


        $command_mock = $this->getMockBuilder('Dropcat\\Command\\PrepareCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'runProcess',
                'exitCommand'
            ])
            ->getMock();

        $process_mock = $this->createMock('Symfony\Component\Process\Process');

        $process_mock->method('setTimeout')->willReturn(true);
        $process_mock->method('run')->willReturn(false);
        $process_mock->method('isSuccessful')->willReturn(true);


        $this->application->add($command_mock);

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->expectOutputString('Database exists
Task: prepare finished'."\n");

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-df' => 'drushFolder',
                '-d' => 'siteAlias',
                '-s' => 'dasServer',
                '-u' => 'Collinius',
                '-p' => '7777',
                '-w' => '/srv/www/webroot/',
                '-a' => 'someAlias',
                '-url' => 'some.url.com',
                '-sn' => 'NameOfSite',
                '-mh' => 'mysqlHost',
                '-mp' => '4321',
                '-md' => 'nameOfSchema',
                '-mu' => 'dbUser',
                '-mpd' => 'dbUserPass',
                '-to' => '99',
            ),
            $options
        );
        echo $this->tester->getDisplay();
    }
}
