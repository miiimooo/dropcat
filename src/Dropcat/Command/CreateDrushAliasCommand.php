<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\CreateDrushAlias;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Dropcat\Command\RunCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class CreateDrushAliasCommand extends Command
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
        $HelpText = 'The <info>create-drush-alias</info> command will create drush alias.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the current dir):
<info>dropcat create-drush-alias</info>
To override config in dropcat.yml, using options, creates alias to stage env.
<info>dropcat create-drush-alias --env=stage</info>';

        $this->setName("create-drush-alias")
        ->setDescription("run command or script on local environment")
        ->setHelp($HelpText);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteName = $this->configuration->siteEnvironmentName();
        $server = $this->configuration->remoteEnvironmentServerName();
        $user = $this->configuration->remoteEnvironmentSshUser();
        $webroot = $this->configuration->remoteEnvironmentWebRoot();
        $alias = $this->configuration->remoteEnvironmentAlias();
        $url =  $this->configuration->siteEnvironmentUrl();
        $sshport = $this->configuration->remoteEnvironmentSshPort();

        $drushAlias = new CreateDrushAlias();
        $drushAlias->setName($siteName);
        $drushAlias->setServer($server);
        $drushAlias->setUser($user);
        $drushAlias->setWebRoot($webroot);
        $drushAlias->setSitePath($alias);
        $drushAlias->setUrl($url);
        $drushAlias->setSSHPort($sshport);

        $home = new CreateDrushAlias();
        $home_dir = $home->drushServerHome();

        $drush_alias_name = $this->configuration->siteEnvironmentDrushAlias();

        $drush_file = new Filesystem();

        try {
            $drush_file->dumpFile($home_dir.'/.drush/'.$drush_alias_name.
              '.aliases.drushrc.php', $drushAlias->getValue());
        } catch (IOExceptionInterface $e) {
            echo 'An error occurred while creating your file at '.$e->getPath();
        }

        $output->writeln('<info>Task: create-drush-alias finished</info>');
    }
}
