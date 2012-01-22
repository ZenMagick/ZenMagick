#
# ZenMagick product group pricing plugin SQL
#
# $Id$
#

#
# Table structure for table product_group_pricing
#
DROP TABLE IF EXISTS product_group_pricing;
CREATE TABLE product_group_pricing (
  group_pricing_id int(11) NOT NULL auto_increment,
  products_id int(11) NOT NULL,
  group_id int(11) NOT NULL,
  discount decimal(15,4) NOT NULL default '0.0000',
  type varchar(2) NOT NULL default '%',
  allow_sale_special tinyint(1) NOT NULL default 1,
  start_date datetime NOT NULL,
  end_date datetime,
  PRIMARY KEY (group_pricing_id),
  FOREIGN KEY (products_id) REFERENCES products (products_id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES group_pricing (group_id) ON DELETE CASCADE
) ENGINE=MyISAM;
