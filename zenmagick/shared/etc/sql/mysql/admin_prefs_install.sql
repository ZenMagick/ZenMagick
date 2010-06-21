DROP TABLE IF EXISTS admin_prefs;
CREATE TABLE admin_prefs (
  admin_pref_id int(11) NOT NULL auto_increment,
  admin_id int(11),
  name varchar(32) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (admin_pref_id),
  UNIQUE(admin_id, name),
  FOREIGN KEY (admin_id) REFERENCES admin (admin_id) ON DELETE CASCADE
) TYPE=MyISAM;

INSERT INTO admin_prefs (admin_id, name, value) VALUES(1, 'dashboard', '{"columns":3,"widgets":[["OrderStatsDashboardWidget#open=false","RecentSearchesDashboardWidget#optionsUrl=abc"],["LatestOrdersDashboardWidget"],["LatestAccountsDashboardWidget"]]}');
