<?php

declare(strict_types=1);

namespace LDL\DevTools\Command;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class LDLSubProjectCommand extends Command
{    
    /**
     * current working directory path
     * 
     * @var string|null
     */
    protected $currentWorkingDirector;
    
    /**
     * the default command name
     * 
     * @var string|null
     */
    protected static $defaultName = 'create:project';

    /**
     * The default command description
     * 
     * @var string|null
     */
    protected static $defaultDescription = 'Pulls in the ldl-project-template repo to create a new library.';

    /**
     * command configuration
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->currentWorkingDirectory = getcwd();

        if (!$this->currentWorkingDirectory) {
            throw new Exception("Invalid directory. Please check the permissions.");
        }

        $this->setDefinition(
            new InputDefinition([
                new InputArgument(
                    'library',
                    InputArgument::REQUIRED,
                    'Library name with ldl prefix.'
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

        $library = $shellInput->getArgument('library');
        
        $this->validateLibrary($library);

        $exitCode = self::SUCCESS;

        $libraryPath = $this->currentWorkingDirectory . '/' .$library;

        //clone ldl-project-template and rename to appropriate directory
        exec("git clone https://github.com/pthreat/ldl-project-template.git {$library} 2>&1 >/dev/null", $output, $resultCode);

        if ($resultCode > 0) {
            $shellOutput->writeln("<bg=red>" . @$output[0] . "</>");
            $exitCode = self::FAILURE;
        } else {            
            // delete .git folder of template (replace this with proper directory function)
            exec("rm -rf {$libraryPath}/.git 2>&1 >/dev/null");

            $output = [];

            /**
             * initialize git folder
             * install php cs fixer in quite mode
             */
            exec("cd {$libraryPath} && git init && composer require friendsofphp/php-cs-fixer -q 2>&1 >/dev/null", $output, $resultCode);

            if ($resultCode > 0) {
                $shellOutput->writeln("<bg=red>" . @$output[0] . "</>");
                $exitCode = self::FAILURE;
            } else {
                // copy precommit git hook to library git hooks
                copy(__DIR__ . "/../Storage/Git/Hooks/pre-commit", "{$libraryPath}/.git/hooks/pre-commit");
    
                // give valid permission to hooks for the execution
                exec("cd {$libraryPath} && chmod ug+x .git/hooks/* 2>&1 >/dev/null");
            }
        }

        if($exitCode == self::SUCCESS) {
            $shellOutput->writeln("<bg=green>Library created successfully.</>");
        } else {
            // delete library on failure (replace this with proper directory function)
            exec("rm -rf {$libraryPath} 2>&1 >/dev/null");

            $shellOutput->writeln("<bg=red>Failed to create library.</>");
        }

        return $exitCode;
    }

    /**
     * check if library already exists and directory is valid
     *
     * @param  string $dir
     * @return void
     */
    public function validateLibrary($library): void
    {
        if (!$library) {
            throw new InvalidArgumentException('Invalid directory provided.');
        } elseif (is_dir($this->currentWorkingDirectory . '/' . $library)) {
            throw new Exception('Library already exists.');
        }
    }
}
