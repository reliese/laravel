<?php

namespace Reliese\MetaCode\Writers;

use Reliese\MetaCode\Definition\CodeDefinitionInterface\CodeDefinitionInterface;
use Reliese\MetaCode\Format\ClassFormatter;

class CodeWriter
{
    private function writeClassFiles(
        ClassFormatter $classFormatter,
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