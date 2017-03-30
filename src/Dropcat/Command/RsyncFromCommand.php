<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RsyncFromCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>rsync command</info> sync local target with remote target.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat rsync:from</info>
To override config in dropcat.yml, using options:
<info>dropcat rsync:from --server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("rsync:from")
            ->setDescription("Rsync folders")
            ->setDefinition(
                array(
                    new InputOption(
                        'from',
                        'f',
                        InputOption::VALUE_OPTIONAL,
                        'From',
                        $this->configuration->remoteEnvironmentRsyncFrom()
                    ),
                    new InputOption(
                        'to',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'To',
                        $this->configuration->localEnvironmentRsyncTo()
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
        $timeout = $input->getOption('timeout');
        $rsync = "rsync -chavzP --stats -e 'ssh -i " . $identity_file . ' -p ' . $port . "' $user@$server:$from $to";
        $newRsync = $this->runProcess("$rsync");
        $newRsync->setTimeout(3600);
        $newRsync->run();
        echo $newRsync->getOutput();
        if (!$newRsync->isSuccessful()) {
            throw new ProcessFailedException($newRsync);
        }

        $output->writeln('<info>Task: rsync:to finished</info>');
    }
}
