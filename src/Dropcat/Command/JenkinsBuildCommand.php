<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use JenkinsApi\Jenkins;

class JenkinsBuildCommand extends DropcatCommand
{
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
          ->setHidden(true)
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

        $resultTime = null;
        $jobLatest = $jenkins->getJob($jenkins_job)->getLastSuccessfulBuild();
        $resultTime = $jobLatest->getEstimatedDuration();
        if (isset($resultTime)) {
            $convert_time =  gmdate("H:i:s", $resultTime);
            $time = ', last build were approx. ' . $convert_time;
        }
        $output->writeln("<info>running deploy, this will take some time$time</info>");


        $job = $jenkins->getJob($jenkins_job)->launchAndWait();
        $output->writeln('<info>and we are done</info>');

        $latestJobStatus =$jenkins->getJob($jenkins_job)->getLastBuild();
        $result = $latestJobStatus->getResult();
        $output->writeln("<info>the status of build is $result</info>");
        $resultText = $latestJobStatus->getConsoleTextBuild();
        $output->writeln("<info>$resultText</info>");

        $output->writeln('<info>Task: Jenkins build done</info>');
    }
}
