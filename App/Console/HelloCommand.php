<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Raichu\Engine\App;

class HelloCommand extends Command
{
    protected function configure()
    {
        $this->setName('hello:world')->setDescription('雷丘');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return 'Hello World';
    }
}