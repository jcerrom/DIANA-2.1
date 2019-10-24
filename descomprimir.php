<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DIANA 2.1 (DIALOGUE ANALYSIS) - Learning Analytics</title>
<link rel="stylesheet" href="estilosInicio.css" />

</head>

<body style="background-color:#FFF;">

<?php

function limpiarDir($dir) { // Función para borrar el contenido de un directorio 
foreach(glob($dir."/*") as $archivos_carpeta) {
	//si no es un directorio lo eliminamos 
	if (!is_dir($archivos_carpeta))
	unlink($archivos_carpeta);
} 
return 0;
}


/* Recuperamos del modelo metodológico a utilizar y sus fechas */
$modelo=$_POST["modelo"];
$fechas=explode("\n",trim($_POST["fechas"]));
$numFechas=count($fechas);

$fecha1=strtotime(str_replace('/', '-',$fechas[0]));
$fecha2=strtotime(str_replace('/', '-',$fechas[1]));
if ($modelo!="Descriptiu") {
	$fecha3=strtotime(str_replace('/', '-',$fechas[2]));
	if ($modelo=="Comparatiu") {
		$fecha4=strtotime(str_replace('/', '-',$fechas[3]));
	}
}



$contadorMensajesValidos=0;
$contadorMensajesNoValidos=0;
$contadorMensajesAnteriores=0;
$contadorMensajesPosteriores=0;

$fichero=$_POST["fichero"];

?>

<div align="center">

<p style="font-size:12px;"><br/>
<strong>RESUM PRE-ANÀLISI</strong><br/><br/>
Arxiu seleccionat: <?php echo $fichero; ?><br/>
Model metodològic aplicat: <?php echo $modelo; ?><br/><br/>

<?php 
// IMPRIMIMOS LA CABECERA
if ($modelo=="Descriptiu") { 
	echo "Data inicial: ".date("d M Y",$fecha1)." | Data final: ".date("d M Y",$fecha2);
} elseif ($modelo=="Progressiu") { 
	echo "Data inicial: ".date("d M Y",$fecha1)." | Fita: ".date("d M Y",$fecha2)." | Data final: ".date("d M Y",$fecha3);
} elseif ($modelo=="Comparatiu") {
	echo "Inici periode 1: ".date("d M Y",$fecha1)." | Fi periode 1: ".date("d M Y",$fecha2)."<br/>";
	echo "Inici periode 2: ".date("d M Y",$fecha3)." | Fi periode 2: ".date("d M Y",$fecha4);
}

?>

</p>

<?php
// PROCEDEMOS A DESCOMPRIMIR LOS MENSAJES EN FUNCIÓN DEL MODELO METODOLÓGICO

//Creamos un objeto de la clase ZipArchive()
$enzipado = new ZipArchive();
//Abrimos el archivo a descomprimir
$enzipado->open("./debates/".$fichero);
if ($modelo=="Descriptiu") { 
	// Borramos primero el contenido de la carpeta
	limpiarDir("debates/seleccionado");
	//Extraemos el contenido del archivo dentro de la carpeta especificada
	$extraido = $enzipado->extractTo("./debates/seleccionado/");
	if($extraido == FALSE){
 	echo '- Ocurrió un error y el archivo no se pudo descomprimir -';
	}
} elseif (($modelo=="Progressiu") || ($modelo=="Comparatiu")) { 
	// Borramos primero el contenido de las carpetas
	limpiarDir("debates/debate1");
	limpiarDir("debates/debate2");
	//Extraemos el contenido del archivo dentro de las carpetas especificadas
	$extraido = $enzipado->extractTo("./debates/debate1/");
	$extraido = $enzipado->extractTo("./debates/debate2/");
	if($extraido == FALSE){
 	echo '- Ocurrió un error y el archivo no se pudo descomprimir -';
	}
}

