<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;

class RunCommand extends Command
{

    /** @var Configuration configuration */
    public $configuration;
    public function __construct(Configuration $conf)
    {
        $this->configuration = $conf;
        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
