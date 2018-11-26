<?php

namespace Dropcat\Command;

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class RunRemoteCommand extends RunCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>run-remote</info> command will run script or command.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat run-remote</info>
To override config in dropcat.yml, using options:
<info>dropcat run-remote --input=script.sh</info>';

        $this->setName("run-remote")
            ->setDescription("run command or script on local environment")
            ->setDefinition(
                array(
                    new InputOption(
                        'input',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Input',
                        $this->configuration->remoteEnvironmentRun()
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
                        'identity_file',
                        'if',
                        InputOption::VALUE_OPTIONAL,
                        'Identify file',
                        $this->configuration->remoteEnvironmentIdentifyFile()
                    ),

                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh_port');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = file_get_contents($identity_file);
        $ssh_key_password = $input->getOption('ssh_key_password');
        $input = $input->getOption('input');
        define('NET_SSH2_LOGGING', 2);
        $ssh = new SSH2($server, $ssh_port);
        if ($output->isVerbose()) {
            $output->writeln("<info>using server $server and port $ssh_port</info>");
        }

        $auth = new RSA();
        if (isset($ssh_key_password)) {
            $auth->setPassword($ssh_key_password);
            if ($output->isVerbose()) {
                $output->writeln("<info>using $ssh_key_password as password</info>");
            }
        }
        if ($output->isVerbose()) {
            $output->writeln("<info>loading key $identity_file</info>");
        }
        $auth->loadKey($identity_file_content);

        if (!$ssh->login($user, $auth)) {
            $output->writeln($ssh->getLog());
            $output->writeln($ssh->getErrors());
            $output->writeln("<info>Login Failed</info>");
            exit(1);
        }
        $ssh->login($user, $auth);
        if ($output->isVerbose()) {
            $output->writeln("<info>logging in with user $user</info>");
        }
        $run = $ssh->exec($input);

        if ($output->isVerbose()) {
            $output->writeln("<info>$run</info>");
        }

        $output->writeln('<info>Task: run-remote finished</info>');
    }
}
