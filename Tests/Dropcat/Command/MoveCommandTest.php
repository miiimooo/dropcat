<?php
namespace Dropcat\tests;

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

/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 26/02/16
 * Time: 10:33
 */
class MoveCommandTest extends \PHPUnit_Framework_TestCase
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
    private $sshMock;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $this->conf = $configuration = new Configuration();

        $this->application = new Application();

        // RSA mock
        $rsaMock_placeholder = $this->getMockBuilder("phpseclib\\Crypt\\RSA");

        $this->rsaMock = $rsaMock_placeholder->setMethods(
            [
                'setPassword',
                'loadKey'
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

        // Factories mock
        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    public function testMoveLoginError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(false);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
Login Failed using /path_to/keyfile and user mikkmeister at upload.server.com ');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }

    public function testMoveMkdirError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(2);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(null))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
Could not create temp folder for deploy, error code 
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }

    public function testMoveMvError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder//dev/null/tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(55);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(55))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
Could not move tar to tar folder, error code 55
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
    public function testMoveTarError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder//dev/null/tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(6))
            ->method('exec')
            ->with($this->equalTo('tar xvf /temp_folder/upload@2//dev/null/tar -C/temp_folder/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(7))
            ->method('getExitStatus')
            ->willReturn(18);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(18))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
path to tar to unpack is: /temp_folder/upload@2//dev/null/tar
Could not untar tar, error code 18
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
    public function testMoveRmTarError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder//dev/null/tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(6))
            ->method('exec')
            ->with($this->equalTo('tar xvf /temp_folder/upload@2//dev/null/tar -C/temp_folder/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(7))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(8))
            ->method('exec')
            ->with($this->equalTo('rm /temp_folder/upload@2//dev/null/tar'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(9))
            ->method('getExitStatus')
            ->willReturn(76);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(76))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
path to tar to unpack is: /temp_folder/upload@2//dev/null/tar
file /dev/null/tar unpacked
Could not remove tar file, error code 76
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
    public function testMoveMvDeployFolderError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder//dev/null/tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(6))
            ->method('exec')
            ->with($this->equalTo('tar xvf /temp_folder/upload@2//dev/null/tar -C/temp_folder/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(7))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(8))
            ->method('exec')
            ->with($this->equalTo('rm /temp_folder/upload@2//dev/null/tar'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(9))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(10))
            ->method('exec')
            ->with($this->equalTo('mv /temp_folder/upload@2 /srv/www/webroot/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(11))
            ->method('getExitStatus')
            ->willReturn(99);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(99))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
path to tar to unpack is: /temp_folder/upload@2//dev/null/tar
file /dev/null/tar unpacked
removed tar file /dev/null/tar
Folder not in place, error code 99
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
    public function testMoveSymlinkError()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder/upload@2.tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(6))
            ->method('exec')
            ->with($this->equalTo('tar xvf /temp_folder/upload@2/upload@2.tar -C/temp_folder/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(7))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(8))
            ->method('exec')
            ->with($this->equalTo('rm /temp_folder/upload@2/upload@2.tar'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(9))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(10))
            ->method('exec')
            ->with($this->equalTo('mv /temp_folder/upload@2 /srv/www/webroot/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(11))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(12))
            ->method('exec')
            ->with($this->equalTo('ln -sfn /srv/www/webroot/upload@2 /srv/www/webroot//some/symlink/alias'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(13))
            ->method('getExitStatus')
            ->willReturn(3);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(3))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: upload@2.tar
path to tar to unpack is: /temp_folder/upload@2/upload@2.tar
file upload@2.tar unpacked
removed tar file upload@2.tar
path to deployed folder is: /srv/www/webroot/upload@2
Could not create symlink to folder, error code 3
');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
    public function testMoveSuccess()
    {
        $this->sshMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sshMock->expects($this->at(2))
            ->method('exec')
            ->with($this->equalTo("mkdir /temp_folder/upload@2"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(3))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(4))
            ->method('exec')
            ->with($this->equalTo("mv /temp_folder//dev/null/tar /temp_folder/upload@2/"))
            ->willReturn(0);

        $this->sshMock->expects($this->at(5))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(6))
            ->method('exec')
            ->with($this->equalTo('tar xvf /temp_folder/upload@2//dev/null/tar -C/temp_folder/upload@2'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(7))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(8))
            ->method('exec')
            ->with($this->equalTo('rm /temp_folder/upload@2//dev/null/tar'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(9))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(10))
            ->method('exec')
            ->with($this->equalTo('mv /temp_folder/upload@2 /srv/www/webroot/upload@'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(11))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(12))
            ->method('exec')
            ->with($this->equalTo('ln -sfn /srv/www/webroot/upload@2 /srv/www/webroot//some/symlink/alias'))
            ->willReturn(0);

        $this->sshMock->expects($this->at(13))
            ->method('getExitStatus')
            ->willReturn(0);

        $this->sshMock->expects($this->at(14))
            ->method('disconnect')
            ->willReturn(true);

        $this->factories_mock->method('ssh')
            ->willReturn($this->sshMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\MoveCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'exitCommand'
            ])
            ->getMock();
        $command_mock->method('getKeyContents')->willReturn('itsok!');

        $this->application->add($command_mock);
        $this->expectOutputString('deploy folder: upload@2
tarfile: /dev/null/tar
path to tar to unpack is: /temp_folder/upload@2//dev/null/tar
file /dev/null/tar unpacked
removed tar file /dev/null/tar
path to deployed folder is: /srv/www/webroot/upload@2
alias to deployed folder are: /srv/www/webroot//some/symlink/alias
');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );


        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'move',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-w' => '/srv/www/webroot',
                '-tf' => '/temp_folder',
                '-aa' => '/some/symlink/alias',
            ),
            $options
        );
    }
}
