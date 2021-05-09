Feature: Verify blueprints can be constructed for test cases

  Scenario: Table Has FK
    Given a new DatabaseBlueprint

    And the DatabaseBlueprint has SchemaBlueprint "products"
    And SchemaBlueprint "products" has TableBlueprint "products"
    And Schema "products" Table "products" has int identity column "id"

    And SchemaBlueprint "products" has TableBlueprint "product_versions"
    And Schema "products" Table "product_versions" has int identity column "id"
    And Schema "products" Table "product_versions" has int column "product_id"
    And Schema "products" Table "product_versions" has string column "version" of length "64"

    And Schema "products" Table "product_versions" Columns "product_id" reference Schema "products" Table "products" Columns "id" as foreign key name "fk_product_version_product"

    And the DatabaseBlueprint has SchemaBlueprint "invoicing"
    And SchemaBlueprint "invoicing" has TableBlueprint "invoice_items"
    And Schema "invoicing" Table "invoice_items" has int identity column "id"
    And Schema "invoicing" Table "invoice_items" has int column "product_version_id"

    And Schema "invoicing" Table "invoice_items" Columns "product_version_id" reference Schema "products" Table "product_versions" Columns "id" as foreign key name "fk_invoice_item_product_version"

