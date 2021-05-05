Feature: Model Class Name Suffix
  In order to customise my Eloquent Models
  As a developer
  I want to my model class names to be generated with a custom suffix

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it generates models with suffix
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration class suffix is "<suffix>"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    And an Abstract Model ClassDefinition is generated
    Then last Model ClassDefinition has class name "<className>"
    And last Abstract Model ClassDefinition has Eloquent table property with value "<table>"

    Examples:
      |  table  |   suffix  |   className  |
      |  users  |   Model  |   UserModel  |
      |  users  | Eloquent | UserEloquent |
