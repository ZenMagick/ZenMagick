# 
# Remove user and product/category data
#
# This script will clean out *ALL* product related data plus the the demo customer
#
# $Id$
#

TRUNCATE TABLE address_book;
TRUNCATE TABLE categories;
TRUNCATE TABLE categories_description;
TRUNCATE TABLE customers;
TRUNCATE TABLE customers_info;
TRUNCATE TABLE featured;
TRUNCATE TABLE group_pricing;
TRUNCATE TABLE manufacturers;
TRUNCATE TABLE manufacturers_info;
TRUNCATE TABLE media_clips;
TRUNCATE TABLE media_manager;
TRUNCATE TABLE media_to_products;
TRUNCATE TABLE media_types;
TRUNCATE TABLE music_genre;
TRUNCATE TABLE product_music_extra;
TRUNCATE TABLE product_types_to_category;
TRUNCATE TABLE products;
TRUNCATE TABLE products_attributes;
TRUNCATE TABLE products_attributes_download;
TRUNCATE TABLE products_description;
TRUNCATE TABLE products_discount_quantity;
TRUNCATE TABLE products_options;
TRUNCATE TABLE products_options_values;
INSERT INTO products_options_values (products_options_values_id, language_id, products_options_values_name) VALUES (0, 1, 'TEXT');
TRUNCATE TABLE products_options_values_to_products_options;
TRUNCATE TABLE products_to_categories;
TRUNCATE TABLE record_artists;
TRUNCATE TABLE record_artists_info;
TRUNCATE TABLE record_company;
TRUNCATE TABLE record_company_info;
TRUNCATE TABLE reviews;
TRUNCATE TABLE reviews_description;
TRUNCATE TABLE salemaker_sales;
TRUNCATE TABLE specials;
