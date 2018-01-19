<?php

namespace Dropcat\Services;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Yaml\Yaml;

class SimpleConfiguration extends DropcatConfigurationBase implements DropcatConfigurationInterface
{

    protected $env;

    /**
     * AppConfiguration constructor.
     */
    public function __construct()
    {
        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('DROPCAT_ENV') ?: 'dev');
        $running_path = getcwd();
        if (file_exists($running_path . '/.dropcat') && is_dir($running_path . '/.dropcat')) {
            $running_path .= '/.dropcat';
        }
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

    public function localEnvironmentAppName()
    {
        return isset($this->configuration['app-name'])?$this->configuration['app-name']:null;
    }

    /**
     * @deprecated
     */
    public function localEnvironmentAppPath()
    {
        return isset($this->configuration['local']['app-path'])?$this->configuration['local']['app-path']:null;
    }


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
        } elseif (isset($this->configuration['local']['build-id'])) {
            $buildId = $this->configuration['local']['build-id'];
        }
        return $buildId;
    }


    public function localEnvironmentTmpPath()
    {
        return isset($this->configuration['local']['tmp-path'])?$this->configuration['local']['tmp-path']:'/tmp';
    }

    public function localEnvironmentSeparator()
    {
        return isset($this->configuration['local']['seperator'])?$this->configuration['local']['seperator']:'_';
    }

    public function localEnvironmentDbImport()
    {
        return isset($this->configuration['local']['db-import'])?$this->configuration['local']['db-import']:null;
    }

    public function localEnvironmentTarName()
    {
        return isset($this->configuration['local']['tar-name'])?$this->configuration['local']['tar-name']:null;
    }

    public function localEnvironmentTarDir()
    {
        return isset($this->configuration['local']['tar-dir'])?$this->configuration['local']['tar-dir']:'/tmp';
    }


    public function localEnvironmentSshKeyPassword()
    {
        return isset($this->configuration['web']['ssh-key-password'])?$this->configuration['web']['ssh-key-password']:null;
    }

    public function localEnvironmentDrushFolder()
    {

        return isset($this->configuration['local']['drush-folder'])?$this->configuration['local']['drush-folder']:null;
    }

    public function localEnvironmentRun()
    {

            return isset($this->configuration['local']['run'])?$this->configuration['local']['run']:null;
    }

    public function localEnvironmentBackupServer()
    {
        return isset($this->configuration['backup']['server'])?$this->configuration['backup']['server']:null;
    }

    public function localEnvironmentBackupServerPort()
    {
        return isset($this->configuration['backup']['port'])?$this->configuration['backup']['port']:'22';
    }

    public function localEnvironmentBackupServerUser()
    {
        return isset($this->configuration['backup']['user'])?$this->configuration['backup']['user']:null;
    }

    public function localEnvironmentBackupPath()
    {
        return isset($this->configuration['backup']['path'])?$this->configuration['backup']['path']:null;
    }

    public function localEnvironmentBackupDbName()
    {
        return isset($this->configuration['backup']['db'])?$this->configuration['backup']['path']:'db.sql.gz';
    }
    public function localEnvironmentBackupFiles()
    {
        return isset($this->configuration['backup']['files'])?$this->configuration['backup']['files']:'files.tar.gz';
    }

    public function remoteEnvironmentServerName()
    {
        return isset($this->configuration['web']['server'])?$this->configuration['web']['server']:null;
    }

    public function remoteEnvironmentSshUser()
    {
        return isset($this->configuration['web']['user'])?$this->configuration['web']['user']:null;
    }

    /**
     * @derecated
     */
    public function remoteEnvironmentTargetPath()
    {
        return isset($this->configuration['web']['target-path'])?$this->configuration['web']['target-path']:null;
    }

    public function remoteEnvironmentSshPort()
    {
        return isset($this->configuration['web']['port'])?$this->configuration['web']['port']:'22';
    }

    public function remoteEnvironmentIdentifyFile()
    {
        return isset($this->configuration['web']['key'])?$this->configuration['web']['key']:'22';
    }

    public function remoteEnvironmentWebRoot()
    {
        return isset($this->configuration['web']['dir'])?$this->configuration['web']['dir']:'/var/www/webroot';
    }

    public function remoteEnvironmentTempFolder()
    {
        return isset($this->configuration['web']['tmp'])?$this->configuration['web']['tmp']:'/tmp';
    }

    public function remoteEnvironmentAlias()
    {
        return isset($this->configuration['web']['alias'])?$this->configuration['web']['alias']:null;
    }

    public function remoteEnvironmentTargetDir()
    {
        return isset($this->configuration['web']['upload-dir'])?$this->configuration['web']['upload-dir']:'/tmp';
    }


    public function remoteEnvironmentRun()
    {
        return isset($this->configuration['site']['run'])?$this->configuration['web']['run']:null;
    }

    public function siteEnvironmentDrushAlias()
    {
        return $this->siteName();
    }

    public function siteEnvironmentDrushInstallOptions()
    {
        return isset($this->configuration['site']['drush-install-options'])?$this->configuration['site']['drush-install-options']:null;
    }

    public function siteEnvironmentBackupPath()
    {
        return isset($this->configuration['local']['backup'])?$this->configuration['local']['backup']:'/backup';
    }

    public function siteEnvironmentConfigName()
    {
        return isset($this->configuration['site']['config-name'])?$this->configuration['site']['config-name']:'sync';
    }

    public function siteEnvironmentOriginalPath()
    {
        return $this->webOriginalPath();
    }

    public function siteEnvironmentSymLink()
    {
        return $this->webSymlinkPath();
    }

    public function siteEnvironmentUrl()
    {
        return isset($this->configuration['site']['url'])?$this->configuration['site']['url']:null;
    }

    public function siteEnvironmentName()
    {
        return $this->siteName();
    }

    public function siteEnvironmentProfile()
    {
        return isset($this->configuration['site']['profile'])?$this->configuration['site']['profile']:null;
    }

    public function siteEnvironmentBackupDbPath()
    {
        return $this->backupPath();
    }

    public function deployIgnoreFiles()
    {
        return isset($this->configuration['ignore'])?$this->configuration['ignore']:null;
    }

    public function deployVarnishIP()
    {
        return isset($this->configuration['varnish']['ip'])?$this->configuration['varnish']['ip']:null;
    }

    public function deployVarnishPort()
    {
        return isset($this->configuration['varnish']['port'])?$this->configuration['varnish']['port']:null;
    }

    public function mysqlEnvironmentHost()
    {
        return isset($this->configuration['mysql']['server'])?$this->configuration['mysql']['server']:null;
    }

    public function mysqlEnvironmentDataBase()
    {
        return isset($this->configuration['mysql']['db'])?$this->configuration['mysql']['db']:null;
    }

    public function mysqlEnvironmentUser()
    {
        return isset($this->configuration['mysql']['user'])?$this->configuration['mysql']['user']:null;
    }

    public function mysqlEnvironmentPort()
    {
        return isset($this->configuration['mysql']['port'])?$this->configuration['mysql']['port']:'3306';
    }

    public function mysqlEnvironmentPassword()
    {
        return isset($this->configuration['mysql']['pass'])?$this->configuration['mysql']['pass']:null;
    }

    /**
     * @deprecated
     */
    public function deployJenkinsServer()
    {
        return null;
    }

    /**
     * @deprecated
     */
    public function deployJenkinsJob()
    {
        return null;
    }

    public function siteEnvironmentAdminPass()
    {
        if (isset($this->configuration['site']['admin-pass'])) {
            return $this->configuration['site']['admin-pass'];
        } else {
            $password = mt_rand();
            return $password;
        }
    }

    public function siteEnvironmentAdminUser()
    {
        return isset($this->configuration['site']['admin-user'])?$this->configuration['site']['admin-user']:null;
    }

    public function remoteEnvironmentLocalSshPort()
    {
        return isset($this->configuration['site']['admin-user'])?$this->configuration['site']['admin-user']:null;
    }

    public function nodeNvmDirectory()
    {
        return isset($this->configuration['node']['nvm-directory'])?$this->configuration['node']['nvm-directory']:null;
    }
    public function nodeNvmRcFile()
    {
        return isset($this->configuration['node']['nvmrc-file'])?$this->configuration['node']['nvmrc-file']:null;
    }

    public function gulpDirectory()
    {
        return isset($this->configuration['node']['gulp-dir'])?$this->configuration['node']['gulp-dir']:null;
    }
    public function gulpOptions()
    {
        return isset($this->configuration['node']['gulp-options'])?$this->configuration['node']['gulp-options']:'';
    }

    public function nodeEnvironment()
    {
        return isset($this->configuration['node']['environment'])?$this->configuration['node']['environment']:null;
    }

    public function localEnvironmentRsyncFrom()
    {
        return isset($this->configuration['local']['rsync-from'])?$this->configuration['local']['rsync-from']:null;
    }

    public function remoteEnvironmentRsyncTo()
    {
        return isset($this->configuration['web']['rsync-to'])?$this->configuration['web']['rsync-to']:null;
    }

    public function remoteEnvironmentRsyncFrom()
    {
        return isset($this->configuration['web']['rsync-from'])?$this->configuration['web']['rsync-from']:null;
    }

    public function localEnvironmentRsyncTo()
    {
        return isset($this->configuration['local']['rsync-to'])?$this->configuration['local']['rsync-to']:null;
    }

    public function remoteEnvironmentLocalServerName()
    {
        return isset($this->configuration['local']['server'])?$this->configuration['local']['server']:null;
    }

    public function remoteEnvironmentLocalSshUser()
    {
        return isset($this->configuration['web']['local-ssh-port'])?$this->configuration['web']['local-ssh-port']:null;
    }
    public function vhostFileName()
    {
        return isset($this->configuration['vhost']['name'])?$this->configuration['vhost']['name']:null;
    }

    public function vhostBashCommand()
    {
        return isset($this->configuration['vhost']['command'])?$this->configuration['vhost']['command']:null;
    }

    public function vhostTarget()
    {
        return isset($this->configuration['vhost']['target'])?$this->configuration['vhost']['target']:null;
    }

    public function vhostPort()
    {
        return isset($this->configuration['vhost']['port'])?$this->configuration['vhost']['port']:'80';
    }

    public function vhostDocumentRoot()
    {
        return isset($this->configuration['vhost']['document-root'])?$this->configuration['vhost']['document-root']:null;
    }

    public function vhostServerName()
    {
        return isset($this->configuration['vhost']['server-name'])?$this->configuration['vhost']['server-name']:null;
    }

    public function getServerAliases()
    {
        return isset($this->configuration['vhost']['server-aliases'])?$this->configuration['vhost']['server-aliases']:null;
    }

    public function getServerExtras()
    {
        return isset($this->configuration['vhost']['extra'])?$this->configuration['vhost']['extra']:null;
    }

    public function trackerDir()
    {
        return isset($this->configuration['tracker']['dir'])?$this->configuration['tracker']['dir']:null;
    }

    public function trackerDbDump()
    {
        return isset($this->configuration['tracker']['db-dump'])?$this->configuration['tracker']['db-dump']:null;
    }

    public function trackerDbUser()
    {
        return isset($this->configuration['tracker']['db-user'])?$this->configuration['tracker']['db-user']:null;
    }

    public function trackerDbPass()
    {
        return isset($this->configuration['tracker']['db-pass'])?$this->configuration['tracker']['db-pass']:null;
    }

    public function trackerDbName()
    {
        return isset($this->configuration['tracker']['db-name'])?$this->configuration['tracker']['db-name']:null;
    }

    public function trackerDbHost()
    {
        return isset($this->configuration['tracker']['db-host'])?$this->configuration['tracker']['db-host']:null;
    }

    public function trackerId()
    {
        return isset($this->configuration['tracker']['id'])?$this->configuration['tracker']['id']:null;
    }

    public function trackerSitePath()
    {
        return isset($this->configuration['tracker']['site-path'])?$this->configuration['tracker']['site-path']:null;
    }

    public function trackerFile()
    {
        return isset($this->configuration['tracker']['file'])?$this->configuration['tracker']['file']:null;
    }

    public function rollbackId()
    {
        return isset($this->configuration['rollback']['id'])?$this->configuration['rollback']['id']:null;
    }

    public function createSite()
    {
        return null;
    }

    public function syncFolder()
    {
        return isset($this->configuration['site']['sync'])?$this->configuration['site']['sync']:'../sync';
    }

    public function configSplitFolder()
    {
        return isset($this->configuration['site']['config-split'])?$this->configuration['site']['config-split']:null;
    }

    public function drupalInstallProfile()
    {
        return 'minimal';
    }

    public function siteSettings()
    {
        return isset($this->configuration['site']['settings'])?$this->configuration['site']['settings']:null;
    }

    public function siteConfig()
    {
        return isset($this->configuration['site']['config'])?$this->configuration['site']['config']:null;
    }

    public function webOriginalPath()
    {
        return isset($this->configuration['web']['original-path'])?$this->configuration['web']['original-path']:null;
    }
    public function webSymlinkPath()
    {
        return isset($this->configuration['web']['symlink-path'])?$this->configuration['web']['symlink-path']:null;
    }
    public function siteName()
    {
        return isset($this->configuration['site']['name'])?$this->configuration['site']['name']:null;
    }
    public function backupPath()
    {
        return isset($this->configuration['local']['backup'])?$this->configuration['local']['backup']:null;
    }
}
