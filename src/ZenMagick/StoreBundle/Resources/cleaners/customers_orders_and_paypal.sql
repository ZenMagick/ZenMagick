# 
# Remove customer order and payment data
#
# This script will clean out *ALL* customer order and paypal related data
#

TRUNCATE TABLE address_book;
TRUNCATE TABLE customers;
TRUNCATE TABLE customers_info;
TRUNCATE TABLE orders;
TRUNCATE TABLE orders_products;
TRUNCATE TABLE orders_products_attributes;
TRUNCATE TABLE orders_products_download;
TRUNCATE TABLE orders_status_history;
TRUNCATE TABLE orders_total;
TRUNCATE TABLE sessions;

TRUNCATE TABLE zen_paypal;
TRUNCATE TABLE zen_paypal_payment_status;
TRUNCATE TABLE zen_paypal_payment_status_history;
TRUNCATE TABLE zen_paypal_session;
TRUNCATE TABLE zen_paypal_testing;
