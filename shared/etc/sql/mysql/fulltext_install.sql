#
# Create fulltext indices for search
#

ALTER TABLE products ENGINE = MYISAM;
CREATE FULLTEXT INDEX ft_products_model ON products (products_model);

ALTER TABLE products_description ENGINE = MYISAM;
CREATE FULLTEXT INDEX ft_products_name ON products_description (products_name);
CREATE FULLTEXT INDEX ft_products_description ON products_description (products_description);
