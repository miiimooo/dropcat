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
       $check = new Filesystem();
       if ($check->exists('web/core/core.api.php') === true) {
           return true;
       }
       if ($check->exists('web/modules/block/block.info') === true) {
           return true;
       }
       else {
         return false;
       }
   }
   public function version() {
       $version = new Filesystem();
       if ($version->exists('web/core/core.api.php') === true) {
           return '8';
       }
       if ($version->exists('web/misc/ajax.js') === true) {
           return '7';
       }
       if ($version->exists('web/misc/ahah.js') === true) {
           return '6';
       }
   }
}
