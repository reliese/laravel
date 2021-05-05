Feature: Parent Class Prefix

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it generates abstract models with given prefix
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration abstract class prefix is "<prefix>"
    And a ModelGenerator is created
    When an Abstract Model ClassDefinition is generated
    Then last Abstract Model ClassDefinition class name is "<className>"

    Examples:
      |  table  |      prefix     |    className   |
      |  users  |                |      User      |
      |  users  |    Abstract    |  AbstractUser  |
