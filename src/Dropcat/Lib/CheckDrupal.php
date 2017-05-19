<?php
namespace Dropcat\Lib;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CheckDrupal
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class CheckDrupal
{
    public $dir;
    public $fs;

    public function __construct()
    {
        $this->dir = getcwd();
        $this->fs = new Filesystem();
    }

    public function isDrupal()
    {
        return $this->fs->exists($this->dir . '/web/core/core.api.php') ||
        $this->fs->exists($this->dir . '/web/modules/block/block.info');
    }

    public function version()
    {
        $checks = [
          '8'   =>    '/web/core/core.api.php',
          '7'   =>    '/web/misc/ajax.js',
          '6'   =>    '/web/misc/ahah.js'
        ];

        foreach ($checks as $version => $path) {
            if ($this->fs->exists($this->dir . $path)) {
                return $version;
            }
        }
    }
}
