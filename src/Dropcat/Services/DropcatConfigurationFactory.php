<?php

namespace Dropcat\Services;

class DropcatConfigurationFactory {
  public static function createConfigurationService() {
    $running_path = getcwd();
    if (file_exists($running_path . '/dropcat_unified.yml')) {
      return new UnifiedConfiguration();
    }
    return new Configuration();
  }
}
