#
# Create ZenMagick sacs permissions table
#

CREATE TABLE sacs_permissions (
  sacs_permission_id int(11) NOT NULL auto_increment,
  rid varchar(32) NOT NULL,
  type ENUM('user', 'role') NOT NULL,
  name varchar(32) NOT NULL,
  PRIMARY KEY (sacs_permission_id),
  UNIQUE(rid, type, name)
) ENGINE=MyISAM;
