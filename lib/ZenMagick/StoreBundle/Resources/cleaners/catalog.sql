# Remove product/category data.
#
# This script will clean out *ALL* product related data except the music product
TRUNCATE TABLE categories_description;
UPDATE categories SET parent_id = null;
DELETE FROM categories;
TRUNCATE TABLE categories;
TRUNCATE TABLE featured;
TRUNCATE TABLE group_pricing;
TRUNCATE TABLE manufacturers;
TRUNCATE TABLE manufacturers_info;
TRUNCATE TABLE product_types_to_category;
TRUNCATE TABLE products_attributes;
TRUNCATE TABLE products_attributes_download;
TRUNCATE TABLE products_description;
TRUNCATE TABLE products;
TRUNCATE TABLE products_discount_quantity;
TRUNCATE TABLE products_options;
TRUNCATE TABLE products_options_values;
INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES (0, 1, 'TEXT');
TRUNCATE TABLE products_options_values_to_products_options;
TRUNCATE TABLE products_to_categories;
TRUNCATE TABLE reviews_description;
TRUNCATE TABLE reviews;
TRUNCATE TABLE salemaker_sales;
TRUNCATE TABLE specials;
