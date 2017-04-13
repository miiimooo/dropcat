<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;
use Dropcat\Lib\Styles;
use Symfony\Component\Yaml\Exception\ParseException;

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

        try {
            $rollback = Yaml::parse(file_get_contents($tracker_file));
        } catch (ParseException $e) {
            printf("unable to parse the YAML string: %s", $e->getMessage());
        }
        if (!isset($rollback['db-host'])) {
            throw new Exception('db-host missing');
        }
        // Just for testing now.
        var_dump($rollback);

       $this->dumpDb($rollback['db-host'], $rollback['db-user'], $rollback['db-pass'], $rollback['db-name']);

        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $mark_formatted = $style->colorize('yellow', $mark);
        $output->writeln('<info>' . $mark_formatted .
          ' rollback finished</info>');
    }
    protected function movedir() {
      // login to apache, remove symlink, add new symlink
    }
    protected function dumpDb($dbhost, $dbuser, $dbpass, $dbname) {
        $mysql = "mysqldump -h $dbhost -u $dbuser -p $dbpass $dbname > /tmp/$dbname.sql";
        echo $mysql;
        die();
        $dump = new Process($mysql);
        die();
        // database backup - use settings for backup
    }
    protected function dropDb() {
      // drop the db
    }
    protected function insertDb() {
      // insert the db
    }
}
