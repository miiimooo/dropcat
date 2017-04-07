<?php
namespace Dropcat\Lib;


class Styles
{

  /**
   * Write a nice check mark
   *
   * @return string
   */
  public function heavyCheckMark()
  {
	return "\xE2\x9C\x94";
  }

  /**
   * Output string in tag with color
   */
  public function colorize($colour, $input)
  {
	return "<fg=$colour>$input</>";
  }
}