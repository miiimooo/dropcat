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

/**
 *
 */
class UpdateCommand extends DropcatCommand
{

    /**
     *
     */
    protected function configure()
    {
        $HelpText = 'The <info>update</info> command updates db if needed, also imports 
        config and do config split if options supplied.
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
                  'no-permission-rebuild',
                  null,
                  InputOption::VALUE_NONE,
                  'Do not rebuild permissions'
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
                  'no-cache-rebuild-after-updatedb',
                  null,
                  InputOption::VALUE_NONE,
                  'Cache rebuild after update db'
              ),
              new InputOption(
                  'no-cache-rebuild-before-updatedb',
                  null,
                  InputOption::VALUE_NONE,
                  'Cache rebuild before update db'
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

    /**
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker_file = $input->getOption('tracker-file');
        $no_entity_update = $input->getOption('no-entity-update') ? true : false;
        $no_permission_rebuild = $input->getOption('no-permission-rebuild') ? true : false;
        $no_db_update = $input->getOption('no-db-update') ? true : false;
        $no_config_import = $input->getOption('no-config-import') ? true : false;
        $config_split = $input->getOption('use-config-split') ? true : false;
        $config_partial = $input->getOption('use-config-import-partial') ? true : false;
        $multi = $input->getOption('multi') ? true : false;
        $no_cr_after_updb = $input->getOption('no-cache-rebuild-after-updatedb') ? true : false;
        $no_cr_before_updb = $input->getOption('no-cache-rebuild-before-updatedb') ? true : false;
        $config_split_settings = $input->getOption('config-split-settings');

        // If we have an option for config split settings, config split should be true.
        if (isset($config_split_settings)) {
            $config_split = true;
        }
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

        // Load tracker file, for each site drush alias.
        $tracker = new Tracker($verbose);
        $sites = $tracker->read($tracker_file);
        foreach ($sites as $site => $siteProperty) {
            if ($multi == true) {
                $exclude = 'default';
            }
            if ($site != $exclude) {
                if ($site == 'default') {
                    $site = $this->configuration->localEnvironmentAppName();
                }
                if (isset($siteProperty['drush']['alias'])) {
                    $alias = $siteProperty['drush']['alias'];

                    // Backup
                    // create dir if it does not exist
                    /*

                    $backup_dir = $this->configuration->siteEnvironmentBackupPath() .
                    '/' . $this->configuration->localEnvironmentAppName() . '/' .
                    $alias;

                    $process = new Process("mkdir -p $backup_dir");
                    $process->setTimeout(9999);
                    $process->run();
                    // Executes after the command finishes.
                    if (!$process->isSuccessful()) {
                    $output->writeln("<info>$this->error could not create backup dir.</info>");
                    throw new ProcessFailedException($process);
                    }
                    if ($output->isVerbose()) {
                    echo $process->getOutput();
                    }

                    $server_time = date("Ymd_His");
                    $process = new Process("drush @$alias sql-dump -y > $backup_dir/$alias-$server_time.sql");
                    $process->setTimeout(9999);
                    $process->run();
                    // Executes after the command finishes.
                    if (!$process->isSuccessful()) {
                    $output->writeln("<info>$this->error could not update db for $site</info>");
                    throw new ProcessFailedException($process);
                    }
                    if ($output->isVerbose()) {
                    echo $process->getOutput();
                    }

                    $output->writeln("<info>$this->mark update db done for $alias</info>");

                     */
                    // end backup.
                    if ($no_db_update == false) {
                        if ($version == '8') {
                            if ($no_cr_before_updb == false) {
                                // First rebuild cahce.
                                $process = new Process("drush @$alias cr");
                                $process->setTimeout(9999);
                                $process->run();
                                // Executes after the command finishes.
                                if (!$process->isSuccessful()) {
                                  $output->writeln("<info>$this->error could not rebuild cache for $site</info>");
                                  throw new ProcessFailedException($process);
                                }
                                if ($output->isVerbose()) {
                                  echo $process->getOutput();
                                }
                            }
                        }
                        $process = new Process("drush @$alias updb -y $ent");
                        $process->setTimeout(9999);
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

                    if ($no_cr_after_updb != true) {
                        if (($version == '7') || ($version == '6')) {
                            $process = new Process("drush @$alias cc all");

                            $process->run();
                            // Executes after the command finishes.
                            if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error could not clear cache for $site</info>");
                                throw new ProcessFailedException($process);
                            }
                            if ($output->isVerbose()) {
                                echo $process->getOutput();
                            }

                            $output->writeln("<info>$this->mark cleared cache for $site</info>");
                        }
                        if ($version == '8') {
                            $process = new Process("drush @$alias sset system.maintenance_mode 1 && drush @$alias sql-query 'TRUNCATE TABLE sessions;'");
                            $process->setTimeout(9999);
                            $process->run();
                            // Executes after the command finishes.
                            if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error could not set $site in maintenance mode</info>");
                                throw new ProcessFailedException($process);
                            }
                            if ($output->isVerbose()) {
                                echo $process->getOutput();
                            }

                            $output->writeln("<info>$this->mark $site is in maintenance mode</info>");


                            $process = new Process("drush @$alias cr");
                            $process->setTimeout(9999);
                            $process->run();
                            // Executes after the command finishes.
                            if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error could not rebuild cache for $site</info>");
                                throw new ProcessFailedException($process);
                            }
                            if ($output->isVerbose()) {
                                echo $process->getOutput();
                            }

                            $process = new Process("drush @$alias cr");
                            $process->setTimeout(9999);
                            $process->run();
                            // Executes after the command finishes.
                            if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error could not rebuild cache for $site</info>");
                                throw new ProcessFailedException($process);
                            }
                            if ($output->isVerbose()) {
                                echo $process->getOutput();
                            }

