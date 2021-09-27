<?php declare(strict_types=1);

namespace LDL\DevTools\Runner;

class RunPHPFile
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
     * @var string
     */
    private $tempDir;

    /**
     * @var string|null
     */
    private $outputDir;

    /**
     * @var bool
     */
    private $overwriteOutput;

    public function __construct(
        string $phpCommand,
        string $diffCommand,
        bool $overwriteOutput = false,
        string $outputDir=null,
        string $tempDir=null
    )
    {
        $this->phpCommand = $phpCommand;
        $this->diffCommand = $diffCommand;
        $this->overwriteOutput = $overwriteOutput;
        $this->outputDir = $outputDir;
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
    }

    public function run(string $file) : RunnerResult
    {
        if(!file_exists($file)){
            throw new \InvalidArgumentException("File: $file does not exists");
        }

        $file = new \SplFileInfo($file);

        if(null === $this->outputDir){
            $this->outputDir = sprintf('%s%s%s', $file->getPath(), \DIRECTORY_SEPARATOR, 'output');
        }

        if(!is_dir($this->outputDir) && !mkdir($this->outputDir,0755)){
            throw new \RuntimeException("Could not create output directory {$this->outputDir}");
        }

        exec("{$this->phpCommand} -f $file", $output, $return);

        $isDynamic = false;

        $previousOutputFile = sprintf(
            '%s%s%s.out',
            $this->outputDir,
            \DIRECTORY_SEPARATOR,
            substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'))
        );

        $previousOutputFileExists = file_exists($previousOutputFile);

        if(!$previousOutputFileExists || $this->overwriteOutput){
            file_put_contents($previousOutputFile, implode("\n", $output));
        }

        if($previousOutputFileExists && !$isDynamic){
            $isDynamic = (bool) preg_match('#!!!DYNAMIC#', file($previousOutputFile)[0]);
        }

        $currentOutputFile = new \SplFileInfo(sprintf(
            '%s%s%s',
            $this->tempDir,
            \DIRECTORY_SEPARATOR,
            uniqid('ldl_example', true)
        ));

        file_put_contents((string) $currentOutputFile, implode("\n", $output));

        return new RunnerResult(
            $this->phpCommand,
            $this->diffCommand,
            $file,
            $currentOutputFile,
            !$isDynamic ? new \SplFileInfo($previousOutputFile) : null,
            $isDynamic,
            0 === $return,
        );
    }
}