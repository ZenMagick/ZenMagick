

#
# Table structure for table feature_types
#
#  feature_type_id : the feature type id
#  feature_type    : there might be different types of features that need
#                    to be treated differently
#

DROP TABLE IF EXISTS zen_feature_types;
CREATE TABLE zen_feature_types (
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

DROP TABLE IF EXISTS zen_features;
CREATE TABLE zen_features (
  feature_id int(11) NOT NULL auto_increment,
  feature_type_id int(11) NOT NULL,
  language_id int(11) NOT NULL default '1',
  feature_name varchar(32) NOT NULL default '',
  feature_description varchar(128) NOT NULL,
  hidden int(1) NOT NULL default '0',
  PRIMARY KEY (feature_id),
  KEY idx_feature_id_language_id_zm (feature_type_id, language_id)
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

DROP TABLE IF EXISTS zen_product_features;
CREATE TABLE zen_product_features (
  product_feature_id int(11) NOT NULL auto_increment,
  product_id int(11) NOT NULL,
  feature_id int(11) NOT NULL,
  feature_index_id int(11) NOT NULL default '1',
  feature_value text NOT NULL default '',
  PRIMARY KEY (product_feature_id),
  KEY idx_product_id_feature_id_zm (product_id, feature_id)
) TYPE=MyISAM;

# --------------------------------------------------------


INSERT INTO zen_feature_types (feature_type_id, feature_type) VALUES (1, 'text');


INSERT INTO zen_products_options_values_to_products_options (products_options_values_to_products_options_id, products_options_id, products_options_values_id) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3);



# --------------------------------------------------------
# some test data
#
# --------------------------------------------------------
INSERT INTO zen_features (feature_id, feature_type_id, language_id, feature_name, feature_description) 
  VALUES (1, 1, 1, 'Heads', 'Number of heads');
INSERT INTO zen_features (feature_id, feature_type_id, language_id, feature_name, feature_description) 
  VALUES (2, 1, 1, 'Humour', 'Related humourous stuff');
INSERT INTO zen_features (feature_id, feature_type_id, language_id, feature_name, feature_description) 
  VALUES (3, 1, 1, 'Special', 'Special stuff');

INSERT INTO zen_product_features (product_feature_id, product_id, feature_id, feature_index_id, feature_value) 
  VALUES (1, 1, 1, 1, '2');
INSERT INTO zen_product_features (product_feature_id, product_id, feature_id, feature_index_id, feature_value) 
  VALUES (2, 1, 3, 1, 'Super gut!');

