<?php
use Dropcat\Command\TarCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Created by PhpStorm.
 * User: mikke
 * Date: 2016-03-05
 * Time: 18:34
 */
class BackupCommandTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->conf = $configuration = new Configuration();
        $application = new Application();
        $application->add(new \Dropcat\Command\BackupCommand());
        $command = $application->find('backup');
        $this->tester =  new CommandTester($command);

    }
    function testBackup()
    {
        $this->tester->execute(
            array(
                'command' => 'backup',
                'b' => 'backup'
            )
        );
        $this->assertEquals(
            $this->tester->getDisplay(),
            "\n" . 'Task: backup finished' . "\n"
        );

        $this->conf     = new Configuration();
        $drush_alias    = $this->conf->siteEnvironmentDrushAlias();
        $backup_path    = $this->conf->siteEnvironmentBackupPath();
        $time_stamp     = $this->configuration->timeStamp();
        $process = new Process(
            "drush @$drush_alias sql-dump > $backup_path" . '/' . "$drush_alias" . '_' . "$time_stamp.sql"
        );
        $process->run();

        $this->assertFileExists($backup_path . '/' . $drush_alias . $time_stamp . '.sql');
    }

    /**
     * @expectedException "The "b" argument does not exist."
     * @expectedExceptionMessage Unable to complete test
     */
    function testBackupError()
    {
        $b = new StdClass();
        $this->tester->execute(
            array(
                'command' => 'backup',
                'b' => $b,
            )
        );
    }
}
