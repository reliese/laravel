<?php

namespace Reliese\Generator;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\ConfigurationProfile;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\PhpFileDefinition;
use Reliese\MetaCode\Tool\FilePathTool;
use function array_key_exists;

/**
 * Trait WithGetPhpFileDefinitions
 */
trait WithGetPhpFileDefinitions
{
    /** @var PhpFileDefinition[] */
    private array $phpFileDefinitions = [];

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return PhpFileDefinition
     */
    public function getPhpFileDefinition(ColumnOwnerInterface $columnOwner): PhpFileDefinition
    {
        if (array_key_exists($columnOwner->getUniqueName(), $this->phpFileDefinitions)) {
            return $this->phpFileDefinitions[$columnOwner->getUniqueName()];
        }

        $phpFileDefinition = $this->generatePhpFileDefinition($columnOwner);
        $this->phpFileDefinitions[$columnOwner->getUniqueName()] = $phpFileDefinition;

        return $phpFileDefinition;
    }

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return PhpFileDefinition
     */
    protected function generatePhpFileDefinition(ColumnOwnerInterface $columnOwner): PhpFileDefinition
    {
        $classDefinition = $this->getClassDefinition($columnOwner);

        $filePath = FilePathTool::fullyQualifiedTypeNameToFilePath(
            $this->getConfigurationProfile()->getFileSystemConfiguration()->getBasePath(),
            $classDefinition->getFullyQualifiedName()
        );

        $phpFileDefinition = new PhpFileDefinition(
            $filePath,
            $this->allowClassFileOverwrite()
        );

        $phpFileDefinition->addStatementDefinition($this->generateClassDefinition($columnOwner));

        return $phpFileDefinition;
    }

    /**
     * @return ConfigurationProfile
     */
    protected abstract function getConfigurationProfile(): ConfigurationProfile;

    /**
     * @return bool
     */
    protected abstract function allowClassFileOverwrite(): bool;

    /**
     * @param ColumnOwnerInterface $columnOwner
     *
     * @return ClassDefinition
     */
    protected abstract function getClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition;
}