<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SetupJenkinsJobCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Setup Jenkins job on server.</info>';

        $this->setName("jenkins:setup")
        ->setDescription("Setup Jenkins job")
        ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $foo = $this->configuration->remoteEnvironmentSshPort();
        $xmlFile = file_get_contents('config.xml');
        $xml = simplexml_load_string($xmlFile);


        $json = new JsonEncoder();
        $json->encode($xmlFile, 'json');

        var_dump($json);




        // var_dump($serializer);
       // echo json_encode($json);

    //    $output = new ConsoleOutput();


    }
}
