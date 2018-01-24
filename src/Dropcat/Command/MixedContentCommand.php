<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Spatie\MixedContentScanner\MixedContentScanner;
use Dropcat\Lib\MixedContentLogger;
use Symfony\Component\Console\Style\SymfonyStyle;

class MixedContentCommand extends DropcatCommand
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
        $domain = $input->getOption('domain');
        if ($output->isVerbose()) {
            echo 'scanning domain: ' . $domain . "\n";
        }

        $styledOutput = new SymfonyStyle($input, $output);

        $logger = new MixedContentLogger($styledOutput);

        $scanner = new MixedContentScanner($logger);

        $scanner->scan("$domain");

        $output->writeln('<info>' . $this->mark .
          'scan:mixed-content finished.</info>');
    }
}
