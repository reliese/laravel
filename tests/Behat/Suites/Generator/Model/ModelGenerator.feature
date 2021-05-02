Feature: Check public methods from ModelGenerator

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario: Test model generator class directory matches configuration
    Given ModelGeneratorConfiguration directory is "x"
    When a ModelGenerator is created
    Then ModelGenerator class directory is "x"