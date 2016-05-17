<?php
namespace Dropcat\Services;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Dropcat\Lib\UUID;

/**
 * Class AppConfiguration
 * @package Services
 *
 * Loads configuration file and return variable from matching method, + some
 * helper-methods for things to do with configuration-file.
 */
class Configuration
{
    /**
     * AppConfiguration constructor.
     */
    public function __construct()
    {
        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('DROPCAT_ENV') ?: 'dev');
        $running_path = getcwd();
        if (file_exists($running_path . '/dropcat.yml')) {
            $default_config = Yaml::parse(
                file_get_contents($running_path . '/dropcat.yml')
            );
            $configs = $default_config;
        }
        // Check for environment dropcat file.
        $environment = '/dropcat.' . $env . '.yml';
        if (file_exists($running_path . $environment)) {
            $env_config = Yaml::parse(
                file_get_contents($running_path . $environment)
            );
            // Recreate configs if env. exists.
            if (isset($default_config)) {
                $configs = array_replace_recursive($default_config, $env_config);
            } else {
                $configs = $env_config;
            }
        }

        if (isset($configs)) {
            // Settings some defaults so isset is not needed for root values.
            $this->configuration['local'] = null;
            $this->configuration['local']['environment'] = null;
            $this->configuration['remote'] = null;
            $this->configuration['remote']['environment'] = null;

            $this->configuration = $configs;
        } else {
            $this->configuration = null;
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
        if (isset($this->configuration['local']['environment']['app_path'])) {
            return $this->configuration['local']['environment']['app_path'];
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
        } elseif (isset($this->configuration['local']['environment']['build_id'])) {
            $buildId = $this->configuration['local']['environment']['build_id'];
        }
        return $buildId;
    }

    /**
     * Gets the absolute path of a tmp-folder in this environment.
     */
    public function localEnvironmentTmpPath()
    {
        if (isset($this->configuration['local']['environment']['tmp_path'])) {
            return $this->configuration['local']['environment']['tmp_path'];
        } else {
            return null;
        }
    }

    /**
     * Gets the separator in names.
     */
    public function localEnvironmentSeparator()
    {
        if (isset($this->configuration['local']['environment']['separator'])) {
            return $this->configuration['local']['environment']['separator'];
        } else {
            return null;
        }

    }

    /**
     * Gets the db to import.
     */
    public function localEnvironmentDbImport()
    {
        if (isset($this->configuration['local']['environment']['db_import'])) {
            return $this->configuration['local']['environment']['db_import'];
        } else {
            return null;
        }
    }

    /**
     * Get name of tar to deploy.
     */
    public function localEnvironmentTarName()
    {
        if (isset($this->configuration['local']['environment']['tar_name'])) {
            return $this->configuration['local']['environment']['tar_name'];
        } else {
            return null;
        }
    }

