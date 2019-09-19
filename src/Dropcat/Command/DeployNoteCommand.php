<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use GuzzleHttp\Client;


/**
 *
 */
class DeployNoteCommand extends DropcatCommand
{

    /**
     *
     */
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> send payload of deploy to endpoint.';
        $this->setName("deploy:note")
          ->setDescription("Send payload of deploy to endpoint")
        ->setDefinition(
            [
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
                'i',
                InputOption::VALUE_OPTIONAL,
                'Identify file',
                $this->configuration->remoteEnvironmentIdentifyFile()
              )

              ]
        )
          ->setHelp($HelpText);
    }

    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh_port');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = file_get_contents($identity_file);
        $ssh_key_password = $input->getOption('ssh_key_password');

        //define('NET_SSH2_LOGGING', 2);
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
        $hostname = $ssh->exec('cat /etc/hostname');

        echo $hostname;

        try {
            if (!isset($hostname)) {
                throw new Exception('could not get hostname');
            }
        } catch (\Exception $e) {
            echo 'error ' . $e->getMessage();
        }


        $deploy_url_endpoint = getenv('DEPLOY_URL_ENDPOINT');
        echo $deploy_url_endpoint;


        $output->writeln('<info>' . $this->mark .
          ' %command.name% finished</info>');
    }


}
