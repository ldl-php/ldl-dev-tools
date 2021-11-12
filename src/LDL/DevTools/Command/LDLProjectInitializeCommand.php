<?php

declare(strict_types=1);

namespace LDL\DevTools\Command;

use Exception;
use LDL\DevTools\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class LDLProjectInitializeCommand extends Command
{    
    /**
     * the default command name
     * 
     * @var string|null
     */
    protected static $defaultName = 'project:init';

    /**
     * The default command description
     * 
     * @var string|null
     */
    protected static $defaultDescription = 'Initialize the cloned LDL library.';

    /**
     * command configuration
     *
     * @return void
     */
    protected function configure(): void
    {
       $this->setDefinition(
            new InputDefinition([
                new InputArgument(
                    'library-dir',
                    InputArgument::REQUIRED,
                    'Library directory to initialize.'
                )
            ])
        );
    }

    /**
     * execute the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $shellInput, OutputInterface $shellOutput): int
    {
        $LibraryDir = $shellInput->getArgument('library-dir');

        if('' === trim($LibraryDir)){
            $shellOutput->writeln("<error>Invalid library directory provided!");
            return self::FAILURE;
        }

        if(!is_dir($LibraryDir)){
            $shellOutput->writeln("<error>Destination directory {$LibraryDir} doesn't exists!");
            return self::FAILURE;
        }

        if(!is_writable($LibraryDir)){
            $shellOutput->writeln("<error>Current working directory is not writable</error>");
            return self::FAILURE;
        }

        try {
            $project =  new Project($LibraryDir);
            $project->initialize();
        } catch (Exception $e) {
            $shellOutput->writeln(sprintf('<error>%s: %s</error>', get_class($e), $e->getMessage()));
            return self::FAILURE;
        }

        $shellOutput->writeln("<bg=green>Initialized successfully.</>");

        return self::SUCCESS;
    }
}
