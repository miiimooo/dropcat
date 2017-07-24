<?php

namespace Dropcat\Services;

abstract class DropcatConfigurationBase
{

  protected $configuration;

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
   * Return server-aliases.
   */
  public function vhostServerAlias()
  {
    $server_aliases_array = $this->getServerAliases();
    $server_aliases = null;
    if (isset($server_aliases_array)) {
      foreach ($server_aliases_array as $server_alias) {
        $server_aliases .= "  ServerAlias $server_alias\n";
      }
      return $server_aliases;
    } else {
      return null;
    }
  }

  /**
   * Return extra config for vhost.
   */
  public function vhostExtra()
  {
    $server_extra_array = $this->getServerExtras();
    $server_extras = null;
    if (isset($server_extra_array)) {
      foreach ($server_extra_array as $server_extra) {
        $server_extras .= "  $server_extra\n";
      }
      return $server_extras;
    } else {
      return null;
    }
  }

  public abstract function deployIgnorefiles();

  public abstract function getServerAliases();

  public abstract function getServerExtras();

}
