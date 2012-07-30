#
# Drop search fulltext indices
#

ALTER TABLE products DROP INDEX ft_products_model;
ALTER TABLE products_description DROP INDEX ft_products_name;
ALTER TABLE products_description DROP INDEX ft_products_description;
