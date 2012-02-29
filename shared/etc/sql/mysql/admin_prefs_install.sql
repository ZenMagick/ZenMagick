#
# Create ZenMagick admin preferences table
#

CREATE TABLE admin_prefs (
  admin_pref_id int(11) NOT NULL auto_increment,
  admin_id int(11),
  name varchar(32) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (admin_pref_id),
  UNIQUE(admin_id, name),
  FOREIGN KEY (admin_id) REFERENCES admin (admin_id) ON DELETE CASCADE
) ENGINE=MyISAM;
