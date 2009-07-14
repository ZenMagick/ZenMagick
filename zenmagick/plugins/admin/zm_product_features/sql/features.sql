#
# ZenMagick features SQL
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


#
# Table structure for table feature_types
#
#  feature_type_id : the feature type id
#  feature_type    : there might be different types of features that need
#                    to be treated differently
#

DROP TABLE IF EXISTS zm_feature_types;
CREATE TABLE zm_feature_types (
  feature_type_id int(11) NOT NULL auto_increment,
  feature_type varchar(32) NOT NULL default '',
  PRIMARY KEY (feature_type_id)
) TYPE=MyISAM;

# --------------------------------------------------------


#
# Table structure for table features
#
#  feature_id :           primary key
#  feature_type_id :      the feature type id
#  language_id :          language of the description
#  feature_name :         the feature name
#  feature_description :  a description
#  hidden :               features that can be used for business logic only
#

DROP TABLE IF EXISTS zm_features;
CREATE TABLE zm_features (
  feature_id int(11) NOT NULL auto_increment,
  feature_type_id int(11) NOT NULL,
  language_id int(11) NOT NULL default '1',
  feature_name varchar(32) NOT NULL default '',
  feature_description varchar(128) NOT NULL,
  hidden int(1) NOT NULL default '0',
  PRIMARY KEY (feature_id),
  KEY idx_feature_id_language_id_zm (feature_type_id, language_id),
  FOREIGN KEY (feature_type_id) REFERENCES zm_feature_types (feature_type_id) ON DELETE CASCADE
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table product_features
#
#  product_feature_id : primary key
#  product_id :         product id
#  feature_id:          feature id
#  feature_index_id :   subindex for list features, etc
#  feature_value :      the actual value
#

DROP TABLE IF EXISTS zm_product_features;
CREATE TABLE zm_product_features (
  product_feature_id int(11) NOT NULL auto_increment,
  product_id int(11) NOT NULL,
  feature_id int(11) NOT NULL,
  feature_index_id int(11) NOT NULL default '1',
  feature_value text NOT NULL,
  PRIMARY KEY (product_feature_id),
  KEY idx_product_id_feature_id_zm (product_id, feature_id),
  FOREIGN KEY (product_id) REFERENCES products (products_id) ON DELETE CASCADE,
  FOREIGN KEY (feature_id) REFERENCES zm_features (feature_id) ON DELETE CASCADE
) TYPE=MyISAM;

# --------------------------------------------------------


INSERT INTO zm_feature_types (feature_type_id, feature_type) VALUES (1, 'text');
