<?php
// Establecer la zona horaria de Buenos Aires
date_default_timezone_set('America/Argentina/Buenos_Aires');
$provincias = array(
  "Buenos Aires" => "Hermoso día para pasear por la Costanera Sur!",
  "Catamarca" => "Lindo día para recorrer el centro histórico de San Fernando del Valle!",
  "Chaco" => "Hermosa jornada para visitar el Parque Nacional Chaco!",
  "Chubut" => "Lindo día para hacer una caminata por el Parque Nacional Los Alerces!",
  "Córdoba" => "Buen día para un paseo por el Parque Sarmiento!",
  "Corrientes" => "Hermosa jornada para navegar por el río Paraná!",
  "Entre Ríos" => "Lindo día para disfrutar de las playas de Gualeguaychú!",
  "Formosa" => "Buen día para conocer el Museo Histórico Regional.",
  "Jujuy" => "Hermoso día para recorrer la Quebrada de Humahuaca!",
  "La Pampa" => "Lindo día para hacer un picnic en el Parque Recreativo Don Tomás!",
  "La Rioja" => "Buen día para visitar el Parque Nacional Talampaya!",
  "Mendoza" => "Hermosa jornada para hacer una excursión al Cerro Aconcagua!",
  "Misiones" => "Lindo día para visitar las Ruinas de San Ignacio!",
  "Neuquén" => "Buen día para hacer una caminata por el Parque Nacional Lanín!",
  "Río Negro" => "Hermosa jornada para disfrutar de la costa del Lago Nahuel Huapi!",
  "Salta" => "Lindo día para subir al mirador del Cerro San Bernardo!",
  "San Juan" => "Buen día para visitar el Parque Provincial Ischigualasto!",
  "San Luis" => "Hermoso día para hacer un recorrido por la Villa de Merlo!",
  "Santa Cruz" => "Lindo día para navegar por el Lago Argentino y acercarse al Glaciar Perito Moreno!",
  "Santa Fe" => "Buen día para hacer una visita guiada por la Casa de Gobierno de Santa Fe!",
  "Santiago del Estero" => "Hermosa jornada para conocer el Monasterio de la Ciudad Sagrada de los Quilmes!",
  "Tierra del Fuego" => "Lindo día para hacer una caminata por el Parque Nacional!",
  "Tucumán" => "Buen día para visitar las ruinas de la antigua ciudad de Quilmes!",
  "Ciudad Autónoma de Buenos Aires" => "Hermosa jornada para caminar por los bosques de Palermo!"
);

$mensaje = "";
$archivo = "../visitas.csv";
$remoto = false;



// Obtener la dirección IP del cliente
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  $direccionIP = explode(':', $_SERVER['HTTP_CLIENT_IP'])[0];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $direccionIP = explode(':', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
} else {
  $direccionIP = $_SERVER['REMOTE_ADDR'];
  $remoto = true;
}






//Obtener información segun la ip
$access_key = '57cbf720-4c34-45c8-b854-5eca72b2090d';

// Initialize CURL
$ch = curl_init('http://apiip.net/api/check?ip=' . $direccionIP . '&accessKey=' . $access_key . '');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Store the data
$json_res = curl_exec($ch);
curl_close($ch);

// Decode JSON response
$api_result = json_decode($json_res, true);

//Gestionar el archivo
if (!file_exists($archivo)) {
  $csv = fopen($archivo, 'w');
  fclose($csv);
}
// Leer el archivo CSV y crear un array con los datos
$archivoCSV = file($archivo);
$numVisitas = (int) trim($archivoCSV[0]);
// Incrementar el contador en 1 y agregar la dirección IP y la fecha y hora actual al array
$numVisitas++;
$direccionesIP = array_map('str_getcsv', array_slice($archivoCSV, 1));
$direccionesIP = array_map(function($item) {return $item[0];}, $direccionesIP);

$cfiudad="";
if ($remoto == false) {
  // Asignar valores a las variables requeridas
  $ciudad = $api_result['city'];
  $pais = $api_result['countryName'];
  //Asignar mensaje a una provincia
  if (array_key_exists($ciudad, $provincias)) {
    $mensaje = $provincias[$ciudad];
  } else {
    $mensaje = "Hermosa Ciudad!";
  }

  // Crear un array con los valores asignados
  $data = array(
    'visitas' => $numVisitas,
    'ip' => $direccionIP,
    'ciudad' => $ciudad,
    'pais' => $pais,
    'latitud' => $api_result['latitude'],
    'longitud' => $api_result['longitude'],
    'mensaje' => $mensaje
  );
} else {
  $ciudad = "Salta";
  $pais = "Argentina";
  $latitud =  "1111111";
  $longitud = "2222222";
  //Asignar mensaje a una provincia
  if (array_key_exists($ciudad, $provincias)) {
    $mensaje = $provincias[$ciudad];
  } else {
    $mensaje = "Hermosa Ciudad!";
  }
  $data = array(
    'visitas' => $numVisitas,
    'ip' => $direccionIP,
    'ciudad' => $ciudad,
    'pais' => $pais,
    'latitud' => $latitud,
    'longitud' => $longitud,
    'mensaje' => $mensaje
  );
}




$direccionesIP[] = $direccionIP . " " . date("d/m/Y H:i"). " - Ciudad: " . $ciudad;

// Abrir el archivo CSV para escribir los datos
$csv = fopen($archivo, 'w');

// Escribir el número actual de visitas en la primera línea
fputcsv($csv, [$numVisitas]);

// Escribir cada dirección IP en una línea separada
foreach ($direccionesIP as $ip) {
    fputcsv($csv, [$ip]);
}

// Cerrar el archivo CSV
fclose($csv);


// Enviar la respuesta al cliente
echo json_encode($data);
