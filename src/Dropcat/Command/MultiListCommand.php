<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;
use Dropcat\Lib\Tracker;

class MultiListCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Display info about installed drupal multi-sites</info>';

        $this->setName("multi:list")
          ->setDescription("list drupal multi sites")
          ->setHelp($HelpText)
          ->setDefinition(
              array(
                  new InputOption(
                      'tracker-file',
                      't',
                      InputOption::VALUE_REQUIRED,
                      'tracker file',
                      null
                  )
              )
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tracker_file = $input->getOption('tracker-file');

        $tracker = new Tracker();
        $sites = $tracker->read($tracker_file);

        $conf = Yaml::parse(file_get_contents($tracker_file));

        $sites[] = '';
        $domains[] = '';
        foreach ($conf['sites'] as $site) {
            if (isset($site['web']['site-domain'])) {
                $domains[] = $site['web']['site-domain'];
            }

        }
        $domains = array_filter($domains);
        foreach ($domains as $domain) {
            //echo $domain . "\n";
            $output->writeln("<info>installed site: http://$domain</info>");
        }
    }
}
