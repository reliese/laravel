<?php

namespace Reliese\MetaCode\Definition;


use Reliese\MetaCode\Format\IndentationProvider;
use function array_unshift;
/**
 * Class PhpFileDefinition
 */
class PhpFileDefinition extends StatementDefinitionCollection implements StatementDefinitionInterface
{
    protected string $filePath;

    private bool $overwriteExistingFile;

    /**
     * PhpFileDefinition constructor.
     *
     * @param string $filePath
     * @param bool   $overwriteExistingFile
     */
    public function __construct(string $filePath, bool $overwriteExistingFile)
    {
        $this->filePath = $filePath;
        $this->overwriteExistingFile = $overwriteExistingFile;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @return bool
     */
    public function getOverwriteExistingFile(): bool
    {
        return $this->overwriteExistingFile;
    }

    public function toPhpCode(IndentationProvider $indentationProvider): string
    {
        return "<?php\n\n".parent::toPhpCode($indentationProvider);
    }
}