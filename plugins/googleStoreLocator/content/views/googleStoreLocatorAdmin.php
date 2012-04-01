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
<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo $adminKey ?>"></script>
<script type="text/javascript">
  var marker = null;

  function copy_location() {
    var inputs = document.getElementsByTagName("input");
    for (var ii=0; ii < inputs.length; ++ii) {
      if (inputs[ii].name == "location") {
        if (marker && marker.getPoint) {
          var point = new String(marker.getPoint());
          point = point.substring(1, point.length-1);
          inputs[ii].value = point;
        }
      }
    }
  }

  google.load("maps", "2.x");

  function load_locator_map() {
      var map = new GMap2(document.getElementById("locator_map"));
      map.addControl(new GLargeMapControl());
      map.setCenter(new GLatLng(<?php echo $location ?>), <?php echo $zoom ?>);
      marker = new GMarker(map.getCenter(), {draggable: true});
      GEvent.addListener(marker, "dragstart", function() { map.closeInfoWindow(); });
      GEvent.addListener(marker, "dragend", function() { });
      map.addOverlay(marker);
      marker.openInfoWindowHtml("Drag me to mark a point<br>to mark your store.<br>Then click \'copy location\' to set your store location.");
      GEvent.addListener(map, "moveend", function() { marker.setPoint(map.getCenter()); });
  }
  google.setOnLoadCallback(load_locator_map);
</script>
<?php $admin->title() ?>
<div id="locator_map" style="width:400px;height:400px;border:1px solid #ccc;margin:10px;float:left;"><?php _vzm("Loading map...") ?></div>
<div style="margin:10px;">
  <form action="<?php echo $admin->url() ?>" method="POST">
    <div>
      <p><label for="zoom">Zoom</label> <input type="text" name="zoom" value="<?php echo $zoom ?>"></p>
      <p><label for="location">Location</label> <input type="text" name="location" value="<?php echo $location ?>"> <a href="#" onclick="copy_location(); return false;">Copy Location</a></p>
      <p><input type="submit" value="<?php _vzm('Update') ?>"></p>
    </div>
  </form>
</div>
<br style="clear:left;">
