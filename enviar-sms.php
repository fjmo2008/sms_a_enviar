<?php
include('E:\xampp\htdocs\sms\httpPHPAltiria.php');
include('E:\xampp\htdocs\sms\log-sms.php');

require_once 'E:\xampp\htdocs\sms\config.php';

// $PRODUCCION
//    true -> Se envían los SMS
//    false -> No se envían los SMS, para hacer pruebas de registro del log sin enviar los sms
$PRODUCCION = true;

$altiriaSMS = new AltiriaSMS();

$altiriaSMS->setLogin(Config::USERNAME);
$altiriaSMS->setPassword(Config::PASSWORD);

$altiriaSMS->setSenderId(Config::SENDERID);

// Llama al webservice para recuperar los datos
// Definir la URL del Web Service
$urlvistas = Config::URL_VISTAS;
$url = Config::URL_A_ENVIAR;

// Crear los datos que se enviarán
$datos = array(
  "username" => Config::WS_USER,
  "password" => Config::WS_PASS,
  "centro" => Config::CENTRO_ID
);

// Codificar los datos a JSON
$json = json_encode($datos);

// Configurar las opciones de la petición
$opciones = array(
  "http" => array(
    "header" => "Content-Type: application/json",
    "method" => "POST",
    "content" => $json,
  ),
  "ssl"=>array(
    "verify_peer"=>false,
    "verify_peer_name"=>false,
  ),
);

// Crear el contexto de la petición
$contexto = stream_context_create($opciones);

// Realizar la petición de las vistas que hay que lanzar
$resultado = file_get_contents($urlvistas, false, $contexto);

// Decodificar la respuesta
$vistas = json_decode($resultado);

if (!is_null($vistas)) {
  for ($j=0;$j<count($vistas);$j++) {
    // Para todas las vistas
    // Crear los datos que se enviarán
    $datos = array(
      "username" => Config::WS_USER,
      "password" => Config::WS_PASS,
      "centro" => Config::CENTRO_ID,
      "vista" => $vistas[$j][3],
      "mensaje" => $vistas[$j][4]
    );

    // Solo enviamos las vistas que están activas
    if ($vistas[$j][5] == 'S') {
      // Mensaje para cada sms enviado, dependiendo de la vista a la que corresponda
      $SMSmensaje = $vistas[$j][4];

      // Codificar los datos a JSON
      $json = json_encode($datos);

      // Configurar las opciones de la petición
      $opciones = array(
        "http" => array(
          "header" => "Content-Type: application/json",
          "method" => "POST",
          "content" => $json,
        ),
        "ssl"=>array(
          "verify_peer"=>false,
          "verify_peer_name"=>false,
        ),
      );

      // Crear el contexto de la petición
      $contexto = stream_context_create($opciones);

      // Realizar la petición para obtener la lista de pacientes a enviar SMS
      $resultado = file_get_contents($url, false, $contexto);

      // Decodificar la respuesta
      $res = json_decode($resultado);

      // Si hay pacientes a los que enviar
      if (!is_null($res)) {
        // Abrimos el fichero log para escribir el registro historico
        $fp = fopen("e:\log\sms.log","a");

        // Conectamos con la bbdd
        $conn = conectar_bd();

        // Para cada paciente en la tabla de resultado $res
        for ($i=0;$i<count($res);$i++) {
          $centro = $res[$i][0];
          $cliente = $res[$i][1];
          $telefono = '34'.$res[$i][2];
          $idpac = $res[$i][3];
          $idprof = $res[$i][4];
          $esp = $res[$i][5];
          $tfinan = $res[$i][6];
          $edad = $res[$i][7];
          $gender_type = $res[$i][8];
          $sexo = $res[$i][9];
          $facogida = $res[$i][10];
          $mensaje = str_replace('DD',$res[$i][10] , $SMSmensaje);
          // EJEMPLO: 'Le recordamos su cita en Atencion Temprana el proximo '.$facogida.'. Si no puede asistir, le rogamos lo comunique lo antes posible. Gracias';

          // Respuesta por defecto
          $respuesta = 'El envio ha terminado con error';

          if ($PRODUCCION) {
            $response = $altiriaSMS->sendSMS($telefono, $mensaje);
            if (!$response) {
              $respuesta = "El envío ha terminado en error";
            } else {
              $respuesta = $response;
            }
          } else {
            $respuesta = 'El envio de prueba ha terminado OK';
          }
          
          $lineatxt = '{"fecha_sms":"'.date('d/m/Y H:i:s').'","destino":"'.$telefono.'","mensaje":"'.$mensaje.'","resultado":"'.$respuesta.'"}'.chr(10) ;
          echo $lineatxt.'<br>';
          fputs($fp,$lineatxt);

          // Graba los datos en la bbdd
          insertar_log($res, $mensaje, $respuesta, $i, $conn);
          
        }  
        fclose($fp);

        $conn->close();
      }

    }

  }
}

?>

