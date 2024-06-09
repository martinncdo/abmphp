<?php

if (isset($_GET["id"])) {
  print_r($_GET["id"]);
  var_dump($_GET["id"]);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <form action="prueba.php" method="GET">
      <input type="text" name="id">
      <input type="submit" value="enviar">
  </form>
</body>
</html>