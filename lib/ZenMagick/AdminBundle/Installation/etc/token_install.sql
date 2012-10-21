#
# Create token table
#

CREATE TABLE token (
  hash_id int(11) NOT NULL auto_increment,
  hash mediumblob NOT NULL,
  resource varchar(128) NOT NULL,
  issued datetime NOT NULL,
  expires datetime NOT NULL,
  PRIMARY KEY (hash_id)
) ENGINE=MyISAM;
