<?php

namespace Reliese\MetaCode\Writers;

use Reliese\Configuration\ConfigurationProfile;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\PhpFileDefinition;
use Reliese\MetaCode\Format\CodeFormatter;
use Symfony\Component\Console\Output\OutputInterface;
use function file_exists;

class CodeWriter
{
    /**
     * @var CodeFormatter
     */
    protected CodeFormatter $codeFormatter;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * CodeWriter constructor.
     *
     * @param CodeFormatter   $codeFormatter
     * @param OutputInterface $output
     */
    public function __construct(CodeFormatter $codeFormatter, OutputInterface $output)
    {
        $this->codeFormatter = $codeFormatter;
        $this->output = $output;
    }

    public function overwriteClassDefinition(
        string $fileName,
        ClassDefinition $classDefinition,
    ) {
        $this->overwriteFile(
            $fileName,
            $this->codeFormatter->getClassFormatter()->format($classDefinition)
        );
    }

    public function writePhpFile(PhpFileDefinition $phpFileDefinition)
    {
        $filePath = $phpFileDefinition->getFilePath();

        if (!file_exists($filePath) || $phpFileDefinition->getOverwriteExistingFile()) {
            $phpCode = $this->codeFormatter->formatStatementCollection($phpFileDefinition);
            $this->overwriteFile($filePath, $phpCode);
        } else {
            $this->output->write("Existing file not modified: \"$filePath\"");
        }
    }

    protected function overwriteFile(
        $filePath,
        $fileContent,
    ) {
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            \mkdir($directory, 0755, true);
        }

        if (false === file_put_contents($filePath, $fileContent)) {
            $this->output->write("ERROR: Failed to write file: \"$filePath\"");
        } else {
            $this->output->write("Generated file: \"$filePath\"");
        }
    }
}