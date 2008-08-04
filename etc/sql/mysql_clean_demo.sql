# 
# Remove demo data
#
# This script will clean out *ALL* product related data plus the the demo customer
#
# $Id$
#

UPDATE configuration SET configuration_value='false' WHERE configuration_key='DOWNLOAD_ENABLED';
DELETE FROM address_book where customers_id = 1;
DELETE FROM categories;
DELETE FROM categories_description;
DELETE FROM customers WHERE customers_id = 1;
DELETE FROM customers_info WHERE customers_info_id = 1;
DELETE FROM ezpages where pages_id in (1,2,3,4,5,6,7,8,9,10,11,12,13,14);
DELETE FROM featured;
DELETE FROM group_pricing;
DELETE FROM manufacturers;
DELETE FROM manufacturers_info;
DELETE FROM media_clips;
DELETE FROM media_manager;
DELETE FROM media_to_products;
DELETE FROM media_types;
DELETE FROM music_genre;
DELETE FROM product_music_extra;
DELETE FROM product_types_to_category;
DELETE FROM products;
DELETE FROM products_attributes;
DELETE FROM products_attributes_download;
DELETE FROM products_description;
DELETE FROM products_discount_quantity;
DELETE FROM products_options;
DELETE FROM products_options_values;
DELETE FROM products_options_values_to_products_options;
DELETE FROM products_to_categories;
DELETE FROM record_artists;
DELETE FROM record_artists_info;
DELETE FROM record_company;
DELETE FROM record_company_info;
DELETE FROM reviews;
DELETE FROM reviews_description;
DELETE FROM salemaker_sales;
DELETE FROM specials;
