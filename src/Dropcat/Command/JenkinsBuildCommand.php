<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use JenkinsApi\Jenkins;

class JenkinsBuildCommand extends Command
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
        $HelpText = 'The <info>jenkins-build</info> will build your site with jenkins.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat jenkins-build</info>
To override config in dropcat.yml, using options:
<info>dropcat jenkins-build</info>';

        $this->setName("jenkins-build")
            ->setDescription("Jenkins build site")
            ->setDefinition(
                array(
                    new InputOption(
                        'jenkins_server',
                        'sv',
                        InputOption::VALUE_OPTIONAL,
                        'Jenkins server',
                        $this->configuration->deployJenkinsServer()
                    ),
                    new InputOption(
                        'jenkins_job',
                        'jj',
                        InputOption::VALUE_OPTIONAL,
                        'Jenkins job to build',
                        $this->configuration->deployJenkinsJob()
                    ),
                )
            )
            ->setHelp($HelpText);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $jenkins_server   = $input->getOption('jenkins_server');
        $jenkins_job      = $input->getOption('jenkins_job');

        $jenkins = new Jenkins($jenkins_server);

        echo "running deploy, this will take some time\n";
        $job = $jenkins->getJob($jenkins_job)->launchAndWait();
        echo " and we are done.\n";
        $output = new ConsoleOutput();
        $output->writeln('<info>Task: Jenkins build done</info>');
    }
}
