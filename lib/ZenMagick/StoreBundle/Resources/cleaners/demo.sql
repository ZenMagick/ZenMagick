# 
# Remove demo data
#
# This script will clean out *ALL* product related data plus the the demo customer
#

UPDATE configuration SET configuration_value='false' WHERE configuration_key='DOWNLOAD_ENABLED';
DELETE FROM address_book where customers_id = 1;
TRUNCATE TABLE categories_description;
UPDATE categories SET parent_id = NULL;
TRUNCATE TABLE categories;
DELETE FROM customers WHERE customers_id = 1;
DELETE FROM customers_info WHERE customers_info_id = 1;
ALTER TABLE customers AUTO_INCREMENT = 1;
DELETE FROM ezpages where pages_id in (1,2,3,4,5,6,7,8,9,10,11,12,13,14);
TRUNCATE TABLE featured;
TRUNCATE TABLE group_pricing;
TRUNCATE TABLE manufacturers;
TRUNCATE TABLE manufacturers_info;
# @todo demo music command removal? or always include these entities
#TRUNCATE TABLE media_clips;
#TRUNCATE TABLE media_manager;
#TRUNCATE TABLE media_to_products;
#TRUNCATE TABLE media_types;
#TRUNCATE TABLE music_genre;
#TRUNCATE TABLE product_music_extra;
#TRUNCATE TABLE product_types_to_category;
TRUNCATE TABLE products_description;
TRUNCATE TABLE products;
TRUNCATE TABLE products_attributes;
TRUNCATE TABLE products_attributes_download;
TRUNCATE TABLE products_discount_quantity;
TRUNCATE TABLE products_options;
TRUNCATE TABLE products_options_values;
INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES (0, 1, 'TEXT');
TRUNCATE TABLE products_options_values_to_products_options;
TRUNCATE TABLE products_to_categories;
#TRUNCATE TABLE record_artists;
#TRUNCATE TABLE record_artists_info;
#TRUNCATE TABLE record_company;
#TRUNCATE TABLE record_company_info;
TRUNCATE TABLE reviews_description;
TRUNCATE TABLE reviews;
TRUNCATE TABLE salemaker_sales;
TRUNCATE TABLE specials;
