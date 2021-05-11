<?php

namespace Reliese\MetaCode\Writers;

use Reliese\MetaCode\Definition\CodeDefinitionInterface;

class CodeWriter
{
    public function createClassDefinition(
        string $fileName,
        string $classSourceCode,
    ) {
        if (\file_exists($fileName)) {
            return;
        }

        $this->overwriteClassDefinition($fileName, $classSourceCode);
    }

    public function overwriteClassDefinition(
        string $fileName,
        string $classSourceCode,
    ) {
        $directory = dirname($fileName);

        if (!is_dir($directory)) {
            \mkdir($directory, 0755, true);
        }

        file_put_contents($fileName, $classSourceCode);
    }

    private function writeClassFiles(
        CodeDefinitionInterface $codeDefinition,
        bool $overrideExisting
    ): void
    {
        if (!is_dir($codeDefinition->getDirectory())) {
            mkdir($codeDefinition->getDirectory(), 0777, true);
        }

        $filePath = $codeDefinition->getFilePath();

        if (!$overrideExisting && file_exists($filePath)) {
            // TODO: Log skipping
            return;
        }

        file_put_contents($filePath, $classFormatter->format($codeDefinition));
    }
}