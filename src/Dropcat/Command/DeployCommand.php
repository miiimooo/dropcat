<?php

namespace Dropcat\Command;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class DeployCommand extends Command
{

    /** @var Configuration configuration */
    private $configuration;

    protected function configure()
    {
        $HelpText = 'The <info>deploy</info> connects to remote server and upload tar and unpack it in path.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat deployt</info>
To override config in dropcat.yml, using options:
<info>dropcat deploy -server 127.0.0.0 -i my_pub.key</info>';


        $this->configuration = new Configuration();
        $this->setName("deploy")
            ->setDescription("Deploy to server")
            ->setDefinition(
                array(
                    new InputOption(
                        'tar',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'Tar',
                        $this->configuration->localEnvironmentTarName()
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
                        'identity_file',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Identify file',
                        $this->configuration->remoteEnvironmentIdentifyFile()
                    ),
                    new InputOption(
                        'ssh_key_password',
                        'skp',
                        InputOption::VALUE_OPTIONAL,
                        'SSH key password',
                        $this->configuration->localEnvironmentSshKeyPassword()
                    ),
                    new InputOption(
                        'target_path',
                        'tp',
                        InputOption::VALUE_OPTIONAL,
                        'Target path',
                        $this->configuration->remoteEnvironmentTargetPath()
                    ),
                    new InputOption(
                        'web_root',
                        'w',
                        InputOption::VALUE_OPTIONAL,
                        'Web root',
                        $this->configuration->remoteEnvironmentWebRoot()
                    ),
                    new InputOption(
                        'temp_folder',
                        'tf',
                        InputOption::VALUE_OPTIONAL,
                        'Temp folder',
                        $this->configuration->remoteEnvironmentTempFolder()
                    ),
                    new InputOption(
                        'alias',
                        'a',
                        InputOption::VALUE_OPTIONAL,
                        'Symlink alias',
                        $this->configuration->remoteEnvironmentAlias()
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tar = $input->getOption('tar');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $target_path = $input->getOption('target_path');
        $port = $input->getOption('ssh_port');
        $ssh_key_password = $input->getOption('ssh_key_password');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = file_get_contents($identity_file);
        $web_root = $input->getOption('web_root');
        $alias = $input->getOption('alias');
        $temp_folder = $input->getOption('temp_folder');

        $ssh = new SSH2($server, $port);
        $auth = new RSA();
        if (isset($ssh_key_password)) {
            $auth->setPassword($ssh_key_password);
        }
        $auth->loadKey($identity_file_content);

        if (!$ssh->login($user, $auth)) {
            exit('Login Failed');
        }

        $ssh->exec('mkdir ' . $temp_folder . '/' . $target_path);
        $ssh->exec('mv ' . $temp_folder . '/' . $tar . ' ' . $temp_folder . '/' . $target_path);
        $ssh->exec('cd ' . $temp_folder . '/' . $target_path);
        $ssh->exec('tar xvf ' . $tar);
        $ssh->exec('cd ..');
        $ssh->exec('mv ' . $target_path . ' ' . $web_root);
        $ssh->exec('cd ' . $web_root);
        $ssh->exec('rm ' . $alias);
        $ssh->exec('ln -s ' . $target_path . ' ' . $alias);

        $ssh->disconnect();

        $output->writeln('<info>Task: deploy finished</info>');
    }
}
