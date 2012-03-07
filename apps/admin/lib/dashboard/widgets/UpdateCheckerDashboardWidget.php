<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php
namespace zenmagick\apps\store\admin\dashboard\widgets;

use zenmagick\base\Runtime;
use zenmagick\apps\store\admin\dashboard\DashboardWidget;

/**
 * Update checker widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class UpdateCheckerDashboardWidget extends DashboardWidget {

    /**
     * Create new user.
     */
    public function __construct() {
        parent::__construct(_zm('Update Checker'));
    }


    /**
     * {@inheritDoc}
     */
    public function getContents($request) {
        $current = Runtime::getSettings()->get('zenmagick.version');
        $contents = '<p id="update-checker">'._zm('Checking...').'</p>';
        $contents .= <<<EOT
<script>
// convert into function that takes id and function
// OR: add as template to an ajax dashboard widget base class
(function() {
  // keep track of things already executed
  var done = false;

  function checkUpdate() {
    if (0 != $('#update-checker').closest('#dashboard').length && !done) {
      ZenMagick.rpc('dashboard', 'getUpdateInfo', '""', {
          success: function(result) {
              //TODO: extend return info and parse...
              var info = jQuery.parseJSON(result.data);
              var latest = info.version;
              var current = '$current';
              if (1 == ZenMagick.versionCompare(latest, current)) {
                  // have update
                  $('#update-checker').html('A new version (<a href="'+info.download+'">'+latest+'</a>) is available. Current version is: '+current);
              } else {
                  $('#update-checker').html('You are using the latest version. Current version is: '+current);
              }
          },
          failure: function() {
              $('#update-checker').html('Could not connect to update server.');
          }
      });
      // remember that we are done
      done = true;
    }
  }

  // check once right away
  checkUpdate();

  $( ".db-column" ).bind("sortreceive", function(event, ui) {
    if ('portlet-updateCheckerDashboardWidget' == ui.item.context.id) {
      // dragged from widget box into drashboard
      checkUpdate();
    }
  });
})();
</script>
EOT;
        return $contents;
    }

}
