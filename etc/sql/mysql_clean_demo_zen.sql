# 
# Remove demo data
#
# This script will clean out *ALL* product related data plus the the demo customer
#

UPDATE zen_configuration SET configuration_value='false' WHERE configuration_key='DOWNLOAD_ENABLED';
DELETE FROM zen_address_book where customers_id = 1;
TRUNCATE TABLE zen_categories;
TRUNCATE TABLE zen_categories_description;
DELETE FROM zen_customers WHERE customers_id = 1;
DELETE FROM zen_customers_info WHERE customers_info_id = 1;
ALTER TABLE zen_customers AUTO_INCREMENT = 1;
DELETE FROM zen_ezpages where pages_id in (1,2,3,4,5,6,7,8,9,10,11,12,13,14);
TRUNCATE TABLE zen_featured;
TRUNCATE TABLE zen_group_pricing;
TRUNCATE TABLE zen_manufacturers;
TRUNCATE TABLE zen_manufacturers_info;
TRUNCATE TABLE zen_media_clips;
TRUNCATE TABLE zen_media_manager;
TRUNCATE TABLE zen_media_to_products;
TRUNCATE TABLE zen_media_types;
TRUNCATE TABLE zen_music_genre;
TRUNCATE TABLE zen_product_music_extra;
TRUNCATE TABLE zen_product_types_to_category;
TRUNCATE TABLE zen_products;
TRUNCATE TABLE zen_products_attributes;
TRUNCATE TABLE zen_products_attributes_download;
TRUNCATE TABLE zen_products_description;
TRUNCATE TABLE zen_products_discount_quantity;
TRUNCATE TABLE zen_products_options;
TRUNCATE TABLE zen_products_options_values;
INSERT INTO zen_products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES (0, 1, 'TEXT');
TRUNCATE TABLE zen_products_options_values_to_products_options;
TRUNCATE TABLE zen_products_to_categories;
TRUNCATE TABLE zen_record_artists;
TRUNCATE TABLE zen_record_artists_info;
TRUNCATE TABLE zen_record_company;
TRUNCATE TABLE zen_record_company_info;
TRUNCATE TABLE zen_reviews;
TRUNCATE TABLE zen_reviews_description;
TRUNCATE TABLE zen_salemaker_sales;
TRUNCATE TABLE zen_specials;
