<?php

namespace Reliese\Generator\Resolution;

use app\DataTransport\Objects\PrimaryDatabase\NotificationGroupDto;
use Reliese\Blueprint\ColumnOwnerInterface;
use Reliese\Configuration\WithConfigurationProfile;
use Reliese\Database\WithPhpTypeMap;
use Reliese\Generator\ColumnBasedCodeGeneratorInterface;
use Reliese\Generator\DataMap\WithModelDataMapClassAccessorGenerator;
use Reliese\Generator\DataMap\WithModelDataMapClassGenerator;
use Reliese\Generator\DataTransport\WithDataTransportObjectAbstractClassGenerator;
use Reliese\Generator\DataTransport\WithDataTransportObjectClassGenerator;
use Reliese\Generator\Model\WithModelAbstractClassGenerator;
use Reliese\Generator\Model\WithModelClassGenerator;
use Reliese\Generator\WithGetClassDefinition;
use Reliese\Generator\WithGetObjectTypeDefinition;
use Reliese\Generator\WithGetPhpFileDefinitions;
use Reliese\MetaCode\Definition\ClassDefinition;
use Reliese\MetaCode\Definition\ClassMethodDefinition;
use Reliese\MetaCode\Definition\ClassPropertyDefinition;
use Reliese\MetaCode\Enum\AbstractEnum;

/**
 * Class DataTransportFieldResolutionAbstractClassGenerator
 */
class DataTransportFieldResolutionAbstractClassGenerator implements ColumnBasedCodeGeneratorInterface
{
    use WithConfigurationProfile;
    use WithGetClassDefinition;
    use WithGetObjectTypeDefinition;
    use WithGetPhpFileDefinitions;
    use WithPhpTypeMap;
    use WithModelAbstractClassGenerator;
    use WithModelClassGenerator;
    use WithModelDataMapClassGenerator;
    use WithModelDataMapClassAccessorGenerator;
    use WithDataTransportObjectAbstractClassGenerator;
    use WithDataTransportObjectClassGenerator;

    /** @var ClassPropertyDefinition[] */
    private array $generatedForeignKeyDtoPropertyDefinitions = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedRequireDtoPropertyMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedRequireValueMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedValidateDtoPropertyMethods = [];

    /**
     * @var ClassMethodDefinition[]
     */
    private array $generatedValidateValueMethods = [];

    protected function allowClassFileOverwrite(): bool
    {
        return true;
    }

    /**
     * @return string
     */
    protected function getClassNamespace(): string
    {
        return $this->getConfigurationProfile()
            ->getValidatorGeneratorConfiguration()
            ->getGeneratedClassNamespace()
            ;
    }

    /**
     * @return string
     */
    protected function getClassPrefix(): string
    {
        return $this->getConfigurationProfile()
            ->getValidatorGeneratorConfiguration()
            ->getGeneratedClassPrefix()
            ;
    }

    /**
     * @return string
     */
    protected function getClassSuffix(): string
    {
        return $this->getConfigurationProfile()
            ->getValidatorGeneratorConfiguration()
            ->getGeneratedClassSuffix()
            ;
    }

    protected function generateClassDefinition(ColumnOwnerInterface $columnOwner): ClassDefinition
    {
        $abstractClassDefinition = new ClassDefinition(
            $this->getObjectTypeDefinition($columnOwner),
            AbstractEnum::abstractEnum()
        );

        foreach ($columnOwner->)


/*
 public function resolveNotificationGroupId(NotificationSubscriberDto $notificationSubscriberDto)
 {
    $notificationSubscriberDto->setNotificationGroupDto(
        $notificationSubscriberDto->getNotificationGroupDto() ?? new NotificationGroupDto()
      );
      $notificationSubscriberDto->getNotificationGroupDto()->setId(
        $notificationSubscriberDto->getNotificationGroupDto()->getId()
        ?? $notificationSubscriberDto->getNotificationGroupId()
      );
      $this->getNotificationGroupService()->getKeyResolver()->resolveExternalKey(
        $notificationSubscriberDto->getNotificationGroupDto()
      );
      $notificationSubscriberDto->setNotificationGroupId(
        $notificationSubscriberDto->getNotificationGroupDto()->getId()
      );
}
*/

        return $abstractClassDefinition;
    }
}