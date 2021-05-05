Feature: Model Namespace
  In order to give my models a namespace
  As a developer
  I want to set the namespace

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it generates models with given namespace
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration namespace is "<namespace>"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last Model ClassDefinition namespace is "<expected>"

    Examples:
      |  table  |   namespace    |    expected    |
      |  users  |   App\Models   |   App\Models   |
      |  users  |   \App\Models  |   App\Models   |
