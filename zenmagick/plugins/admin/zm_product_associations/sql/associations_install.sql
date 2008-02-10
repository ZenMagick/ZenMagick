#*******************************************************************************
#
# ZenMagick product associations SQL
#
# $Id$
#
#*******************************************************************************



#*******************************************************************************
# Table structure for table product_association_types
#
#  association_type_id : the association type id
#  association_type    : the association type; this can be used to access
#                        associations
#
#*******************************************************************************
CREATE TABLE zm_product_association_types (
  association_type_id int(11) NOT NULL auto_increment,
  association_type int(11) NOT NULL,
  association_type_name varchar(32) NOT NULL,
  PRIMARY KEY (association_type_id)
) TYPE=MyISAM;



#*******************************************************************************
#
# Table structure for table product_associations
#
#  association_id :      primary key
#  association_type :    the feature type id
#  source_product_id :    the source product
#  target_product_id :    the target (associated) product
#  start_date :           start date of the association
#  end_date :             optional end date; excluded if set
#  default_quantity :     the default quantity for add to cart if no other given
#  sort_order :           the sort order of associations
#
CREATE TABLE zm_product_associations (
  association_id int(11) NOT NULL auto_increment,
  association_type int(11) NOT NULL,
  source_product_id int(11) NOT NULL,
  target_product_id int(11) NOT NULL,
  start_date datetime NOT NULL,
  end_date datetime,
  default_quantity float NOT NULL default '0',
  sort_order int(10) unsigned NOT NULL default 1,
  PRIMARY KEY (association_id),
  KEY source_target_product_id_zm (source_product_id, target_product_id),
  FOREIGN KEY (source_product_id) REFERENCES products (products_id) ON DELETE CASCADE,
  FOREIGN KEY (target_product_id) REFERENCES products (products_id) ON DELETE CASCADE,
  FOREIGN KEY (association_type) REFERENCES zm_product_association_types (association_type) ON DELETE CASCADE
) TYPE=MyISAM;



#*******************************************************************************
# default association types
#*******************************************************************************
INSERT INTO zm_product_association_types (association_type, association_type_name) VALUES (1, 'cross-sell');
INSERT INTO zm_product_association_types (association_type, association_type_name) VALUES (2, 'up-sell');
INSERT INTO zm_product_association_types (association_type, association_type_name) VALUES (3, 'warranty');
INSERT INTO zm_product_association_types (association_type, association_type_name) VALUES (4, 'accessory');
