<?php

namespace Oxygen\Dev\Command;

use Boris\Boris;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShellCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('shell')
            ->setDescription("Opens PHP REPL inside the module context.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        require_once __DIR__.'/../../autoload.php';

        $this->getApplication()->setCatchExceptions(false);
        $boris = new Boris();

        $boris->setPrompt('oxygen> ');

        $boris->start();
    }
}
