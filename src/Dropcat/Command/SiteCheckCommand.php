<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

class SiteCheckCommand extends Command
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
        $HelpText = 'The <info>site:check</info> command will check for installed sites.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat run-remote</info>
To override config in dropcat.yml, using options:
<info>dropcat run-remote --input=script.sh</info>';

        $this->setName("site:check")
            ->setDescription("rcheck for existing sites")
            ->setDefinition(
                array(
                    new InputOption(
                        'yml',
                        'y',
                        InputOption::VALUE_OPTIONAL,
                        'Yml file',
                        null
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $running_path = getcwd();
        if (file_exists($running_path . '/sites.yml')) {

            $yaml = new Parser();
            try {
                $sites = $yaml->parse(file_get_contents($running_path . '/sites.yml'));
            } catch (ParseException $e) {
                printf("Unable to parse the YAML string: %s", $e->getMessage());
            }
            if ($sites)
            {
                foreach ($sites as $site) {
                    if ($site['name'] == 'foo.mysite.net') {
                        var_dump('foo');
                    }
                }
                $dumper = new Dumper();
                $newYaml = $dumper->dump($sites, 1);
                file_put_contents($running_path . '/sites2.yml', $newYaml);
            }
        }
        $output->writeln('<info>Task: site:check finished</info>');
    }
}
