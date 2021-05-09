Feature: Connection

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it adds the connection property to generated models
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And last SchemaBlueprint is on connection "<connection>"
    And ModelGeneratorConfiguration has enabled the connection property
    And a ModelGenerator is created
    When an Abstract Model ClassDefinition is generated
    Then last Abstract Model ClassDefinition has Eloquent connection property with value "<connection>"

    Examples:
      |  table  | connection |
      |  users  |    mysql   |
      |  users  |   sqlite   |
      |  users  |   testing  |
