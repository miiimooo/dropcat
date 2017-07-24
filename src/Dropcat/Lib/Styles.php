<?php

namespace Dropcat\Lib;

/**
 * Class CheckDrupal
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */

class Styles
{

    # reference https://apps.timwhitlock.info/emoji/tables/unicode

    /**
     * Write a heavy check mark
     *
     * @return string
     */
    public function heavyCheckMark()
    {
        return "\xE2\x9C\x94";
    }

    public function start()
    {
        return "\xF0\x9F\x9A\x80";
    }

    public function heart()
    {
        return "\xE2\x9D\xA4";
    }

    public function heavyMulti()
    {
        return "\xE2\x9C\x96";
    }

    public function cat()
    {
        return "\xF0\x9F\x98\xB8";
    }

    /**
     * Output string in tag with color
     */
    public function colorize($colour, $input)
    {
        return "<fg=$colour>$input</>";
    }
}