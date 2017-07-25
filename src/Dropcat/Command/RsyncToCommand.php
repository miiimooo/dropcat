<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RsyncToCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>rsync command</info> sync local target with remote target.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat rsync:to</info>
To override config in dropcat.yml, using options:
<info>dropcat rsync:to --server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("rsync:to")
            ->setDescription("Rsync folders")
            ->setDefinition(
                array(
                    new InputOption(
                        'from',
                        'f',
                        InputOption::VALUE_OPTIONAL,
                        'From',
                        $this->configuration->localEnvironmentRsyncFrom()
                    ),
                    new InputOption(
                        'to',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'To',
                        $this->configuration->remoteEnvironmentRsyncTo()
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
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $from = $input->getOption('from');
        $to = $input->getOption('to');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $port = $input->getOption('ssh_port');
        $ssh_key_password = $input->getOption('ssh_key_password');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = file_get_contents($identity_file);
        $timeout = $input->getOption('timeout');

        $rsync = 'rsync -a ' . $from . ' -e "ssh -i ' . $identity_file . ' -p ' . $port . '" --progress ' . $user . '@' . $server . ':' . $to;

        $newRsync = new Process("$rsync");
        $newRsync->setTimeout(3600);
        $newRsync->run();
        echo $newRsync->getOutput();
        if (!$newRsync->isSuccessful()) {
            throw new ProcessFailedException($newRsync);
            exit(1);
        }


        $output->writeln('<info>Task: rsync:to finished</info>');
    }
}
