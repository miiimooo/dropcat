<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class SettingsFileGeneratorCommand extends DropcatCommand
{
    /** @var  Filesystem */
    private $filesystem;

    public function __construct(
        ContainerBuilder $container,
        Configuration $conf
    ) {
        parent::__construct($container, $conf);
    }

    protected function configure()
    {
        $HelpText = '<info>Overrides settings.php settings.</info>';

        $this->setName("settingsfile-generator")
            ->setDescription("Overrides settings.php settings.")
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output            = new ConsoleOutput();
        $new_settings_file = $this->addCustomSettings();
        $output->writeln($new_settings_file);
    }

    /**
     * 1. Check if we have settings in yml
     * 2. check if we have settings in command line value
     * 3. parse the yml
     * 4. append to settings file ( should be in configs )
     */
    protected function addCustomSettings()
    {
        $custom_settings = $this->configuration->getCustomSettings();
        if ($custom_settings) {
            // injecta via containern senare
            $this->filesystem = new Filesystem();
            $local_settingsfile = $this->configuration->getCustomSettings();

            // Make local settings file writable.
            // $this->setLocalSettingsfilePermissions(0777);
            $this->filesystem->chmod($local_settingsfile, 0777);

            $local_settingsfile_contents = $this->getLocalSettingsfileContent($local_settingsfile);

            $generated_configs = $local_settingsfile_contents . "\n" .
                $this->generateNewLocalSettingsFileContent($custom_settings);

            $this->filesystem->dumpFile($local_settingsfile, $generated_configs);
            // Reset local settings file permissions to normal, not writable.
            // $this->setLocalSettingsfilePermissions(0644);
            $this->filesystem->chmod($local_settingsfile, 0644);
        }
        return 'wrote something?';
    }

    /**
     * Returns the contents of the settings file we want to rewrite
     *
     * @param string $settingsFile path to the settings file
     *
     * @return string
     */
    protected function getLocalSettingsfileContent($settingsFile)
    {
        return file_get_contents($settingsFile, false);
    }

    /**
     * Generate a PHP-parsable string of the provided variable.
     *
     * @param array $custom_settings Settings to add to settings file
     *
     * @return string
     */
    protected function generateNewLocalSettingsFileContent($custom_settings)
    {
        $parseableSettings = <<<EOF
# START GENERATED CONFIGS, Oh Happy Days!

extract(
EOF;

        $parseableSettings .= var_export($custom_settings, true);

        $parseableSettings .= <<<EOF
);
# END GENERATED CONFIGS!
EOF;

        return $parseableSettings;
    }

    // Not needed, we have filesystem object to mock instead
    protected function writeToLocalSettingsfile()
    {

    }

    // Not needed, we have filesystem object to mock instead
    protected function setLocalSettingsfilePermissions($permissions = 0644)
    {
        $localSettingsFile = $this->localSettingsFile();
        $this->filesystem->chmod($localSettingsFile, $permissions);
    }

    protected function localSettingsFile()
    {
        $run_path          = getcwd();
        $app_path          = $this->configuration->localEnvironmentAppPath();
        $settings_filename = $run_path . '/web' . $app_path . '/sites/default/settings.local.php';
        return $settings_filename;
    }
}
