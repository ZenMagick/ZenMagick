# 
# Demo tax data
#
# This is demo data that is actually in the core Zen Cart SQL.
#

# USA/Florida
INSERT INTO tax_rates VALUES (1, 1, 1, 1, 7.0, 'FL TAX 7.0%', now(), now());
INSERT INTO geo_zones (geo_zone_id,geo_zone_name,geo_zone_description,date_added) VALUES (1,'Florida','Florida local sales tax zone',now());
INSERT INTO zones_to_geo_zones (association_id,zone_country_id,zone_id,geo_zone_id,date_added) VALUES (1,223,18,1,now());
INSERT INTO tax_class (tax_class_id, tax_class_title, tax_class_description, date_added) VALUES (1, 'Taxable Goods', 'The following types of products are included: non-food, services, etc', now());
