<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use mysqli;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class CheckDrupal
 *
 * Checking if it is Drupal, and which version.
 *
 * @package Dropcat\Lib
 */
class Db
{
    public $fs;
    public $mark;
    public $output;

    public function __construct()
    {
        $this->fs = new Filesystem();
        $this->output = new ConsoleOutput();
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
    }

    public function createUser($conf) {
        $mysql_root_user = $conf['mysql-root-user'];
        $mysql_root_pass = $conf['mysql-root-pass'];
        $mysql_host = $conf['mysql-host'];
        $new_mysql_user = $conf['mysql-user'];
        $new_mysql_pass = $conf['mysql-password'];
        $timeout = $conf['timeout'];

        // Create db user.
        $process = new Process(
            "mysql -u $mysql_root_user -p$mysql_root_pass -h $mysql_host -e \"CREATE USER '$new_mysql_user'@'%' IDENTIFIED BY '$new_mysql_pass'\";"
        );
        $process->setTimeout($timeout);
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        // Flush Privileges.
        $process = new Process(
            "mysqladmin -u$mysql_root_user -p$mysql_root_pass -h $mysql_host FLUSH-PRIVILEGES"
        );
        $process->setTimeout($timeout);
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $this->output->writeln('<info>' . $this->mark . ' database user created</info>');
    }

    public function createDb($conf) {
        $mysql_host = $conf['mysql-host'];
        $mysql_user = $conf['mysql-user'];
        $mysql_password = $conf['mysql-password'];
        $mysql_db = $conf['mysql-db'];
        $mysql_port = $conf['mysql-port'];
        $timeout = $conf['timeout'];
        $mysql_root_user = $conf['mysql-root-user'];
        $mysql_root_pass = $conf['mysql-root-pass'];
        $db_dump_path = $conf['db-dump-path'];


        try {
            $mysqli = new mysqli(
                "$mysql_host",
                "$mysql_user",
                "$mysql_password"
            );
        } catch (\Exception $e) {
            echo $e->getMessage(), PHP_EOL;
            exit(1);
        }

        // If db does not exist.
        if ($mysqli->select_db("$mysql_db") === false) {
            // Fix privileges for db user.
            $process = new Process(
                "mysql -u $mysql_root_user -p$mysql_root_pass -h $mysql_host -e \"GRANT ALL PRIVILEGES ON * . * TO '$mysql_user'@'%' IDENTIFIED BY '$mysql_password'\";"
            );
            $process->setTimeout($timeout);
            $process->run();
            // Executes after the command finishes.
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            echo $process->getOutput();

            $process = new Process(
                "mysqladmin -u $mysql_user -p$mysql_password -h $mysql_host -P $mysql_port create $mysql_db"
            );
            $process->setTimeout($timeout);
            $process->run();
            // Executes after the command finishes.
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            echo $process->getOutput();

            $this->output->writeln('<info>' . $this->mark . ' database created</info>');
        } else {
            if (isset($db_dump_path)) {
                $process = new Process(
                    "mysqldump -u $mysql_user -p$mysql_password -h $mysql_host -P $mysql_port $mysql_db > $db_dump_path"
                );
                $process->setTimeout($timeout);
                $process->run();
                // Executes after the command finishes.
                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
                echo $process->getOutput();
                $this->output->writeln("<info>$this->mark database backed up to $db_dump_path</info>");
            }

            $this->output->writeln('<info>' . $this->mark . ' database exists</info>');
        }
    }


}
