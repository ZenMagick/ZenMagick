#
# ZenMagick account type SQL
#
# $Id$
#

#
# Table structure for table account_type
#
#  account_type_id : the account type id
#  account_id      : the account id
#  account_type    : the account type
#                    'r' : registered/regular (default)
#                    'a' : anonymous
#

DROP TABLE IF EXISTS zm_account_type;
CREATE TABLE zm_account_type (
  account_type_id int(11) NOT NULL auto_increment,
  account_id int(11) NOT NULL,
  account_type varchar(2) NOT NULL default 'r',
  PRIMARY KEY (account_type_id),
  FOREIGN KEY (account_id) REFERENCES customers (customers_id) ON DELETE CASCADE
) TYPE=MyISAM;

