<?php
namespace Dropcat\Lib;

use Dropcat\Services\DropcatConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

   public function runCheck() {
       // @todo check if it is drupal codebase.
   }
   public function version() {
       $version = new Filesystem();
       if ($version->exists('web/core/core.api.php') === true) {
           return '8';
       }
   }
}
