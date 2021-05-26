<?php

namespace Reliese\MetaCode\Writers;

use Reliese\Configuration\RelieseConfiguration;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Format\CodeFormatter;

class CodeWriter
{
    /**
     * @var CodeFormatter
     */
    private CodeFormatter $codeFormatter;

    /**
     * @var RelieseConfiguration
     */
    private RelieseConfiguration $relieseConfiguration;

    public function __construct(RelieseConfiguration $relieseConfiguration) {
        $this->codeFormatter = new CodeFormatter($relieseConfiguration);
    }

    public function createClassDefinition(
        string $fileName,
        ClassDefinition $classDefinition,
    ) {
        if (\file_exists($fileName)) {
            return;
        }

        $this->overwriteFile(
            $fileName,
            $this->codeFormatter->getClassFormatter()->format($classDefinition)
        );
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

    protected function overwriteFile(
        $filePath,
        $fileContent,
    ) {
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            \mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $fileContent);
    }
}