<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use phpseclib\Crypt\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GetDatabaseBackupCommand extends Command
{

  /** @var Configuration configuration */
  private $configuration;

  public function __construct(Configuration $conf)
  {
    $this->configuration = $conf;
    parent::__construct();
  }

  protected function configure()
  {
    $HelpText = 'The <info>get:db-backup</info> command will run script or command.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat get:db-backup</info>';

    $this->setName("get:db-backup")
      ->setDescription("run command or script on local environment")
      ->setDefinition(
        array(
          new InputOption(
            'remote_backup_path',
            'rbp',
            InputOption::VALUE_OPTIONAL,
            'Remote backup path',
            $this->configuration->localEnvironmentBackupPath()
          ),
          new InputOption(
            'remote_db_backup_name',
            'rdbn',
            InputOption::VALUE_OPTIONAL,
            'Remote db backup name',
            $this->configuration->localEnvironmentBackupDbName()
          ),
          new InputOption(
            'remote_backup_server',
            'rps',
            InputOption::VALUE_OPTIONAL,
            'Remote backup server',
            $this->configuration->localEnvironmentBackupServer()
          ),
          new InputOption(
            'remote_backup_server_user',
            'rpsu',
            InputOption::VALUE_OPTIONAL,
            'User for backup server',
            $this->configuration->localEnvironmentBackupServerUser()
          ),
          new InputOption(
            'remote_backup_server_port',
            'rpsp',
            InputOption::VALUE_OPTIONAL,
            'SSH Remote backup up server ssh port',
            $this->configuration->remoteEnvironmentSshPort()
          ),
        )
      )
      ->setHelp($HelpText);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $remote_backup_path = $input->getOption('remote_backup_path');
    $remote_db_backup_name = $input->getOption('remote_db_backup_name');
    $remote_backup_server = $input->getOption('remote_backup_server');
    $remote_backup_server_user = $input->getOption('remote_backup_server_user');
    $remote_backup_server_port = $input->getOption('remote_backup_server_port');

    exec("scp -C -P $remote_backup_server_port $remote_backup_server_user@$remote_backup_server:$remote_backup_path/$remote_db_backup_name .");

    $output->writeln('<info>Task: ' . $remote_db_backup_name . ' copied to current folder</info>');
  }
}
