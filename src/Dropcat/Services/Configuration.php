<?php
namespace Dropcat\Services;

use Symfony\Component\Yaml\Parser;

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
        $config_parser = new Parser();
        $running_path = getcwd();
        $configuration_file_content = file_get_contents($running_path . '/dropcat.yml');
        $this->configuration = $config_parser->parse($configuration_file_content);
    }

    /**
     * Gets the absolute path of the actual app we want to deploy.
     */
    public function localEnvironmentAppPath()
    {
        return $this->configuration['local']['environment']['app_path'];
    }

    /**
     * Gets the app name.
     */
    public function localEnvironmentAppName()
    {
        return $this->configuration['app_name'];
    }

    /**
     * Get build id, prefderable overriden with option.
     */
    public function localEnvironmentBuildId()
    {
        return $this->configuration['local']['environment']['build_id'];
    }

    /**
     * Gets the absolute path of a tmp-folder in this environment.
     */
    public function localEnvironmentTmpPath()
    {
        return $this->configuration['local']['environment']['tmp_path'];
    }

    /**
     * Gets the seperator in names.
     */
    public function localEnvironmentSeperator()
    {
        return $this->configuration['local']['environment']['seperator'];
    }

    /**
     * Gets the seperator in names.
     */
    public function localEnvironmentDbImport()
    {
        return $this->configuration['local']['environment']['db_import'];
    }

    /**
     * Get name of tar to deploy.
     */
    public function localEnvironmentTarName()
    {
        return $this->configuration['local']['environment']['tar_name'];
    }
    /**
     * Get name of tar to deploy.
     */
    public function localEnvironmentSshKeyPassword()
    {
        return $this->configuration['local']['environment']['ssh_key_password'];
    }

    /**
     * Get remote server name.
     */
    public function remoteEnvironmentServerName()
    {
        return $this->configuration['remote']['environment']['server'];
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentSshUser()
    {
        return $this->configuration['remote']['environment']['ssh_user'];
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentTargetPath()
    {
        return $this->configuration['remote']['environment']['target_path'];
    }

    /**
     * Get ssh user.
     */
    public function remoteEnvironmentSshPort()
    {
        return $this->configuration['remote']['environment']['ssh_port'];
    }

    /**
     * Get ssh pub key.
     */
    public function remoteEnvironmentIdentifyFile()
    {
        return $this->configuration['remote']['environment']['identity_file'];
    }

    /**
     * Get ssh web root.
     */
    public function remoteEnvironmentWebRoot()
    {
        return $this->configuration['remote']['environment']['web_root'];
    }

    /**
     * Get remote temp folder.
     */
    public function remoteEnvironmentTempFolder()
    {
        return $this->configuration['remote']['environment']['temp_folder'];
    }

    /**
     * Get environment alias.
     */
    public function remoteEnvironmentAlias()
    {
        return $this->configuration['remote']['environment']['alias'];
    }

    /**
     * Get upload target dir.
     */
    public function remoteEnvironmentTargetDir()
    {
        return $this->configuration['remote']['environment']['target_dir'];
    }

    /**
     * Gets the drush alias.
     */
    public function siteEnvironmentDrushAlias()
    {
        return $this->configuration['site']['environment']['drush_alias'];
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentBackupPath()
    {
        return $this->configuration['site']['environment']['backup_path'];
    }

    /**
     * Gets the sites backup path.
     */
    public function siteEnvironmentConfigName()
    {
        return $this->configuration['site']['environment']['config_name'];
    }

    /**
     * Gets the sites backup path.
     */
    public function timeStamp()
    {
        $timestamp = date("Ymd_His");
        return $timestamp;
    }

    /**
     * Gets the sites backup path.
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
        return $this->configuration['deploy']['ignore'];
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
