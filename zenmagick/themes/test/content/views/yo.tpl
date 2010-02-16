<html>
  <head>
    <title>Yo - Savant works!</title>
  </head>
  <body>
    <h1>Yo - Savant works!</h1>
    <p>yo - this works because savant always adds the location of Savant3.php to the path list!</p>

    <p>$yodata: <?php echo $yodata ?></p>

    <h2>Here comes an include [fetch(..)]</h2>
    <div style="border:1px solid black;padding:3px;"><?php echo $this->fetch('views/savant3.tpl'); ?></div>
  </body>
</html>
