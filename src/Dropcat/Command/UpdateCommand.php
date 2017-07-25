<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\Tracker;
use Dropcat\Lib\CheckDrupal;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Exception;

class UpdateCommand extends DropcatCommand
{

    protected function configure()
    {
        $HelpText = 'The <info>update-database</info> command updates db if needed.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat update</info>';

        $this->setName("update")
          ->setDescription("Run needed updates after a deploy.")
          ->setDefinition(
              array(
              new InputOption(
                  'tracker-file',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Trackerfile',
                  $this->configuration->trackerFile()
              ),
              new InputOption(
                  'site',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Site to update',
                  null
              ),
              new InputOption(
                  'no-entity-update',
                  null,
                  InputOption::VALUE_NONE,
                  'Do not run entity updates'
              ),
              new InputOption(
                  'no-db-update',
                  null,
                  InputOption::VALUE_NONE,
                  'Do not run update database'
              ),
              new InputOption(
                  'no-config-import',
                  null,
                  InputOption::VALUE_NONE,
                  'Do not import config'
              ),
              new InputOption(
                  'use-config-split',
                  null,
                  InputOption::VALUE_NONE,
                  'Use config split'
              ),
              new InputOption(
                  'use-config-import-partial',
                  null,
                  InputOption::VALUE_NONE,
                  'Use partial import of config'
              ),
              new InputOption(
                  'multi',
                  null,
                  InputOption::VALUE_NONE,
                  'Use multi-site setup'
              ),
              new InputOption(
                  'config-split-settings',
                  null,
                  InputOption::VALUE_OPTIONAL,
                  'Config split settings to use',
                  null
              ),
              )
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker_file = $input->getOption('tracker-file');
        $no_entity_update = $input->getOption('no-entity-update') ? true : false;
        $no_db_update = $input->getOption('no-db-update') ? true : false;
        $no_config_import = $input->getOption('no-config-import') ? true : false;
        $config_split = $input->getOption('use-config-split') ? true : false;
        $config_partial = $input->getOption('use-config-import-partial') ? true : false;
        $multi = $input->getOption('multi') ? true : false;
        $only_site = $input->getOption('site');
        $config_split_settings = $input->getOption('config-split-settings');

        $env = getenv('DROPCAT_ENV');

        $output->writeln('<info>' . $this->start . ' update started</info>');

        if ($tracker_file == null) {
            $tracker_dir = $this->configuration->trackerDir();
            if (isset($tracker_dir)) {
                $app_name = $this->configuration->localEnvironmentAppName();
                $tracker_file = $tracker_dir . '/default/' . $app_name . '-' . $env . '.yml';
            } else {
                $output->writeln("<info>$this->error no tracker dir defined</info>");
                throw new Exception('no tracker dir defined');
            }
        }

        $verbose = false;
        if ($output->isVerbose()) {
            $verbose = true;
        }

        $ent = '';
        $part = '';
        $exclude = '';

        if ($no_entity_update == false) {
            $ent = ' --entity-updates';
        }

        if ($config_partial == true) {
            $part = ' --partial';
        }

        $check = new CheckDrupal();
        $version = $check->version();

        if ($version == '8') {
            $output->writeln("<info>$this->mark this is a drupal 8 site</info>");
        }
        if ($version == '7') {
            $output->writeln("<info>$this->mark this is a drupal 7 site</info>");
            // We don't run entity updates in drupal 7, so:
            $ent = null;
        }
        if ($version == '6') {
            $output->writeln("<info>$this->mark this is a drupal 6 site</info>");
            // We don't run entity updates in drupal 6, so:
            $ent = null;
        }
        if (!isset($version) || $version == '') {
            throw new Exception('version of drupal not recognised.');
        }

        // load tracker file, for each site drush alias
        $tracker = new Tracker($verbose);
        $sites = $tracker->read($tracker_file);
        foreach ($sites as $site => $siteProperty) {
            if ($multi == true) {
                $exclude = 'default';
                echo 'excluding default';
            }
            if ($site != $exclude) {
                if ($site == 'default') {
                    $site = $this->configuration->localEnvironmentAppName();
                }
                if (isset($siteProperty['drush']['alias'])) {
                    $alias = $siteProperty['drush']['alias'];

                    if ($no_db_update == false) {
                        $process = new Process("drush @$alias updb -y $ent");
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error could not update db for $site</info>");
                            throw new ProcessFailedException($process);
                        }
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }

                        $output->writeln("<info>$this->mark update db done for $site</info>");
                    }
                    if ($config_split == true) {
                        if ($version == '7') {
                            $output->writeln("<info>Seems like you are trying to run config split on a drupal 7 site</info>");
                        }
                        // we had a bug about drush did not see drush csex, this was
                        // the solution, but it seems not needed if config_split is installed
                        // from the beginning.
                        $process = new Process("drush @$alias cc drush");
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error could not clear drush cache for $site</info>");
                            throw new ProcessFailedException($process);
                        }
                        $output->writeln("<info>$this->mark cleared drush cache for $site</info>");
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }

                        $process = new Process("drush @$alias en config_split -y");
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error could not enable config split for $site</info>");
                            throw new ProcessFailedException($process);
                        }
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }
                        $output->writeln("<info>$this->mark config split is enabled for $site</info>");

                        $process = new Process("drush @$alias csex $config_split_settings -y");
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error config split failed for $site</info>");
                            throw new ProcessFailedException($process);
                        }
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }
                        $output->writeln("<info>$this->mark config split export done for $site</info>");
                    }
                    if ($no_config_import == false) {
                        if ($version == '8') {
                            $output->writeln("<info>$this->mark starting config import for $site</info>");
                            $process = new Process("drush @$alias cim -y $part");
                            $process->run();
                            // Executes after the command finishes.
                            if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error config import failed for $site</info>");
                                throw new ProcessFailedException($process);
                            }
                            if ($output->isVerbose()) {
                                echo $process->getOutput();
                            }
                            $output->writeln("<info>$this->mark config import done for $site</info>");
                        }
                    }
                }
            }
        }

        $output->writeln("<info>$this->heart update finished</info>");
    }
}
