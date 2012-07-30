#
# Create ZenMagick admin user role tables
#

CREATE TABLE admin_roles (
  admin_role_id int(11) NOT NULL auto_increment,
  name varchar(32) NOT NULL,
  PRIMARY KEY (admin_role_id),
  UNIQUE (name)
) ENGINE=MyISAM;

CREATE TABLE admins_to_roles (
  admin_id int(11),
  admin_role_id int(11),
  PRIMARY KEY (admin_id, admin_role_id),
  FOREIGN KEY (admin_role_id) REFERENCES admin_roles (admin_role_id) ON DELETE CASCADE,
  FOREIGN KEY (admin_id) REFERENCES admin (admin_id) ON DELETE CASCADE
) ENGINE=MyISAM;

## create default mapping for main admin
INSERT INTO admin_roles VALUES(1, 'admin');
INSERT INTO admins_to_roles VALUES(1, 1);
