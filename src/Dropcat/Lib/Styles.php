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

    public function heart()
    {
        return "\xE2\x9D\xA4";
    }

    public function heavyMulti()
    {
        return "\xE2\x9C\x96";
    }

    /**
     * Output string in tag with color
     */
    public function colorize($colour, $input)
    {
        return "<fg=$colour>$input</>";
    }
}