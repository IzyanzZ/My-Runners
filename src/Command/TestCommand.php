<?php

namespace App\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    protected function configure()
    {
        $this->setName('run')
            ->setDescription('Shows current date and time')
            ->setHelp('This command prints the current date and time')
            ->addArgument('type', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('<fg=green>                                    
_|_|_|                                                      
_|    _|  _|    _|  _|_|_|    _|_|_|      _|_|    _|  _|_|  
_|_|_|    _|    _|  _|    _|  _|    _|  _|_|_|_|  _|_|      
_|    _|  _|    _|  _|    _|  _|    _|  _|        _|        
_|    _|    _|_|_|  _|    _|  _|    _|    _|_|_|  _|        
');
        $output->writeln("");
        $output->writeln("<fg=default>Loading...");

        $type = $input->getArgument('type');

        $commands = [
            "echo \"Hello\""
        ];

        if ($type === "dev") {
            $this->runCommands($commands, $input, $output);
        } else {
            $output->writeln("<fg=red>Type of Run Is <fg=default><dev>");
        }

        return Command::SUCCESS;
    }

    protected function runCommands($commands, InputInterface $input, OutputInterface $output, array $env = [])
    {
        if (!$output->isDecorated()) {
            $commands = array_map(function ($value) {
                if (substr($value, 0, 5) === 'chmod') {
                    return $value;
                }

                return $value . ' --no-ansi';
            }, $commands);
        }

        if ($input->getOption('quiet')) {
            $commands = array_map(function ($value) {
                if (substr($value, 0, 5) === 'chmod') {
                    return $value;
                }

                return $value . ' --quiet';
            }, $commands);
        }

        $process = Process::fromShellCommandline(implode(' && ', $commands), null, $env, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $output->writeln('Warning: ' . $e->getMessage());
            }
        }

        $process->run(function ($type, $line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }
}
