<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>ZenMagick Unit Testing</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" type="text/css" href="<?php $plugin->pluginURL('js/jquery.treeview.css') ?>">
    <style type="text/css">
        h1 {width:100%;border-bottom:1px solid gray;}
        .fail {background-color:inherit;color:red;font-weight:bold;}
        .pass {background-color:inherit;color:green;font-weight:bold;}
         pre {background-color:lightgray;color:inherit;}
         label strong {color:black;font-weight:bold;}
         fieldset {width:14em;min-height:8em;height:11.5em;float:left;margin-right:5px;padding:8px;}
         form p {clear:left;padding:7px;}
         #root {margin-left:3px;}
         .filetree {width:17em;border:1px solid gray;min-height:670px;overflow:hidden;float:left;padding:3px;}
         #report {margin-left:19em;}
         #run {float:right;}
    </style>
    <script type="text/javascript" src="<?php $plugin->pluginURL('js/jquery-1.2.1.pack.js') ?>"></script>
    <script type="text/javascript" src="<?php $plugin->pluginURL('js/jquery.treeview.pack.js') ?>"></script>
    <script type="text/javascript">
      // select/unselect all
      function sync_all(box) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if ((0 == boxes[ii].id.indexOf(box.id) || 'all__all' == box.id) && !boxes[ii].disabled) {
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

    <div class="filetree">
      <?php $form->open('tests', '', false, array('method'=>'post')); ?>
        <div id="root">
          <input type="submit" id="run" value="Run Selected">
          <input type="checkbox" id="all__all" onclick="sync_all(this)"> <label for="all__all"><strong>All Tests</strong></label>
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
                <?php foreach ($testCases as $testCase) { $label = $testCase->getLabel(); $tests = $testCase->getTests(); $result = $all_results[$label]; ?>
                  <?php $selected = isset($all_selected_testCases[$label]); ?>
                  <li<?php echo (($selected && !$result['status']) ? ' class="open"' : '') ?>>
                    <div class="<?php if ($selected) { echo (($result['status']) ? "pass" : "fail"); } ?>">
                      <input type="checkbox" name="testCases[]" id="<?php echo $idGroup.'-'.$label ?>" onclick="sync_all(this)"
                           value="<?php echo $label ?>" <?php echo ($selected ? 'checked' : '') ?>> 
                      <label for="<?php echo $idGroup.'-'.$label ?>"><?php echo $label ?></label>
                    </div>
                    <ul>
                      <?php foreach ($tests as $test) { ?>
                          <?php $selected = isset($all_selected_tests[$label.'-'.$test]); ?>
                          <li>
                            <span class="<?php if ($selected) { echo (($result['tests'][$test]) ? "pass" : "fail"); } ?>">
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
