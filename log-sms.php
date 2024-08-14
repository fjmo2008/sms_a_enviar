<?php

function conectar_bd() {
    $servername = "localhost";
    $username = "sms";
    $password = "pw_sms";
    $dbname = "sms";

    // Crear la conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Comprobar la conexión
    if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
    } 

    return $conn;
}


function insertar_log($resultado, $mensaje, $respuesta, $i, $conn) {

    $sql = "INSERT INTO sms_log (centro, fecha_envio, cliente, telefono, nhc, profesional, servicio, fecha_cita, mensaje, resultado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);

    // Vincular los parámetros
    $stmt->bind_param('ssssssssss', $centro, $fecha_envio, $cliente, $telefono, $nhc, $profesional, $servicio, $fecha_cita, $mensaje_a_enviar, $estado);

    $centro = $resultado[$i][0];
    $fecha_envio = date("Y-m-d H:i:s");
    $cliente = $resultado[$i][1];
    $telefono = '34'.$resultado[$i][2];
    $nhc = $resultado[$i][3];
    $profesional = $resultado[$i][4];
    $servicio = $resultado[$i][5];
    if (strlen($resultado[$i][10]) > 10) {
        $formato = 'd/m/Y H:i:s';
        $fecha = DateTime::createFromFormat($formato, $resultado[$i][10]);
        $fecha_cita = $fecha->format('Y-m-d H:i:s');
    } else {
        $formato = 'd/m/Y';
        $fecha = DateTime::createFromFormat($formato, $resultado[$i][10]);
        $fecha_cita = $fecha->format('Y-m-d');
    }
    
    $mensaje_a_enviar = $mensaje;
    $estado = $respuesta;

    $stmt->execute();

    // Comprobar si la consulta se ejecutó correctamente
    if ($stmt->error) {
    die("Error al ejecutar la consulta: " . $stmt->error);
    }

    // Cerrar la consulta
    $stmt->close();

}

//$conn = conectar_bd();
//insertar_log($resultado, $mensaje, $respuesta, $i, $conn);
//$conn->close();

?>
