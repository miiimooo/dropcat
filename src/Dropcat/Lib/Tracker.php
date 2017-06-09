<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class CheckDrupal
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class Tracker
{
    public $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    public function add($tracker_file, $new_site, $dir, $id)
    {
        // read tracker file
        $conf = [];
        try {
            $conf = Yaml::parse(file_get_contents($tracker_file));
        } catch (ParseException $e) {
            printf("unable to parse the YAML string: %s", $e->getMessage());
        }
        foreach ($new_site as $key => $value) {
            $conf['sites'][$key] = $value;
        }

        $file = new Filesystem();
        $yaml = Yaml::dump($conf, 4, 2);
        try {
            $file->dumpFile($dir . '/default/' . $id . '.yml', $yaml);
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while creating your file at " . $e->getPath();
        }
    }

    public function read($tracker_file)
    {
        // read tracker file
        $conf = [];
        try {
            $conf = Yaml::parse(file_get_contents($tracker_file));
        } catch (ParseException $e) {
            printf("unable to parse the YAML string: %s", $e->getMessage());
        }
        $sites = $conf['sites'];
        return $sites;
    }
}
