<?php

namespace Reliese\Generator\DataAccess;

use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataAccess\DataAccessAbstractClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
/**
 * Class DataAccessClassGenerator
 */
class DataAccessClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;

    /**
     * @var DataAccessAbstractClassGenerator
     */
    protected DataAccessAbstractClassGenerator $abstractClassGenerator;

    /**
     * DataAccessClassGenerator constructor.
     *
     * @param DataAccessAbstractClassGenerator $abstractClassGenerator
     */
    public function __construct(DataAccessAbstractClassGenerator $abstractClassGenerator)
    {
        $this->abstractClassGenerator = $abstractClassGenerator;
    }

    protected function allowClassFileOverwrite(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getClassNamespace();
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getClassPrefix();
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()->getDataAccessGeneratorConfiguration()
            ->getClassSuffix();
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $classDefinition = new ClassDefinition($this->getObjectTypeDefinition($columnOwner));

        $classDefinition
            ->setOriginatingBlueprint($columnOwner)
            ->setParentClass(
                $this->abstractClassGenerator->getObjectTypeDefinition($columnOwner)
            )
        ;

        return $classDefinition;
    }
}