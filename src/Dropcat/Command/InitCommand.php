<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
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

                )
            )
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $my_profile = $input->getOption('profile');

        // (startdir is needed for application)
        $process = new Process("git clone git@gitlab.wklive.net:mikke-schiren/wk-drupal-template.git web_init");

        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {

            /** @var \PEAR_Error $error_object */
            $error_object = $process->error_object;
            $exceptionMessage = sprintf(
                "Unable to clone repo, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }

        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Wk Drupal Template cloned to web_init/web</info>');


        // Rename files and functions
        $fs = new Filesystem();

        // Rename profile to project name
        $fs->rename('web_init/web/profiles/wk-standard', 'web_init/web/profiles/' . $my_profile);


        // Rename files to project-name
        $fs->rename('web_init/web/profiles/' . $my_profile . '/wk-standard.profile', 'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.profile');
        $fs->rename('web_init/web/profiles/' . $my_profile . '/wk-standard.install', 'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install');
        $fs->rename('web_init/web/profiles/' . $my_profile . '/wk-standard.info.yml', 'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.info.yml');


        // Create uuid for profile
        $uuid = uniqid(NULL, TRUE);

        // Replace what is needed
        $path_to_profile_install = 'web_init/web/profiles/' . $my_profile . '/' . $my_profile . '.install';
        $file_contents = file_get_contents($path_to_profile_install);
        $file_contents = str_replace("wk-standard_install", "$my_profile" . "_install", $file_contents);
        $file_contents = str_replace("Install, update and uninstall functions for the wk-standard installation profile.", "Install, update and uninstall functions for $my_profile installation profile.", $file_contents);
        $file_contents = str_replace("\Drupal::configFactory()->getEditable('system.site')->set('uuid', 'a0bb6f1c-dda6-477b-938a-4f0219775c28')->save(TRUE);", "\Drupal::configFactory()->getEditable('system.site')->set('uuid', '" . $uuid . "')->save(TRUE);", $file_contents);

        // Write all to file.
        file_put_contents($path_to_profile_install, $file_contents);

        $output = new ConsoleOutput();
        $output->writeln('<info>Renaming of functions and files finished</info>');

        $process = new Process("mv web_init/* . && rm -rf web_init");

        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {

            /** @var \PEAR_Error $error_object */
            $error_object = $process->error_object;
            $exceptionMessage = sprintf(
                "Unable to copy web_init/web to web, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }

        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Site is setup in current folder.</info>');
    }
}