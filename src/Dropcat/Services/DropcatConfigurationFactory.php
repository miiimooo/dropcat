<?php

namespace Dropcat\Services;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\ArgvInput;

class DropcatConfigurationFactory
{

    public static function createConfigurationService()
    {

        $running_path = getcwd();
        // figure out if the we should use the simple configuration.
        if (file_exists($running_path . '/.dropcat') && is_dir($running_path . '/.dropcat')) {
            $running_path .= '/.dropcat';
        }
        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('DROPCAT_ENV') ?: 'dev');

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
            $appname = array_search('app-name', $configs);
            if (isset($appname)) {
                return new SimpleConfiguration();
            }
        }


        if (file_exists($running_path . '/dropcat_unified.yml')) {
            return new UnifiedConfiguration();
        }
        return new Configuration();
    }
}
