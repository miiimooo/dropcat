<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class DnsCommand extends DropcatCommand
{
    protected static $defaultName = 'scan:mixed-content';
    protected function configure()
    {
        $HelpText = '<info>Scan doman for mixed content warnings using full address, like https://mydomain.com</info>';

        $this->setName("scan:mixed-content")
            ->setDescription("Scan doman for mixed content warnings using full address, like https://mydomain.com")
            ->setDefinition(
                array(
                    new InputOption(
                        'domain',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        'domain',
                        null
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (version_compare(phpversion(), '7.1', '<')) {
          $output->writeln('<error>' . $this->error .
            ' scan:mixed-content only works on php 7.1 and higher.</error>');
          exit();
        }

        $domain = $input->getOption('domain');
        if (!isset($domain)) {
          $output->writeln('<error>' . $this->error .
            ' please provide an domain, like --domain=https://mydomain.com</error>');
          exit();
        }

        $styledOutput = new SymfonyStyle($input, $output);

        $logger = new MixedContentLogger($styledOutput);

        $scanner = new MixedContentScanner($logger);

        $scanner->scan("$domain");

        $output->writeln('<info>' . $this->mark .
          'scan:mixed-content finished.</info>');
    }
}
