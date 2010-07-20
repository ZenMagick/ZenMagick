<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
  //TODO: where do they go??

  function zm_title($view, $title=null) {
    $root = ZMAdminMenu::getRootItemForRequestId($view->request->getRequestId());
    $pref = (null != $root) ? $root['title'] : null;
    if (null == $title) {
        $title = $pref;
    } else if (null != $pref) {
        $title = sprintf(_zm("%1s: %2s"), $pref, $title);
    }
    ?>
    <h1><?php echo $title  ?></h1>
    <?php echo $view->fetch('sub-menu.php');
  }

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo ZMSettings::get('zenmagick.mvc.html.charset') ?>">
    <title><?php _vzm('ZenMagick Admin') ?></title>
    <link rel="shortcut icon" href="favicon.ico"> 
    <?php $resources->cssFile('style/zenmagick.css') ?>
    <?php $resources->cssFile('style/jquery-ui/jquery-ui-1.8.2.custom.css') ?>
    <?php $resources->cssFile('style/jquery.cluetip.css') ?>
    <?php $resources->jsFile('js/jquery-1.4.2.min.js') ?>
    <?php $resources->jsFile('js/jquery-ui-1.8.2.custom.min.js') ?>
    <?php $resources->jsFile('js/jquery.form.js') ?>
    <?php $resources->jsFile('js/jquery.cluetip.min.js') ?>
    <?php $resources->jsFile('js/zenmagick.js') ?>
  </head>
  <body id="p-<?php echo $request->getRequestId() ?>">
    <div id="main">
      <?php echo $this->fetch('header.php'); ?>
      <div id="content">
        <?php if (ZMMessages::instance()->hasMessages()) { ?>
            <ul id="messages" class="ui-widget">
            <?php
              $messageClass = array(
                  ZMMessages::T_SUCCESS => array('ui-state-default', 'ui-icon ui-icon-check'),
                  ZMMessages::T_MESSAGE => array('ui-state-default', 'ui-icon ui-icon-info'),
                  ZMMessages::T_WARN => array('ui-state-highlight', 'ui-icon ui-icon-alert'),
                  ZMMessages::T_ERROR => array('ui-state-error', 'ui-icon ui-icon-alert')
              );
            ?>
            <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                <li class="ui-corner-all <?php echo $messageClass[$message->getType()][0] ?>"><span class="<?php echo $messageClass[$message->getType()][1] ?>" style="float:left;margin-right:0.3em;"></span><?php echo $message->getText() ?></li>
            <?php } ?>
            </ul>
        <?php } ?>
        <?php echo $this->fetch($viewTemplate); ?>
        <br clear="both">
      </div>
      <?php echo $this->fetch('footer.php'); ?>
    </div>
    <script> $('.tt[title]').cluetip({clickThrough: true, splitTitle: '|', arrows: true }); </script>
  </body>
</html>
