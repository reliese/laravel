Feature: Model Path
  In order to place my models on a directory
  As a developer
  I want to configure the output path

  Background:
    Given a default ModelGeneratorConfiguration
    And a default DatabaseBlueprintConfiguration
    And a new DatabaseBlueprint
    And the DatabaseBlueprint has SchemaBlueprint "sample"

  Scenario Outline: it generates models on given directory
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration directory is "<directory>"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last Model ClassDefinition file path is "<filePath>"

    Examples:
      |  table  |     directory   |          filePath         |
      |  users  | /opt/app/models | /opt/app/models/User.php |

  Scenario Outline: it generates file path with suffix on given directory
    Given SchemaBlueprint "sample" has TableBlueprint "<table>"
    And ModelGeneratorConfiguration directory is "<directory>"
    And ModelGeneratorConfiguration class suffix is "<suffix>"
    And a ModelGenerator is created
    When a Model ClassDefinition is generated
    Then last Model ClassDefinition file path is "<filePath>"

    Examples:
      |  table  |  suffix  |     directory   |            filePath            |
      |  users  |  Model  | /opt/app/models | /opt/app/models/UserModel.php |
