<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\UUID;
use Dropcat\Lib\Styles;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Exception;

/**
 *
 */
class RollbackCommand extends DropcatCommand
{

    /**
     *
     */
    protected function configure()
    {
        $HelpText = '<info>rollback</info> rollbacks site with given info.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat rollback</info>
To override config in dropcat.yml, using options:
<info>dropcat rollback --tracker-file=/my/dir/file.yml</info>';
        $this->setName("rollback")
          ->setDescription("Rollback a site")
          ->setDefinition(
              [
              new InputOption(
                  'tracker-file',
                  't',
                  InputOption::VALUE_OPTIONAL,
                  'Trackerfile',
                  $this->configuration->trackerFile()
              ),
              new InputOption(
                  'id',
                  'i',
                  InputOption::VALUE_OPTIONAL,
                  'Id (used for backups done during rollback, og not set a UUID will be generated instead',
                  $this->configuration->rollbackId()
              ),
              ]
          )
          ->setHelp($HelpText);
    }

    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker_file = $input->getOption('tracker-file');
        $rollback_id = $input->getOption('id');

        if (!isset($rollback_id)) {
            $uuid = new UUID();
            $rollback_id = $uuid->v4();
        }

        try {
            $rollback = Yaml::parse(file_get_contents($tracker_file));
        } catch (ParseException $e) {
            printf("unable to parse the YAML string: %s", $e->getMessage());
        }
        if (!isset($rollback['db-host'])) {
            throw new Exception('db-host missing');
        }
        // Do db backup.
        $this->dumpDb(
            $rollback['db-host'],
            $rollback['db-user'],
            $rollback['db-pass'],
            $rollback['db-name'],
            $rollback_id
        );
        $this->dropDb(
            $rollback['db-host'],
            $rollback['db-user'],
            $rollback['db-pass'],
            $rollback['db-name']
        );
        $this->createDb(
            $rollback['db-host'],
            $rollback['db-user'],
            $rollback['db-pass'],
            $rollback['db-name']
        );
        $this->insertDb(
            $rollback['db-host'],
            $rollback['db-user'],
            $rollback['db-pass'],
            $rollback['db-name'],
            $rollback['db-dump']
        );

        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $mark_formatted = $style->colorize('yellow', $mark);
        $output->writeln('<info>' . $mark_formatted .
          ' rollback finished</info>');
    }
    protected function movedir()
    {
      // login to apache, remove old symlink, add new symlink to dir in tracker.
    }
    protected function dumpDb($dbhost, $dbuser, $dbpass, $dbname, $id)
    {
        $mysql = "mysqldump -h $dbhost -u $dbuser -p$dbpass $dbname > /tmp/$dbname" . '_' . "$id" . '.sql';
        $dump = new Process($mysql);
        $dump->run();
        if (!$dump->isSuccessful()) {
            throw new ProcessFailedException($dump);
        }
    }
    protected function dropDb($dbhost, $dbuser, $dbpass, $dbname)
    {
        $mysql = "mysqladmin -h $dbhost -u $dbuser -p$dbpass drop $dbname -f";
        $drop = new Process($mysql);
        $drop->run();
        if (!$drop->isSuccessful()) {
            throw new ProcessFailedException($drop);
        }
    }
    protected function createDb($dbhost, $dbuser, $dbpass, $dbname)
    {
        $mysql = "mysqladmin -h $dbhost -u $dbuser -p$dbpass create $dbname";
        $drop = new Process($mysql);
        $drop->run();
        if (!$drop->isSuccessful()) {
            throw new ProcessFailedException($drop);
        }
    }
    protected function insertDb($dbhost, $dbuser, $dbpass, $dbname, $dump)
    {
        $mysql = "mysql -h $dbhost -u $dbuser -p$dbpass $dbname < $dump";
        $dump = new Process($mysql);
        $dump->run();
        if (!$dump->isSuccessful()) {
            throw new ProcessFailedException($dump);
        }
    }
}
