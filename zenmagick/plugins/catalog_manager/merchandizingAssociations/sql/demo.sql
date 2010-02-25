#*******************************************************************************
#
# ZenMagick merchandizing associations SQL demo data
#
# $Id$
#
#*******************************************************************************



INSERT INTO product_associations(association_type, source_product_id, target_product_id, start_date) 
  VALUES ('xsell', 19, 7, now());
INSERT INTO product_associations(association_type, source_product_id, target_product_id, start_date) 
  VALUES ('xsell', 19, 6, now());
