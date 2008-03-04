#
# ZenMagick features SQL - undo
#
# $Id: features_undo.sql 146 2007-03-15 09:29:34Z DerManoMann $
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
