#
# ZenMagick product tags plugin SQL
#
# $Id$
#

#
# Table structure for table tags
#
DROP TABLE IF EXISTS tags;
CREATE TABLE tags (
  tag_id int(11) NOT NULL auto_increment,
  name varchar(64) NOT NULL,
  language_id int(11) NOT NULL default '1',
  PRIMARY KEY (tag_id)
) ENGINE=MyISAM;

#
# Table structure for table product_tags
#
DROP TABLE IF EXISTS product_tags;
CREATE TABLE product_tags (
  product_tag_id int(11) NOT NULL auto_increment,
  product_id int(11) NOT NULL,
  tag_id int(11) NOT NULL,
  PRIMARY KEY (product_tag_id),
  KEY idx_product_id_tag_id_zm (product_id, tag_id),
  FOREIGN KEY (product_id) REFERENCES products (products_id) ON DELETE CASCADE
) ENGINE=MyISAM;
