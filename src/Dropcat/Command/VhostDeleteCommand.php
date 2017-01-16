<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class VhostDeleteCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>vhost:delete</info> command will delete a vhost for a site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat vhost:create</info>
To override config in dropcat.yml, using options:
<info>dropcat vhost:delete --target=/etc/httpd/conf.d</info>';
        $this->setName("vhost:delete")
            ->setDescription("delete a vhost on a remote server")
            ->setDefinition(
                array(
                   new InputOption(
                       'target',
                       't',
                       InputOption::VALUE_OPTIONAL,
                       'Vhost target folder',
                       $this->configuration->vhostTarget()
                   ),
                    new InputOption(
                        'file_name',
                        'f',
                        InputOption::VALUE_OPTIONAL,
                        'Vhost file name',
                        $this->configuration->vhostFileName()
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
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getOption('target');
        $file_name = $input->getOption('file_name');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $port = $input->getOption('ssh_port');
        $ssh_key_password = $input->getOption('ssh_key_password');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = file_get_contents($identity_file);
        $aliasDelete = new Process(
            "ssh -o LogLevel=Error $user@$server -p $port \"rm $target/$file_name\""
        );
        $aliasDelete->setTimeout(999);
        $aliasDelete->run();
        // executes after the command finishes
        if (!$aliasDelete->isSuccessful()) {
            throw new ProcessFailedException($aliasDelete);
        }

        echo $aliasDelete->getOutput();
        $output = new ConsoleOutput();

        $output->writeln('<info>Task: vhost:delete finished</info>');
    }
}
