<?php

namespace Dropcat\Services;

class DropcatConfigurationFactory
{

    public static function createConfigurationService()
    {
        $running_path = getcwd();
        if (file_exists($running_path . '/.dropcat') && is_dir($running_path . '/.dropcat')) {
            $running_path .= '/.dropcat';
        }
        if (file_exists($running_path . '/dropcat_unified.yml')) {
            return new UnifiedConfiguration();
        }
        return new Configuration();
    }
}
