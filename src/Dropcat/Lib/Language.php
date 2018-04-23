<?php
namespace Dropcat\Lib;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Language
 *
 * Tasks that has to with language.
 *
 * @package Dropcat\Lib
 */
class Language
{

    public function setLang($lang, $alias, $verbose)
    {
        $v = '';
        if ($verbose == true) {
            $v = ' -v';
        }
        $lang = new Process(
            "drush @$alias cset system.site default_langcode $lang --yes $v"
        );
        $lang->setTimeout(999);
        $lang->run();

        if (!$lang->isSuccessful()) {
            throw new ProcessFailedException($lang);
        }
        echo $lang->getOutput();
    }
}
