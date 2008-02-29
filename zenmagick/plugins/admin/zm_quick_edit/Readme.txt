This is a Catalog Manager plugin to quickly edit all products of a selected category.

All fields are text based, so use this careful as there is no validation (yet).

It is possible to configure your own set of fields by creating a function with
name 'zm_quick_edit_field_list' that returns custom field settings.

An example might be a file field_list.php in the plugin folder with the following content:

<?php

  // custom fields
  function zm_quick_edit_field_list() {
      return array(
          // title, form field name, getter/setter name
          array('title' => 'Name', 'field' => 'name', 'method' => 'name', 'size' => 35),
          array('title' => 'metatags_title_status', 'field' => 'metatags_title_status', 'method' => null, 'size' => 14),
          array('title' => 'Quantity', 'field' => 'quantity', 'method' => 'quantity', 'size' => 4)
      );
  }

?>

Each field is configured separately, and requires the following information:
- title: The field title (for the table header)
- field: The field name; this has to be unique.
- method: the common bit of the get/set method name in ZMProduct; for example 'model' is the common string
          of the getModel() and setModel() methods.
          For custom fields, set the method to null. Please note that in this case the field name (see above)
          is assumed to be the database column name.
- size: The inoput field size

NOTE: For custom fields you also have to configure ZMProducts in order to actually read/write those columns. This is done
by setting 'sql.products.customFields'.

Example:

    zm_set_setting('sql.products.customFields', 'metatags_title_status;integer');

