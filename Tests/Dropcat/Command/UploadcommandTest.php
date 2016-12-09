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
class UploadCommandTest extends \PHPUnit_Framework_TestCase
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
    private $sftpMock;

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
        $sftpMock_placeholder = $this->getMockBuilder("phpseclib\\Net\\SFTP")
            ->setConstructorArgs(['some.server']);

        $this->sftpMock = $sftpMock_placeholder->setMethods(
            [
                'setTimeout',
                'login',
                'put',
                'file_exists',
                'exec',
                'disconnect',
            ]
        )->getMock();

        // Factories mock
        $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
    }

    public function testLoginError()
    {

        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(false);

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $sha1         = sha1('Dropcat is our lord & saviour');
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('getSha1OfFile')->willReturn($sha1);
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);

        $this->expectOutputString('login Failed using /path_to/keyfile and user mikkmeister at upload.server.com');
        $this->expectException('\\Exception');

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'upload',
                '-bi' => '2',
                '-se' => '@',
                '-t' => '/dev/null/tar',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666',
                '-kt' => 'FALSE',
                '-dsha1' => 'FALSE',
            )
        );
    }

    public function testUploadError()
    {

        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(2))
            ->method('put')
            ->willReturn(false);

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $sha1         = sha1('Dropcat is our lord & saviour');
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('getSha1OfFile')->willReturn($sha1);
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);

        $this->expectOutputString('Upload failed of uploadTest@2.tar');
        $this->expectException('\\Exception');

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'uploadTest',
                '-bi' => '2',
                '-se' => '@',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666',
                '-kt' => 'FALSE',
                '-dsha1' => 'FALSE',
            )
        );
    }

    public function testUploadedFileExistsError()
    {

        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(2))
            ->method('put')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(3))
            ->method('file_exists')
            ->with($this->Equalto('dirOfTaruploadTest@2.tar'))
            ->willReturn(false);

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $sha1         = sha1('Dropcat is our lord & saviour');
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('getSha1OfFile')->willReturn($sha1);
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);

        $this->expectOutputString('tar is at dirOfTaruploadTest@2.tar
local file hash is 7fd9d832fd2a1a45b8820876e1b52e6b1d58f2b1
remote file hash is 
check for upload did not succeed.'."\n");
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );

        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'uploadTest',
                '-bi' => '2',
                '-se' => '@',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666',
                '-dsha1' => 'FALSE',
            ),
            $options
        );
    }


    public function testSha1Error()
    {

        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(2))
            ->method('put')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(3))
            ->method('file_exists')
            ->with($this->Equalto('dirOfTaruploadTest@2.tar'))
            ->willReturn(true);

        $this->sftpMock->expects($this->at(4))
            ->method('exec')
            ->with($this->Equalto('sha1sum dirOfTaruploadTest@2.tar | awk \'{print $1}\''))
            ->willReturn('fel');

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $sha1         = sha1('Dropcat is our lord & saviour');
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand',
                'removeTar'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('removeTar')->willReturn(true);
        $command_mock->method('getSha1OfFile')->willReturn($sha1);
        $command_mock->expects($this->once())
            ->method('exitCommand')
            ->with($this->equalTo(1))
            ->will($this->returnCallback(function ($code) {
                throw new \Exception($code);
            }));


        $this->application->add($command_mock);

        $this->expectOutputString('tar is at dirOfTaruploadTest@2.tar
local file hash is 7fd9d832fd2a1a45b8820876e1b52e6b1d58f2b1
remote file hash is fel' ."\n". 'SHA1 for file do not match.');
        $this->expectException('\\Exception');

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'uploadTest',
                '-bi' => '2',
                '-se' => '@',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666'
            ),
            $options
        );
    }
    public function testNotUsingSha1()
    {

        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(2))
            ->method('put')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(3))
            ->method('file_exists')
            ->with($this->Equalto('dirOfTaruploadTest@2.tar'))
            ->willReturn(true);

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $sha1         = sha1('Dropcat is our lord & saviour');
        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand',
                'removeTar'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('removeTar')->willReturn(true);
        $command_mock->method('getSha1OfFile')->willReturn($sha1);


        $this->application->add($command_mock);

        $this->expectOutputString('upload seems to be successful, but SHA1 for file is not checked '."\n".'tar is going to be saved TRUE
path to tar dirOfTaruploadTest@2.tar
tar file is not deleted '."\n");
        #$this->expectException('\\Exception');
        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'uploadTest',
                '-bi' => '2',
                '-se' => '@',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666',
                '-kt' => 'FALSE',
                '-dsha1' => 'FALSE',
            ),
            $options
        );
    }


    public function testSha1Correct()
    {

        $sha1         = sha1('Dropcat is our lord & saviour');
        $this->sftpMock->expects($this->at(1))
            ->method('login')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(2))
            ->method('put')
            ->willReturn(true);

        $this->sftpMock->expects($this->at(3))
            ->method('file_exists')
            ->with($this->Equalto('dirOfTaruploadTest@2.tar'))
            ->willReturn(true);

        $this->sftpMock->expects($this->at(4))
            ->method('exec')
            ->with($this->Equalto('sha1sum dirOfTaruploadTest@2.tar | awk \'{print $1}\''))
            ->willReturn($sha1);

        $this->factories_mock->method('sftp')->willReturn($this->sftpMock);

        $this->container->set('rsa', $this->rsaMock);

        $this->container->set('dropcat.factory', $this->factories_mock);

        $command_mock = $this->getMockBuilder('Dropcat\\Command\\UploadCommand')
            ->setConstructorArgs([
                $this->container,
                $this->conf
            ])
            ->setMethods([
                'getKeyContents',
                'getSha1OfFile',
                'exitCommand',
                'removeTar'
            ])
            ->getMock();

        $command_mock->method('getKeyContents')->willReturn('itsok!');
        $command_mock->method('removeTar')->willReturn(true);
        $command_mock->method('getSha1OfFile')->willReturn($sha1);


        $this->application->add($command_mock);

        $this->expectOutputString('tar is at dirOfTaruploadTest@2.tar
local file hash is 7fd9d832fd2a1a45b8820876e1b52e6b1d58f2b1
remote file hash is 7fd9d832fd2a1a45b8820876e1b52e6b1d58f2b1' ."\n". 'SHA1 for file match
upload successful
tar is going to be saved FALSE
path to tar dirOfTaruploadTest@2.tar
tar file is deleted 
');


        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        $this->tester = new CommandTester($command_mock);
        $this->tester->execute(
            array(
                'command' => 'upload',
                '-a' => 'uploadTest',
                '-bi' => '2',
                '-se' => '@',
                '-td' => 'dirOfTar',
                '-s' => 'upload.server.com',
                '-u' => 'mikkmeister',
                '-tp' => '/server/target/path',
                '-p' => '1234',
                '-skp' => 'sch_secret_pass',
                '-i' => '/path_to/keyfile',
                '-to' => '666'
            ),
            $options
        );
    }
}
