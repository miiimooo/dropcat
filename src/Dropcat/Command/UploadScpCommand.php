<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class UploadCommand extends Command
{

    /**
     * @var Configuration configuration
     */
    private $configuration;

    public function __construct(Configuration $conf)
    {
        $this->configuration = $conf;
        parent::__construct();
    }

    protected function configure()
    {
        $HelpText = 'The <info>upload</info> connects to remote server with scp and uploads tar file.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat uploadscp</info>
To override config in dropcat.yml, using options:
<info>dropcat uploadscp -server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("uploadscp")
            ->setDescription("Upload to server with scp")
            ->setDefinition(
                array(
                    new InputOption(
                        'app-name',
                        'a',
                        InputOption::VALUE_OPTIONAL,
                        'App name',
                        $this->configuration->localEnvironmentAppName()
                    ),
                    new InputOption(
                        'build-id',
                        'bi',
                        InputOption::VALUE_OPTIONAL,
                        'Id',
                        $this->configuration->localEnvironmentBuildId()
                    ),
                    new InputOption(
                        'separator',
                        'se',
                        InputOption::VALUE_OPTIONAL,
                        'Name separator',
                        $this->configuration->localEnvironmentSeparator()
                    ),
                    new InputOption(
                        'tar',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'Tar',
                        $this->configuration->localEnvironmentTarName()
                    ),
                    new InputOption(
                        'tar_dir',
                        'td',
                        InputOption::VALUE_OPTIONAL,
                        'Tar dir',
                        $this->configuration->localEnvironmentTarDir()
                    ),
                    new InputOption(
                        'server',
                        's',
                        InputOption::VALUE_OPTIONAL,
                        'Server',
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
                        'ssh_key_password',
                        'skp',
                        InputOption::VALUE_OPTIONAL,
                        'SSH key password',
                        $this->configuration->localEnvironmentSshKeyPassword()
                    ),
                    new InputOption(
                        'target_dir',
                        'tp',
                        InputOption::VALUE_OPTIONAL,
                        'Target dir',
                        $this->configuration->remoteEnvironmentTargetDir()
                    ),
                    new InputOption(
                        'identity_file',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Identify file',
                        $this->configuration->remoteEnvironmentIdentifyFile()
                    ),
                    new InputOption(
                        'timeout',
                        'to',
                        InputOption::VALUE_OPTIONAL,
                        'Timeout',
                        $this->configuration->timeOut()
                    ),
                    new InputOption(
                        'keeptar',
                        'kt',
                        InputOption::VALUE_NONE,
                        'Keep tar after upload  (defaults to no)'
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app_name = $input->getOption('app-name');
        $build_id = $input->getOption('build-id');
        $separator = $input->getOption('separator');
        $tar = $input->getOption('tar');
        $tar_dir = $input->getOption('tar_dir');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $targetdir = $input->getOption('target_dir');
        $port = $input->getOption('ssh_port');
        $ssh_key_password = $input->getOption('ssh_key_password');
        $identity_file = $input->getOption('identity_file');
        //$identity_file_content = file_get_contents($identity_file);
        //$timeout = $input->getOption('timeout');
        //$keeptar = $input->getOption('keeptar') ? 'TRUE' : 'FALSE';

        if (isset($tar)) {
            $tarfile = $tar;
        } else {
            $tarfile = $app_name . $separator . $build_id . '.tar';
        }

        $command_line = sprintf('scp -P %s %s/%s %s@%s:%', $port, $tar_dir,$tar, $user, $server);
        $process = new Process("$command_line");
        $process->run();
        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        }
     }
}
