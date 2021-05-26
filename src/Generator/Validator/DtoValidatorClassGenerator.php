<?php

namespace Reliese\Generator\Validator;

use Reliese\Blueprint\TableBlueprint;
use Reliese\Generator\ClassGeneratorInterface;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ObjectTypeDefinition;
/**
 * Class DtoValidatorClassGenerator
 */
class DtoValidatorClassGenerator implements ClassGeneratorInterface
{
    /**
     * @var ClassDefinition
     */
    private ClassDefinition $abstractClassDefinition;

    public function __construct(
        ClassDefinition $abstractClassDefinition,
    ) {
        $this->abstractClassDefinition = $abstractClassDefinition;
    }

    public function getClassDefinition(): ClassDefinition
    {
        $this->classDefinition = $this->generateClass();
    }

    protected function generateClass(): ClassDefinition
    {
        $classDefinition = new ClassDefinition(
            $this->getClassName($tableBlueprint),
            $this->getClassNamespace($tableBlueprint)
        );

        $classDefinition->setParentClass(
            $this->getFullyQualifiedAbstractClassName($tableBlueprint)
        );

        return $classDefinition;
    }

    public function getObjectTypeDefinition(): ObjectTypeDefinition
    {
        // TODO: Implement getObjectTypeDefinition() method.
        throw new \Exception(__METHOD__ . " has not been implemented.");
    }
}