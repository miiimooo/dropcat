<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Exception;

class UploadCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>upload</info> connects to remote server and upload tar and unpack it in path.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat upload</info>
To override config in dropcat.yml, using options:
<info>dropcat upload -server 127.0.0.0 -i my_pub.key</info>';

        $this->setName("upload")
          ->setDescription("Upload to server")
          ->setDefinition(
            array(
              new InputOption(
                'app-name',
                'a',
                InputOption::VALUE_OPTIONAL,
                'App name',
                $this->configuration->localEnvironmentAppName()
              ),
              new InputOption(
                'build-id',
                'bi',
                InputOption::VALUE_OPTIONAL,
                'Id',
                $this->configuration->localEnvironmentBuildId()
              ),
              new InputOption(
                'separator',
                'se',
                InputOption::VALUE_OPTIONAL,
                'Name separator',
                $this->configuration->localEnvironmentSeparator()
              ),
              new InputOption(
                'tar',
                't',
                InputOption::VALUE_OPTIONAL,
                'Tar',
                $this->configuration->localEnvironmentTarName()
              ),
              new InputOption(
                'tar_dir',
                'td',
                InputOption::VALUE_OPTIONAL,
                'Tar dir',
                $this->configuration->localEnvironmentTarDir()
              ),
              new InputOption(
                'server',
                's',
                InputOption::VALUE_OPTIONAL,
                'Server',
                $this->configuration->remoteEnvironmentServerName()
              ),
              new InputOption(
                'user',
                'u',
                InputOption::VALUE_OPTIONAL,
                'User (ssh)',
                $this->configuration->remoteEnvironmentSshUser()
              ),
              new InputOption(
                'ssh_port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'SSH port',
                $this->configuration->remoteEnvironmentSshPort()
              ),
              new InputOption(
                'ssh_key_password',
                'skp',
                InputOption::VALUE_OPTIONAL,
                'SSH key password',
                $this->configuration->localEnvironmentSshKeyPassword()
              ),
              new InputOption(
                'target_dir',
                'tp',
                InputOption::VALUE_OPTIONAL,
                'Target dir',
                $this->configuration->remoteEnvironmentTargetDir()
              ),
              new InputOption(
                'identity_file',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Identify file',
                $this->configuration->remoteEnvironmentIdentifyFile()
              ),
              new InputOption(
                'timeout',
                'to',
                InputOption::VALUE_OPTIONAL,
                'Timeout',
                $this->configuration->timeOut()
              ),
              new InputOption(
                'keeptar',
                'kt',
                InputOption::VALUE_NONE,
                'Keep tar after upload  (defaults to no)'
              ),
              new InputOption(
                'dontchecksha1',
                'dsha1',
                InputOption::VALUE_NONE,
                "Don't check SHA1 hash for file (defaults to no)"
              ),
            )
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $app_name = $input->getOption('app-name');
        $build_id = $input->getOption('build-id');
        $separator = $input->getOption('separator');
        $tar = $input->getOption('tar');
        $tar_dir = $input->getOption('tar_dir');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $targetdir = $input->getOption('target_dir');
        $port = $input->getOption('ssh_port');
        $ssh_key_password = $input->getOption('ssh_key_password');
        $identity_file = $input->getOption('identity_file');
        $identity_file_content = $this->readIdentityFile($identity_file);
        $timeout = $input->getOption('timeout');
        $keeptar = $input->getOption('keeptar') ? 'TRUE' : 'FALSE';
        $checksha1 = $input->getOption('dontchecksha1') ? 'false' : 'true';


        if (isset($tar)) {
            $tarfile = $tar;
        } else {
            $tarfile = $app_name . $separator . $build_id . '.tar';
        }
        $localFileSha1 = $this->getSha1OfFile($tar_dir, $tarfile);
        $sftp = $this->container->get('dropcat.factory')->sftp($server, $port, $timeout);
        $sftp->setTimeout(999);

        $auth = $this->container->get('rsa');
        if (isset($ssh_key_password)) {
            $auth->setPassword($ssh_key_password);
        }
        $auth->loadKey($identity_file_content);
        try {
            $login = $sftp->login($user, $auth);
            if (!$login) {
                throw new Exception('login Failed using ' . $identity_file . ' and user ' . $user . ' at ' . $server);
            }
            $transfer = $sftp->put("$targetdir/$tarfile", "$tar_dir$tarfile", 1);
            if (!$transfer) {
                throw new Exception('Upload failed of ' . $tarfile);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            $this->exitCommand(1);
        }

        $tarExists = $sftp->file_exists("$tar_dir$tarfile");
        // Setting default value

        if ($output->isVerbose()) {
            echo "checksha1 is set to $checksha1 \n";
        }
        $remoteFileSha1 = null;
        if ($tarExists) {
            if ($checksha1 == 'true') {
                $remoteFileSha1 = $sftp->exec("sha1sum $tar_dir$tarfile | awk '{print $1}'");
                if ($output->isVerbose()) {
                    echo "tar is at $tar_dir$tarfile\n";
                    echo "local file hash is $localFileSha1\n";
                    echo "remote file hash is $remoteFileSha1\n";
                }
                if (trim($localFileSha1) == trim($remoteFileSha1)) {
                    echo "SHA1 for file match\n";
                    echo 'upload successful' . "\n";
                } else {
                    echo "SHA1 for file do not match.";
                    $this->exitCommand(1);
                }
            } else {
                echo 'upload seems to be successful, but SHA1 for file is not checked' . " $checksha1\n";
            }
        } else {
            if ($output->isVerbose()) {
                echo "tar is at $tar_dir$tarfile\n";
                echo "local file hash is $localFileSha1\n";
                echo "remote file hash is $remoteFileSha1\n";
            }
            echo 'check for upload did not succeed.' . "\n";
            $this->exitCommand(1);
        }
        $sftp->disconnect();
        $output->writeln('<info>Task: upload finished</info>');
        if ($output->isVerbose()) {
            echo 'tar is going to be saved ' . (($keeptar) ? 'TRUE':'FALSE') . "\n";
            echo 'path to tar ' . "$tar_dir$tarfile" . "\n";
        }
        if ($keeptar === true) {
            if ($output->isVerbose()) {
                echo "tar file is not deleted \n";
            }
        } else {
            $this->removeTar($tar_dir, $tarfile);
            if ($output->isVerbose()) {
                echo "tar file is deleted \n";
            }
        }
    }

    /**
     * @param $identity_file
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getKeyContents($identity_file)
    {
        $identity_file_content = file_get_contents($identity_file);
        return $identity_file_content;
    }

    /**
     * @param $tar_dir
     * @param $tarfile
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getSha1OfFile($tar_dir, $tarfile)
    {
        $localFileSha1 = sha1_file("$tar_dir$tarfile");
        return $localFileSha1;
    }

    /**
     * @param $tar_dir
     * @param $tarfile
     * @codeCoverageIgnore
     */
    protected function removeTar($tar_dir, $tarfile)
    {
        unlink("$tar_dir$tarfile");
    }

    /**
     * @param $identity_file
     *
     * @return bool|string
     */
    protected function readIdentityFile($identity_file)
    {
        $identity_file_content = file_get_contents($identity_file);
        return $identity_file_content;
    }
}
