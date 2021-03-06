<?php
namespace Dropcat\tests;

use Dropcat\Services\Configuration;
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
class TarCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var Configuration */
    private $conf;
    /** @var  CommandTester */
    private $tester;

    public function setUp()
    {

        $this->conf = $this->getMockBuilder('Dropcat\Services\Configuration')
            ->getMock();

        $this->conf->method('localEnvironmentTmpPath')
            ->willReturn(realpath(__DIR__));
        $this->conf->method('localEnvironmentAppName')
            ->willReturn('Tarcommandapptest');
        $this->conf->method('localEnvironmentAppPath')
            ->willReturn(realpath(__DIR__));

        // set up to ignore files
        $dh              = opendir($this->conf->localEnvironmentAppPath());
        $files_to_ignore = array();
        while (($file = readdir($dh)) !== false) {
            if ($file[0] !== '.' && basename(__FILE__) !== $file) {
                $files_to_ignore[] = $file;
            }
        }
        $this->conf->method('deployIgnoreFiles')->willReturn($files_to_ignore);

        // Setting up the container
        $this->container = new ContainerBuilder();
        $this->container->setProxyInstantiator(new RuntimeInstantiator());
        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator(__DIR__ . '/../../../app/config')
        );

        $loader->load('services.yml');
        // Setting DropcatContainer to the DI-container we use.
        // This way, it will be available to the command.
        $this->container->set('DropcatContainer', $this->container);

        $this->container->set('dropcat.configuration', $this->conf);

        $application = new Application();
        $application->add($this->container->get('dropcat.command.tar'));
        $command      = $application->find('tar');

        // Silly assertion - is it a _proxy_ we have? For lazy-loading-check
        $this->assertEquals(
            substr(get_class($command), 0, 26),
            'ProxyManagerGeneratedProxy'
        );
        $this->tester = new CommandTester($command);
    }

    public function testTarAppFolder()
    {

        $filename = $this->conf->localEnvironmentTmpPath() .
            $this->conf->localEnvironmentSeparator() .
            $this->conf->localEnvironmentAppName() .
            $this->conf->localEnvironmentSeparator() .
            $this->conf->localEnvironmentBuildId() . '.tar';

        $options  = array(
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        );
        # Testing output since verbose is enabled.
        $this->expectOutputString("Build number from CI server is: \nBuild date from CI server is: \n");
        $this->tester->execute(
            array(
                'command' => 'tar',
            ),
            $options
        );
        $this->assertEquals(
            $this->tester->getDisplay(),
            'Task: tar finished' . "\n"
        );
        $this->assertFileExists($filename);

        $tar_library = new \Archive_Tar($filename);

        $contents = $tar_library->listContent();

        $this->assertEquals(\count($contents), 1);
        $this->assertEquals($contents[0]['filename'], basename(__FILE__));
    }

    /**
     * @expectedException \RuntimeException
     *
     */
    public function testTarError()
    {
        $t = new \stdClass();
        $this->tester->execute(
            array(
                'command' => 'tar',
                '-f'      => $t
            )
        );
    }
}
