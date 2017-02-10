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

class CreateCustomSettingsfileCommand extends DropcatCommand
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

        $this->setName("create-custom-settingsfile")
            ->setDescription("Creates a custom settingsfile based on existing settings-file.")
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output            = new ConsoleOutput();
        $settings_file = $this->addCustomSettings();
        $output->writeln($settings_file);
    }

    /**
     * Adds custom settings to the custom settings file based on dropcat
     * configuration in the custom_settings section.
     */
    protected function addCustomSettings()
    {
        $custom_settings = $this->configuration->getCustomSettings();
        if ($custom_settings) {
            // injecta via containern senare
            $this->filesystem = new Filesystem();
            $custom_settingsfile = $this->configuration->getCustomSettingsFilePath();

            $custom_settingsfile_contents = $this->getCustomSettingsfileContent($custom_settingsfile);

            $generated_configs = $custom_settingsfile_contents . "\n" .
                $this->generateNewCustomSettingsFileContent($custom_settings);

            // Make custom settings file writable.
            $this->filesystem->chmod($custom_settingsfile, 0777);

            // Create a new settings-file with the combined settings.
            $this->filesystem->dumpFile($custom_settingsfile, $generated_configs);

            // Reset custom settings file permissions to normal, not writable.
            $this->filesystem->chmod($custom_settingsfile, 0644);
        }
        return 'Added custom settings to '. $custom_settingsfile;
    }

    /**
     * Returns the contents of the settings file we want to rewrite
     *
     * @param string $settingsFile path to the settings file
     *
     * @return string
     */
    protected function getCustomSettingsfileContent($settingsFile)
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
    protected function generateNewCustomSettingsFileContent($custom_settings)
    {
        $parseableSettings = <<<EOF

# CUSTOM GENERATED SETTINGS by DROPCAT

EOF;

        foreach ($custom_settings as $var_name => $var_contents) {
            $parseableVariableContents = var_export($var_contents, true);
            // merge and other lovely shite
            if (is_array($var_contents)) {
                $parseableSettings .= <<<EOF

if ( !isset(\${$var_name}) ) {
    \${$var_name} = array();
}
\${$var_name} = array_replace_recursive(\${$var_name}, {$parseableVariableContents});
EOF;
            } else {
                $parseableSettings .= <<<EOF

\${$var_name} = {$parseableVariableContents};
EOF;
            }
        }

        $parseableSettings .= <<<EOF

# END CUSTOM GENERATED SETTINGS by DROPCAT

EOF;
        return $parseableSettings;
    }

}