    /**
     * Get name of dir that tar is in.
     */
    public function localEnvironmentTarDir()
    {
        if (isset($this->configuration['local']['environment']['tar_dir'])) {
            return $this->configuration['local']['environment']['tar_dir'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh-key password
     */
    public function localEnvironmentSshKeyPassword()
    {
        if (isset($this->configuration['local']['environment']['ssh_key_password'])) {
            return $this->configuration['local']['environment']['ssh_key_password'];
        } else {
            return null;
        }
    }

    /**
     * Get path to drush folder.
     */
    public function localEnvironmentDrushFolder()
    {
        if (isset($this->configuration['local']['environment']['drush_folder'])) {
            return $this->configuration['local']['environment']['drush_folder'];
        } else {
            return null;
        }
    }

    /**
     * Get path to drush folder.
     */
    public function localEnvironmentRun()
    {
        if (isset($this->configuration['local']['environment']['run'])) {
            return $this->configuration['local']['environment']['run'];
        } else {
            return null;
        }
    }
    

    /**
     * Get remote server name.
     */
    public function remoteEnvironmentServerName()
    {
        if (isset($this->configuration['remote']['environment']['server'])) {
            return $this->configuration['remote']['environment']['server'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentSshUser()
    {
        if (isset($this->configuration['remote']['environment']['ssh_user'])) {
            return $this->configuration['remote']['environment']['ssh_user'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentTargetPath()
    {
        if (isset($this->configuration['remote']['environment']['target_path'])) {
            return $this->configuration['remote']['environment']['target_path'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentSshPort()
    {
        if (isset($this->configuration['remote']['environment']['ssh_port'])) {
            return $this->configuration['remote']['environment']['ssh_port'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh pub key.
     */
    public function remoteEnvironmentIdentifyFile()
    {
        if (isset($this->configuration['remote']['environment']['identity_file'])) {
            return $this->configuration['remote']['environment']['identity_file'];
        } else {
            return null;
        }
    }

    /**
     * Get ssh web root.
     */
    public function remoteEnvironmentWebRoot()
    {
        if (isset($this->configuration['remote']['environment']['web_root'])) {
            return $this->configuration['remote']['environment']['web_root'];
        } else {
            return null;
        }
    }

    /**
     * Get remote temp folder.
     */
    public function remoteEnvironmentTempFolder()
    {
        if (isset($this->configuration['remote']['environment']['temp_folder'])) {
            return $this->configuration['remote']['environment']['temp_folder'];
        } else {
            return null;
        }
    }

    /**
     * Get environment alias.
     */
    public function remoteEnvironmentAlias()
    {
        if (isset($this->configuration['remote']['environment']['alias'])) {
            return $this->configuration['remote']['environment']['alias'];
        } else {
            return null;
        }
    }

    /**
     * Get upload target dir.
     */
    public function remoteEnvironmentTargetDir()
    {
        if (isset($this->configuration['remote']['environment']['target_dir'])) {
            return $this->configuration['remote']['environment']['target_dir'];
        } else {
            return null;
        }
    }

    /**
     * Gets the drush alias.
     */
    public function siteEnvironmentDrushAlias()
    {
        if (isset($this->configuration['site']['environment']['drush_alias'])) {
            return $this->configuration['site']['environment']['drush_alias'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentBackupPath()
    {
        if (isset($this->configuration['site']['environment']['backup_path'])) {
            return $this->configuration['site']['environment']['backup_path'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentConfigName()
    {
        if (isset($this->configuration['site']['environment']['config_name'])) {
            return $this->configuration['site']['environment']['config_name'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentOriginalPath()
    {
        if (isset($this->configuration['site']['environment']['original_path'])) {
            return $this->configuration['site']['environment']['original_path'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentSymLink()
    {
        if (isset($this->configuration['site']['environment']['symlink'])) {
            return $this->configuration['site']['environment']['symlink'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentUrl()
    {
        if (isset($this->configuration['site']['environment']['url'])) {
            return $this->configuration['site']['environment']['url'];
        } else {
            return null;
        }
    }

    /**
     * Gets the sites name.
     */
    public function siteEnvironmentName()
    {
        if (isset($this->configuration['site']['environment']['name'])) {
            return $this->configuration['site']['environment']['name'];
        } else {
            return null;
        }
    }


    /**
     * Get the profile name
     */
    public function siteEnvironmentProfile()
    {
        if (isset($this->configuration['site']['environment']['profile'])) {
            return $this->configuration['site']['environment']['profile'];
        } else {
            return null;
        }
    }

    /**
     * Set a timestamp.
     */
    public function timeStamp()
    {
        $timestamp = date("Ymd_His");
        return $timestamp;
    }

    /**
     * Sets timeout for processes.
     */
    public function timeOut()
    {
        return '3600';
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
     * Gets Mysql/MariaDB host
     */
    public function mysqlEnvironmentHost()
    {
        if (isset($this->configuration['mysql']['environment']['host'])) {
            return $this->configuration['mysql']['environment']['host'];
        } else {
            return null;
        }
    }

    /**
     * Gets Mysql database
     */
    public function mysqlEnvironmentDataBase()
    {
        if (isset($this->configuration['mysql']['environment']['database'])) {
            return $this->configuration['mysql']['environment']['database'];
        } else {
            return null;
        }
    }

    /**
     * Gets Mysql user
     */
    public function mysqlEnvironmentUser()
    {
        if (isset($this->configuration['mysql']['environment']['user'])) {
            return $this->configuration['mysql']['environment']['user'];
        } else {
            return null;
        }
    }

    /**
     * Gets Mysql port
     */
    public function mysqlEnvironmentPort()
    {
        if (isset($this->configuration['mysql']['environment']['port'])) {
            return $this->configuration['mysql']['environment']['port'];
        } else {
            return null;
        }
    }

    /**
     * Gets Mysql port
     */
    public function mysqlEnvironmentPassword()
    {
        if (isset($this->configuration['mysql']['environment']['password'])) {
            return $this->configuration['mysql']['environment']['password'];
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
        if (isset($this->configuration['site']['environment']['admin_pass'])) {
            return $this->configuration['site']['environment']['admin_pass'];
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
        if (isset($this->configuration['site']['environment']['admin_user'])) {
            return $this->configuration['site']['environment']['admin_user'];
        } else {
            return 'admin';
        }
    }

    /**
     * Gets all ignore-files formatted for tar-excluding.
     */
    public function deployIgnoreFilesTarString()
    {
        $ignore_files_array = $this->deployIgnoreFiles();
        $ignore_files = null;
        foreach ($ignore_files_array as $ignore_file) {
            $ignore_files .= "--exclude='$ignore_file' ";
        }
        $ignore_files = rtrim($ignore_files);
        return $ignore_files;
    }
}
