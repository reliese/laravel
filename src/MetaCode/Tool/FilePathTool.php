<?php

namespace Reliese\MetaCode\Tool;

use function explode;
use function implode;
use function str_replace;
use const DIRECTORY_SEPARATOR;
/**
 * Class FilePathTool
 */
abstract class FilePathTool
{

    public static function fullyQualifiedTypeNameToFilePath(
        string $basePath,
        string $fullyQualifiedTypeName
    ): string {

        $isRootPath = false;
        $filePathParts = [];

        if (!empty($basePath)) {
            if (DIRECTORY_SEPARATOR === $basePath[0]) {
                $isRootPath = true;
            }
            foreach (explode(DIRECTORY_SEPARATOR, $basePath) as $part) {
                if (empty($part)) {
                    // this handles any repeated / characters
                    continue;
                }
                $filePathParts[] = $part;
            }
        }

        foreach (explode('\\', $fullyQualifiedTypeName) as $namespacePart) {
            if (empty($namespacePart)) {
                // this handles any repeated \ characters
                continue;
            }
            $filePathParts[] = $namespacePart;
        }

        $i = -1;
        foreach ($filePathParts as $part) {
            $i++;
        }

        $filePath = implode(DIRECTORY_SEPARATOR,$filePathParts).'.php';
        if ($isRootPath) {
            $filePath = DIRECTORY_SEPARATOR.$filePath;
        }

        return $filePath;
    }
}