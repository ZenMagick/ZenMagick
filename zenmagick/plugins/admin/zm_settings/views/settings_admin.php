<h1>Settings</h1>

<?php foreach ($plugin->getConfigValues(false) as $value) { ?>
    <?php var_dump($value) ?>
<?php } ?>

<?php echo 'setting some.other: '.ZMSettings::get('some.other'); ?>
 
