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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>ZenMagick Unit Testing</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" type="text/css" href="<?php echo $this->asUrl('js/jquery.treeview.css') ?>">
    <style type="text/css">
        h1 {width:100%;border-bottom:1px solid gray;}
        .fail {background-color:inherit;color:red;font-weight:bold;}
        .pass {background-color:inherit;color:green;font-weight:bold;}
        .skip {background-color:inherit;color:gray;font-weight:bold;}
        .msg {margin-left:1em;}
        pre {background-color:#eaeaea;color:inherit;}
        label strong {color:black;font-weight:bold;}
        fieldset {width:14em;min-height:8em;height:11.5em;float:left;margin-right:5px;padding:8px;}
        form p {clear:left;padding:7px;}
        #root {margin-left:3px;}
        .filetree {width:20em;border:1px solid gray;min-height:670px;overflow:hidden;float:left;padding:3px;}
        #report {margin-left:22em;}
        #run {float:right;}
        ul#messages {margin-left:0;padding-left:0;}
        ul#messages li {list-style:none;padding:4px 3px;margin-left:0;border:1px solid #9a9a9a;}
        ul#messages .msg, #content ul#messages .success {background:#6f6;color:#000;}
        ul#messages .warn {background:#ff0;color:#000;}
        ul#messages .error {background:#e55;color:#fff;}
    </style>
    <script type="text/javascript" src="<?php echo $this->asUrl('js/jquery-1.2.1.pack.js') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->asUrl('js/jquery.treeview.pack.js') ?>"></script>
    <script type="text/javascript">
      // select/unselect all
      function sync_all(box) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if ((0 == boxes[ii].id.indexOf(box.id+'-') || 'all__all' == box.id) && !boxes[ii].disabled) {
            boxes[ii].checked = box.checked;
          }
        }
      }
    </script>
    <script type="text/javascript"> $(document).ready(function() {
      $(".filetree").treeview({ collapsed: true, unique: false, prerendered: false }); });
    </script>
  </head>
  <body>
    <h1>ZenMagick Unit Testing</h1>
    <div>
        <ul id="messages">
        <?php foreach ($messageService->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    </div>

    <div class="filetree">
      <?php echo $form->open('unitTests', '', false, array('method'=>'post')); ?>
        <div id="root">
          <input type="submit" id="run" value="Run Selected">
          <input type="checkbox" id="hideErrors" name="hideErrors" value="true"<?php echo ($hideErrors ? ' checked' : '') ?>> <label for="hideErrors"><strong>Hide errors</strong></label><br>
          <input type="checkbox" id="all__all" onclick="sync_all(this)"> <label for="all__all"><strong>Select All</strong></label>
        </div>
        <ul>
          <?php foreach ($all_tests as $group => $testCases) { $idGroup = str_replace('@', '', $group); ?>
            <?php $open = false; foreach ($testCases as $testCase) { if (isset($all_selected_testCases[$testCase->getLabel()])) { $open = true; break; } } ?>
            <li<?php if ($open) { echo ' class="open"'; } ?>>
              <div class="">
                <input type="checkbox" id="<?php echo $idGroup ?>" onclick="sync_all(this)">
                <label for="<?php echo $idGroup ?>"><strong><?php echo $group ?></strong></label>
              </div>
              <ul>
                <?php foreach ($testCases as $testCase) { $label = $testCase->getLabel(); $tests = $testCase->getTests(); $result = array_key_exists($label, $all_results) ? $all_results[$label] : null; ?>
                  <?php $incomplete = false; foreach ($tests as $test) { if (!isset($all_selected_tests[$label.'-'.$test])) { $incomplete = true; break; } } ?>
                  <?php $selected = isset($all_selected_testCases[$label]); ?>
                  <li<?php echo (($selected && ($incomplete || (null !== $result && !$result['status']))) ? ' class="open"' : '') ?>>
                    <div class="<?php if ($selected) { echo (null === $result ? "skip" : ($result['status'] ? "pass" : "fail")); } ?>">
                      <input type="checkbox" name="testCases[]" id="<?php echo $idGroup.'-'.$label ?>" onclick="sync_all(this)"
                           value="<?php echo $label ?>" <?php echo ($selected ? 'checked' : '') ?>>
                      <label for="<?php echo $idGroup.'-'.$label ?>"><?php echo $label ?></label>
                    </div>
                    <ul>
                      <?php foreach ($tests as $test) { ?>
                          <?php $selected = isset($all_selected_tests[$label.'-'.$test]); ?>
                          <li>
                            <span class="<?php if ($selected) { echo (null === $result ? "skip" : ($result['tests'][$test]['status'] ? "pass" : "fail")); } ?>">
                              <input type="checkbox" name="tests[]" id="<?php echo $idGroup.'-'.$label.'-'.$test ?>"
                                   value="<?php echo $label.'-'.$test ?>" <?php echo ($selected ? 'checked' : '') ?>>
                              <label for="<?php echo $idGroup.'-'.$label.'-'.$test ?>"><?php echo $test ?></label>
                            </span>
                          </li>
                      <?php } ?>
                    </ul>
                  </li>
                <?php } ?>
              </ul>
            </li>
          <?php } ?>
        </ul>
      </form>
    </div>

    <div id="report"><?php echo $html_report ?></div>
    <br style="clear:left;">
  </body>
</html>
