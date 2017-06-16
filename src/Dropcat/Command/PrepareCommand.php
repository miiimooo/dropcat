<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\CheckDrupal;
use Dropcat\Lib\Tracker;
use Dropcat\Lib\Db;
use Dropcat\Lib\Write;
use Dropcat\Lib\Upload;
use Dropcat\Lib\Create;
use Dropcat\Lib\Vhost;
use Dropcat\Lib\Install;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;
use Dropcat\Lib\UUID;


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
                  'drush-folder',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Drush folder',
                  $this->configuration->localEnvironmentDrushFolder()
              ),
              new InputOption(
                  'drush-script',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Drush script path (can be remote)'
              ),
              new InputOption(
                  'drush-alias',
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
                  'ssh-port',
                  'p',
                  InputOption::VALUE_OPTIONAL,
                  'SSH port',
                  $this->configuration->remoteEnvironmentSshPort()
              ),
              new InputOption(
                  'ssh-key-password',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'SSH key password',
                  $this->configuration->localEnvironmentSshKeyPassword()
              ),
              new InputOption(
                  'ssh-key',
                  'i',
                  InputOption::VALUE_OPTIONAL,
                  'SSH key',
                  $this->configuration->remoteEnvironmentIdentifyFile()
              ),
              new InputOption(
                  'web-root',
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
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Site url',
                  $this->configuration->siteEnvironmentUrl()
              ),
              new InputOption(
                  'site-name',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Site name',
                  $this->configuration->siteEnvironmentName()
              ),
              new InputOption(
                  'mysql-host',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Mysql host',
                  $this->configuration->mysqlEnvironmentHost()
              ),
              new InputOption(
                  'mysql-port',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Mysql port',
                  $this->configuration->mysqlEnvironmentPort()
              ),
              new InputOption(
                  'mysql-db',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Mysql db',
                  $this->configuration->mysqlEnvironmentDataBase()
              ),
              new InputOption(
                  'mysql-user',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Mysql user',
                  $this->configuration->mysqlEnvironmentUser()
              ),
              new InputOption(
                  'mysql-password',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Mysql password',
                  $this->configuration->mysqlEnvironmentPassword()
              ),
              new InputOption(
                  'timeout',
                  null,
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
              new InputOption(
                  'sync-folder',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Sync folder',
                  $this->configuration->syncFolder()
              ),
              new InputOption(
                  'config-split-folder',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Config split folder',
                  $this->configuration->configSplitFolder()
              ),
              new InputOption(
                  'profile',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Install profile to use',
                  $this->configuration->drupalInstallProfile()
              ),
              ]
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_script = $input->getOption('drush-script');
        $drush_folder = $input->getOption('drush-folder');
        $drush_alias = $input->getOption('drush-alias');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $ssh_port = $input->getOption('ssh-port');
        $identity_file = $input->getOption('ssh-key');
        $ssh_key_password = $input->getOption('ssh-key-password');
        $web_root = $input->getOption('web-root');
        $alias = $input->getOption('alias');
        $url = $input->getOption('url');
        $site_name = $input->getOption('site-name');
        $mysql_host = $input->getOption('mysql-host');
        $mysql_port = $input->getOption('mysql-port');
        $mysql_db = $input->getOption('mysql-db');
        $mysql_user = $input->getOption('mysql-user');
        $mysql_password = $input->getOption('mysql-password');
        $timeout = $input->getOption('timeout');
        $tracker_file = $input->getOption('tracker-file');
        $create_site = $input->getOption('create-site');
        $sync_folder = $input->getOption('sync-folder');
        $config_split_folder = $input->getOption('config-split-folder');
        $profile = $input->getOption('profile');
        $verbose = false;

        if ($output->isVerbose()) {
            $verbose = true;
        }

        $tracker_dir = $this->configuration->trackerDir();
        $app_name = $this->configuration->localEnvironmentAppName();

        $mysql_root_user = $mysql_user;
        $mysql_root_pass = $mysql_password;
        $new_site_name = '';


        // Use the $create_site variable for setting up.
        if (isset($create_site)) {
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
                    $cleaned_string = str_replace(".", "", $create_site);
                    $new_site_name = mb_strimwidth($cleaned_string, 0, 59);
                    // check if a site already exists with that name.
                    if (strstr($site, $new_site_name)) {
                        throw new Exception('site already exists');
                    }
                }
            }


            $new_site_name = mb_strimwidth($new_site_name, 0, 16);
            $site_domain = $create_site;
            $drush_alias = $new_site_name;
            $mysql_user = $new_site_name;
            $mysql_db = $new_site_name;
            $mysql_password = uniqid();

            $mysql_conf = [
              'mysql-root-user' => $mysql_root_user,
              'mysql-root-pass' => $mysql_root_pass,
              'mysql-host' =>$mysql_host,
              'mysql-user' => $mysql_user,
              'mysql-password' => $mysql_password,
              'timeout' => $timeout,
            ];

            $db = new Db();
            $db->createUser($mysql_conf);

            $new_db_conf = [
              'mysql-host' =>$mysql_host,
              'mysql-user' => $mysql_user,
              'mysql-password' => $mysql_password,
              'mysql-db' => $mysql_db,
              'mysql-port' => $mysql_port,
              'timeout' => $timeout,
              'mysql-root-user' => $mysql_root_user,
              'mysql-root-pass' => $mysql_root_pass,
            ];
            // Create database.
            $db = new Db();
            $db->createDb($new_db_conf);

            $site_name = $drush_alias;
            $url = 'http://' . $create_site;
            $site_alias = "$web_root/$alias";
            $uuid = UUID::v4();
            $hash = hash('ripemd320', $uuid);
            $url_safe_hash = str_replace(['+', '/', '='], ['-', '_', ''], $hash);

            $site = [
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
                      'hash' => $url_safe_hash,
                      'user' => $user,
                      'port' => $ssh_port,
                      'id-file' => $identity_file,
                      'pass' => $ssh_key_password,
                      'alias-path' => $site_alias,
                      'site-domain' => $site_domain,
                      'sync-folder' => $sync_folder,
                      'config-split-folder' => $config_split_folder,
                    ],
                    'drush' => [
                      'alias' => $drush_alias,
                    ]
                  ],
            ];

            $tracker_conf = [
              'tracker-file' => $tracker_file,
              'new-site' => $site,
              'tracker-dir' => $tracker_dir,
              'app-name' =>  $app_name,
            ];

            $write = new Tracker();
            $write->add($tracker_conf);

            // Create drush alias, if it is drupal.
            $check = new CheckDrupal();
            if ($check->isDrupal()) {
                $drush_alias_conf = [
                  'site-name' => $site_name,
                  'server' => $server,
                  'user' => $user,
                  'web-root' => $web_root,
                  'alias' => $alias,
                  'url' => $url,
                  'ssh-port' =>$ssh_port,
                  'drush-script' => $drush_script,
                  'drush-folder' => $drush_folder,
                  'drush-alias' => $drush_alias,
                ];
                $write = new Write();
                $write->drushAlias($drush_alias_conf);
            }

            $sites_php_conf = [
              'app-name' => $app_name,
              'tracker-file' => $tracker_file,
            ];
            $sitesphp = new Write();
            $sitesphp->sitesPhp($sites_php_conf);

            $conf = [
              'tracker-file'  => $tracker_file,
              'site'          =>  $site_name,
              'app-name'      => $app_name,
            ];
            $localSettings = new Write();
            $localSettings->localSettingsPhp($conf);

            $target = $site_alias . '/web/sites/' . $site_domain;

            $remote_config = [
              'server' => $server,
              'user' => $user,
              'port' => $ssh_port,
              'key' => $identity_file,
              'pass' => $ssh_key_password,
              'timeout' => $timeout,
              'target' => $target,
            ];

            $create = new Create();
            $create->folder($remote_config);

            if (isset($config_split_folder)) {
                $remote_config['target'] = $site_alias . '/web/sites/' . $site_domain . '/sync';
                $create = new Create();
                $create->folder($remote_config);
                $output->writeln('<info>' . $this->mark . ' config split folder created.</info>');
            }

            $from = "/tmp/$app_name.local.settings.php";
            $to = "$target/settings.local.php";
            $upload_settings_local = new Upload();
            $upload_settings_local->place($remote_config, $from, $to, $verbose);

            $from = "/tmp/$app_name-sites.php";
            $to = $site_alias . '/web/sites/sites.php';
            $upload_sites_php = new Upload();
            $upload_sites_php->place($remote_config, $from, $to, $verbose);

            $from = realpath("settings/default.settings.php");
            $to = "$target/settings.php";
            $upload_settings_php = new Upload();
            $upload_settings_php->place($remote_config, $from, $to, $verbose);

            $from = realpath("settings/default.services.yml");
            $to = "$target/services.yml";
            $upload_settings_php = new Upload();
            $upload_settings_php->place($remote_config, $from, $to, $verbose);

            $output->writeln('<info>' . $this->mark . ' needed files in place.</info>');

            // add option for this, now hardcoded
            $server_alias = '';
            $target = '/etc/httpd/conf.d';
            $extra = '';
            $port = '80';
            $bash_command = 'sudo service httpd restart';

            $vhost_config = [
              'target' => $target,
              'file-name' => $new_site_name,
              'document-root' => "$site_alias/web",
              'port' => $port,
              'server-name' => $site_domain,
              'server-alias' => $server_alias,
              'extra' => $extra,
              'bash-command' => $bash_command,
              'server' => $server,
              'user' => $user,
              'ssh-port' => $ssh_port,
              'ssh-key-password' => $ssh_key_password,
              'identity-file' => $identity_file,
            ];

            // Create a vhost file.
            $vhost = new Vhost();
            $vhost->create($vhost_config);

            // If it is drupal, install the site.
            $check = new CheckDrupal();
            if ($check->isDrupal()) {
                $drush_config = [
                  'drush-alias' => $drush_alias,
                  'profile' => $profile,
                  'site-name' => $site_name,
                  'subdir' => $site_domain,
                ];

                $install = new Install();
                $install->drupal($drush_config, $verbose);
            }
        }





        $output->writeln('<info>' . $this->mark . ' prepare finished</info>');
    }

}
