#
# ZenMagick features SQL - undo
#
# $Id$
#

## upgrade cleanup
DROP TABLE IF EXISTS feature_types;
DROP TABLE IF EXISTS zen_feature_types;
DROP TABLE IF EXISTS features;
DROP TABLE IF EXISTS zen_features;
DROP TABLE IF EXISTS product_features;
DROP TABLE IF EXISTS zen_product_features;

## drop all ZenMagick tables
DROP TABLE IF EXISTS zm_feature_types;
DROP TABLE IF EXISTS zm_features;
DROP TABLE IF EXISTS zm_product_features;
