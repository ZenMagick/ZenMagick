#
# Table structure for table 'media_clips'
#

DROP TABLE IF EXISTS media_clips;
CREATE TABLE media_clips (
  clip_id int(11) NOT NULL auto_increment,
  media_id int(11) NOT NULL default '0',
  clip_type smallint(6) NOT NULL default '0',
  clip_filename text NOT NULL,
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (clip_id),
  KEY idx_media_id_zen (media_id),
  KEY idx_clip_type_zen (clip_type)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_manager'
#

DROP TABLE IF EXISTS media_manager;
CREATE TABLE media_manager (
  media_id int(11) NOT NULL auto_increment,
  media_name varchar(255) NOT NULL default '',
  last_modified datetime NOT NULL default '0001-01-01 00:00:00',
  date_added datetime NOT NULL default '0001-01-01 00:00:00',
  PRIMARY KEY  (media_id),
  KEY idx_media_name_zen (media_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_to_products'
#

DROP TABLE IF EXISTS media_to_products;
CREATE TABLE media_to_products (
  media_id int(11) NOT NULL default '0',
  product_id int(11) NOT NULL default '0',
  KEY idx_media_product_zen (media_id,product_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'media_types'
#

DROP TABLE IF EXISTS media_types;
CREATE TABLE media_types (
  type_id int(11) NOT NULL auto_increment,
  type_name varchar(64) NOT NULL default '',
  type_ext varchar(8) NOT NULL default '',
  PRIMARY KEY  (type_id),
  KEY idx_type_name_zen (type_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'music_genre'
#

DROP TABLE IF EXISTS music_genre;
CREATE TABLE music_genre (
  music_genre_id int(11) NOT NULL auto_increment,
  music_genre_name varchar(32) NOT NULL default '',
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (music_genre_id),
  KEY idx_music_genre_name_zen (music_genre_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'product_music_extra'
#

DROP TABLE IF EXISTS product_music_extra;
CREATE TABLE product_music_extra (
  products_id int(11) NOT NULL default '0',
  artists_id int(11) NOT NULL default '0',
  record_company_id int(11) NOT NULL default '0',
  music_genre_id int(11) NOT NULL default '0',
  PRIMARY KEY  (products_id),
  KEY idx_music_genre_id_zen (music_genre_id),
  KEY idx_artists_id_zen (artists_id),
  KEY idx_record_company_id_zen (record_company_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_artists'
#

DROP TABLE IF EXISTS record_artists;
CREATE TABLE record_artists (
  artists_id int(11) NOT NULL auto_increment,
  artists_name varchar(32) NOT NULL default '',
  artists_image varchar(64) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (artists_id),
  KEY idx_rec_artists_name_zen (artists_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_artists_info'
#

DROP TABLE IF EXISTS record_artists_info;
CREATE TABLE record_artists_info (
  artists_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  artists_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime default NULL,
  PRIMARY KEY  (artists_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_company'
#

DROP TABLE IF EXISTS record_company;
CREATE TABLE record_company (
  record_company_id int(11) NOT NULL auto_increment,
  record_company_name varchar(32) NOT NULL default '',
  record_company_image varchar(64) default NULL,
  date_added datetime default NULL,
  last_modified datetime default NULL,
  PRIMARY KEY  (record_company_id),
  KEY idx_rec_company_name_zen (record_company_name)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table 'record_company_info'
#

DROP TABLE IF EXISTS record_company_info;
CREATE TABLE record_company_info (
  record_company_id int(11) NOT NULL default '0',
  languages_id int(11) NOT NULL default '0',
  record_company_url varchar(255) NOT NULL default '',
  url_clicked int(5) NOT NULL default '0',
  date_last_click datetime default NULL,
  PRIMARY KEY  (record_company_id,languages_id)
) ENGINE=MyISAM;

# --------------------------------------------------------

