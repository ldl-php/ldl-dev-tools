<?php

namespace LDL\DevTools;

use Exception;
use LDL\File\File;
use LDL\File\Directory;
use LDL\DevTools\Exceptions\InstallException;
use LDL\DevTools\Exceptions\RunTimeException;
use LDL\DevTools\Exceptions\NotWritableException;
use LDL\DevTools\Helper\CommandHelper as Command;
use LDL\DevTools\Exceptions\TemplateCloneException;

final class Project
{
    /**
     * project path
     *
     * @var string
     */
    protected $path;

    /**
     * is new project flag
     *
     * @var bool
     */
    protected $newProject = false;

    /**
     * composer path
     *
     * @var string
     */
    protected $composerPath;

    /**
     * pre-commit hook library path
     *
     * @var string
     */
    protected $preCommitHookLibraryPath;

    /**
     * preCommitHookTemplatePath
     *
     * @var string
     */
    protected $preCommitHookTemplatePath;

    /**
     * __construct
     *
     * @param  string $path
     * @return void
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $this->composerPath = 'composer';
        $this->preCommitHookTemplatePath = __DIR__ . '/Storage/Git/Hooks/pre-commit';
        $this->preCommitHookLibraryPath = $this->path . '/.git/hooks/pre-commit';
    }

    /**
     * create project
     *
     * @return void
     */
    public function create(): void
    {
        $this->newProject = true;
        $this->cloneTemplateRepo();
        $this->_initialize();
    }

    /**
     * initialize the project
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->_initialize();
    }

    /**
     * install project
     *
     * @return void
     */
    private function _initialize(): void
    {
        $this->installComposer();
        $this->installCsFixer();
        $this->addPreCommitHook();

        $gitIgnore = '.php-cs-fixer.cache';
        if ($this->newProject) {
            $gitIgnore .= PHP_EOL . 'vendor';
        }

        $this->modifyOrAddGitIgnore($gitIgnore);
    }

    /**
     * clone temlate repo
     *
     * @return void
     */
    private function cloneTemplateRepo()
    {
        $pathChunks = explode('/', $this->path);

        $library = end($pathChunks);

        //clone ldl-project-template and rename to appropriate directory
        $command = Command::run('git clone https://github.com/pthreat/ldl-project-template.git ' . $library);

        if ($command->failed) {
            throw new TemplateCloneException($command->error);
        }
        
        // delete .git folder of template
        (new Directory($this->path . '/.git'))->delete();
    }

    /**
     * install cs-fixer
     *
     * @return void
     */
    private function installCsFixer(): void
    {
        $command = Command::run(
            sprintf(
                'cd %s %s && %s require friendsofphp/php-cs-fixer -q',
                $this->path,
                $this->newProject ? '&& git init' : '',
                $this->composerPath
            )
        );

        if ($command->failed) {
            throw new InstallException($command->error);
        }
    }

    /**
     * add pre-commit hook
     *
     * @return void
     */
    private function addPreCommitHook(): void
    {
        copy($this->preCommitHookTemplatePath, $this->preCommitHookLibraryPath);

        // give valid permission to hooks for the execution rwx for user and groups and rx for public
        chmod($this->preCommitHookLibraryPath, 0775);
    }

    /**
     * check or install composer
     *
     * @return void
     */
    private function installComposer(): void
    {
        $command = Command::run('composer');

        $ldlDevToolsComposerPath = __DIR__ . '/../../../bin/composer.phar';

        //skip installation if composer is installed globally or at ldl-dev-tools/bin
        if (!$command->failed || is_file($ldlDevToolsComposerPath)) {
            return;
        }

        $this->composerPath = __DIR__ . '/../../../bin';

        if (!is_writable($this->composerPath)) {
            throw new NotWritableException('ldl-dev-tools/bin directory is not writable');
        }

        $composerSetupPath = $this->composerPath . DIRECTORY_SEPARATOR . 'composer-setup.php';

        $expectedChecksum = file_get_contents('https://composer.github.io/installer.sig');

        copy('https://getcomposer.org/installer', $composerSetupPath);

        $actualChecksum = hash_file('sha384', $composerSetupPath);

        if ($expectedChecksum != $actualChecksum) {
            throw new RunTimeException('Composer not found and installer is corrupt.');
        }

        $command = Command::run(
            sprintf(
                'php %s --install-dir=%s',
                $composerSetupPath,
                $this->composerPath
            )
        );

        if ($command->failed) {
            throw new RunTimeException('Failed to install composer.' . $command->error);
        }

        $this->composerPath = $ldlDevToolsComposerPath;

        unlink($composerSetupPath);
    }

    /**
     * add or update .gitignore
     *
     * @param  string $content
     * @return void
     */
    private function modifyOrAddGitIgnore($content): void
    {
        $path = $this->path . DIRECTORY_SEPARATOR . '.gitignore';

        $fileExists = is_file($path);

        if ($fileExists && !is_writable($this->path . DIRECTORY_SEPARATOR . '.gitignore')) {
            throw new NotWritableException('ldl-dev-tools/bin directory is not writable');
        }
        
        $fileExists ? (new File($path))->append(PHP_EOL . $content) : File::create($path, $content, 0664);
    }
}
