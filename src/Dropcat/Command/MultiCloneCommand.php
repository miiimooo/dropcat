<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Dropcat\Lib\Tracker;
use Symfony\Component\Console\Input\ArrayInput;

class MultiCloneCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Clone a drupal site</info>';

        $this->setName("multi:clone")
          ->setDescription("clone a drupal site")
          ->setHelp($HelpText)
          ->setDefinition(
              [
                new InputOption(
                    'tracker-file',
                    't',
                    InputOption::VALUE_REQUIRED,
                    'tracker file',
                    null
                ),
                new InputOption(
                  'site',
                  's',
                  InputOption::VALUE_REQUIRED,
                  'site',
                  null
                ),
                new InputOption(
                  'new-site',
                  null,
                  InputOption::VALUE_REQUIRED,
                  'new site',
                  null
                ),
                new InputOption(
                  'profile',
                  'p',
                  InputOption::VALUE_REQUIRED,
                  'profile',
                  null
                ),
                new InputOption(
                  'language',
                  'l',
                  InputOption::VALUE_REQUIRED,
                  'language',
                  null
                ),
                new InputOption(
                  'config-split-settings',
                  'c',
                  InputOption::VALUE_REQUIRED,
                  'config split settings',
                  null
                ),
              ]
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker_file = $input->getOption('tracker-file');
        $site = $input->getOption('site');
        $new_site = $input->getOption('new-site');
        $profile = $input->getOption('profile');
        $language = $input->getOption('language');
        $config_split_settings = $input->getOption('config-split-settings');

        $tracker = new Tracker();
        $sites = $tracker->read($tracker_file);
        if (isset($sites["$site"]) && is_array($sites["$site"])) {
            $to_clone = $sites["$site"];
        } else {
            $output->writeln("not a valid site to clone");
            exit;
        }
        // run prepare to create a site
        $command = $this->getApplication()->find('prepare');
        $arguments = array(
          'command' => 'prepare',
          '--create-site' => $new_site,
          '--config-split-folder' => "sites/$new_site/sync",
          '--profile' => $profile,
          '--lang' => $language,
          '--config-split-settings' => $config_split_settings,
        );

        $prepareInput = new ArrayInput($arguments);
        $returnCode = $command->run( $prepareInput, $output);

    }
}
