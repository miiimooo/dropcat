<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\UUID;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Process\Process;

class InitCommand extends Command
{
    /** @var Configuration configuration */
    private $configuration;

    public function __construct(Configuration $conf)
    {
        $this->configuration = $conf;
        parent::__construct();
    }


    protected function configure()
    {
        $HelpText = 'The <info>init</info> command clones the wk-standard drupal setup and replaces profile related.
<comment>Samples:</comment>
The command needs the --profile parameter):
<info>dropcat init --profile=myprofile</info>';

        $this->setName("init")
            ->setDescription("Init D8 site from template repo")
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
                        'template',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'Project template',
                        'git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git'
                    ),

                )
            )
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $myProfile = $input->getOption('profile');
        $myTemplate = $input->getOption('template');
        if (!isset($myProfile)) {
            throw new \Exception("You need to specify profile");
        }
        if (!isset($myTemplate)) {
            throw new \Exception("Template does not except empty value");
        }

        $process = new Process("git clone $myTemplate web_init");

        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new \Exception("Something went wrong, could not clone $myTemplate");
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Wk Drupal Template cloned to web_init/web</info>');

        // Rename files and functions
        $fs1 = new Filesystem();

        try {
            // Rename profile to project name
            $fs1->rename(
                'web_init/web/profiles/wk-standard',
                'web_init/web/profiles/' . $myProfile
            );

            // Rename files to project-name
            $fs1->rename(
                'web_init/web/profiles/' . $myProfile . '/wk-standard.profile',
                'web_init/web/profiles/' . $myProfile . '/' . $myProfile . '.profile'
            );
            $fs1->rename(
                'web_init/web/profiles/' . $myProfile . '/wk-standard.install',
                'web_init/web/profiles/' . $myProfile . '/' . $myProfile . '.install'
            );
            $fs1->rename(
                'web_init/web/profiles/' . $myProfile . '/wk-standard.info.yml',
                'web_init/web/profiles/' . $myProfile . '/' . $myProfile . '.info.yml'
            );

        } catch (IOExceptionInterface $e) {
            echo "An error occurred while renaming ".$e->getPath();
        }

        $output = new ConsoleOutput();
        $output->writeln('<info>Renamed files</info>');

        // Create uuid for profile
        $uuid = UUID::v4();

        // Replace what is needed in install.
        $pathToProfileInstall = 'web_init/web/profiles/' . $myProfile . '/' . $myProfile . '.install';

        $fileContents = file_get_contents($pathToProfileInstall);
        $fileContents = str_replace(
            "wk-standard_install",
            "$myProfile" . "_install",
            $fileContents
        );
        $fileContents = str_replace(
            "Install, update and uninstall functions for the wk-standard installation profile.",
            "Install, update and uninstall functions for $myProfile installation profile.",
            $fileContents
        );
        $fileContents = str_replace(
            "getEditable('system.site')->set('uuid', 'a0bb6f1c-dda6-477b-938a-4f0219775c28')->save(TRUE);",
            "getEditable('system.site')->set('uuid', '" . $uuid . "')->save(TRUE);",
            $fileContents
        );

        $fs2 = new Filesystem();
        try {
            $fs2->remove($pathToProfileInstall);
            $fs2->dumpFile($pathToProfileInstall, $fileContents);
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while replacing content in ".$e->getPath();
        }

        $output = new ConsoleOutput();
        $output->writeln('<info>Profile install file rewritten</info>');

        $pathToComposerJson = 'web_init/composer.json';
        $fileContents = file_get_contents($pathToComposerJson);
        $fileContents = str_replace(
            "web/profiles/wk-standard/modules/contrib/",
            "web/profiles/" . $myProfile . "/modules/contrib/",
            $fileContents
        );
        $fileContents = str_replace(
            "web/profiles/wk-standard/themes/contrib/",
            "web/profiles/" . $myProfile . "/themes/contrib/",
            $fileContents
        );
        $fileContents = str_replace(
            "web/profiles/wk-standard/composer.json",
            "web/profiles/". $myProfile . "/composer.json",
            $fileContents
        );

        $fs3 = new Filesystem();
        try {
            $fs3->remove($pathToComposerJson);
            $fs3->dumpFile($pathToComposerJson, $fileContents);
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while replacing content in ".$e->getPath();
        }

        $output = new ConsoleOutput();
        $output->writeln('<info>Path to profile composer.json rewritten</info>');

        $pathToProfileComposerJson = 'web_init/web/profiles/' . $myProfile . '/composer.json';
        $fileContents = file_get_contents($pathToProfileComposerJson);
        $fileContents = str_replace(
            "nodeone/wk-standard",
            "nodeone/" . $myProfile,
            $fileContents
        );
        $fileContents = str_replace(
            "Installation profile for wk-standard",
            "Installation profile for " . $myProfile,
            $fileContents
        );

        $fs4 = new Filesystem();
        try {
            $fs4->remove($pathToProfileComposerJson);
            $fs4->dumpFile($pathToProfileComposerJson, $fileContents);
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while replacing content in ".$e->getPath();
        }

        $output = new ConsoleOutput();
        $output->writeln('<info>Profile composer.json rewritten</info>');

        $process = new Process("mv web_init/* . && rm -rf web_init");
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new \Exception('Something went wrong, folder could not be moved or deleted');
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Site is setup.</info>');
    }
}
