Feature: Eloquent Model Generator
  In order to work with Eloquent Models with an existing database
  As a developer
  I want to my models to be generated from the Database Blueprint

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario:
    Given SchemaBlueprint "sample" has TableBlueprint "users"
    And last TableBlueprint has identity ColumnBlueprint "id"
    And last TableBlueprint has string ColumnBlueprint "title" of length "127"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last Model ClassDefinition has class name "User"
