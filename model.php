<?php

function open_database_connection() {
  try {
      $dbh = new PDO('sqlite:personas.db');
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $dbh;
  } catch (PDOException $e) {
      print "¡Error!: " . $e->getMessage() . "<br/>";
      die();
  }
}

function close_database_connection(&$dbh) {
    $dbh = null;
}

function get_registers() {
  try {
    $registers = array();
    $dbh = open_database_connection();
    $stmt = $dbh->prepare("SELECT * FROM users");
    $stmt->execute();
    foreach ($stmt as $row) {
      $registers[] = $row;
    }
    close_database_connection($dbh);
    return $registers;
  } catch (Exception $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

function get_register_by_dni($dni) {
  try {
    $dbh = open_database_connection();
    $stmt = $dbh->prepare("SELECT * FROM users WHERE dni = ?");
    $stmt->bindParam(1, $dni);
    $stmt->execute();
    $register = $stmt->fetch();
    close_database_connection($dbh);
    return $register;
  } catch (Exception $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}

function create_register($data) {
  try {  
    $dni = $data["dni"];
    $nombre = $data["nombre"];
    $apellido = $data["apellido"];
    $sexo = $data["sexo"];
    $fechanac = $data["fechanac"];

    $dbh = open_database_connection();

    $dbh->beginTransaction();

    $stmt = $dbh->prepare("INSERT INTO users (dni, nombre, apellido, sexo, fecha_nac) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $dni);
    $stmt->bindParam(2, $nombre);
    $stmt->bindParam(3, $apellido);
    $stmt->bindParam(4, $sexo);
    $stmt->bindParam(5, $fechanac);
    $stmt->execute();
    
    $dbh->commit();

    close_database_connection($dbh);
  } catch (Exception $e) {
    $dbh->rollBack();
    echo "Failed: " . $e->getMessage();
  }
}

function delete_register($dni) {
  try {  
    $dbh = open_database_connection();

    $dbh->beginTransaction();

    $stmt = $dbh->prepare("DELETE FROM users WHERE dni = ?");
    $stmt->bindParam(1, $dni);
    $stmt->execute();
    
    $dbh->commit();

    close_database_connection($dbh);
  } catch (Exception $e) {
    $dbh->rollBack();
    echo "Failed: " . $e->getMessage();
  }
}

function update_register($data) {
  try {  
    $dbh = open_database_connection();

    $dbh->beginTransaction();

    $stmt = $dbh->prepare("UPDATE users SET dni = ?, nombre = ?, apellido = ?, sexo = ?, fecha_nac = ? WHERE dni = ?");
    $stmt->bindParam(1, $data["dni"]);
    $stmt->bindParam(2, $data["nombre"]);
    $stmt->bindParam(3, $data["apellido"]);
    $stmt->bindParam(4, $data["sexo"]);
    $stmt->bindParam(5, $data["fechanac"]);
    $stmt->bindParam(6, $data["originaldni"]);
    
    $stmt->execute();
    
    $dbh->commit();

    close_database_connection($dbh);
  } catch (Exception $e) {
    $dbh->rollBack();
    echo "Failed: " . $e->getMessage();
  }
}

function get_registers_by_filter($dni, $nombre, $apellido) {
  try {
    $registers = array();
    $dniquery = '%' . $dni . '%';
    $nombrequery = '%' . $nombre . '%';
    $apellidoquery = '%' . $apellido . '%';
    $dbh = open_database_connection();

    if (!empty($dni)) {
      $stmt = $dbh->prepare("SELECT * FROM users WHERE dni like ?");
      $stmt->bindParam(1, $dniquery);
      $stmt->execute();
      $registerswithDNI = $stmt->fetchAll();
      foreach($registerswithDNI as $register) {
        $registers[] = $register;
      }
      $stmt = null;
    }

    if (!empty($nombre)) {
      $stmt = $dbh->prepare("SELECT * FROM users WHERE nombre like ?");
      $stmt->bindParam(1, $nombrequery);
      $stmt->execute();
      $registerswithname = $stmt->fetchAll();
      foreach($registerswithname as $register) {
        $registers[] = $register;
      }
      $stmt = null;
    }

    if (!empty($apellido)) {
      $stmt = $dbh->prepare("SELECT * FROM users WHERE apellido like ?");
      $stmt->bindParam(1, $apellidoquery);
      $stmt->execute();
      $registerswithlastname = $stmt->fetchAll();
      foreach($registerswithlastname as $register) {
        $registers[] = $register;
      }
      $stmt = null;
    }

    close_database_connection($dbh);
    $registers = array_unique($registers, SORT_REGULAR);
    return $registers;
  } catch (Exception $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}