<?php
  session_start();

  $HTMLPersonas = array();

  if (!isset($_SESSION["personas"])) {
    $_SESSION["personas"] = array();
  }

  function verificarDNI($dniEntrante) {
    foreach ($_SESSION["personas"] as $dniRegistrado => $datos) {
      if ($dniEntrante == $dniRegistrado) {
        return true;
      }
    }
  }

  function renderizarListado() {
    global $HTMLPersonas;
    foreach ($_SESSION["personas"] as $key => $persona) {
      $HTMLPersonas[] = "<div class='registro'><u>" . $persona["nombre"] . " " . $persona["apellido"] . "</u>
      <br>DNI: " . $persona["dni"] . "
      <br>Sexo: " . $persona["sexo"] . " 
      <br>Fecha de nacimiento: " . $persona["fechanac"] . "
      <br>Edad: " . $persona["edad"] . " 
      <form action='index.php' method='POST'>
      <button type='submit' class='btn-borrar' name='borrar' value=" . $persona['dni'] . ">Borrar</button>
      <button type='submit' class='btn-editar' name='editar' value = " . $persona['dni'] . ">Editar</button>
      </form>
      <hr></div>";
    }
  }

  function verifCamposVacios($campos) {
    $camposVacios = false;
    foreach($campos as $campo => $data) {
      if (empty($data) or $data == "indef") {
        echo "<div class='notif-campo'>Debes seleccionar: $campo.</div>";
        $camposVacios = true;
        return $camposVacios;
      }
    }
    return $camposVacios;
  }

  function calculoEdad($nacimiento) {
    $fechaNacimiento = new DateTime($nacimiento);
    $fechaActual = new DateTime();
    $edad = $fechaActual->diff($fechaNacimiento)->y;
    return $edad;
  }

  if (isset($_POST["visualizar"])) {
    renderizarListado();
  }

  if (isset($_POST["agregar"])) {
    $sexo;
    
    if (isset($_POST["sexo"])) {
      $sexo = ($_POST["sexo"] == "masculino" ? "masculino" : "femenino");
    } else {
      $sexo = "indef";
    }
    
    $campos = array("dni" => $_POST["dni"], "nombre" => $_POST["nombre"], "apellido" => $_POST["apellido"], "sexo" => $sexo, "fecha de nacimiento" => $_POST["fechanac"]);

    $camposVacios = verifCamposVacios($campos);

    if ($camposVacios == false) {
      if (verificarDNI($_POST["dni"]) == true) {
        echo "<div class='notif-campo'>El DNI " . $_POST["dni"] . " ya está en uso.</div>";
      } else {
        $edad = calculoEdad($_POST["fechanac"]);
        $registro = array(
          'dni' => strval($_POST["dni"]),
          'nombre' => $_POST["nombre"],
          'apellido' => $_POST["apellido"],
          'sexo' => $sexo,
          'fechanac' => $_POST["fechanac"],
          'edad'=> $edad
        );
    
        $_SESSION["personas"][$_POST["dni"]] = $registro;
      }
    }
    renderizarListado();
  };


  if (isset($_POST["borrar"])) {
    unset($_SESSION["personas"][$_POST["borrar"]]);
    echo "<div class='delete-registro'>Registro con DNI " . $_POST["borrar"] . " eliminado.</div>";
    renderizarListado();
  }

  if (isset($_POST["editar"])) {
    $formularioEdit = "<br><br><form class='form-editar' action='index.php' method='POST'>
      <input type='hidden' name='originaldni' value=" . $_POST["editar"] . " >
      <input type='number' name='dni' value=" . $_SESSION["personas"][$_POST["editar"]]["dni"] . " > 
      <input type='text' name='nombre' value=" . $_SESSION["personas"][$_POST["editar"]]["nombre"] . " > 
      <input type='text' name='apellido' value=" . $_SESSION["personas"][$_POST["editar"]]["apellido"] . " > 
      <select name='sexo' id='sexo'>
          <option disabled selected>Seleccionar opción</option>
          <option value='masculino'" . ($_SESSION["personas"][$_POST["editar"]]["sexo"] == "masculino" ? "selected" : ""). ">Masculino</option>
          <option value='femenino'" . ($_SESSION["personas"][$_POST["editar"]]["sexo"] == "femenino" ? "selected" : "") . ">Femenino</option>
        </select>
      <input type='date' name='fechanac' value=" . $_SESSION["personas"][$_POST["editar"]]["fechanac"] . " > 
      <input type='text' name='edad' readonly value=" . $_SESSION["personas"][$_POST["editar"]]["edad"] . " >
      <button type='submit' class='btn-confirmar' name='actualizar'>Actualizar</button>
      <button type='submit' class='btn-borrar' name='retroceder'>Cancelar</button>
    </form>";
  }

  if (isset($_POST["actualizar"])) {
    if (isset($_POST["sexo"])) {
      $sexo = ($_POST["sexo"] == "masculino" ? "masculino" : "femenino");
    } else {
      $sexo = "indef";
    }

    $campos = array("dni" => $_POST["dni"], "nombre" => $_POST["nombre"], "apellido" => $_POST["apellido"], "sexo" => $sexo, "fecha de nacimiento" => $_POST["fechanac"]);

    $camposVacios = verifCamposVacios($campos);

    if ($camposVacios == false) {
      $edad = calculoEdad($_POST["fechanac"]);
      unset($_SESSION["personas"][$_POST["originaldni"]]);
      $_SESSION["personas"][$_POST["dni"]]["dni"] = $_POST["dni"];
      $_SESSION["personas"][$_POST["dni"]]["nombre"] = $_POST["nombre"];
      $_SESSION["personas"][$_POST["dni"]]["apellido"] = $_POST["apellido"];
      $_SESSION["personas"][$_POST["dni"]]["sexo"] = $_POST["sexo"];
      $_SESSION["personas"][$_POST["dni"]]["fechanac"] = $_POST["fechanac"];
      $_SESSION["personas"][$_POST["dni"]]["edad"] = $edad; 
      echo "<div class='edit-registro'>Registro actualizado correctamente.</div>";
    }

    renderizarListado();
  }

  if (isset($_POST["cancelar-listado"])) {
    $HTMLPersonas = "";
  }

  if (isset($_POST["retroceder"])) {
    $formularioEdit = "";
    renderizarListado();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="main">
    <div class="seccion-form">

      <form class="agregar-registro" action="<?php $_SERVER["PHP_SELF"]?>" method="POST">
        <label for="dni">DNI</label>
        <input type="number" name="dni" id="dni">
        <br><br>
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre">
        <br><br>
        <label for="apellido">Apellido</label>
        <input type="text" name="apellido" id="apellido">
        <br><br>
        <label for="sexo">Sexo</label>
        <select name="sexo" id="sexo">
          <option disabled selected>Seleccionar opción</option>
          <option value="masculino">Masculino</option>
          <option value="femenino">Femenino</option>
        </select>
        <br><br>
        <label for="fechanac">Fecha de nacimiento</label>
        <input type="date" name="fechanac" id="fechanac">
        <br><br>
        <button type="submit" name="agregar" class="btn-agregar">Enviar</button>
        <button type="submit" name="visualizar" class="btn-agregar">Visualizar registros</button>
      </form>
      </div>

      <div class="registros">
        <form class="encabezado-listado" action="<?php $_SERVER["PHP_SELF"]?>" method="POST"><p class="titulo-listado">Listado de personas</p><button class="cancelar-listado" value="cancelar-listado" type="submit">X</button></form>
        <?php
            foreach($HTMLPersonas as $person_HTML) {
              echo $person_HTML;
            }

            if (isset($formularioEdit)) {
              echo $formularioEdit;
            }
            ?>
      </div>
    </div>
    <script src="script.js"></script>
</body>
</html>