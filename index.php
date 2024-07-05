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
      <button type='submit' class='btn btn-danger mt-2' name='borrar' value=" . $persona['dni'] . ">Borrar</button>
      <button type='submit' class='btn btn-primary mt-2' name='editar' value = " . $persona['dni'] . ">Editar</button>
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
      if (empty($data) || $data == "indef") {
        echo "<div class='notif-campo alert alert-warning'>Debes seleccionar: $campo.</div>";
        $camposVacios = true;
        break;
      }
    }
    return $camposVacios;
  }


  // Condicional para visualizar los registros de manera manual realizando click sobre el botón HTML.
  if (isset($_POST["visualizar"])) {
    header("Location: index.php");
  }

  if (isset($_GET["buscar"])) {
    $registers = get_registers_by_filter($_GET["parametro"]);
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
        echo "<div class='notif-campo alert alert-warning'>El DNI " . $_POST["dni"] . " ya está en uso.</div>";
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
    echo "<div class='delete-registro alert alert-danger'>Registro con DNI " . $_POST["borrar"] . " eliminado.</div>";
  }

  if (isset($_POST["editar"])) {
    $register = get_register_by_dni($_POST["editar"]);
    $formularioEdit = "<div class='layer-edit'>
    <form class='form-editar' action='index.php' method='POST'>
      <input type='hidden' class='form-control' name='originaldni' value=" . $_POST["editar"] . " >
      <input type='number' class='form-control' name='dni' value=" . $register["dni"]. " > 
      <input type='text' class='form-control' name='nombre' value=" . $register["nombre"] . " > 
      <input type='text' class='form-control' name='apellido' value=" . $register["apellido"] . " > 
      <select class='form-select' name='sexo' id='sexo'>
          <option disabled selected>Seleccionar opción</option>
          <option value='masculino'" . ($register["sexo"] == "masculino" ? "selected" : ""). ">Masculino</option>
          <option value='femenino'" . ($register["sexo"] == "femenino" ? "selected" : "") . ">Femenino</option>
        </select>
      <input type='date' class='form-control' name='fechanac' value=" . $register["fecha_nac"] . " > 
      <input type='text' class='form-control' name='edad' readonly value=" . calculoEdad($register["fecha_nac"]) . " >
      <button type='submit' class='btn btn-success' name='actualizar'>Actualizar</button>
      <button type='submit' class='btn btn-danger' name='retroceder'>Cancelar</button>
    </form>
    </div>";
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
      echo "<div class='edit-registro alert alert-success'>Registro actualizado correctamente.</div>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="style.css" rel="stylesheet">
</head>
<body>
  <?php
      if (!empty($formularioEdit)) {
        echo $formularioEdit;
      }
    ?>
  <nav class="navbar nav-page bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand">ABM - Cristaldo Martín</a>
      <form action="<?php $_SERVER["PHP_SELF"]?>" method="GET" class="d-flex" role="search">
        <input class="form-control buscador me-2" type="search"  name="parametro" placeholder="Buscar por DNI, apellido o nombre" aria-label="Search">
        <button class="btn btn-outline-success" name="buscar" type="submit">Buscar</button>
      </form>
    </div>
  </nav>
  <div class="main">
    <div class="container">
      <div class="row">
        <div class="col">
            <form class="agregar-registro p-3" action="<?php $_SERVER["PHP_SELF"]?>" method="POST">
              <label for="dni">DNI</label>
              <input class="form-control" type="number" name="dni" id="dni">
              <br><br>
              <label for="nombre">Nombre</label>
              <input class="form-control" type="text" name="nombre" id="nombre">
              <br><br>
              <label for="apellido">Apellido</label>
              <input class="form-control" type="text" name="apellido" id="apellido">
              <br><br>
              <label for="sexo">Sexo</label>
              <select class="form-select" name="sexo" id="sexo">
                <option disabled selected>Seleccionar opción</option>
                <option value="masculino">Masculino</option>
                <option value="femenino">Femenino</option>
              </select>
              <br><br>
              <label for="fechanac">Fecha de nacimiento</label>
              <input class="form-control" type="date" name="fechanac" id="fechanac">
              <br><br>
              <button type="submit" name="agregar" class="btn btn-secondary">Enviar</button>
              <button type="submit" name="visualizar" class="btn btn-primary">Visualizar registros</button>
            </form>
        </div>

        <div class="col">
            <div class="encabezado-listado p-3"><p class="text-center">Listado de personas</p></div>
            <?php
                if (!isset($_GET["buscar"])) {
                  $registers = get_registers();
                  renderizarListado($registers);
                }

                foreach($HTMLPersonas as $person_HTML) {
                  echo $person_HTML;
                }

                ?>
        </div>
      </div>
    </div>
    </div>

</body>
</html>