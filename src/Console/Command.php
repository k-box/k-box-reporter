<?php

namespace KBox\Statistics\Console;

use TightenCo\Jigsaw\Console\ConsoleSession;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class Command extends SymfonyCommand
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->console = new ConsoleSession(
            $this->input,
            $this->output,
            $this->getHelper('question')
        );

        return (int) $this->fire();
    }

    abstract protected function fire();
}
