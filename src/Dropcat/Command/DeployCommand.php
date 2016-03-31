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
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class DeployCommand extends Command
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
        $HelpText = 'The <info>deploy</info> connects to remote server and upload tar and unpack it in path.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat deployt</info>
To override config in dropcat.yml, using options:
<info>dropcat deploy -server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("deploy")
            ->setDescription("Deploy to server")
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
                        'seperator',
                        'se',
                        InputOption::VALUE_OPTIONAL,
                        'Name seperator',
                        $this->configuration->localEnvironmentSeperator()
                    ),
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
                        'aa',
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
        $app_name = $input->getOption('app-name');
        $build_id = $input->getOption('build-id');
        $seperator = $input->getOption('seperator');
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

        if (isset($tar)) {
            $tarfile = $tar;
        }
        else {
            $tarfile = $app_name . $seperator . $build_id . '.tar';
        }
        $deploy_folder = "$app_name$seperator$build_id";

        if ($output->isVerbose()) {
            echo "deploy folder: $deploy_folder\n";
            echo "tarfile: $tarfile\n";
        }

        $ssh->exec("mkdir $temp_folder/$deploy_folder");
        $ssh->exec("mv $temp_folder/$tarfile $temp_folder/$deploy_folder/");
        $ssh->exec('tar xvf ' . $temp_folder/$deploy_folder/$tarfile);
        //$ssh->exec('cd ..');
        //$ssh->exec('mv ' . $target_path . ' ' . $web_root);
        //$ssh->exec('cd ' . $web_root);
        //$ssh->exec('rm ' . $alias);
        //$ssh->exec('ln -s ' . $target_path . ' ' . $alias);
        $ssh->disconnect();

        $output->writeln('<info>Task: deploy finished</info>');
    }
}
