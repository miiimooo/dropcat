<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\CreateDrushAlias;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use mysqli;

class PrepareCommand extends Command
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
        $HelpText = 'The <info>prepare</info> command setups what is needed for a drupal site on a remote server.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat prepare</info>
To override config in dropcat.yml, using options:
<info>dropcat prepare -url http://mysite --drush-alias=mysite</info>';

        $this->setName('prepare')
            ->setDescription('Prepare site')
            ->setDefinition(
                array(
                    new InputOption(
                        'drush_folder',
                        'df',
                        InputOption::VALUE_OPTIONAL,
                        'Drush folder',
                        $this->configuration->localEnvironmentDrushFolder()
                    ),
                    new InputOption(
                        'drush_alias',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        'Drush alias',
                        $this->configuration->siteEnvironmentDrushAlias()
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
                        'web_root',
                        'w',
                        InputOption::VALUE_OPTIONAL,
                        'Web root',
                        $this->configuration->remoteEnvironmentWebRoot()
                    ),
                    new InputOption(
                        'alias',
                        'a',
                        InputOption::VALUE_OPTIONAL,
                        'Symlink alias',
                        $this->configuration->remoteEnvironmentAlias()
                    ),
                    new InputOption(
                        'url',
                        'url',
                        InputOption::VALUE_OPTIONAL,
                        'Site url',
                        $this->configuration->siteEnvironmentUrl()
                    ),
                    new InputOption(
                        'site_name',
                        'sn',
                        InputOption::VALUE_OPTIONAL,
                        'Site name',
                        $this->configuration->siteEnvironmentName()
                    ),
                    new InputOption(
                        'mysql_host',
                        'mh',
                        InputOption::VALUE_OPTIONAL,
                        'Mysql host',
                        $this->configuration->mysqlEnvironmentHost()
                    ),
                    new InputOption(
                        'mysql_port',
                        'mp',
                        InputOption::VALUE_OPTIONAL,
                        'Mysql port',
                        $this->configuration->mysqlEnvironmentPort()
                    ),
                    new InputOption(
                        'mysql_db',
                        'md',
                        InputOption::VALUE_OPTIONAL,
                        'Mysql db',
                        $this->configuration->mysqlEnvironmentDataBase()
                    ),
                    new InputOption(
                        'mysql_user',
                        'mu',
                        InputOption::VALUE_OPTIONAL,
                        'Mysql user',
                        $this->configuration->mysqlEnvironmentUser()
                    ),
                    new InputOption(
                        'mysql_password',
                        'mpd',
                        InputOption::VALUE_OPTIONAL,
                        'Mysql password',
                        $this->configuration->mysqlEnvironmentPassword()
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
        $drush_folder = $input->getOption('drush_folder');
        $drush_alias = $input->getOption('drush_alias');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh_port');
        $web_root = $input->getOption('web_root');
        $alias = $input->getOption('alias');
        $url = $input->getOption('url');
        $site_name = $input->getOption('site_name');
        $mysql_host = $input->getOption('mysql_host');
        $mysql_port = $input->getOption('mysql_port');
        $mysql_db = $input->getOption('mysql_db');
        $mysql_user = $input->getOption('mysql_user');
        $mysql_password = $input->getOption('mysql_password');
        $timeout = $input->getOption('timeout');

        $drushAlias = new CreateDrushAlias();
        $drushAlias->setName($site_name);
        $drushAlias->setServer($server);
        $drushAlias->setUser($user);
        $drushAlias->setWebRoot($web_root);
        $drushAlias->setSitePath($alias);
        $drushAlias->setUrl($url);
        $drushAlias->setSSHPort($ssh_port);

        $drush_file = new Filesystem();

        try {
            $drush_file->dumpFile($drush_folder.'/'.$drush_alias.'.aliases.drushrc.php', $drushAlias->getValue());
        } catch (IOExceptionInterface $e) {
            $output->writeln('<info>An error occurred while creating your file at ' . $e->getPath() . '</info>');

            echo 'An error occurred while creating your file at '.$e->getPath();
            exit(1);
        }
        try {
            $mysqli = new mysqli("$mysql_host", "$mysql_user", "$mysql_password");
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
            exit(1);
        }
        // If db does not exist
        if ($mysqli->select_db("$mysql_db") === false) {
            $process = new Process(
                "mysqladmin -u $mysql_user -p$mysql_password -h $mysql_host -P $mysql_port create $mysql_db"
            );
            $process->setTimeout($timeout);
            $process->run();
            // Executes after the command finishes.
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            echo $process->getOutput();
            $output->writeln('<info>Database created</info>');
        } else {
            $output->writeln('<info>Database exists</info>');
        }
        $output->writeln('<info>Task: prepare finished</info>');
    }
}
