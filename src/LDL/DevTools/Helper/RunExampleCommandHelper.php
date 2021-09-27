<?php declare(strict_types=1);

namespace LDL\DevTools\Helper;

use LDL\DevTools\Runner\RunnerResult;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class RunExampleCommandHelper {

    public static function getCommonInputDefinition() : array
    {
        return [
            new InputArgument(
                'output-dir',
                InputArgument::OPTIONAL,
                'Output directory'
            ),
            new InputArgument(
                'diff-app',
                InputArgument::OPTIONAL,
                'Diff application',
                'diff -y --color=always'
            ),
            new InputOption(
                'accept-diff',
                'a',
                InputOption::VALUE_REQUIRED,
                'Accept differences (can be one of y (yes), n (no) or m (mark as dynamic)'
            ),
            new InputOption(
                'overwrite-output',
                'w',
                InputOption::VALUE_REQUIRED,
                'Overwrite previously generated output',
                false
            ),
            new InputOption(
                'print-output',
                'p',
                InputOption::VALUE_REQUIRED,
                'Print script output',
                0
            ),
            new InputOption(
                'interactive',
                'i',
                InputOption::VALUE_REQUIRED ,
                'Run in interactive mode',
                true
            ),
            new InputOption(
                'php-command',
                'c',
                InputOption::VALUE_REQUIRED,
                'PHP command',
                'php'
            ),
            new InputOption(
                'temp-dir',
                't',
                InputOption::VALUE_REQUIRED,
                'Temp directory',
                sys_get_temp_dir()
            )
        ];
    }

    public static function printDiffMenu(
        OutputInterface $output,
        RunnerResult $result,
        ?string $accept
    ) : string
    {
        $accept = $accept ?? '';
        $error = str_pad('Files differ:', 80, ' ');
        $output->writeln("\n\n<error>$error</error>\n");
        $output->writeln("<fg=white>{$result->getInputFile()}</>");
        $output->writeln("<fg=white>{$result->getPreviousOutputFile()}</>\n");

        while(!in_array(strtolower($accept), ['y', 'n', 'm'])){
            $output->writeln(str_repeat('-', 80));
            $output->writeln('m - mark as dynamic');
            $output->writeln('y - accept diff');
            $output->writeln('n - no action');
            $output->writeln(str_repeat('-', 80));
            $accept = ConsoleInputHelper::readInput($output->write('<info>Accept diff? y/n/m:</info>'));
        }

        $output->writeln(str_repeat('-', 80)."\n");

        switch($accept){
            case 'y':
                $result->overwriteOutput();
                break;

            case 'm':
                $result->markAsDynamic();
                break;
        }

        return $accept;
    }

}
