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
class Configuration {

  /**
   * AppConfiguration constructor.
   */
  public function __construct() {
    $config_parser = new Parser();
    $running_path = getcwd();
    $configuration_file_content = file_get_contents($running_path .'/dropcat.yml');
    $this->configuration = $config_parser->parse($configuration_file_content);
  }

  /**
   * Gets the absolute path of the actual app we want to deploy.
   */
  public function localEnvironmentAppPath() {
    return $this->configuration['local']['environment']['app_path'];
  }

  /**
   * Gets the app name.
   */
  public function localEnvironmentAppName() {
    return $this->configuration['app_name'];
  }

  /**
   * Get build id, prefderable overriden with option.
   */
  public function localEnvironmentBuildId() {
    return $this->configuration['local']['environment']['build_id'];
  }

  /**
   * Gets the absolute path of a tmp-folder in this environment.
   */
  public function localEnvironmentTmpPath() {
    return $this->configuration['local']['environment']['tmp_path'];
  }

  /**
   * Gets the seperator in names.
   */
  public function localEnvironmentSeperator() {
    return $this->configuration['local']['environment']['seperator'];
  }

  /**
   * Gets the drush alias.
   */
  public function siteEnvironmentDrushAlias() {
    return $this->configuration['site']['environment']['drush_alias'];
  }

  /**
   * Gets the sites backup path.
   */
  public function siteEnvironmentBackupPath() {
    return $this->configuration['site']['environment']['backup_path'];
  }

  /**
   * Gets the sites backup path.
   */
  public function timeStamp() {
    $timestamp = date("Ymd_His");
    return $timestamp;
  }

  /**
   * Gets all ignore-files from config-file.
   */
  public function deployIgnoreFiles() {
    return $this->configuration['deploy']['ignore'];
  }

  /**
   * Gets all ignore-files formatted for tar-excluding.
   */
  public function deployIgnoreFilesTarString() {
    $ignore_files_array = $this->deployIgnoreFiles();
    $ignore_files = NULL;
    foreach($ignore_files_array as $ignore_file) {
      $ignore_files .= "--exclude='$ignore_file' ";
    }
    $ignore_files = rtrim($ignore_files);
    return $ignore_files;
  }
}
