Feature: Add traits

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it adds traits to generated models
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration uses trait "<trait>"
    And a ModelGenerator is created
    When an Abstract Model ClassDefinition is generated
    Then last Abstract Model ClassDefinition has trait "<trait>"

    Examples:
      |  table  |     trait   |
      |  users  | \Some\Trait |
      |  users  |    \Trait   |
      |  users  |    Trait    |
