#
# Add variation column for theme chaining
#

ALTER TABLE template_select ADD variation_dir varchar(64) default NULL;

