<?php declare(strict_types=1);

namespace LDL\DevTools\Command;

use LDL\DevTools\Helper\ConsoleInputHelper;
use LDL\DevTools\Helper\RunExampleCommandHelper;
use LDL\DevTools\Runner\RunPHPFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunExampleDirectoryCommand extends Command
{
// the name of the command (the part after "bin/console")
    protected static $defaultName = 'run:example:dir';

    protected function configure(): void
    {
        $input = RunExampleCommandHelper::getCommonInputDefinition();

        array_unshift($input, new InputArgument(
                'directory',
                InputArgument::REQUIRED,
                'Directory containing LDL examples'
            )
        );

        $this->setDefinition(new InputDefinition($input));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if(!is_dir($input->getArgument('directory'))){
            throw new \InvalidArgumentException('First argument must be a directory containing PHP files');
        }

        $dp = new \DirectoryIterator($input->getArgument('directory'));

        $diff = [];
        $failed = [];
        $dynamic = [];

        $interactive = (bool) $input->getOption('interactive');

        foreach($dp as $file) {

            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $runner = new RunPHPFile(
                $input->getOption('php-command'),
                $input->getArgument('diff-app'),
                (bool) $input->getOption('overwrite-output'),
                $input->getArgument('output-dir'),
                $input->getOption('temp-dir')
            );

            $result = $runner->run($file->getRealPath());

            if($input->getOption('print-output')){
                $title = str_pad("Output of: {$result->getInputFile()}", 80, ' ');
                $output->writeln("<fg=black;bg=green>$title</>");
                $output->writeln(file_get_contents((string) $result->getOutputFile()));
                $title = str_pad(' ', 80, ' ');
                $output->writeln("<bg=green>$title</>");
            }

            if($interactive && $input->getOption('print-output')){
                ConsoleInputHelper::readInput('Script paused, press any key to continue ...');
            }

            if(!$result->isSuccess()){
                $failed[] = $result;
                continue;
            }

            if ($result->getPreviousOutputFile() && $result->hasDiff()) {
                $diff[] = $result;
            }

            if($interactive && $result->hasDiff()){
                $result->runDiffCommand();
                $accept = RunExampleCommandHelper::printDiffMenu($output, $result, $input->getOption('accept-diff'));

                if('n' !== $accept){
                    array_pop($diff);
                }
            }

            if ($result->isDynamic()) {
                $dynamic[] = $result;
            }

            unlink((string)$result->getOutputFile());
        }

        $exit = self::SUCCESS;

        if(count($dynamic)){
            $output->write("\n");
            $output->writeln('<info>The following examples have been marked as dynamic (no diff evaluated)</info>');
            $output->writeln(str_repeat('-', 80));
            $output->write("\n");
            array_map(static function($item) use ($output){
                $output->writeln("<fg=yellow>{$item->getInputFile()}</>");
            }, $dynamic);
        }

        if(count($diff)){
            $title = str_pad('The following examples have differences!', 80, ' ');
            $output->writeln("<bg=yellow;fg=black>$title</>");
            $output->write("\n");

            array_map(static function($item) use ($output){
                $output->writeln("<fg=yellow>{$item->getInputFile()}</>");
                $output->writeln("<fg=yellow>{$item->getPreviousOutputFile()}</>");
                $output->writeln(str_repeat('-', 80));
            }, $diff);

            $exit = self::FAILURE;
        }

        if(count($failed)) {
            $error = str_pad('The following files have FAILED!', 80, ' ');
            $output->writeln("\n<error>$error</error>\n");

            array_map(static function ($item) use ($output) {
                $output->writeln("<fg=yellow>{$item->getInputFile()}</>");
                $output->writeln("<fg=yellow>{$item->getPreviousOutputFile()}</>");
                $output->writeln(str_repeat('-', 80));
            }, $failed);

            $exit = self::FAILURE;
        }

        return $exit;
    }
}