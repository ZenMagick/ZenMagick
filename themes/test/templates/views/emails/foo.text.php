Request: <?php echo $id ?>

Data:
<?php foreach ($data as $name => $value) { ?>
 <?php echo $name ?>: <?php echo (is_array($value) ? implode(', ', $value) : $value) ?>

<?php } ?>
