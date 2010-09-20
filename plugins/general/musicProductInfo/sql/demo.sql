#
# Dumping data for table `media_clips`
#

INSERT INTO media_clips (clip_id, media_id, clip_type, clip_filename, date_added, last_modified) VALUES 
(1, 1, 1, 'thehunter.mp3', '2004-06-01 20:57:43', '0000-00-00 00:00:00'),
(6, 2, 1, 'thehunter.mp3', '2004-07-13 00:45:09', '0000-00-00 00:00:00');

#
# Dumping data for table `media_manager`
#

INSERT INTO media_manager (media_id, media_name, last_modified, date_added) VALUES 
(1, 'Russ Tippins - The Hunter', '2004-06-01 20:57:43', '2004-06-01 20:42:53'),
(2, 'Help!', '2004-07-13 01:01:14', '2004-07-12 17:57:45');

#
# Dumping data for table `media_to_products`
#

INSERT INTO media_to_products (media_id, product_id) VALUES (1, 166),
(2, 169);

#
# Dumping data for table `media_types`
#

INSERT INTO media_types (type_id, type_name, type_ext) VALUES 
(1, 'MP3', '.mp3');

#
# Dumping data for table `music_genre`
#

INSERT INTO music_genre (music_genre_id, music_genre_name, date_added, last_modified) VALUES 
(1, 'Rock', '2004-06-01 20:53:26', NULL),
(2, 'Jazz', '2004-06-01 20:53:45', NULL);

#
# Dumping data for table `product_music_extra`
#

INSERT INTO product_music_extra (products_id, artists_id, record_company_id, music_genre_id) VALUES 
(166, 1, 0, 1),
(169, 1, 1, 2);

#
# Dumping data for table `record_artists`
#

INSERT INTO record_artists (artists_id, artists_name, artists_image, date_added, last_modified) VALUES 
(1, 'The Russ Tippins Band', 'sooty.jpg', '2004-06-01 20:53:00', NULL);

#
# Dumping data for table `record_artists_info`
#

INSERT INTO record_artists_info (artists_id, languages_id, artists_url, url_clicked, date_last_click) VALUES 
(1, 1, 'russtippinsband.users.btopenworld.com/', 0, NULL);

#
# Dumping data for table `record_company`
#

INSERT INTO record_company (record_company_id, record_company_name, record_company_image, date_added, last_modified) VALUES 
(1, 'HMV Group', NULL, '2004-07-09 14:11:52', NULL);

#
# Dumping data for table `record_company_info`
#

INSERT INTO record_company_info (record_company_id, languages_id, record_company_url, url_clicked, date_last_click) VALUES 
(1, 1, 'www.hmvgroup.com', 0, NULL);

