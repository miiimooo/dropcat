<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\Styles;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Backup a site db and files.
 */
class BackupCommand extends DropcatCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>backup</info> command will create a backup of site db.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat backup</info>
To override config in dropcat.yml, using options:
<info>dropcat backup -d mysite -b /var/dump -t 20160101</info>';

        $this->setName("backup")
          ->setDescription("Backup site")
          ->setDefinition(
              [
              new InputOption(
                  'app-name',
                  'ap',
                  InputOption::VALUE_OPTIONAL,
                  'application name',
                  $this->configuration->localEnvironmentAppName()
              ),
              new InputOption(
                  'mysql-host',
                  'mh',
                  InputOption::VALUE_OPTIONAL,
                  'mysql host',
                  $this->configuration->mysqlEnvironmentHost()
              ),
              new InputOption(
                  'mysql-port',
                  'mp',
                  InputOption::VALUE_OPTIONAL,
                  'mysql port',
                  $this->configuration->mysqlEnvironmentPort()
              ),
              new InputOption(
                  'mysql-db',
                  'md',
                  InputOption::VALUE_OPTIONAL,
                  'mysql db',
                  $this->configuration->mysqlEnvironmentDataBase()
              ),
              new InputOption(
                  'mysql-user',
                  'mu',
                  InputOption::VALUE_OPTIONAL,
                  'mysql user',
                  $this->configuration->mysqlEnvironmentUser()
              ),
              new InputOption(
                  'mysql-password',
                  'mpd',
                  InputOption::VALUE_OPTIONAL,
                  'mysql password',
                  $this->configuration->mysqlEnvironmentPassword()
              ),
              new InputOption(
                  'backup-path',
                  'b',
                  InputOption::VALUE_OPTIONAL,
                  'backup path',
                  $this->configuration->siteEnvironmentBackupPath()
              ),
              new InputOption(
                  'time-out',
                  'to',
                  InputOption::VALUE_OPTIONAL,
                  'time out',
                  $this->configuration->timeOut()
              ),
              new InputOption(
                  'backup-site',
                  'bs',
                  InputOption::VALUE_NONE,
                  'backup whole site'
              ),
              new InputOption(
                  'no-db-backup',
                  'ndb',
                  InputOption::VALUE_NONE,
                  'no database backup',
                  null
              ),
              new InputOption(
                  'backup-name',
                  'bn',
                  InputOption::VALUE_OPTIONAL,
                  'name of backup',
                  null
              ),
              new InputOption(
                  'server',
                  's',
                  InputOption::VALUE_OPTIONAL,
                  'server',
                  $this->configuration->remoteEnvironmentServerName()
              ),
              new InputOption(
                  'user',
                  'u',
                  InputOption::VALUE_OPTIONAL,
                  'User (ssh)',
                  $this->configuration->remoteEnvironmentSshUser()
              ),
              new InputOption(
                  'ssh_port',
                  'p',
                  InputOption::VALUE_OPTIONAL,
                  'SSH port',
                  $this->configuration->remoteEnvironmentSshPort()
              ),
              new InputOption(
                  'web_root',
                  'w',
                  InputOption::VALUE_OPTIONAL,
                  'web root',
                  $this->configuration->remoteEnvironmentWebRoot()
              ),
              new InputOption(
                  'alias',
                  'a',
                  InputOption::VALUE_OPTIONAL,
                  'symlink alias',
                  $this->configuration->remoteEnvironmentAlias()
              ),
              ]
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $input->getOption('app-name');
        $mysql_host = $input->getOption('mysql-host');
        $mysql_port = $input->getOption('mysql-port');
        $mysql_db = $input->getOption('mysql-db');
        $mysql_user = $input->getOption('mysql-user');
        $mysql_password = $input->getOption('mysql-password');
        $backup_path = $input->getOption('backup-path');
        $timeout = $input->getOption('time-out');
        $backup_site = $input->getOption('backup-site');
        $no_db_backup = $input->getOption('no-db-backup');
        $backup_name = $input->getOption('backup-name');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh_port');
        $web_root = $input->getOption('web_root');
        $alias = $input->getOption('alias');
        $timestamp = $this->configuration->timeStamp();


        // Nifty styles on output.
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $mark_formatted = $style->colorize('yellow', $mark);

        if (!isset($backup_name)) {
            $backup_name = $timestamp;
        }
        if ($no_db_backup != true) {
            $backupDb = new Process(
                "mkdir -p $backup_path/$app &&
                mysqldump --port=$mysql_port -u $mysql_user -p$mysql_password -h $mysql_host $mysql_db  > $backup_path/$app/$backup_name.sql"
            );
            $backupDb->setTimeout($timeout);
            $backupDb->run();
            if (!$backupDb->isSuccessful()) {
                throw new ProcessFailedException($backupDb);
            }
            echo $backupDb->getOutput();
            $output->writeln('<info>' . $mark_formatted .
              ' db backup finished</info>');
        }
        if ($backup_site === true) {
            $rsyncSite = new Process(
                "mkdir -p $backup_path/$app &&
                rsync -L -a -q -P -e \"ssh -p $ssh_port -o LogLevel=Error\" $user@$server:$web_root/$alias $backup_path/$app"
            );
            $rsyncSite->setTimeout($timeout);
            $rsyncSite->run();
            // executes after the command finishes
            if (!$rsyncSite->isSuccessful()) {
                throw new ProcessFailedException($rsyncSite);
            }
            $mark_formatted = $style->colorize('yellow', $mark);
            $output->writeln('<info>' . $mark_formatted .
              ' site backup finished</info>');
        }
    }
}
