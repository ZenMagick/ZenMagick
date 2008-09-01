<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>ZenMagick Unit Testing</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <style type="text/css">
        .fail {background-color:inherit;color:red;}
        .pass {background-color:inherit;color:green;}
         pre {background-color:lightgray;color:inherit;}
         legend {color:#467aa7;font-weight:bold;padding:3px;}
         .all {margin-top:5px;border-top:1px dotted #467aa7;position:absolute;bottom:2em;}
         fieldset {width:12em;height:6em;float:left;position:relative;margin-right:5px;}
         fieldset div {width:11em;}
         form p {clear:left;padding:7px;}
    </style>
    <script type="text/javascript">
      // select/unselect all
      function sync_all(box) {
        var boxes = document.getElementsByTagName('input');
        for (var ii=0; ii<boxes.length; ++ii) {
          if ((0 == boxes[ii].id.indexOf(box.id) || '@all' == box.id) && !boxes[ii].disabled) {
            boxes[ii].checked = box.checked;
          }
        }
      }
    </script>
  </head>
  <body>
    <h1>ZenMagick Unit Testing</h1>

    <h2>Select from the following test cases to run</h2>
    <?php $all_selected_tests = array_flip($all_selected_tests); ?>
    <?php $form->open(null, '', false, array('method'=>'get')); ?>
      <?php $lastGroup = null; foreach ($all_tests as $group => $cases) { ?>
        <?php if ($group != $lastGroup) { ?>
          <?php if (null != $lastGroup) { ?>
              <div class="all"><input type="checkbox" id="<?php echo $lastGroup ?>" onclick="sync_all(this)"> <label for="<?php echo $lastGroup ?>">Select All</label<div>
            </fieldset>
          <?php } ?>
          <?php $lastGroup = $group; ?>
          <fieldset>
            <legend><?php echo $group ?></legend>
        <?php } ?>
            <?php foreach ($cases as $case) { ?>
            <input type="checkbox" name="tests[]" id="<?php echo $group.'_'.$case ?>" value="<?php echo $case ?>" <?php echo (isset($all_selected_tests[$case]) ? 'checked' : '') ?>>
                <label for="<?php echo $group.'_'.$case ?>"><?php echo $case ?></label<br>
            <?php } ?>
      <?php } ?>
        <div class="all"><input type="checkbox" id="<?php echo $group ?>" onclick="sync_all(this)"> <label for="<?php echo $group ?>">Select All</label<div>
      </fieldset>
      <p>
        <input type="checkbox" id="@all" onclick="sync_all(this)"> <label for="@all">Select All</label><br>
        <input type="submit" value="Run Tests">
      </p>
    </form>

    <?php
        if (isset($test_suite)) {
            $test_suite->run(ZMLoader::make('ZMHtmlReporter'));
        }
    ?>
  </body>
</html>
