<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class CheckDrupal
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class Write
{
    public $fs;
    public $mark;
    public $output;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
        $this->output = new ConsoleOutput();
    }

    /**
     * Write a sites.php
     */
    public function sitesPhp($conf)
    {
        $tracker = new Tracker();
        $sites = $tracker->read($conf['tracker-file']);
        $out = "<?php\n" . '$sites = [' . "\n";
        foreach ($sites as $site => $siteProperty) {
            if (isset($siteProperty['web']['server-alias'])) {
                $alias = $siteProperty['web']['server-alias'];
                $domain = $siteProperty['web']['site-domain'];
                $out .=  "  '$alias' => '$domain',\n";
            }
        }
        $out .= "];\n";
        $file = $this->fs;
        $file->dumpFile('/tmp/' . $conf['app-name'] . '-sites.php', $out);
    }

    /**
     * Write a drush alias.
     */
    public function drushAlias($conf)
    {
        $drushAlias = new CreateDrushAlias();
        $drushAlias->setName($conf['site-name']);
        $drushAlias->setServer($conf['server']);
        $drushAlias->setUser($conf['user']);
        $drushAlias->setWebRoot($conf['web-root']);
        $drushAlias->setSitePath($conf['alias']);
        $drushAlias->setUrl($conf['url']);
        $drushAlias->setSSHPort($conf['ssh-port']);
        if ($conf['drush-script']) {
            $drushAlias->setDrushScriptPath($conf['drush-script']);
        }

        $drush_file = $this->fs;

        try {
            $drush_file->dumpFile(
                $conf['drush-folder'] . '/' . $conf['drush-alias'] . '.aliases.drushrc.php',
                $drushAlias->getValue()
            );
        } catch (IOExceptionInterface $e) {
            echo 'an error occurred while creating your file at ' . $e->getPath();
            exit(1);
        }
        $this->output->writeln("<info>$this->mark drush alias " . $conf['drush-alias'] .
          " created</info>");
    }


    /**
     * Write local.settings.php
     */
    public function localSettingsPhp($conf)
    {
        $tracker = new Tracker();

        $sites = $tracker->read($conf['tracker-file']);
        foreach ($sites as $site => $siteProperty) {
            if ($site == $conf['site']) {
                $out = '<?php' . "\n";
                $out .= '$settings[\'hash_salt\'] = \'' . $siteProperty['web']['hash']. '\';' . "\n\n";
                $out .= '$databases[\'default\'][\'default\'] = [' . "\n";
                $out .= '  \'database\' => \'' . $siteProperty['db']['name'] . '\',' . "\n";
                $out .= '  \'username\' => \'' . $siteProperty['db']['user'] . '\',' . "\n";
                $out .= '  \'password\' => \'' . $siteProperty['db']['pass'] . '\',' . "\n";
                $out .= '  \'host\' => \'' . $siteProperty['db']['host'] . '\',' . "\n";
                $out .= "  'prefix' => '',\n";
                $out .= "  'port' => '',\n";
                $out .= "  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',\n";
                $out .= "  'driver' => 'mysql',\n";
                $out .= '];';
                $out .= "\n\n";
                $out .= "if (file_exists('../global.overrides.php')) {\n";
                $out .= "  include '../global.overrides.php';\n";
                $out .= "}\n";
            }
        }
        $this->fs->dumpFile('/tmp/' . $conf['app-name'] . '.local.settings.php', $out);
    }

    /**
     * Write local.settings.php for multi setup
     */
    public function localSettingsPhpMulti($conf)
    {
        $tracker = new Tracker();
        $sites = $tracker->read($conf['tracker-file']);
        foreach ($sites as $site => $siteProperty) {
            if ($site == $conf['site']) {
                $out = '<?php' . "\n";
                $out .= '$settings[\'hash_salt\'] = \'' . $siteProperty['web']['hash']. '\';' . "\n\n";
                $out .= '$databases[\'default\'][\'default\'] = [' . "\n";
                $out .= '  \'database\' => \'' . $siteProperty['db']['name'] . '\',' . "\n";
                $out .= '  \'username\' => \'' . $siteProperty['db']['user'] . '\',' . "\n";
                $out .= '  \'password\' => \'' . $siteProperty['db']['pass'] . '\',' . "\n";
                $out .= '  \'host\' => \'' . $siteProperty['db']['host'] . '\',' . "\n";
                $out .= "  'prefix' => '',\n";
                $out .= "  'port' => '',\n";
                $out .= "  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',\n";
                $out .= "  'driver' => 'mysql',\n";
                $out .= '];';
                $out .= "\n\n";
                $out .= '$config_directories[\'sync\'] = \'' . $siteProperty['web']['sync-folder'] .'\';' . "\n\n";
                $out .= "if (file_exists('../global.overrides.php')) {\n";
                $out .= "  include '../global.overrides.php';\n";
                $out .= "}\n";
                if (isset($siteProperty['web']['config-split-folder'])) {
                    $out .= '$config[\'config_split.config_split.basic_site_settings\'][\'folder\'] = \'' .
                      $siteProperty['web']['config-split-folder'] . "';\n";
                }
                $out .= '$config[\'locale.settings\'][\'translation\'][\'path\'] = \'' . 'sites/' .
                  $siteProperty['web']['site-domain'] . '/files/translations' ."';\n";
            }
        }
        $this->fs->dumpFile('/tmp/' . $conf['app-name'] . '.local.settings.php', $out);
    }

    /**
     * Write opcache reset file
     */
    public function file($conf)
    {
        $this->fs->dumpFile('/tmp/' . $conf['name'], $conf['content']);
    }
}
