<?php

namespace Dropcat\Command;

use Humbug\SelfUpdate\Updater;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdaterCommand extends Command
{
    /** @var Configuration configuration */
    private $configuration;


    public function __construct(Configuration $conf) {
        $this->configuration = $conf;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates dropcat.phar/dropcat to the latest version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urlToPhar = 'https://dropcat.org/dropcat.phar';
        $urlToVersionFile = 'https://dropcat.org/dropcat.phar.version';
        $updater = new Updater(null, false);
        $updater->getStrategy()->setPharUrl($urlToPhar);
        $updater->getStrategy()->setVersionUrl($urlToVersionFile);
        try {
            $result = $updater->update();
            if (! $result) {
                printf("No update is needed");
                exit;
            }
            $new = $updater->getNewVersion();
            $old = $updater->getOldVersion();
            printf('Updated from %s to %s', $old, $new);
            exit;
        } catch (\Exception $e) {
            printf( "Error occurred");
            exit;
        }
    }
}
