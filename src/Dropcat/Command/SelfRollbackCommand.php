<?php

namespace Dropcat\Command;

use Humbug\SelfUpdate\Updater;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

class SelfRollbackCommand extends Command
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
        $this
            ->setName('self-rollback')
            ->setDescription('Rollbacks dropcat.phar to the last version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        try {
            $result = $updater->rollback();
            $result ? exit('Success!') : exit('Failure!');
        } catch (\Exception $e) {
            exit('Something went wrong, sorry.');
        }
    }
}
