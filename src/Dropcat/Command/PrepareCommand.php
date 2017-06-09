<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\CreateDrushAlias;
use Dropcat\Lib\CheckDrupal;
use Dropcat\Lib\Tracker;
use Dropcat\Lib\Db;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use mysqli;
use Exception;

class PrepareCommand extends DropcatCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>prepare</info> command setups what is needed for a site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml):
<info>dropcat prepare</info>
To override config in dropcat.yml, using options:
<info>dropcat prepare --ssh_port=2200 --drush-alias=mysite</info>';

        $this->setName('prepare')
          ->setDescription('Prepare site')
          ->setDefinition(
              [
              new InputOption(
                  'drush_folder',
                  'df',
                  InputOption::VALUE_OPTIONAL,
                  'Drush folder',
                  $this->configuration->localEnvironmentDrushFolder()
              ),
              new InputOption(
                  'drush_script',
                  'ds',
                  InputOption::VALUE_OPTIONAL,
                  'Drush script path (can be remote)'
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
              new InputOption(
                  'tracker-file',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Trackerfile',
                  $this->configuration->trackerFile()
              ),
              new InputOption(
                  'create-site',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Create site',
                  $this->configuration->createSite()
              ),
              ]
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_script = $input->getOption('drush_script');
        $drush_folder = $input->getOption('drush_folder');
        $drush_alias = $input->getOption('drush_alias');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh_port');
        $identity_file = $input->getOption('identity_file');
        $ssh_key_password = $input->getOption('ssh_key_password');
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
        $tracker_file = $input->getOption('tracker-file');
        $create_site = $input->getOption('create-site');

        $tracker_dir = $this->configuration->trackerDir();
        $app_name = $this->configuration->localEnvironmentAppName();

        if ($tracker_file == null) {
            $tracker_file = $tracker_dir . '/default/' . $app_name . '.yml';
        }

        $tracker = new Tracker();
        $sites = $tracker->read($tracker_file);

        foreach ($sites as $site => $siteProperty) {
            // getting the default user - same as the root user.
            if ($site === 'default') {
                $mysql_host = $siteProperty['db']['host'];
                $mysql_root_user = $siteProperty['db']['user'];
                $mysql_root_pass = $siteProperty['db']['pass'];
            }

            if (isset($create_site)) {
                // check if a site already exists with that name.
                if (strstr($site, $create_site)) {
                    throw new Exception('site already exists');
                }
            }
        }
        // Use the $create_site variable for setting up.
        if (isset($create_site)) {
            $cleaned_string = str_replace(".", "", $create_site);
            $truncated_string = mb_strimwidth($cleaned_string, 0, 59);
            $drush_alias = $truncated_string;
            $mysql_user = $truncated_string . '_u1';
            $mysql_db = $truncated_string . '_db1';
            $mysql_password = uniqid();

            $db = new Db();
            $db->createUser(
                $mysql_root_user,
                $mysql_root_pass,
                $mysql_host,
                $mysql_user,
                $mysql_password,
                $timeout
            );

            $site_name = $drush_alias;
            $url = 'http://' . $create_site;
            $site_alias = "$web_root/$alias";
            $new_site = [
                  $site_name => [
                    'db' => [
                      'dump' => null,
                      'name' => $mysql_db,
                      'user' => $mysql_user,
                      'pass' => $mysql_password,
                      'host' => $mysql_host,
                    ],
                    'web' => [
                      'host' => $server,
                      'user' => $user,
                      'port' => $ssh_port,
                      'id-file' => $identity_file,
                      'pass' => $ssh_key_password,
                      'alias-path' => $site_alias,
                    ],
                    'drush' => [
                      'alias' => $drush_alias,
                    ]
                  ],
                ];

            $write = new Tracker();
            $write->add($tracker_file, $new_site, $tracker_dir, $app_name);

        }
        $db = new Db();
        $db->create(
            $mysql_host,
            $mysql_user,
            $mysql_password,
            $mysql_db,
            $mysql_port,
            $timeout,
            $mysql_root_user,
            $mysql_root_pass
        );

        $check = new CheckDrupal();
        if ($check->isDrupal()) {
            // Create drush alias.
            $this->writeDrushAlias(
                $site_name,
                $server,
                $user,
                $web_root,
                $alias,
                $url,
                $ssh_port,
                $drush_script,
                $drush_folder,
                $drush_alias
            );
        }

        $output->writeln('<info>' . $this->mark_formatted . ' prepare finished</info>');
    }

    /**
     * Write drush alias.
     */
    private function writeDrushAlias(
        $site_name,
        $server,
        $user,
        $web_root,
        $alias,
        $url,
        $ssh_port,
        $drush_script,
        $drush_folder,
        $drush_alias
    ) {
        $output = new ConsoleOutput();

        $drushAlias = new CreateDrushAlias();
        $drushAlias->setName($site_name);
        $drushAlias->setServer($server);
        $drushAlias->setUser($user);
        $drushAlias->setWebRoot($web_root);
        $drushAlias->setSitePath($alias);
        $drushAlias->setUrl($url);
        $drushAlias->setSSHPort($ssh_port);
        if ($drush_script) {
            $drushAlias->setDrushScriptPath($drush_script);
        }

        $drush_file = new Filesystem();

        try {
            $drush_file->dumpFile(
                $drush_folder . '/' . $drush_alias . '.aliases.drushrc.php',
                $drushAlias->getValue()
            );
        } catch (IOExceptionInterface $e) {
            echo 'an error occurred while creating your file at ' . $e->getPath();
            exit(1);
        }
        $output->writeln('<info>' . $this->mark_formatted . ' drush alias ' .  $drush_alias  . ' created</info>');
    }

}
