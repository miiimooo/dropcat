<?php

namespace Dropcat\Services;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Yaml\Yaml;

class UnifiedConfiguration extends DropcatConfigurationBase implements DropcatConfigurationInterface
{

    protected $env;
  /**
   * UnifiedConfiguration constructor.
   */
    public function __construct()
    {

        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('DROPCAT_ENV') ?: 'dev');
        $running_path = getcwd();
        if (file_exists($running_path . '/.dropcat') && is_dir($running_path . '/.dropcat')) {
            $running_path .= '/.dropcat';
        }

        if (file_exists($running_path . '/dropcat_unified.yml')) {
            $config = Yaml::parse(
                file_get_contents($running_path . '/dropcat_unified.yml')
            );
            $this->configuration = isset($config[$env]) ? $config[$env] : $config['default'];
        }
    }


  /**
   * Gets the app name.
   */
    public function localEnvironmentAppName()
    {
        if (isset($this->configuration['app_name'])) {
            return $this->configuration['app_name'];
        } else {
            return null;
        }
    }

  /**
   * Gets the absolute path of the actual app we want to deploy.
   */
    public function localEnvironmentAppPath()
    {
        if (isset($this->configuration['local']['app_path'])) {
            return $this->configuration['local']['app_path'];
        } else {
            return null;
        }
    }


  /**
   * Get build id, prefderable overriden with option.
   */
    public function localEnvironmentBuildId()
    {
        $buildId = null;
        $buildNumber = getenv('BUILD_NUMBER');
        $buildDate = getenv('BUILD_DATE');
        if (isset($buildNumber)) {
            $buildId = $buildNumber;
            if (isset($buildDate)) {
                $buildId .= '_' . $buildDate;
            }
        } elseif (isset($buildDate)) {
            $buildId = $buildDate;
        } elseif (isset($this->configuration['local']['build_id'])) {
            $buildId = $this->configuration['local']['build_id'];
        }
        return $buildId;
    }

  /**
   * Gets the absolute path of a tmp-folder in this environment.
   */
    public function localEnvironmentTmpPath()
    {
        if (isset($this->configuration['local']['tmp_path'])) {
            return $this->configuration['local']['tmp_path'];
        } else {
            return null;
        }
    }

  /**
   * Gets the separator in names.
   */
    public function localEnvironmentSeparator()
    {
        if (isset($this->configuration['local']['separator'])) {
            return $this->configuration['local']['separator'];
        } else {
            return null;
        }
    }

  /**
   * Gets the db to import.
   */
    public function localEnvironmentDbImport()
    {
        if (isset($this->configuration['local']['db_import'])) {
            return $this->configuration['local']['db_import'];
        } else {
            return null;
        }
    }

  /**
   * Get name of tar to deploy.
   */
    public function localEnvironmentTarName()
    {
        if (isset($this->configuration['local']['tar_name'])) {
            return $this->configuration['local']['tar_name'];
        } else {
            return null;
        }
    }

  /**
   * Get name of dir that tar is in.
   */
    public function localEnvironmentTarDir()
    {
        if (isset($this->configuration['local']['tar_dir'])) {
            return $this->configuration['local']['tar_dir'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh-key password
   */
    public function localEnvironmentSshKeyPassword()
    {
        if (isset($this->configuration['local']['ssh_key_password'])) {
            return $this->configuration['local']['ssh_key_password'];
        } else {
            return null;
        }
    }

  /**
   * Get path to drush folder.
   */
    public function localEnvironmentDrushFolder()
    {
        if (isset($this->configuration['local']['drush_folder'])) {
            return $this->configuration['local']['drush_folder'];
        } else {
            return null;
        }
    }

  /**
   * Get path to drush folder.
   */
    public function localEnvironmentRun()
    {
        if (isset($this->configuration['local']['run'])) {
            return $this->configuration['local']['run'];
        } else {
            return null;
        }
    }

  /**
   * Get server for backups.
   */
    public function localEnvironmentBackupServer()
    {
        if (isset($this->configuration['local']['remote_backup_server'])) {
            return $this->configuration['local']['remote_backup_server'];
        } else {
            return null;
        }
    }

  /**
   * Get port for server for backups.
   */
    public function localEnvironmentBackupServerPort()
    {
        if (isset($this->configuration['local']['remote_backup_server_port'])) {
            return $this->configuration['local']['remote_backup_server_port'];
        } else {
            return null;
        }
    }

  /**
   * Get user to login to server for backups.
   */
    public function localEnvironmentBackupServerUser()
    {
        if (isset($this->configuration['local']['remote_backup_server_user'])) {
            return $this->configuration['local']['remote_backup_server_user'];
        } else {
            return null;
        }
    }

  /**
   * Get path to backups
   */
    public function localEnvironmentBackupPath()
    {
        if (isset($this->configuration['local']['remote_backup_path'])) {
            return $this->configuration['local']['remote_backup_path'];
        } else {
            return null;
        }
    }
  /**
   * Get user to login to server for backups.
   */
    public function localEnvironmentBackupDbName()
    {
        if (isset($this->configuration['local']['remote_db_backup_name'])) {
            return $this->configuration['local']['remote_db_backup_name'];
        } else {
            return null;
        }
    }


  /**
   * Get remote server name.
   */
    public function remoteEnvironmentServerName()
    {
        if (isset($this->configuration['remote']['server'])) {
            return $this->configuration['remote']['server'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh user.
   */
    public function remoteEnvironmentSshUser()
    {
        if (isset($this->configuration['remote']['ssh_user'])) {
            return $this->configuration['remote']['ssh_user'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh user.
   */
    public function remoteEnvironmentTargetPath()
    {
        if (isset($this->configuration['remote']['target_path'])) {
            return $this->configuration['remote']['target_path'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh user.
   */
    public function remoteEnvironmentSshPort()
    {
        if (isset($this->configuration['remote']['ssh_port'])) {
            return $this->configuration['remote']['ssh_port'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh pub key.
   */
    public function remoteEnvironmentIdentifyFile()
    {
        if (isset($this->configuration['remote']['identity_file'])) {
            return $this->configuration['remote']['identity_file'];
        } else {
            return null;
        }
    }

  /**
   * Get ssh web root.
   */
    public function remoteEnvironmentWebRoot()
    {
        if (isset($this->configuration['remote']['web_root'])) {
            return $this->configuration['remote']['web_root'];
        } else {
            return null;
        }
    }

  /**
   * Get remote temp folder.
   */
    public function remoteEnvironmentTempFolder()
    {
        if (isset($this->configuration['remote']['temp_folder'])) {
            return $this->configuration['remote']['temp_folder'];
        } else {
            return null;
        }
    }

  /**
   * Get environment alias.
   */
    public function remoteEnvironmentAlias()
    {
        if (isset($this->configuration['remote']['alias'])) {
            return $this->configuration['remote']['alias'];
        } else {
            return null;
        }
    }

  /**
   * Get upload target dir.
   */
    public function remoteEnvironmentTargetDir()
    {
        if (isset($this->configuration['remote']['target_dir'])) {
            return $this->configuration['remote']['target_dir'];
        } else {
            return null;
        }
    }

  /**
   * Get command, script to run remote.
   */

    public function remoteEnvironmentRun()
    {
        if (isset($this->configuration['remote']['run'])) {
            return $this->configuration['remote']['run'];
        } else {
            return null;
        }
    }

  /**
   * Gets the drush alias.
   */
    public function siteEnvironmentDrushAlias()
    {
        if (isset($this->configuration['web']['drush_alias'])) {
            return $this->configuration['web']['drush_alias'];
        } else {
            return null;
        }
    }

  /**
   * Gets site install drush extra options.
   */
    public function siteEnvironmentDrushInstallOptions()
    {
        if (isset($this->configuration['web']['drush_install_options'])) {
            return $this->configuration['web']['drush_install_options'];
        } else {
            return null;
        }
    }



  /**
   * Gets the sites backup path.
   */
    public function siteEnvironmentBackupPath()
    {
        if (isset($this->configuration['web']['backup_path'])) {
            return $this->configuration['web']['backup_path'];
        } else {
            return null;
        }
    }

  /**
   * Gets the sites backup path.
   */
    public function siteEnvironmentConfigName()
    {
        if (isset($this->configuration['web']['config_name'])) {
            return $this->configuration['web']['config_name'];
        } else {
            return null;
        }
    }

  /**
   * Gets the sites backup path.
   */
    public function siteEnvironmentOriginalPath()
    {
        if (isset($this->configuration['web']['original_path'])) {
            return $this->configuration['web']['original_path'];
        } else {
            return null;
        }
    }

  /**
   * Gets the sites backup path.
   */
    public function siteEnvironmentSymLink()
    {
        if (isset($this->configuration['web']['symlink'])) {
            return $this->configuration['web']['symlink'];
        } else {
            return null;
        }
    }

  /**
   * Gets the sites backup path.
   */
    public function siteEnvironmentUrl()
    {
        if (isset($this->configuration['web']['url'])) {
            return $this->configuration['web']['url'];
        } else {
            return null;
        }
    }

  /**
   * Gets the sites name.
   */
    public function siteEnvironmentName()
    {
        if (isset($this->configuration['web']['name'])) {
            return $this->configuration['web']['name'];
        } else {
            return null;
        }
    }


  /**
   * Get the profile name
   */
    public function siteEnvironmentProfile()
    {
        if (isset($this->configuration['web']['profile'])) {
            return $this->configuration['web']['profile'];
        } else {
            return null;
        }
    }


  /**
   * Gets all ignore-files from config-file.
   */
    public function deployIgnoreFiles()
    {
        if (isset($this->configuration['deploy']['ignore'])) {
            return $this->configuration['deploy']['ignore'];
        } else {
            return null;
        }
    }

  /**
   * Gets varnish IP from config-file.
   */
    public function deployVarnishIP()
    {
        if (isset($this->configuration['deploy']['varnish_ip'])) {
            return $this->configuration['deploy']['varnish_ip'];
        } else {
            return null;
        }
    }

  /**
   * Gets varnish port from config-file.
   */
    public function deployVarnishPort()
    {
        if (isset($this->configuration['deploy']['varnish_port'])) {
            return $this->configuration['deploy']['varnish_port'];
        } else {
            return null;
        }
    }

  /**
   * Gets Mysql/MariaDB host
   */
    public function mysqlEnvironmentHost()
    {
        if (isset($this->configuration['db']['host'])) {
            return $this->configuration['db']['host'];
        } else {
            return null;
        }
    }

  /**
   * Gets Mysql database
   */
    public function mysqlEnvironmentDataBase()
    {
        if (isset($this->configuration['db']['database'])) {
            return $this->configuration['db']['database'];
        } else {
            return null;
        }
    }

  /**
   * Gets Mysql user
   */
    public function mysqlEnvironmentUser()
    {
        if (isset($this->configuration['db']['user'])) {
            return $this->configuration['db']['user'];
        } else {
            return null;
        }
    }

  /**
   * Gets Mysql port
   */
    public function mysqlEnvironmentPort()
    {
        if (isset($this->configuration['db']['port'])) {
            return $this->configuration['db']['port'];
        } else {
            return null;
        }
    }

  /**
   * Gets Mysql port
   */
    public function mysqlEnvironmentPassword()
    {
        if (isset($this->configuration['db']['password'])) {
            return $this->configuration['db']['password'];
        } else {
            return null;
        }
    }

  /**
   * Gets Jenkins server
   */
    public function deployJenkinsServer()
    {
        if (isset($this->configuration['deploy']['jenkins_server'])) {
            return $this->configuration['deploy']['jenkins_server'];
        } else {
            return null;
        }
    }

  /**
   * Gets Jenkins job
   */
    public function deployJenkinsJob()
    {
        if (isset($this->configuration['deploy']['jenkins_job'])) {
            return $this->configuration['deploy']['jenkins_job'];
        } else {
            return null;
        }
    }


  /**
   * Get admin pass for site.
   */
    public function siteEnvironmentAdminPass()
    {
        if (isset($this->configuration['web']['admin_pass'])) {
            return $this->configuration['web']['admin_pass'];
        } else {
            $password = mt_rand();
            return $password;
        }
    }

  /**
   * Get admin pass for site.
   */
    public function siteEnvironmentAdminUser()
    {
        if (isset($this->configuration['web']['admin_user'])) {
            return $this->configuration['web']['admin_user'];
        } else {
            return 'admin';
        }
    }

    public function nodeNvmDirectory()
    {
        if (isset($this->configuration['build']['node']['nvm_directory'])) {
            return $this->configuration['build']['node']['nvm_directory'];
        } else {
            return null;
        }
    }

    public function nodeNvmRcFile()
    {
        if (isset($this->configuration['build']['node']['nvmrc_file'])) {
            return $this->configuration['build']['node']['nvmrc_file'];
        } else {
            return null;
        }
    }

    public function gulpDirectory()
    {
        if (isset($this->configuration['build']['node']['gulp_directory'])) {
            return $this->configuration['build']['node']['gulp_directory'];
        } else {
            return null;
        }
    }
    public function gulpOptions()
    {
        if (isset($this->configuration['build']['node']['gulp_options'])) {
            return $this->configuration['build']['node']['gulp_options'];
        } else {
            return '';
        }
    }
    public function nodeEnvironment()
    {
        if (isset($this->configuration['build']['node'])) {
            $nodeEnvironment = $this->configuration['build']['node'];
            return $nodeEnvironment;
        } else {
            return null;
        }
    }
    public function localEnvironmentRsyncFrom()
    {
        if (isset($this->configuration['local']['rsync_from'])) {
            $from = $this->configuration['local']['rsync_from'];
            return $from;
        } else {
            return null;
        }
    }
    public function remoteEnvironmentRsyncTo()
    {
        if (isset($this->configuration['remote']['rsync_to'])) {
            $from = $this->configuration['remote']['rsync_to'];
            return $from;
        } else {
            return null;
        }
    }
  /**
   * Get ssh port for local use.
   */
    public function remoteEnvironmentLocalSshPort()
    {
        if (isset($this->configuration['remote']['local_ssh_port'])) {
            return $this->configuration['remote']['local_ssh_port'];
        } else {
            return null;
        }
    }
  /**
   * Get server for local use.
   */
    public function remoteEnvironmentLocalServerName()
    {
        if (isset($this->configuration['remote']['local_server'])) {
            return $this->configuration['remote']['local_server'];
        } else {
            return null;
        }
    }
  /**
   * Get ssh user for local use.
   */
    public function remoteEnvironmentLocalSshUser()
    {
        if (isset($this->configuration['remote']['local_ssh_user'])) {
            return $this->configuration['remote']['local_ssh_user'];
        } else {
            return null;
        }
    }
    public function remoteEnvironmentRsyncFrom()
    {
        if (isset($this->configuration['remote']['rsync_from'])) {
            $from = $this->configuration['remote']['rsync_from'];
            return $from;
        } else {
            return null;
        }
    }
    public function localEnvironmentRsyncTo()
    {
        if (isset($this->configuration['local']['rsync_to'])) {
            $from = $this->configuration['local']['rsync_to'];
            return $from;
        } else {
            return null;
        }
    }
    public function vhostFileName()
    {
        if (isset($this->configuration['web']['vhost']['file_name'])) {
            $file_name = $this->configuration['web']['vhost']['file_name'];
            return $file_name;
        } else {
            return null;
        }
    }
    public function vhostTarget()
    {
        if (isset($this->configuration['web']['vhost']['target'])) {
            $target = $this->configuration['web']['vhost']['target'];
            return $target;
        } else {
            return null;
        }
    }
    public function vhostPort()
    {
        if (isset($this->configuration['web']['vhost']['port'])) {
            $port = $this->configuration['web']['vhost']['port'];
            return $port;
        } else {
            return '80';
        }
    }
    public function vhostDocumentRoot()
    {
        if (isset($this->configuration['web']['vhost']['document_root'])) {
            $port = $this->configuration['web']['vhost']['document_root'];
            return $port;
        } else {
            return null;
        }
    }
    public function vhostServerName()
    {
        if (isset($this->configuration['web']['vhost']['server_name'])) {
            $server_name = $this->configuration['web']['vhost']['server_name'];
            return $server_name;
        } else {
            return null;
        }
    }

  /**
   * Return extra config for vhost.
   */
    public function vhostBashCommand()
    {
        if (isset($this->configuration['web']['vhost']['bash_command'])) {
            $bash_command = $this->configuration['web']['vhost']['bash_command'];
            return $bash_command;
        } else {
            return null;
        }
    }


  /**
   * Gets all server-aliases.
   */
    public function getServerAliases()
    {
        if (isset($this->configuration['web']['vhost']['server_alias'])) {
            return $this->configuration['web']['vhost']['server_alias'];
        } else {
            return null;
        }
    }
  /**
   * Gets all server extras.
   */
    public function getServerExtras()
    {
        if (isset($this->configuration['web']['vhost']['extra'])) {
            return $this->configuration['web']['vhost']['extra'];
        } else {
            return null;
        }
    }


    /**
     * Get drush php memory
     */
    public function remoteEnvironmentDrushMemoryLimit()
    {
        if (isset($this->configuration['remote']['environment']['drush']['limit'])) {
            return '"-d memory_limit=' . $this->configuration['remote']['environment']['drush']['limit'] . '"';
        } else {
            return '-d memory_limit=1024M';
        }
    }
}
