<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Dropcat\Lib\UUID;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Style\SymfonyStyle;
use SplFileObject;
use Exception;

class InitCommand extends DropcatCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>deploy</info> connects to remote server and upload tar and unpack it in path.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat deployt</info>
To override config in dropcat.yml, using options:
<info>dropcat deploy -server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("init")
            ->setDescription("Init D8 site")
            ->setDefinition(
                array(
                    new InputOption(
                        'profile',
                        'p',
                        InputOption::VALUE_REQUIRED,
                        'Profile name',
                        null
                    ),
                    new InputOption(
                        'theme',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Theme name',
                        null
                    ),
                )
            )
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $my_profile = $input->getOption('profile');
        $my_theme = $input->getOption('theme');

        // Check profile name for illegal chars.
        if (!isset($my_profile)) {
            throw new Exception('You need to specify a profile name.');
        }
        if (preg_match('/\s/', $my_profile)) {
            throw new Exception('Profile name can not have spaces.');
        }
        if (!preg_match('/^[a-z]+$/', $my_profile)) {
            throw new Exception('Profiles must use a-z i names.');
        }

        // Check theme name for illegal chars.
        if (!isset($my_theme)) {
            throw new Exception('You need to specify a theme name.');
        }
        if (preg_match('/\s/', $my_theme)) {
            throw new Exception('Theme name can not have spaces.');
        }
        if (!preg_match('/^[a-z]+$/', $my_theme)) {
            throw new Exception('Themes must use a-z i names.');
        }

        $io = new SymfonyStyle($input, $output);

        $io->confirm('This will add files for setting up a drupal site in current folder, continue?', true);

        // (startdir is needed for application)
        $process = new Process("git clone -b WOPS-108 git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git web_init");

        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $io->note('Wk Drupal Template cloned to web_init/web');

        // Rename profile and theme-files and placeholder-strings.
        $this->renameProfileFiles($my_profile);
        $this->renameThemeFiles($my_profile, $my_theme);

        $io->note('Renaming of functions and files finished');

        $process = new Process("mv web_init/* . && rm -rf web_init");
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $io->note('Move web folder in place, removed web_init folder');

        $io->newLine(2);
        $io->success('Site is setup');

    }

    /**
     * Renames the profile-files and strings within some of the depending files with the supplied profile-name.
     *
     * @param $my_profile
     *      The new profile name.
     */
    private function renameProfileFiles($my_profile)
    {
        // Rename files and functions
        $fs = new Filesystem();

        // Rename profile to project name
        $fs->rename('web_init/web/profiles/wkstandard', 'web_init/web/profiles/' . $my_profile);

        // Rename files to project-name
        $fs->rename(
            'web_init/web/profiles/' . $my_profile . '/wkstandard.profile',
            'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.profile'
        );
        $fs->rename(
            'web_init/web/profiles/' . $my_profile . '/wkstandard.install',
            'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install'
        );
        $fs->rename(
            'web_init/web/profiles/' . $my_profile . '/wkstandard.info.yml',
            'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.info.yml'
        );

        // Replace in profile composer.json
        // Create uuid for profile
        $uuid = UUID::v4();
        // Replace what is needed
        $read = new SplFileObject('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install', 'r');
        $content = $read->fread($read->getSize());
        $content = str_replace("wkstandard_install", "$my_profile" . "_install", $content);
        $content = str_replace(
            "Install, update and uninstall functions for the wkstandard installation profile.",
            "Install, update and uninstall functions for $my_profile installation profile.",
            $content
        );
        $content = str_replace(
            "web/profiles/wkstandard/",
            "web/profiles/$my_profile/",
            $content
        );
        $content = str_replace(
            "('system.site')->set('uuid', 'a0bb6f1c-dda6-477b-938a-4f0219775c28')->save(TRUE);",
            "('system.site')->set('uuid', '" . $uuid . "')->save(TRUE);",
            $content
        );
        $write = new SplFileObject($read->getPathname(), 'w+');
        $write->fwrite($content);

        // Replace in profile info file
        $read = new SplFileObject('web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.info.yml', 'r');
        $content = $read->fread($read->getSize());
        $content = str_replace("wkstandard", "$my_profile", $content);
        $content = str_replace("WK-standard", "$my_profile", $content);
        $write = new SplFileObject($read->getPathname(), 'w+');
        $write->fwrite($content);

        // Replace in root composer.json
        $read = new SplFileObject('web_init/composer.json', 'r');
        $content = $read->fread($read->getSize());
        $content = str_replace("web/profiles/wkstandard/", "web/profiles/$my_profile/", $content);
        $write = new SplFileObject($read->getPathname(), 'w+');
        $write->fwrite($content);
    }

    /**
     * Renames the theme-files and strings within some of the depending files with the supplied theme-name.
     *
     * @param $my_profile
     *      The new profile name.
     *
     * @param $my_theme
     *      The new theme name.
     */
    private function renameThemeFiles($my_profile, $my_theme)
    {
        $fs = new Filesystem();

        // Rename theme to theme name
        $fs->rename('web_init/web/profiles/'. $my_profile .'/themes/custom/wktheme', 'web_init/web/profiles/'. $my_profile .'/themes/custom/'. $my_theme);

        // Rename theme info file to theme name.
        $fs->rename(
            'web_init/web/profiles/' . $my_profile . '/themes/custom/'. $my_theme .'/wktheme.info.yml',
            'web_init/web/profiles/' . $my_profile . '/themes/custom/'.  $my_theme .'/' . $my_theme . '.info.yml'
        );

        // Rename theme libraries file to theme name.
        $fs->rename(
            'web_init/web/profiles/' . $my_profile . '/themes/custom/'. $my_theme .'/wktheme.libraries.yml',
            'web_init/web/profiles/' . $my_profile . '/themes/custom/'. $my_theme .'/' . $my_theme . '.libraries.yml'
        );

        // Replace wktheme with provided theme-name in theme info file.
        $theme_info_file = new SplFileObject('web_init/web/profiles/'. $my_profile .'/themes/custom/'. $my_theme .'/'. $my_theme .'.info.yml');
        $theme_info_file_content = $theme_info_file->fread($theme_info_file->getSize());
        $theme_info_file_content = str_replace("WK Theme", $my_theme, $theme_info_file_content);
        $theme_info_file_write = new SplFileObject($theme_info_file->getPathname(), 'w+');
        $theme_info_file_write->fwrite($theme_info_file_content);

        // Replace default theme name with provided theme-name in config-file.
        $config_theme_file = new SplFileObject('web_init/web/profiles/'. $my_profile .'/config/install/system.theme.yml');
        $config_theme_file_content = $config_theme_file->fread($config_theme_file->getSize());
        $config_theme_file_content = str_replace("wktheme", $my_theme, $config_theme_file_content);
        $config_theme_file_write = new SplFileObject($config_theme_file->getPathname(), 'w+');
        $config_theme_file_write->fwrite($config_theme_file_content);
    }

}
