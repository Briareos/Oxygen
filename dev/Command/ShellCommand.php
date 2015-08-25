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
        // Prevent PHP notice.
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        define('DRUPAL_ROOT', __DIR__.'/../../../../../..');

        require_once DRUPAL_ROOT.'/includes/bootstrap.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

        $this->getApplication()->setCatchExceptions(false);
        $boris = new Boris();

        $boris->setPrompt('oxygen> ');

        $boris->start();
    }
}
