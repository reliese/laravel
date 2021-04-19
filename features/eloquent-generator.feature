Feature: Eloquent Model Generator
  In order to work with Eloquent Models with an existing database
  As a developer
  I want to my models to be generated from the Database Blueprint

  Scenario:
    Given a TableBlueprint with an autoincrementing id and string title
    And a ModelGeneratorConfiguration
    When I run fromTableBlueprint
    Then I should get an new model class