if ($modelo=="Descriptiu") { 
	$contador=0;
	$directorio = opendir("./debates/seleccionado/"); // Cargamos el directorio
	while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
	{
		if (!is_dir($archivos)) //Verificamos si es o no un directorio
		{
		   $mensaje= fopen("./debates/seleccionado/".$archivos, "r");
		   
		   $linea = fgets($mensaje);
		   // Cogemos la fecha de envío del mensaje
		   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
				$linea = fgets($mensaje);
		   }
		   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
		   $fecha=strtotime($fecha);
	
		   if (($fecha<$fecha1) || ($fecha>($fecha2)+86400)){
			   unlink("./debates/seleccionado/".$archivos);
			   $contador++;
			   if ($fecha<$fecha1) {
				   $contadorMensajesAnteriores++;
			   } else {
				   $contadorMensajesPosteriores++;
			   }
		   } else {
			   $contadorMensajesValidos++;
		   }
		   fclose($mensaje);
	
		}
	}
} elseif ($modelo=="Progressiu") {
	// EN PRIMER LUGAR DESCOMPRIMIMOS TENIENDO EN CUENTA FECHA1 Y FECHA2
	$contador=0;
	$directorio = opendir("./debates/debate1/"); // Cargamos el directorio
	while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
	{
		if (!is_dir($archivos)) //Verificamos si es o no un directorio
		{
		   $mensaje= fopen("./debates/debate1/".$archivos, "r");
		   
		   $linea = fgets($mensaje);
		   // Cogemos la fecha de envío del mensaje
		   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
				$linea = fgets($mensaje);
		   }
		   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
		   $fecha=strtotime($fecha);
	
		   if (($fecha<$fecha1) || ($fecha>($fecha2)+86400)){
			   unlink("./debates/debate1/".$archivos);
			   $contador++;
		   } 
		   fclose($mensaje);
	
		}
	}
	// EN SEGUNDO LUGAR DESCOMPRIMIMOS TENIENDO EN CUENTA FECHA1 Y FECHA3
	$contador=0;
	$directorio = opendir("./debates/debate2/"); // Cargamos el directorio
	while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
	{
		if (!is_dir($archivos)) //Verificamos si es o no un directorio
		{
		   $mensaje= fopen("./debates/debate2/".$archivos, "r");
		   
		   $linea = fgets($mensaje);
		   // Cogemos la fecha de envío del mensaje
		   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
				$linea = fgets($mensaje);
		   }
		   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
		   $fecha=strtotime($fecha);
	
		   if (($fecha<$fecha1) || ($fecha>($fecha3)+86400)){
			   unlink("./debates/debate2/".$archivos);
			   $contador++;
			   if ($fecha<$fecha1) {
				   $contadorMensajesAnteriores++;
			   } else {
				   $contadorMensajesPosteriores++;
			   }
		   } else {
			   $contadorMensajesValidos++;
		   }
		   fclose($mensaje);
	
		}
	}
} elseif ($modelo=="Comparatiu") {
	// EN PRIMER LUGAR DESCOMPRIMIMOS TENIENDO EN CUENTA FECHA1 Y FECHA2
	$contador=0;
	$directorio = opendir("./debates/debate1/"); // Cargamos el directorio
	while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
	{
		if (!is_dir($archivos)) //Verificamos si es o no un directorio
		{
		   $mensaje= fopen("./debates/debate1/".$archivos, "r");
		   
		   $linea = fgets($mensaje);
		   // Cogemos la fecha de envío del mensaje
		   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
				$linea = fgets($mensaje);
		   }
		   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
		   $fecha=strtotime($fecha);
	
		   if (($fecha<$fecha1) || ($fecha>($fecha2)+86400)){
			   unlink("./debates/debate1/".$archivos);
			   $contador++;
		   } else {
			   $contadorMensajesValidos++;
		   }
		   fclose($mensaje);
	
		}
	}
	// EN SEGUNDO LUGAR DESCOMPRIMIMOS TENIENDO EN CUENTA FECHA3 Y FECHA4
	$contador=0;
	$directorio = opendir("./debates/debate2/"); // Cargamos el directorio
	while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
	{
		if (!is_dir($archivos)) //Verificamos si es o no un directorio
		{
		   $mensaje= fopen("./debates/debate2/".$archivos, "r");
		   $contador++;
		   $linea = fgets($mensaje);
		   // Cogemos la fecha de envío del mensaje
		   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
				$linea = fgets($mensaje);
		   }
		   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
		   $fecha=strtotime($fecha);
	
		   if (($fecha<$fecha3) || ($fecha>($fecha4)+86400)){
			   unlink("./debates/debate2/".$archivos);
		   } else {
			   $contadorMensajesValidos++;
		   }
		   fclose($mensaje);
	
		}
	}
	$contadorMensajesNoValidos=$contador-$contadorMensajesValidos;
}



//A continuación se visualiza la gráfica de resumen
if ($modelo!="Comparatiu") {
	echo "<img src=\"http://chart.apis.google.com/chart?chs=400x100&amp;chd=t:".$contadorMensajesAnteriores.",".$contadorMensajesValidos.",".$contadorMensajesPosteriores."&amp;cht=p3&amp;chl=".$contadorMensajesAnteriores."|".$contadorMensajesValidos."|".$contadorMensajesPosteriores."&amp;&chco=FFFF00CC,00FF00,FF0000CC&chdl=Missatges anteriors descartats|Missatges vàlids|Missatges posteriors descartats&chdlp=r\" width=\"400\" height=\"100\">";
} else {
	echo "<img src=\"http://chart.apis.google.com/chart?chs=400x100&amp;chd=t:".$contadorMensajesValidos.",".$contadorMensajesNoValidos."&amp;cht=p3&amp;chl=".$contadorMensajesValidos."|".$contadorMensajesNoValidos."&amp;&chco=00FF00,FF0000CC&chdl=Missatges vàlids|Missatges no vàlids&chdlp=r\" width=\"400\" height=\"100\">";
}
?>

<p>
<a href="http://www.paucasals.com/diana" target="_top"><input type="button" value="Cancel·lar"></a>

<?php

if ($modelo=="Descriptiu") {
	echo "<a href=\"http://www.paucasals.com/diana/analisisDescriptivo.php?carpeta=debates/seleccionado&fecha1=".$fecha1."&fecha2=".$fecha2."\" target=\"_top\"><input type=\"button\" value=\"Mostrar anàlisi\">";
} elseif (($modelo=="Progressiu") || ($modelo=="Comparatiu")) {
	echo "<a href=\"http://www.paucasals.com/diana/analisisProgComp.php?modelo=".$modelo."&carpeta1=debates/debate1&carpeta2=debates/debate2&fecha1=".$fecha1."&fecha2=".$fecha2."&fecha3=".$fecha3."&fecha4=".$fecha4."\" target=\"_top\"><input type=\"button\" value=\"Mostrar anàlisi\">";
}

?>
</p>

</div>

</body>
</html>