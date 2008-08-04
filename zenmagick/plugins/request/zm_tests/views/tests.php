<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>ZenMagick Unit Testing</title>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <style type="text/css">
        .fail { background-color: inherit; color: red; }
        .pass { background-color: inherit; color: green; }
         pre { background-color: lightgray; color: inherit; }
    </style>
  </head>
  <body>
    <h1>ZenMagick Unit Testing</h1>

    <h2>Select from the following test cases to run</h2>
    <?php $all_selected_tests = array_flip($all_selected_tests); ?>
    <?php $form->open(null, '', false, array('method'=>'get')); ?>
        <fieldset>
            <legend>Available Tests</legend>
            <?php foreach ($all_tests as $case) { ?>
            <input type="checkbox" name="tests[]" id="<?php echo $case ?>" value="<?php echo $case ?>" <?php echo (isset($all_selected_tests[$case]) ? 'checked' : '') ?>>
                <label for="<?php echo $case ?>"><?php echo $case ?></label<br>
            <?php } ?>
        </fieldset>
        <p><input type="submit" value="Run"></p>
    </form>

    <?php
        if (isset($test_suite)) {
            $test_suite->run(ZMLoader::make('ZMHtmlReporter'));
        }
    ?>
  </body>
</html>
