<?php

use Dropcat\Command\ConfigImportCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Created by SOPA
 */
class ConfigImportCommandTest extends \PHPUnit_Framework_TestCase
{

    function setUp() {
        $this->conf = $configuration = new Configuration();
        $application = new Application();
        $application->add(new ConfigImportCommand($this->conf));
        $command = $application->find('config-import');
        $this->tester =  new CommandTester($command);
    }

    function testConfigImport()
    {
        $this->tester->execute(
          array(
            'command' => 'configimport',
            '-d'      => 'mysite',
            '-c'      => 'myconfig'
          )
        );
        $this->assertEquals($this->tester->getDisplay(),
                'Task: configimport finished' . "\n");
      // @todo more testing needed
    }
}
