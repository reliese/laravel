<?php

namespace Reliese\Configuration;

/**
 * Interface GeneratorConfigurationInterface
 */
interface ClassGeneratorConfigurationInterface
{
    function getClassNamespace(): string;

    function getClassPrefix(): string;

    function getClassSuffix(): string;

    function getGeneratedClassPrefix(): string;

    function getGeneratedClassSuffix(): string;

    function getGeneratedClassNamespace(): string;
}