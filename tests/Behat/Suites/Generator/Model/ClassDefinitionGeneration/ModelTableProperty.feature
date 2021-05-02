Feature: Model Table Property
  In order to let the ORM know which table it references
  As a developer
  I want to my model to fill its table property when needed

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario: Adds table property when suffix is enabled
    Given SchemaBlueprint "sample" has TableBlueprint "users"
    And ModelGeneratorConfiguration class suffix is "Model"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last AbstractClassDefinition has Eloquent table property with value "users"

  Scenario: Hides table property when suffix is absent
    Given SchemaBlueprint "sample" has TableBlueprint "users"
    And ModelGeneratorConfiguration class suffix is ""
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last AbstractClassDefinition must not have Eloquent table property
