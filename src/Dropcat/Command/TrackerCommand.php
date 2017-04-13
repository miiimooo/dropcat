<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;
use Dropcat\Lib\Styles;

/**
 *
 */
class TrackerCommand extends DropcatCommand
{

  /**
   *
   */
    protected function configure()
    {
        $HelpText = '<info>tracker</info> tracks settings set to the tracker.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat tracker</info>
To override config in dropcat.yml, using options:
<info>dropcat move --tracker-dir=/my/dir</info>';
        $this->setName("tracker")
        ->setDescription("Tracks configuration")
        ->setDefinition(
            [
            new InputOption(
                'app-name',
                'a',
                InputOption::VALUE_OPTIONAL,
                'App name',
                $this->configuration->localEnvironmentAppName()
            ),
            new InputOption(
                'tracker-dir',
                'td',
                InputOption::VALUE_OPTIONAL,
                'Tracker direcory',
                $this->configuration->trackerDir()
            ),
            new InputOption(
                'db-dump',
                'dd',
                InputOption::VALUE_OPTIONAL,
                'Complete path to db backup',
                $this->configuration->trackerDbDump()
            ),
            new InputOption(
                'db-name',
                'dn',
                InputOption::VALUE_OPTIONAL,
                'Database name.',
                $this->configuration->trackerDbName()
            ),
            new InputOption(
                'db-user',
                'du',
                InputOption::VALUE_OPTIONAL,
                'Database name.',
                $this->configuration->trackerDbUser()
            ),
            new InputOption(
                'db-pass',
                'dp',
                InputOption::VALUE_OPTIONAL,
                'Database password.',
                $this->configuration->trackerDbPass()
            ),
            new InputOption(
                'db-host',
                'dh',
                InputOption::VALUE_OPTIONAL,
                'Database host.',
                $this->configuration->trackerDbHost()
            ),
            new InputOption(
                'id',
                'id',
                InputOption::VALUE_OPTIONAL,
                'Id of tracker',
                $this->configuration->trackerId()
            ),
            new InputOption(
                'site-path',
                'sp',
                InputOption::VALUE_OPTIONAL,
                'Path to site',
                $this->configuration->trackerSitePath()
            ),
            ]
        )
        ->setHelp($HelpText);
    }

  /**
   *
   */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app_name = $input->getOption('app-name');
        $tracker_dir = $input->getOption('tracker-dir');
        $id = $input->getOption('id');
        $db_dump = $input->getOption('db-dump');
        $db_name = $input->getOption('db-name');
        $db_user = $input->getOption('db-user');
        $db_pass = $input->getOption('db-pass');
        $db_host = $input->getOption('db-host');
        $site_path = $input->getOption('site-path');

        if (!isset($tracker_dir)) {
            throw new Exception('tracker dir must be set');
        }
        if (!isset($id)) {
            throw new Exception('tracker id must be set');
        }
        if (!isset($site_path)) {
            throw new Exception('tracker site path must be set');
        }
        // Directory to write tracker to.
        $dir = "$tracker_dir/$app_name";
        // Create directory.
        $this->writeDir($dir);

        if (!isset($db_name)) {
            $db_name = $this->configuration->mysqlEnvironmentDataBase();
        }
        if (!isset($db_user)) {
            $db_user = $this->configuration->mysqlEnvironmentUser();
        }
        if (!isset($db_pass)) {
            $db_pass = $this->configuration->mysqlEnvironmentPassword();
        }
        if (!isset($db_host)) {
            $db_host = $this->configuration->mysqlEnvironmentHost();
        }
        $web_host = $this->configuration->remoteEnvironmentServerName();
        $web_host_user = $this->configuration->remoteEnvironmentSshUser();
        $web_host_port = $this->configuration->remoteEnvironmentSshPort();
        $web_host_id_file = $this->configuration->remoteEnvironmentIdentifyFile();
        $web_host_pass = $this->configuration->localEnvironmentSshKeyPassword();

        if (!isset($web_host, $web_host_user, $web_host_port, $web_host_id_file, $web_host_pass)) {
            throw new Exception('mising needed values for web host');
        }

        // Populate the yaml.
        $conf = [
          'db-dump' => $db_dump,
          'db-name' => $db_name,
          'db-user' => $db_user,
          'db-pass' => $db_pass,
          'db-host' => $db_host,
          'web_host' => $web_host,
          'web_host_user' => $web_host_user,
          'web_host_port' => $web_host_port,
          'web_host_id_file' => $web_host_id_file,
          'web_host_pass' => $web_host_pass,
          'site-path' => $site_path,
        ];

        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $mark_formatted = $style->colorize('yellow', $mark);

        $this->writeYaml($dir, $id, $conf);
        $output->writeln('<info>' . $mark_formatted .
        ' tracker finished</info>');
    }

  /**
   * Write Yaml file.
   *
   * @param $dir
   *   string
   * @param $id
   *   string
   * @param $conf
   *   array
   */
    private function writeYaml($dir, $id, $conf)
    {
        $yaml = Yaml::dump($conf);
        file_put_contents($dir . '/' . $id . '.yml', $yaml);
    }

  /**
   * Create tracker dir.
   *
   * @param $dir
   *   string
   */
    private function writeDir($dir)
    {
        $createTrackerDir = new Process(
            "mkdir -p $dir"
        );
        $createTrackerDir->run();
        // Executes after the command finishes.
        if (!$createTrackerDir->isSuccessful()) {
            throw new ProcessFailedException($createTrackerDir);
        }
    }
}
