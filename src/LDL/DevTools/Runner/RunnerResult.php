<?php declare(strict_types=1);

namespace LDL\DevTools\Runner;

class RunnerResult
{
    /**
     * @var string
     */
    private $phpCommand;

    /**
     * @var string
     */
    private $diffCommand;

    /**
     * @var \SplFileInfo
     */
    private $inputFile;

    /**
     * @var \SplFileInfo
     */
    private $currentOutputFile;

    /**
     * @var \SplFileInfo|null
     */
    private $previousOutputFile;

    /**
     * @var bool
     */
    private $isDynamic;

    /**
     * @var bool
     */
    private $success;

    public function __construct(
        string $phpCommand,
        string $diffCommand,
        \SplFileInfo $inputFile,
        \SplFileInfo $currentOutputFile,
        ?\SplFileInfo $previousOutputFile,
        bool $isDynamic,
        bool $success
    )
    {
        $this->phpCommand = $phpCommand;
        $this->diffCommand = $diffCommand;
        $this->inputFile = $inputFile;
        $this->currentOutputFile = $currentOutputFile;
        $this->previousOutputFile = $previousOutputFile;
        $this->isDynamic = $isDynamic;
        $this->success = $success;
    }

    public function isDynamic() : bool
    {
        return $this->isDynamic;
    }

    public function hasDiff() : bool
    {
        if(null === $this->previousOutputFile){
            return false;
        }

        return file_get_contents((string) $this->previousOutputFile) !== file_get_contents((string) $this->currentOutputFile);
    }

    public function getInputFile() : \SplFileInfo
    {
        return $this->inputFile;
    }

    public function isSuccess() : bool
    {
        return $this->success;
    }

    public function getOutputFile() : \SplFileInfo
    {
        return $this->currentOutputFile;
    }

    public function getPreviousOutputFile() : ?\SplFileInfo
    {
        return $this->previousOutputFile;
    }

    public function runFile(array &$output) : int
    {
        exec("{$this->phpCommand} {$this->inputFile}", $output, $result);
        return (int) $result;
    }

    public function runDiffCommand() : void
    {
        passthru("{$this->diffCommand} {$this->getOutputFile()} {$this->getPreviousOutputFile()}");
    }

    public function overwriteOutput() : int
    {
        return file_put_contents(
            (string)$this->getPreviousOutputFile(),
            file_get_contents((string) $this->getOutputFile())
        );
    }

    public function markAsDynamic() : void
    {
        $contents = file((string)$this->getPreviousOutputFile());
        array_unshift($contents, '!!!DYNAMIC');
        file_put_contents((string)$this->getPreviousOutputFile(), implode("\n", $contents));
        $this->isDynamic = true;
    }

}