<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */
?>
<?php  
require_once('includes/application_top.php');
require_once('includes/zmCatalogDtree.php');
require_once('../zenmagick/init.php');
require_once('../zenmagick/admin_init.php');

  $themePath = '';
  $merge = isset($_POST['merge']) || isset($_GET['merge']);
  if (isset($_POST['theme'])) {
      $themePath = $_POST['theme'];
  }

  if (isset($_GET['theme']) && isset($_GET['download'])) {
      header('Content-Type: text/PHP');

      $map = zm_build_theme_l10n_map(DIR_FS_CATALOG."zenmagick/themes/" . $_GET['theme'], $merge);
      echo "<?php\n\n    /*\n     * Language mapping generated by ZenMagick Admin v" . zm_setting('ZenMagickVersion') . "\n     */\n";
      echo "\n";
      echo '    $zm_l10n_text = array('."\n";
      $komma = false;
      $firstfile = true;
      foreach ($map as $file => $strings) {
          if (!$firstfile) echo ",\n\n";
          echo "        // " . $file . "\n";
          $nextfile = true;
          foreach ($strings as $key => $value) {
              if ($komma && !$nextfile) echo ",\n";
              echo "        '" . $key . "' => '" . $value . "'";
              $komma = true;
              $nextfile = false;
          }
          $firstfile = false;
      }
      echo "\n    );\n?>";
      return;
  }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Language Tool</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript">
      function init() {
        cssjsmenu('navbar');
        if (document.getElementById) {
          var kill = document.getElementById('hoverJS');
          kill.disabled = true;
        }
      }
    </script>
  </head>
  <body id="b_l10n" onload="init()">
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>

    <div id="main">
      <div id="content">
        <h2>ZenMagick Language Tool</h2>
        <p>This tool helps you find language strings in your themes. Just select a theme and you will 
          get a full list of all strings and where they are used.</p>
        <p>The selected mapping can also be downloaded in a format that you can cut'paste right into your <code>l10n.php</code> file.</p>
        <p><strong>NOTE:</strong> '%s' and other strings starting wiht '%' are used as placeholders for things like order numbers, etc.</p>

        <form action="<?php echo ZM_ADMINFN_L10N ?>" method="post">
          <fieldset>
            <legend>Select Theme to display the language mappings</legend>
            <?php $theme = new ZMTheme(); $themeInfoList = $theme->getThemeInfoList(); ?>
            <select id=theme" name="theme" onchange="this.form.submit()">
              <option value="">Select Theme</option>
              <?php foreach ($themeInfoList as $themeInfo) { ?>
                <?php $selected = $themePath == $themeInfo->getPath() ? ' selected="selected"' : ''; ?>
                <option value="<?php echo $themeInfo->getPath(); ?>"<?php echo $selected ?>><?php echo $themeInfo->getName(); ?></option>
              <?php } ?>
            </select>
            <input type="checkbox" id="merge" name="merge" value="true"<?php echo ($merge?' checked="checked"':'')?>><label for="merge">Merge with existing mappings</label><br>
            <input type="submit" value="Display Mapping">
          </fieldset>
        </form>
        <?php if ('' != $themePath) { ?>
          <a href="<?php echo ZM_ADMINFN_L10N ?>?theme=<?php echo $themePath ?>&amp;download=full<?php echo ($merge?"&amp;merge=true":"")?>">Download full maping</a>
          <?php $map = zm_build_theme_l10n_map(DIR_FS_CATALOG."zenmagick/themes/" . $themePath, $merge) ?>
          <?php foreach ($map as $file => $strings) { ?>
            <h3><?php echo $file ?></h3>
            <?php foreach ($strings as $key => $value) { ?>
              &nbsp;&nbsp;'<?php echo $key ?>' =&gt; '<?php echo $value ?>';<br>
            <?php } ?>
          <?php } ?>
        <?php } ?>
    </div>

  </body>
</html>
