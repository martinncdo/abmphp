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

function get_registers_by_filter($parametro) {
  try {
    $parametroQuery = '%' . $parametro . '%';
    $dbh = open_database_connection();

    
    $stmt = $dbh->prepare("SELECT * FROM users WHERE dni like ? or nombre like ? or apellido like ?");
    $stmt->bindParam(1, $parametroQuery);
    $stmt->bindParam(2, $parametroQuery);
    $stmt->bindParam(3, $parametroQuery);
    $stmt->execute();
    $registers = $stmt->fetchAll();

    close_database_connection($dbh);
    return $registers;
  } catch (Exception $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
  }
}