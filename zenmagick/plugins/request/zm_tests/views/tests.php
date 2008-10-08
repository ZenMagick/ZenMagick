<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>ZenMagick Unit Testing</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <style type="text/css">
        h1 {width:100%;border-bottom:1px solid gray;}
        .fail {background-color:inherit;color:red;}
        .pass {background-color:inherit;color:green;}
         pre {background-color:lightgray;color:inherit;}
         legend {color:#467aa7;font-weight:bold;padding:3px;}
         fieldset {width:14em;min-height:8em;height:11.5em;float:left;margin-right:5px;padding:8px;}
         form p {clear:left;padding:7px;}
    </style>
    <script type="text/javascript" src="<?php $plugin->pluginURL('js/jquery-1.2.1.pack.js'); ?>"></script>
    <script type="text/javascript" src="<?php $plugin->pluginURL('js/jquery.treeview.pack.js'); ?>"></script>
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
  </head>
  <body>
    <h1>ZenMagick Unit Testing</h1>


    <h2>Select from the following test cases to run</h2>
    <?php $all_selected_tests = array_flip($all_selected_tests); ?>
    <?php $form->open(null, '', false, array('method'=>'get')); ?>
      <?php $lastGroup = null; foreach ($all_tests as $group => $testCases) { $idGroup = str_replace('@', '', $group); ?>
        <?php if ($group != $lastGroup) { ?>
          <?php if (null != $lastGroup) { ?>
            </fieldset>
          <?php } ?>
          <?php $lastGroup = $group; ?>
          <fieldset>
            <legend>
              <input type="checkbox" id="<?php echo $idGroup ?>" onclick="sync_all(this)"> 
              <label for="<?php echo $idGroup ?>"><?php echo $group ?></label>
            </legend>
        <?php } ?>
            <?php foreach ($testCases as $testCase) { $label = $testCase->getLabel(); $tests = $testCase->getTests(); ?>
                <div>
                  <input type="checkbox" name="tests[]" id="<?php echo $idGroup.'_'.$label ?>"
                       value="<?php echo $label ?>" <?php echo (isset($all_selected_tests[$label]) ? 'checked' : '') ?>> 
                  <label for="<?php echo $idGroup.'_'.$label ?>"><?php echo $label ?></label>
                  <?php /* TODO: we need a tree! */foreach ($tests as $test) { continue; ?>
                    &nbsp;&nbsp;<?php echo $test ?><br>
                  <?php } ?>
                </div>
            <?php } ?>
      <?php } ?>
      </fieldset>
      <p>
        <input type="submit" value="Run Tests"> <input type="checkbox" id="all__all" onclick="sync_all(this)"> <label for="all__all">Select All</label>
      </p>
    </form>

    <?php echo $test_results ?>
  </body>
</html>
