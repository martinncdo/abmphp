<?php
  include 'model.php';

  $HTMLPersonas = array();

  function verificarDNI($dniEntrante) {
    $registers = get_registers();
    foreach ($registers as $person) {
      foreach ($person as $key => $dniRegistrado) {
        if ($dniEntrante == $dniRegistrado) {
          return true;
        }
      }
    }
  }

  function renderizarListado($registers) {
    global $HTMLPersonas;
    $HTMLPersonas = array();
    foreach ($registers as $key => $persona) {
      $HTMLPersonas[] = "<div class='registro'><u>" . $persona["nombre"] . " " . $persona["apellido"] . "</u>
      <br>DNI: " . $persona["dni"] . "
      <br>Sexo: " . $persona["sexo"] . " 
      <br>Fecha de nacimiento: " . $persona["fecha_nac"] . "
      <br>Edad: " . calculoEdad($persona["fecha_nac"]) . " 
      <form action='index.php' method='POST'>
      <button type='submit' class='btn-borrar' name='borrar' value=" . $persona['dni'] . ">Borrar</button>
      <button type='submit' class='btn-editar' name='editar' value = " . $persona['dni'] . ">Editar</button>
      </form>
      <hr></div>";
    }
  }

  function calculoEdad($nacimiento) {
    $fechaNacimiento = new DateTime($nacimiento);
    $fechaActual = new DateTime();
    $edad = $fechaActual->diff($fechaNacimiento)->y;
    return $edad;
  }

  function verifCamposVacios($campos) {
    $camposVacios = false;
    foreach($campos as $campo => $data) {
      if (empty($data) or $data == "indef") {
        echo "<div class='notif-campo'>Debes seleccionar: $campo.</div>";
        $camposVacios = true;
      }
    }
    return $camposVacios;
  }


  // Condicional para visualizar los registros de manera manual realizando click sobre el botón HTML.
  if (isset($_POST["visualizar"])) {
    $registers = get_registers();
    renderizarListado($registers);
  }

  if (isset($_GET["buscar"])) {
    $registers = get_registers_by_filter($_GET["dni"], $_GET["nombre"], $_GET["apellido"]);
    renderizarListado($registers);
  }

  // Condicional para agregar un nuevo registro a la base de datos.
  if (isset($_POST["agregar"])) {
    $sexo;
    
    // Lógica para evitar una entrada inválida para el campo "sexo", debido al elemento select HTML.
    if (isset($_POST["sexo"])) {
      $sexo = ($_POST["sexo"] == "masculino" ? "masculino" : "femenino");
      } else {
      $sexo = "indef";
      // En caso de que no haya seleccionado masculino o femenino, establecerá el dato enviado
      // desde el cliente como "indef".
      // Luego, la función verifCamposVacios() (que valida todos los datos enviados desde el cliente), 
      // evalúa:
      // 1. si algún dato es una string vacía, o 
      // 2. si es una string "indef", que claramente se utiliza únicamente para evaluar el campo "sexo", 
      // ya que es el único con posibilidad de establecerse como "indef" en el servidor.
      // Si alguna de las dos opciones se cumple, la función verifCamposVacios() devuelve true,
      // lo que impedirá que se agregue un registro a la base de datos.
      // En caso contrario, la función devuelve false, lo que significa que todo es correcto, y deja
      // añadir registros.
    }
    
    // Creo un array con todos los datos enviados desde el cliente.
    $campos = array("dni" => $_POST["dni"], "nombre" => $_POST["nombre"], "apellido" => $_POST["apellido"], "sexo" => $sexo, "fecha de nacimiento" => $_POST["fechanac"]);

    // Paso este array como parámetro a la función verifCamposVacios(), para la validación de los datos.
    $camposVacios = verifCamposVacios($campos);

    // Evalúo si $camposVacios es true o false.
    // Si es falso, quiere decir que no encontró errores, por lo tal puede añadir el registro.
    // Sin embargo, dentro de esta condicional, vuelve a evaluar si el DNI del array está en uso.
    // Si es así, devuelve true, y notifica al usuario que el DNI ya existe.
    // Caso contrario, prosigue con la operación para crear el nuevo registro.
    if ($camposVacios == false) {
      if (verificarDNI($_POST["dni"]) == true) {
        echo "<div class='notif-campo'>El DNI " . $_POST["dni"] . " ya está en uso.</div>";
      } else {
        $register = array(
          'dni' => strval($_POST["dni"]),
          'nombre' => $_POST["nombre"],
          'apellido' => $_POST["apellido"],
          'sexo' => $sexo,
          'fechanac' => $_POST["fechanac"]
        );
    
        create_register($register);
      }
    }
  };


  if (isset($_POST["borrar"])) {
    delete_register($_POST["borrar"]);
    echo "<div class='delete-registro'>Registro con DNI " . $_POST["borrar"] . " eliminado.</div>";
  }

  if (isset($_POST["editar"])) {
    $register = get_register_by_dni($_POST["editar"]);
    $formularioEdit = "<br><br><form class='form-editar' action='index.php' method='POST'>
      <input type='hidden' name='originaldni' value=" . $_POST["editar"] . " >
      <input type='number' name='dni' value=" . $register["dni"]. " > 
      <input type='text' name='nombre' value=" . $register["nombre"] . " > 
      <input type='text' name='apellido' value=" . $register["apellido"] . " > 
      <select name='sexo' id='sexo'>
          <option disabled selected>Seleccionar opción</option>
          <option value='masculino'" . ($register["sexo"] == "masculino" ? "selected" : ""). ">Masculino</option>
          <option value='femenino'" . ($register["sexo"] == "femenino" ? "selected" : "") . ">Femenino</option>
        </select>
      <input type='date' name='fechanac' value=" . $register["fecha_nac"] . " > 
      <input type='text' name='edad' readonly value=" . calculoEdad($register["fecha_nac"]) . " >
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

    $register = array(
      "dni" => $_POST["dni"], 
      "nombre" => $_POST["nombre"], 
      "apellido" => $_POST["apellido"], 
      "sexo" => $sexo, 
      "fechanac" => $_POST["fechanac"],
      "originaldni" => $_POST["originaldni"]
    );

    $camposVacios = verifCamposVacios($register);

    if ($camposVacios == false) {
      update_register($register);
      echo "<div class='edit-registro'>Registro actualizado correctamente.</div>";
    }
  }

  if (isset($_POST["retroceder"])) {
    $formularioEdit = "";
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
  <div class="buscador">
    <form action="<?php $_SERVER["PHP_SELF"]?>" method="GET">
      <input type="text" name="dni" placeholder="Buscar por DNI">
      <input type="text" name="nombre" placeholder="Buscar por nombre">
      <input type="text" name="apellido" placeholder="Buscar por apellido">
      <input type="submit" name="buscar" value="Buscar">
    </form>
  </div>
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
        <div class="encabezado-listado"><p class="titulo-listado">Listado de personas</p></div>
        <?php
            if (!isset($_GET["buscar"])) {
              $registers = get_registers();
              renderizarListado($registers);
            }

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