                            $output->writeln("<info>$this->mark rebuild cache done for $site</info>");
                        }
                    }

                    if ($config_split == true) {
                        if ($version == '7') {
                            $output->writeln('<info>Seems like you are trying to run config split ' .
                              'on a drupal 7 site</info>');
                        }
                        // We had a bug about drush did not see drush csex, this was
                        // the solution, but it seems not needed if config_split is installed
                        // from the beginning.
                        $process = new Process("drush @$alias cc drush");
                        $process->setTimeout(9999);
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
                        $process->setTimeout(9999);
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
                        $process->setTimeout(9999);
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
                        if ($no_config_import == false) {
                            if ($version == '8') {
                                // Remove partial for now.
                                /*  $output->writeln("<info>$this->mark starting partial config import for $site</info>");
                                $process = new Process("drush @$alias cim -y --partial");
                                $process->setTimeout(9999);
                                $process->run();
                                // Executes after the command finishes.
                                if (!$process->isSuccessful()) {
                                $output->writeln("<info>$this->error config import failed for $site</info>");
                                throw new ProcessFailedException($process);
                                }
                                if ($output->isVerbose()) {
                                echo $process->getOutput();
                                }*/
                                $output->writeln("<info>$this->mark starting config import for $site</info>");
                                $process = new Process("drush @$alias cim -y $part");
                                $process->setTimeout(9999);
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
                    } else {
                        if ($no_config_import == false) {
                            if ($version == '8') {
                                $output->writeln("<info>$this->mark starting config import for $site</info>");
                                $process = new Process("drush @$alias cim -y $part");
                                $process->setTimeout(9999);
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
                    if ($no_permission_rebuild == false) {
                        $process = new Process("drush @$alias php-eval 'node_access_rebuild();'");
                        $process->setTimeout(9999);
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error could not rebuild permissions for $site</info>");
                            throw new ProcessFailedException($process);
                        }
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }
                        $output->writeln("<info>$this->mark permissions rebuilt for $site</info>");
                    }

                    if ($version == '7') {
                        // @todo
                    } else {
                        $process = new Process("drush @$alias sset system.maintenance_mode 0");
                        $process->setTimeout(9999);
                        $process->run();
                        // Executes after the command finishes.
                        if (!$process->isSuccessful()) {
                            $output->writeln("<info>$this->error could not remove $site from maintenance mode</info>");
                            throw new ProcessFailedException($process);
                        }
                        if ($output->isVerbose()) {
                            echo $process->getOutput();
                        }
                    }

                    $output->writeln("<info>$this->mark $site is now online.</info>");
                }
            }
        }

        $output->writeln("<info>$this->heart update finished</info>");
    }
}
