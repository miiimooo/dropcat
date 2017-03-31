<?php
/**
 * Created by PhpStorm.
 * User: henrikpejer
 * Date: 2017-03-31
 * Time: 12:35
 */

namespace Dropcat\Tests {

    use Dropcat\Command\VarnishPurgeCommand;

    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Dropcat\Lib\DropcatFactories;
    use Dropcat\Services\Configuration;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Tester\CommandTester;

    class VarnishPurgeCommandTest extends \PHPUnit_Framework_TestCase
    {

        public function setUp()
        {
            // building the container!
            $this->container = new ContainerBuilder();

            // Setting DropcatContainer to the DI-container we use.
            // This way, it will be available to the command.
            $this->container->set('DropcatContainer', $this->container);

            $conf = $this->createMock('Dropcat\Services\Configuration');

            $conf->method('siteEnvironmentUrl')->willReturn('http://www.something.com');
            $this->conf = $conf;


            $this->application = new Application();

            // We mock the command so that we later on can test Process.
            $this->mock = $this->getMockBuilder('Dropcat\Command\VarnishPurgeCommand')
                ->setConstructorArgs(array($this->container, $this->conf));

            $this->factories_mock = $this->createMock('Dropcat\Lib\DropcatFactories');
        }



        function testVarnishCommand()
        {
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

            $options = array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            );

            $this->openSocketAndListen();
            $this->expectOutputString('fgets gets this');
            $this->tester->execute(
                array(
                    'command' => 'varnish:purge',
                    '-vi'     => '123.456.789.012',
                    '-vp'     => '8080',
                    '-u'      => '/some/page'
                ),
                $options
            );

            echo $this->tester->getDisplay();
        }

        function testVarnishCommandMissingParams()
        {
            $this->container->set('dropcat.factory', $this->factories_mock);

            $this->expectException('\\RuntimeException');
            $this->expectExceptionMessage('No configuration related with varnish deploy environment');

            $process_mock = $this->createMock('Symfony\Component\Process\Process');

            $process_mock->method('isSuccessful')
                ->willReturn(true);

            $command_mock = $this->mock->setMethods(['runProcess'])
                ->getMock();

            // Add our mocked command from above.
            $this->application->add($command_mock);

            // Initiate the tester.
            $this->tester = new CommandTester($command_mock);

            $options = array(
                'verbosity' => OutputInterface::VERBOSITY_VERBOSE
            );
            $this->tester->execute(
                array(
                    'command' => 'varnish:purge'
                ),
                $options
            );

            echo $this->tester->getDisplay();
        }

        public function fsockopen($ip, $port)
        {
            $this->assertEquals($ip, '123.456.789.012');
            $this->assertEquals($port, '8080');
        }

        public function fwrite()
        {
        }

        public function feof()
        {
            static $counter = 0;
            if ($counter > 0) {
                return true;
            }
            ++$counter;
            return false;
        }

        public function fgets()
        {
            # var_dump(func_get_args());
            return 'fgets gets this';
        }

        public function fclose()
        {
            return false;
        }

        private function openSocketAndListen()
        {
            \Dropcat\Command\setTestClass($this);
        }
    }
}

namespace Dropcat\Command {

    $testObject = null;

    function setTestClass($o)
    {
        global $testObject;
        $testObject = $o;
    }

    function fsockopen()
    {
        global $testObject;
        return call_user_func_array(array($testObject, 'fsockopen'), func_get_args());
    }

    function fwrite()
    {
        global $testObject;
        return call_user_func_array(array($testObject, 'fwrite'), func_get_args());
    }

    function feof()
    {
        global $testObject;
        return call_user_func_array(array($testObject, 'feof'), func_get_args());
    }

    function fgets()
    {
        global $testObject;
        return call_user_func_array(array($testObject, 'fgets'), func_get_args());
    }
    function fclose()
    {
        global $testObject;
        return call_user_func_array(array($testObject, 'fclose'), func_get_args());
    }
}
