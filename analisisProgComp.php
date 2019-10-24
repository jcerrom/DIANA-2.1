<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DIANA 2.1 (DIALOGUE ANALYSIS) - Learning Analytics</title>
<link rel="stylesheet" type="text/css" href="estilosAnalisis.css" />

<script language="javascript">

function ocultarPanel(primera, segunda, tercera) {
	document.getElementById('panelMetricasGlobales').hidden=primera;
	document.getElementById('panelAlertas').hidden=segunda;
	document.getElementById('panelMetricasIndividuales').hidden=tercera;
	return 0;
}

</script>

</head>

<body>
<?php
// DECLARACIÓN DE FUNCIONES ------------------------------------------------------------------------
function standard_deviation($aValues)
{
    $fMean = array_sum($aValues) / count($aValues);
    $fVariance = 0.0;
    foreach ($aValues as $i)
    {
        $fVariance += pow($i - $fMean, 2);

    }       
    $size = count($aValues) - 1;
    return (float) sqrt($fVariance)/sqrt($size);
}

?>

<!-- MOSTRAMOS LA CABECERA DE LA PAGINA ----------------------------------------------->

<div id="contenedorPrincipal">

<?php $modelo=$_GET["modelo"]; ?>

<div id="cabecera"><strong>RESULTAT DE LES ANALÍTIQUES DE L'APRENENTATGE - DIANA 2.1</strong><br/>
Model metodològic aplicat: <?php echo strtoupper($modelo); ?><br/>
<strong>
<?php
$fecha1=$_GET["fecha1"];
$hora=date("G",$fecha1)*3600;
$minutos=date("i",$fecha1)*60;
$segundos=date("s",$fecha1);
$fecha1=$fecha1-$hora-$minutos-$segundos; // Quitamos las horas de la fecha

$fecha2=$_GET["fecha2"];
$hora=date("G",$fecha2)*3600;
$minutos=date("i",$fecha2)*60;
$segundos=date("s",$fecha2);
$fecha2=$fecha2-$hora-$minutos-$segundos; // Quitamos las horas de la fecha

if (($modelo=="Progressiu") || ($modelo=="Comparatiu")){
	$fecha3=$_GET["fecha3"];
	$hora=date("G",$fecha3)*3600;
	$minutos=date("i",$fecha3)*60;
	$segundos=date("s",$fecha3);
	$fecha3=$fecha3-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
}
if ($modelo=="Comparatiu"){
	$fecha4=$_GET["fecha4"];
	$hora=date("G",$fecha4)*3600;
	$minutos=date("i",$fecha4)*60;
	$segundos=date("s",$fecha4);
	$fecha4=$fecha4-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
}

if ($modelo=="Progressiu"){
	$dias1=round((($fecha2-$fecha1)/86400),0);
	$dias2=round((($fecha3-$fecha1)/86400),0);
	echo "- Primer període de ".$dias1." dies des de [".date("d M Y",$fecha1)."] al [".date("d M Y",$fecha2)."] -<br/>";
	echo "- Segon període de ".$dias2." dies des de [".date("d M Y",$fecha1)."] al [".date("d M Y",$fecha3)."] -<br/>";
}

if ($modelo=="Comparatiu"){
	$dias1=round((($fecha2-$fecha1)/86400),0);
	$dias2=round((($fecha4-$fecha3)/86400),0);
	echo "- Primer període de ".$dias1." dies des de [".date("d M Y",$fecha1)."] al [".date("d M Y",$fecha2)."] -<br/>";
	echo "- Segon període de ".$dias2." dies des de [".date("d M Y",$fecha3)."] al [".date("d M Y",$fecha4)."] -<br/>";
}
?>
</strong>
</div>

<br />

<div align="center" id="titulo">
<p>&copy; 2015-2019 Juan Pedro Cerro Martínez & Montse Guitert Catasús & Teresa Romeu Fontanillas. Tots els drets reservats pels autors.<br />
Resolució mínima recomanada: 1.366 x 768 píxels.</p>
</div>



<br />
<div class="titulo2" align="center"><strong></strong>
 &nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="TORNAR" style="alignment-baseline:central;" onClick="window.open('http://www.paucasals.com/diana/index.php','_self');"/>
 </div>
<br />

<?php 

// DEFINICIÓN DE VARIABLES GLOBALES -----------------------------------------------------------------------

$carpeta1=$_GET["carpeta1"];
$carpeta2=$_GET["carpeta2"];

$directorio1 = opendir("./".$carpeta1."/"); // Cargamos el directorio
$directorio2 = opendir("./".$carpeta2."/"); // Cargamos el directorio



// Creamos el fichero de salida
$ficheroSalida1=fopen("sna1.gexf","w"); 
$ficheroSalida2=fopen("sna2.gexf","w"); 
    
// Declaramos la lista de estudiantes para contar mensajes
$mensajesPorEstudiante1= array();
$mensajesPorEstudiante2= array();

// Declaramos la lista de estudiantes para contar mensajes de respuesta
$mensajesRespuestaPorEstudiante1= array();
$mensajesRespuestaPorEstudiante2= array();

// Declaramos la lista de estudiantes populares para contar respuestas
$popularidadPorEstudiante1= array();
$popularidadPorEstudiante2= array();

// Declaramos la lista de palabras promedio por estudiante
$palabrasPorEstudiante1= array();
$palabrasPorEstudiante2= array();

// Declaramos la lista de fechas (timestamp) de los mensajes de cada estudiante
$fechasMensajePorEstudiante1=array(array());
$fechasMensajePorEstudiante2=array(array());

// Declaramos el número de mensajes por día
$mensajesPorDia= array();

// Declaramos la lista con el número de enlaces por usuario
$enlacesPorEstudiante1= array(); 
$enlacesPorEstudiante2= array(); 

// Declaramos la lista con el número de ficheros adjuntos por usuario
$adjuntosPorEstudiante1= array(); 
$adjuntosPorEstudiante2= array(); 

// Declaramos el fichero de configuracion
$ficheroConfiguracion=fopen("conf.txt","r"); 

// Definimos el array con la lista de palabras de la conversación
$palabras1 = array();
$palabras2 = array();

// Declaramos la lista con los nombres de los ficheros adjuntos por usuario
$nombresAdjuntosPorEstudiant1e=array(array()); 
$nombresAdjuntosPorEstudiante2=array(array()); 

//Declaramos la lista de estudiantes que participan en la conversacion
$listaParticipantes1=array();
$listaParticipantes2=array();

//Declaramos la lista de direcciones de correo de cada estudiante que participa en la conversacion
$listaCorreos1=array();
$listaCorreos2=array();

// CARGAMOS LAS VARIABLES DEL FICHERO DE CONFIGURACION ------------------------------------------------
$severidad=fgets($ficheroConfiguracion);
$minimo=fgets($ficheroConfiguracion);
$maximo=fgets($ficheroConfiguracion);
$dispersion=fgets($ficheroConfiguracion);
$diasInactividad=fgets($ficheroConfiguracion);

$palabrasClave=array();
$linea=fgets($ficheroConfiguracion);
while (!feof($ficheroConfiguracion)) {
	$palabrasClave[mb_strtoupper(trim($linea),'UTF-8')]=0;
	$linea=fgets($ficheroConfiguracion);
}


// CARGAMOS LA LISTA DE ESTUDIANTES ---------------------------------------------------------------------
$ficheroEstudiantes=fopen("estudiantes.txt","r"); 
$listaEstudiantes=array();
$linea=fgets($ficheroEstudiantes);
while (!feof($ficheroEstudiantes)) {
	$linea=trim($linea);
	$listaEstudiantes[$linea]=0;
	$linea=fgets($ficheroEstudiantes);
}
fclose($ficheroEstudiantes);

// Ahora inicializamos el resto de listas (arrays) como la de estudiantes --------------------------------
$mensajesPorEstudiante1=$listaEstudiantes;
$mensajesPorEstudiante2=$listaEstudiantes;
$mensajesRespuestaPorEstudiante1=$listaEstudiantes;
$mensajesRespuestaPorEstudiante2=$listaEstudiantes;
$popularidadPorEstudiante1=$listaEstudiantes;
$popularidadPorEstudiante2=$listaEstudiantes;
$palabrasPorEstudiante1=$listaEstudiantes;
$palabrasPorEstudiante2=$listaEstudiantes;
$enlacesPorEstudiante1=$listaEstudiantes;
$enlacesPorEstudiante2=$listaEstudiantes;
$adjuntosPorEstudiante1=$listaEstudiantes;
$adjuntosPorEstudiante2=$listaEstudiantes;
$listaCorreos1=$listaEstudiantes;
$listaCorreos2=$listaEstudiantes;

// ###################### TRATAMIENTO PARA EL PERIODO 1 ######################


// CREACIÓN DEL FICHERO DE NODOS Y ARISTAS PARA IMPORTARLO A GEPHI DEL PRIMER PERIODO------------------------------

// Escribimos la cabecera del archivo GEPHI
fputs($ficheroSalida1,"<?xml version='1.0' encoding='UTF-8'?>\r\n<gexf xmlns='http://www.gexf.net/1.2draft' version='1.2'>\r\n<meta lastmodifieddate='2009-03-20'>\r\n<creator>Juan Pedro Cerro Martínez</creator>\r\n        <description>Generador de fitxers GEXF a partir d'espais de comunicació de la UOC en format d'aula nova</description>\r\n</meta>\r\n<graph mode='static' defaultedgetype='directed'>\r\n<nodes>\r\n");

// Declaramos el contador de mensajes
$numMensajes1=0;

// Declaramos el contador de respuestas
$numRespuestas1=0;

// Nos recorremos todo el directorio para detectar nodos y hacer estadística
while ($archivos = readdir($directorio1)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes1++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta1."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   
	   // De esa misma línea obtenemos la dirección de correo
	   $listaCorreos1[$estudiante]=utf8_encode(substr($linea,strpos($linea,"<")+1,strlen($linea)-strpos($linea,"<")-4));
	   
	   // Computamos este mensaje al contador del estudiante
	   if ($mensajesPorEstudiante1[$estudiante]>0) {
		   $mensajesPorEstudiante1[$estudiante]++;
	   } else {
		   $mensajesPorEstudiante1[$estudiante]=1;
	   }
	   
	   // Añadimos el estudiante a la lista de participantes
	   $listaParticipantes1[$estudiante]=0;
	   
	   // Cogemos la fecha de envío del mensaje
	   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
	   $fecha=strtotime($fecha);
	   $fechasMensajePorEstudiante1[$estudiante][sizeof($fechasMensajePorEstudiante1[$estudiante])]=$fecha;

	// Incrementamos el contador de mensajes por días
	   $hora=date("G",$fecha)*3600;
	   $minutos=date("i",$fecha)*60;
	   $segundos=date("s",$fecha);
	   $fechaConvertida=$fecha-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	   
	   if ($mensajesPorDia1[$fechaConvertida]==0){
		   $mensajesPorDia1[$fechaConvertida]=1;
	   } else {
		   $mensajesPorDia1[$fechaConvertida]++;
	   }
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));

	   // Escribimos la línea en el fichero de nodos
	   fputs($ficheroSalida1,"<node id=\"".$id."\" label=\"".$estudiante."\"></node>\r\n");

	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	
		if (!feof($mensaje)) {
			// Incrementamos el contador de respuestas
			$numRespuestas1++;
			
				   // Computamos este mensaje al contador de respuestas del estudiante
	   				if ($mensajesRespuestaPorEstudiante1[$estudiante]>0) {
		   				$mensajesRespuestaPorEstudiante1[$estudiante]++;
	   				} else {
		   				$mensajesRespuestaPorEstudiante1[$estudiante]=1;
	   				}

			
			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			
		// Incrementamos el contador de estudiantes populares
			
			// Declaramos la variable de apertura temporal de ficheros respuesta
			$ficheroRespuesta1=fopen("./".$carpeta1."/".$idRespuesta.".mail", "r");
			// Buscamos el nombre del estudiante al que se le responde
	   		$linea = fgets($ficheroRespuesta1);
	   		$estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
			// Computamos este mensaje de respuesta al estudiante
	   		if ($popularidadPorEstudiante1[$estudiante]>0) {
				$popularidadPorEstudiante1[$estudiante]++;
	   		} else {
		   		$popularidadPorEstudiante1[$estudiante]=1;
	   		}
			fclose($ficheroRespuesta1);
			
		}
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Acabamos con los nodos
fputs($ficheroSalida1,"</nodes>\r\n");

// Cerramos el directorio liberando recursos
closedir($directorio1);

// Empezamos con las aristas
fputs($ficheroSalida1,"<edges>\r\n");

$idArista=0; // Definimos el contador de aristas

// Recorremos una segunda vez el directorio para guardar las aristas

$directorio1 = opendir("./".$carpeta1."/"); // Cargamos el directorio de nuevo
while ($archivos = readdir($directorio1)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   $mensaje= fopen("./".$carpeta1."/".$archivos, "r");
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));
	   
	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }

		if (!feof($mensaje)) {

			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			// Escribimos la linea en el fichero de aristas
			fputs($ficheroSalida1,"<edge source=\"".$id."\" target=\"".$idRespuesta."\" type=\"directed\" id=\"".$idArista++."\" weight=\"1\"></edge>\r\n");

		}
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Escribimos el final del archivo
fputs($ficheroSalida1,"</edges>\r\n</graph>\r\n</gexf>");

// Cerramos el fichero de salida
fclose($ficheroSalida1);

// Cerramos el directorio liberando recursos
closedir($directorio1);

// VOLVEMOS A RECORRER LOS ARCHIVOS PARA COMPUTAR EL NÚMERO DE PALABRAS PROMEDIO
// Y CONTAR EL NÚMERO DE ENLACES Y ADJUNTOS QUE TIENE EL MENSAJE DEL PRIMER PERIODO

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes

$directorio1 = opendir("./".$carpeta1."/"); // Cargamos el directorio de nuevo

while ($archivos = readdir($directorio1)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta1."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);

	   // Comenzamos a leer el cuerpo del mensaje para contar las palabras que contiene
	   
	   if (!feof($mensaje)) {
		   $linea =  utf8_encode(quoted_printable_decode(fgets($mensaje)));
		   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			   $numPalabras = str_word_count($linea);
				// Sumamos el número de palabras al estudiante
	   			if ($palabrasPorEstudiante1[$estudiante]>0) {
					$palabrasPorEstudiante1[$estudiante]+=$numPalabras;
	   			} else {
		   			$palabrasPorEstudiante1[$estudiante]=$numPalabras;
	   			}
			   $linea =  utf8_encode(quoted_printable_decode(fgets($mensaje)));
		   } 
	   }


	   // Buscamos la parte del mensaje que está en formato HTML para contar los enlaces que contiene
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,23))!="content-type: text/html") && (!feof($mensaje))) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
	   }

	   // Saltamos 2 líneas
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);

	   $linea = fgets($mensaje);
	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
		   if ((strpos(strtolower($linea),"<a ")!=false) || (strpos(strtolower($linea),"http")!=false)) {
			   // Sumamos el número de enlaces al estudiante
   				if ($enlacesPorEstudiante1[$estudiante]>0) {
					$enlacesPorEstudiante1[$estudiante]++;
   				} else {
	   				$enlacesPorEstudiante1[$estudiante]=1;
   				}
		   }
		   	   
		   $linea =  fgets($mensaje);
	   } 



	   // Buscamos si el mensaje contiene alguna otra sección que indicará si hay ficheros
 	   while (!feof($mensaje)) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
			if (utf8_encode(substr($linea,0,13))=="Content-Type:") {
			   // Sumamos el número de adjuntos al estudiante
   				if ($adjuntosPorEstudiante1[$estudiante]>0) {
					$adjuntosPorEstudiante1[$estudiante]++;
   				} else {
	   				$adjuntosPorEstudiante1[$estudiante]=1;
   				}
				
				// A continuación, rescatamos el nombre del archivo y lo añadimos la lista del estudiante
				if (strpos($linea," name=")==false) {
					$linea = htmlspecialchars_decode(fgets($mensaje));
				}
					
				$valor=str_replace("\"","",substr($linea,strpos($linea," name=")+6,strlen($linea)-strpos($linea," name=")+6));
				$nombresAdjuntosPorEstudiante1[$estudiante][sizeof($nombresAdjuntosPorEstudiante1[$estudiante])]=$valor;
			
			}
	   }
   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// A CONTINUACIÓN NOS RECORREMOS EL DIRECTORIO PARA LEER TODOS LOS MENSAJES
// Y CREAR EL FICHERO "TAGCLOUD1.TXT" CON EL CONTENIDO TEXTUAL SIN LOS CARACTERES
// DELIMITADORES, Y TAMBIÉN CREAMOS EL ARRAY CON LA LISTA DE PALABRAS Y EL CÁLCULO
// DE SUS APARICIONES -----------------------------------------------------------------------------------


$directorio1 = opendir("./".$carpeta1."/"); // Cargamos el directorio

// Creamos el fichero de salida y escribimos la cabecera
$ficheroSalida1=fopen("tagcloud1.txt","w"); 

$numMensajes1=0;

// Definimos los delimitadores
$delimitadores=array("_","[","]","¡","(",")","="," ",",",":","|","-","&",";","?","¿","*","!",".","/","\\","'","\"");

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes
while ($archivos = readdir($directorio1)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes1++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta1."/".$archivos, "r");
	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);
	   // Comenzamos a leer el cuerpo del mensaje
	   
	   if (!feof($mensaje)) {

		   $linea =  utf8_encode(quoted_printable_decode((fgets($mensaje))));
		   while ((substr($linea,0,38)!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			  $linea=str_replace($delimitadores," ",$linea);
			  $lista = explode(" ", $linea); 
			  foreach ($lista as $palabra) {
			  	$palabra=mb_strtoupper($palabra,'UTF-8');
				if (strlen($palabra)>4) {
					if ($palabras1[$palabra]==0) {
					  $palabras1[$palabra]=1;
					} else {
					  $palabras1[$palabra]++;
					}
				}
			  } 
				
			   fputs($ficheroSalida1,utf8_decode($linea)."\r\n");
			   
		   $linea =  utf8_encode(quoted_printable_decode((fgets($mensaje))));
		   } 
		   
	   }
	   else {
		   // echo "<blockquote>No conté cos del missatge</blockquote>";
	   }
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Cerramos el fichero de salida
fclose($ficheroSalida1);

// Cerramos el directorio liberando recursos
closedir($directorio1);



















// ###################### TRATAMIENTO PARA EL PERIODO 2 ######################


// CREACIÓN DEL FICHERO DE NODOS Y ARISTAS PARA IMPORTARLO A GEPHI DEL SEGUNDO PERIODO------------------------------

// Escribimos la cabecera del archivo GEPHI
fputs($ficheroSalida2,"<?xml version='1.0' encoding='UTF-8'?>\r\n<gexf xmlns='http://www.gexf.net/1.2draft' version='1.2'>\r\n<meta lastmodifieddate='2009-03-20'>\r\n<creator>Juan Pedro Cerro Martínez</creator>\r\n        <description>Generador de fitxers GEXF a partir d'espais de comunicació de la UOC en format d'aula nova</description>\r\n</meta>\r\n<graph mode='static' defaultedgetype='directed'>\r\n<nodes>\r\n");

// Declaramos el contador de mensajes
$numMensajes2=0;

// Declaramos el contador de respuestas
$numRespuestas2=0;

// Nos recorremos todo el directorio para detectar nodos y hacer estadística
while ($archivos = readdir($directorio2)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes2++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta2."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   
	   // De esa misma línea obtenemos la dirección de correo
	   $listaCorreos2[$estudiante]=utf8_encode(substr($linea,strpos($linea,"<")+1,strlen($linea)-strpos($linea,"<")-4));
	   
	   // Computamos este mensaje al contador del estudiante
	   if ($mensajesPorEstudiante2[$estudiante]>0) {
		   $mensajesPorEstudiante2[$estudiante]++;
	   } else {
		   $mensajesPorEstudiante2[$estudiante]=1;
	   }
	   
	   // Añadimos el estudiante a la lista de participantes
	   $listaParticipantes2[$estudiante]=0;
	   
	   // Cogemos la fecha de envío del mensaje
	   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
	   $fecha=strtotime($fecha);
	   $fechasMensajePorEstudiante2[$estudiante][sizeof($fechasMensajePorEstudiante2[$estudiante])]=$fecha;

	// Incrementamos el contador de mensajes por días
	   $hora=date("G",$fecha)*3600;
	   $minutos=date("i",$fecha)*60;
	   $segundos=date("s",$fecha);
	   $fechaConvertida=$fecha-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	   
	   if ($mensajesPorDia2[$fechaConvertida]==0){
		   $mensajesPorDia2[$fechaConvertida]=1;
	   } else {
		   $mensajesPorDia2[$fechaConvertida]++;
	   }
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));

	   // Escribimos la línea en el fichero de nodos
	   fputs($ficheroSalida2,"<node id=\"".$id."\" label=\"".$estudiante."\"></node>\r\n");

	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	
		if (!feof($mensaje)) {
			// Incrementamos el contador de respuestas
			$numRespuestas2++;
			
				   // Computamos este mensaje al contador de respuestas del estudiante
	   				if ($mensajesRespuestaPorEstudiante2[$estudiante]>0) {
		   				$mensajesRespuestaPorEstudiante2[$estudiante]++;
	   				} else {
		   				$mensajesRespuestaPorEstudiante2[$estudiante]=1;
	   				}

			
			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			
		// Incrementamos el contador de estudiantes populares
			
			// Declaramos la variable de apertura temporal de ficheros respuesta
			$ficheroRespuesta2=fopen("./".$carpeta2."/".$idRespuesta.".mail", "r");
			// Buscamos el nombre del estudiante al que se le responde
	   		$linea = fgets($ficheroRespuesta2);
	   		$estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
			// Computamos este mensaje de respuesta al estudiante
	   		if ($popularidadPorEstudiante2[$estudiante]>0) {
				$popularidadPorEstudiante2[$estudiante]++;
	   		} else {
		   		$popularidadPorEstudiante2[$estudiante]=1;
	   		}
			fclose($ficheroRespuesta2);
			
		}
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Acabamos con los nodos
fputs($ficheroSalida2,"</nodes>\r\n");

// Cerramos el directorio liberando recursos
closedir($directorio2);

// Empezamos con las aristas
fputs($ficheroSalida2,"<edges>\r\n");

$idArista=0; // Definimos el contador de aristas

// Recorremos una segunda vez el directorio para guardar las aristas

$directorio2 = opendir("./".$carpeta2."/"); // Cargamos el directorio de nuevo
while ($archivos = readdir($directorio2)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   $mensaje= fopen("./".$carpeta2."/".$archivos, "r");
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));
	   
	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }

		if (!feof($mensaje)) {

			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			// Escribimos la linea en el fichero de aristas
			fputs($ficheroSalida2,"<edge source=\"".$id."\" target=\"".$idRespuesta."\" type=\"directed\" id=\"".$idArista++."\" weight=\"1\"></edge>\r\n");

		}
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Escribimos el final del archivo
fputs($ficheroSalida2,"</edges>\r\n</graph>\r\n</gexf>");

// Cerramos el fichero de salida
fclose($ficheroSalida2);

// Cerramos el directorio liberando recursos
closedir($directorio2);

// VOLVEMOS A RECORRER LOS ARCHIVOS PARA COMPUTAR EL NÚMERO DE PALABRAS PROMEDIO
// Y CONTAR EL NÚMERO DE ENLACES Y ADJUNTOS QUE TIENE EL MENSAJE DEL SEGUNDO PERIODO

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes

$directorio2 = opendir("./".$carpeta2."/"); // Cargamos el directorio de nuevo

while ($archivos = readdir($directorio2)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta2."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);

	   // Comenzamos a leer el cuerpo del mensaje para contar las palabras que contiene
	   
	   if (!feof($mensaje)) {
		   $linea =  utf8_encode(quoted_printable_decode(fgets($mensaje)));
		   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			   $numPalabras = str_word_count($linea);
				// Sumamos el número de palabras al estudiante
	   			if ($palabrasPorEstudiante2[$estudiante]>0) {
					$palabrasPorEstudiante2[$estudiante]+=$numPalabras;
	   			} else {
		   			$palabrasPorEstudiante2[$estudiante]=$numPalabras;
	   			}
			   $linea =  utf8_encode(quoted_printable_decode(fgets($mensaje)));
		   } 
	   }


	   // Buscamos la parte del mensaje que está en formato HTML para contar los enlaces que contiene
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,23))!="content-type: text/html") && (!feof($mensaje))) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
	   }

	   // Saltamos 2 líneas
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);

	   $linea = fgets($mensaje);
	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
		   if ((strpos(strtolower($linea),"<a ")!=false) || (strpos(strtolower($linea),"http")!=false)) {
			   // Sumamos el número de enlaces al estudiante
   				if ($enlacesPorEstudiante2[$estudiante]>0) {
					$enlacesPorEstudiante2[$estudiante]++;
   				} else {
	   				$enlacesPorEstudiante2[$estudiante]=1;
   				}
		   }
		   	   
		   $linea =  fgets($mensaje);
	   } 



	   // Buscamos si el mensaje contiene alguna otra sección que indicará si hay ficheros
 	   while (!feof($mensaje)) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
			if (utf8_encode(substr($linea,0,13))=="Content-Type:") {
			   // Sumamos el número de adjuntos al estudiante
   				if ($adjuntosPorEstudiante2[$estudiante]>0) {
					$adjuntosPorEstudiante2[$estudiante]++;
   				} else {
	   				$adjuntosPorEstudiante2[$estudiante]=1;
   				}
				
				// A continuación, rescatamos el nombre del archivo y lo añadimos la lista del estudiante
				if (strpos($linea," name=")==false) {
					$linea = htmlspecialchars_decode(fgets($mensaje));
				}
					
				$valor=str_replace("\"","",substr($linea,strpos($linea," name=")+6,strlen($linea)-strpos($linea," name=")+6));
				$nombresAdjuntosPorEstudiante2[$estudiante][sizeof($nombresAdjuntosPorEstudiante2[$estudiante])]=$valor;
			
			}
	   }
   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// A CONTINUACIÓN NOS RECORREMOS EL DIRECTORIO PARA LEER TODOS LOS MENSAJES
// Y CREAR EL FICHERO "TAGCLOUD2.TXT" CON EL CONTENIDO TEXTUAL SIN LOS CARACTERES
// DELIMITADORES, Y TAMBIÉN CREAMOS EL ARRAY CON LA LISTA DE PALABRAS Y EL CÁLCULO
// DE SUS APARICIONES -----------------------------------------------------------------------------------


$directorio2 = opendir("./".$carpeta2."/"); // Cargamos el directorio

// Creamos el fichero de salida y escribimos la cabecera
$ficheroSalida2=fopen("tagcloud2.txt","w"); 

$numMensajes2=0;

// Definimos los delimitadores
$delimitadores=array("_","[","]","¡","(",")","="," ",",",":","|","-","&",";","?","¿","*","!",".","/","\\","'","\"");

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes
while ($archivos = readdir($directorio2)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes2++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta2."/".$archivos, "r");
	   
	   // Buscamos el inicio del cuerpo del mensaje
	   $linea = htmlspecialchars_decode(fgets($mensaje));
 	   while ((utf8_encode(substr($linea,0,38))!="----------------UOCGENERATED_multipart") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   
	   // Saltamos 3 líneas
	    $linea = fgets($mensaje);
		$linea = fgets($mensaje);
		$linea = fgets($mensaje);
	   // Comenzamos a leer el cuerpo del mensaje
	   
	   if (!feof($mensaje)) {

		   $linea =  utf8_encode(quoted_printable_decode((fgets($mensaje))));
		   while ((substr($linea,0,38)!="----------------UOCGENERATED_multipart") && (!feof($mensaje))){
			  $linea=str_replace($delimitadores," ",$linea);
			  $lista = explode(" ", $linea); 
			  foreach ($lista as $palabra) {
			  	$palabra=mb_strtoupper($palabra,'UTF-8');
				if (strlen($palabra)>4) {
					if ($palabras2[$palabra]==0) {
					  $palabras2[$palabra]=1;
					} else {
					  $palabras2[$palabra]++;
					}
				}
			  } 
				
			   fputs($ficheroSalida2,utf8_decode($linea)."\r\n");
			   
		   $linea =  utf8_encode(quoted_printable_decode((fgets($mensaje))));
		   } 
		   
	   }
	   else {
		   // echo "<blockquote>No conté cos del missatge</blockquote>";
	   }
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Cerramos el fichero de salida
fclose($ficheroSalida2);

// Cerramos el directorio liberando recursos
closedir($directorio2);






















// AHORA COMENZAMOS A VISUALIZAR LOS RESULTADOS ------------------------------------------------------------

?>




<table id="rejillaPantalla" cols="2" cellpadding="0px" cellspacing="0px" align="center">
<tr>
<td valign="top">
	<div id="metricasGlobales" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(false,true,true);">
    <img src="img/equipo.png"><br/>
    <strong>IND./MÈTRIQUES<BR/>GLOBALS</strong>
	</div>
    
	<div id="alertas" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,false,true);">
    <img src="img/campana.png" style="margin-top:5px;"><br/>
    <strong>ALERTES</strong>
	</div>
    
	<div id="metricasIndividuales" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,false);">
    <img src="img/estudiante.png"><br/>
    <strong>IND./MÈTRIQUES<BR/>INDIVIDUALS</strong>
	</div>
</td>
<td rowspan="8" valign="top">





















	<div id="panelMetricasGlobales" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE MÉTRICAS GLOBALES -->
    
        <div class="tituloPanel"><p><strong>PANELL D'INDICADORS I MÈTRIQUES GLOBALS<br/></strong></p></div>
			<table cols="3" width="1050px" align="center" cellpadding="0" cellspacing="0" style="background-color:#EEE; line-height:25px;">
            	<tr style="line-height:50px;">
            		<td class="tituloPanel" width="350px" style="color:black; text-decoration:underline;">
					<?php echo "[".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]"; ?>
					</td>
            		<td class="tituloPanel" width="350px" style="color:black; text-decoration:underline;">
					<?php 
						if ($modelo=="Progressiu") {
							echo "[".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]"; 
						} elseif ($modelo=="Comparatiu") {
 							echo "[".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]";
						}
                     ?>
					</td>
            		<td class="tituloPanel" width="350px" style="background-color:#EEE; text-decoration:underline;">
						DIFERÈNCIA			
                    </td>
            	</tr>
                
                
				<tr>
                
                <!-- PRIMERA COLUMNA Y PRIMERA FILA ----------------------------------------------------------------->
                
                	<td aling="center" width="300px" style="font-size:12px;">
                        <img src="img/usuarios.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        <?php
                        //Mostrar el número total de usuarios participantes
                        echo " Participants: <strong>".count($listaParticipantes1)." de ".count($mensajesPorEstudiante1)." (".(round(count($listaParticipantes1)/count($mensajesPorEstudiante1)*100,2))."% del total)</strong><br/>";
                        ?>
                        <img src="img/mensajes.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" />
                        <?php
                        //Mostrar el número total de mensajes
                        echo " Missatges totals: <strong>".$numMensajes1."</strong><br/>";
                        ?>
                        <img src="img/homogeneidad.png" width="20px" style="vertical-align:middle; margin:0px 3px 3px 3px;" />
                        <?php
                        //Mostrar el grado de homogeneidad
                        if (array_sum($mensajesPorEstudiante1)==0) {
							$valor2=0;
						} else {
                        $valor1=1-(standard_deviation($mensajesPorEstudiante1)/((max($mensajesPorEstudiante1)+min($mensajesPorEstudiante1))/2));
						}
                        $valor1*=100;
                        $valor1=round($valor1,2);
                        echo " <span title='Mostra el grau d’igualtat en la participació dels usuaris dins els espais de comunicació asíncrona. Un 100% indicaria que tots els estudiants han participat amb el mateix nombre de missatges. El grau d’homogeneïtat mesura la dispersió mitja del nombre de missatges publicats per cada usuari en base a la mitja global.'>Homogeneïtat participativa:</span> <strong>".$valor1."%</strong><br/>";
                        ?>
                        <img src="img/responder.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Missatges de resposta totals: <strong>".$numRespuestas1."</strong><br/>";
                        ?>
                        <img src="img/dialogo.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " <span title='Mostra el grau de reciprocitat en el lliurament de missatges dins la conversa, mitjançant la relació entre el número total de missatges resposta i els missatges totals publicats.'>Nivell de diàleg (resp. vs. miss.):</span> <strong>".(round($numRespuestas1/($numMensajes1-1),4)*100)."%</strong><br/>";
                        ?> 
                        <img src="img/expandir.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        <?php
                        //Mostrar el nivell de dispersió del debat
                        echo " <span title='Mostra el grau de dispersió d’una conversa asíncrona a través del còmput dels missatges publicats i les respostes rebudes. Una conversa concentrada es aquella on hi ha pocs fils de debat però amb moltes respostes penjant d’elles, mentre que una conversa molt dispersa conté molts fils de debat oberts però amb poques o cap resposta al seu interior.'>Dispersió: </span><strong>";
                            $valor=round((($numMensajes1-1)/$numMensajes1)-($numRespuestas1/$numMensajes1),2);
                                if ($valor>0.55){
                                echo "Conversa dispersa";
                            } elseif ($valor>=0.45) {
                                echo "Conversa equilibrada";
                            } else {
                                echo "Conversa concentrada";
                            }
                        echo " (".$valor*100 ."%)</strong><br>";
                        ?>
                        <img src="img/archivos_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Nombre d’arxius globals: <strong>".array_sum($adjuntosPorEstudiante1)."</strong><br/>";
                        ?>
                        <img src="img/enlaces_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Nombre d’enllaços externs globals: <strong>".array_sum($enlacesPorEstudiante1)."</strong>";
                        //Mostrar el enlace al fichero de nube de etiquetas
                        ?> 
                       <br/>
                       <img src="img/extension.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      Extensió mitjana: <strong>
                       <?php
                       echo round(array_sum($palabrasPorEstudiante1)/array_sum($mensajesPorEstudiante1),0);
                       ?>   
                        paraules</strong><br />
                        <img src="img/grafo_nodos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        
                         Graf de nodes .gexf (GEPHI): <a href="sna1.gexf" target="_blank">sna1.gexf</a>

                        <br/>
                       <img src="img/nube_etiquetas.gif" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      Contingut textual: <a href="tagcloud1.txt" target="_blank">tagcloud1.txt</a><br/>
                       <img src="img/semantica.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      <span title="Percentatge de la conversa que conté les paraules clau definides pel professor i que defineixen el camp semàntic desitjat per a la comunicació.">Grau d'adequació del discurs al camp semàntic:</span><br />
                        <?php
                        
                        $totalPalabras1=array_sum($palabras1);
                        $totalPalabrasClave1=0;
                        foreach ($palabrasClave as $palabra=>$valor) {
                            if ($palabra!=NULL) {
                                $totalPalabrasClave1+=$palabras1[$palabra];
                            }
                        }
                    
                        $valor=(($totalPalabrasClave1/$totalPalabras1)*100)*100/$severidad;
                        echo "<div style=\"text-align:center;\"><img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".$valor."&chl=".round(($totalPalabrasClave1/$totalPalabras1)*100,2)."%&chf=bg,s,ffffff00&chco=ff0000,ffff00,00ff00\"><br />";
                                    echo "Grau de severitat establert: ".$severidad."%";
                                    echo "</div>";
						?>
						<br/><div style="text-align:left; color:#000; font-family:Verdana, Geneva, sans-serif; font-size:12px;"><strong>Detall de l'aplicació del camp semàntic:</strong></div><br/>
						<?php
                         
                         foreach ($palabrasClave as $palabra=>$valor) {
                             if ($palabra!=NULL) {
                                 echo "&nbsp;&nbsp;&nbsp;- ".$palabra." (<span style=\"color:#F00\">".round((($palabras1[$palabra]/$totalPalabras1)*100),2)."%</span>)<br />";
                             }
                         }
                         ?>
                         <br/>

            		</td>

                <!-- SEGUNDA COLUMNA Y PRIMERA FILA ----------------------------------------------------------------->
                
                	<td aling="center" width="300px" style="font-size:12px;">
                        <img src="img/usuarios.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        <?php
                        //Mostrar el número total de usuarios participantes
                        echo " Participants: <strong>".count($listaParticipantes2)." de ".count($mensajesPorEstudiante2)." (".(round(count($listaParticipantes2)/count($mensajesPorEstudiante2)*100,2))."% del total)</strong><br/>";
                        ?>
                        <img src="img/mensajes.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" />
                        <?php
                        //Mostrar el número total de mensajes
                        echo " Missatges totals: <strong>".$numMensajes2."</strong><br/>";
                        ?>
                        <img src="img/homogeneidad.png" width="20px" style="vertical-align:middle; margin:0px 3px 3px 3px;" />
                        <?php
                        //Mostrar el grado de homogeneidad
                        if (array_sum($mensajesPorEstudiante2)==0) {
							$valor2=0;
						} else {
							$valor2=1-(standard_deviation($mensajesPorEstudiante2)/((max($mensajesPorEstudiante2)+min($mensajesPorEstudiante2))/2));
						}
                        $valor2*=100;
                        $valor2=round($valor2,2);
                        echo " <span title='Mostra el grau d’igualtat en la participació dels usuaris dins els espais de comunicació asíncrona. Un 100% indicaria que tots els estudiants han participat amb el mateix nombre de missatges. El grau d’homogeneïtat mesura la dispersió mitja del nombre de missatges publicats per cada usuari en base a la mitja global.'>Homogeneïtat participativa:</span> <strong>".$valor2."%</strong><br/>";
                        ?>
                        <img src="img/responder.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Missatges de resposta totals: <strong>".$numRespuestas2."</strong><br/>";
                        ?>
                        <img src="img/dialogo.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " <span title='Mostra el grau de reciprocitat en el lliurament de missatges dins la conversa, mitjançant la relació entre el número total de missatges resposta i els missatges totals publicats.'>Nivell de diàleg (resp. vs. miss.):</span> <strong>".(round($numRespuestas2/($numMensajes2-1),4)*100)."%</strong><br/>";
                        ?> 
                        <img src="img/expandir.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        <?php
                        //Mostrar el nivell de dispersió del debat
                        echo " <span title='Mostra el grau de dispersió d’una conversa asíncrona a través del còmput dels missatges publicats i les respostes rebudes. Una conversa concentrada es aquella on hi ha pocs fils de debat però amb moltes respostes penjant d’elles, mentre que una conversa molt dispersa conté molts fils de debat oberts però amb poques o cap resposta al seu interior.'>Dispersió: </span><strong>";
                            $valor=round((($numMensajes2-1)/$numMensajes2)-($numRespuestas2/$numMensajes2),2);
                                if ($valor>0.55){
                                echo "Conversa dispersa";
                            } elseif ($valor>=0.45) {
                                echo "Conversa equilibrada";
                            } else {
                                echo "Conversa concentrada";
                            }
                        echo " (".$valor*100 ."%)</strong><br>";
                        ?>
                        <img src="img/archivos_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Nombre d’arxius globals: <strong>".array_sum($adjuntosPorEstudiante2)."</strong><br/>";
                        ?>
                        <img src="img/enlaces_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Nombre d’enllaços externs globals: <strong>".array_sum($enlacesPorEstudiante2)."</strong>";
                        ?> 
                       <br/>
                       <img src="img/extension.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      Extensió mitjana: <strong>
                       <?php
                       echo round(array_sum($palabrasPorEstudiante2)/array_sum($mensajesPorEstudiante2),0);
                       ?>   
                        paraules</strong><br />
                           <img src="img/grafo_nodos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        
                         Graf de nodes .gexf (GEPHI): <a href="sna2.gexf" target="_blank">sna2.gexf</a>
                        <br/>
                       <img src="img/nube_etiquetas.gif" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      Contingut textual: <a href="tagcloud2.txt" target="_blank">tagcloud2.txt</a><br/>
                       <img src="img/semantica.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      <span title="Percentatge de la conversa que conté les paraules clau definides pel professor i que defineixen el camp semàntic desitjat per a la comunicació.">Grau d'adequació del discurs al camp semàntic:</span><br />
                        <?php
                        
                        $totalPalabras2=array_sum($palabras2);
                        $totalPalabrasClave2=0;
                        foreach ($palabrasClave as $palabra=>$valor) {
                            if ($palabra!=NULL) {
                                $totalPalabrasClave2+=$palabras2[$palabra];
                            }
                        }
                    
                        $valor=(($totalPalabrasClave2/$totalPalabras2)*100)*100/$severidad;
                        echo "<div style=\"text-align:center;\"><img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".$valor."&chl=".round(($totalPalabrasClave2/$totalPalabras2)*100,2)."%&chf=bg,s,ffffff00&chco=ff0000,ffff00,00ff00\"><br />";
                                    echo "Grau de severitat establert: ".$severidad."%";
                                    echo "</div>";
						?>
						<br/><div style="text-align:left; color:#000; font-family:Verdana, Geneva, sans-serif; font-size:12px;"><strong>Detall de l'aplicació del camp semàntic:</strong></div><br/>
						<?php
                         
                         foreach ($palabrasClave as $palabra=>$valor) {
                             if ($palabra!=NULL) {
                                 echo "&nbsp;&nbsp;&nbsp;- ".$palabra." (<span style=\"color:#F00\">".round((($palabras2[$palabra]/$totalPalabras2)*100),2)."%</span>)<br />";
                             }
                         }
                         ?>
                         <br/>

            		</td>
                    
                <!-- TERCERA COLUMNA Y PRIMERA FILA ----------------------------------------------------------------->

                	<td aling="center" width="300px" style="font-size:12px; background-color:#EEE; vertical-align:top;">
                            <img src="img/usuarios.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                    	<?php 
						$valor=(round(count($listaParticipantes2)/count($mensajesPorEstudiante2)*100,2))-(round(count($listaParticipantes1)/count($mensajesPorEstudiante1)*100,2));
						if ($valor<0) { ?>
                            <?php
                            //Mostrar el número total de usuarios participantes
                            echo " Participants: <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."%</strong><br/>";
                            } else { ?>
                            <?php
                                echo " Participants: <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."%</strong><br/>";
                            }
						?>
                        <img src="img/mensajes.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" />
                        <?php
                        //Mostrar el número total de mensajes
						$valor=$numMensajes2-$numMensajes1;
						if ($valor<0) { 
							echo " Missatges totals: <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."</strong><br/>";
                         } else {
                                echo " Missatges totals: <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."</strong><br/>";
                         }
                        ?>
                        <img src="img/homogeneidad.png" width="20px" style="vertical-align:middle; margin:0px 3px 3px 3px;" />
						<?php
                        //Mostrar el grado de homogeneidad
                        $valor=$valor2-$valor1;
                        echo " <span title='Mostra el grau d’igualtat en la participació dels usuaris dins els espais de comunicació asíncrona. Un 100% indicaria que tots els estudiants han participat amb el mateix nombre de missatges. El grau d’homogeneïtat mesura la dispersió mitja del nombre de missatges publicats per cada usuari en base a la mitja global.'>Homogeneïtat participativa:</span>";
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."%</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."%</strong><br/>";
                         }
                        ?>
                        <img src="img/responder.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        echo " Missatges de resposta totals: ";
						$valor=$numRespuestas2-$numRespuestas1;
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> <strong>".$valor."</strong><br/>";
                         }
                        ?>
                        <img src="img/dialogo.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el nivel de diálogo
						echo " <span title='Mostra el grau de reciprocitat en el lliurament de missatges dins la conversa, mitjançant la relació entre el número total de missatges resposta i els missatges totals publicats.'>Nivell de diàleg (resp. vs. miss.):</span>";
						$valor=(round($numRespuestas2/($numMensajes2-1),4)*100)-(round($numRespuestas1/($numMensajes1-1),4)*100);
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."%</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."%</strong><br/>";
                         }
                        ?> 
                        <img src="img/expandir.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                        <?php
                        //Mostrar el nivell de dispersió del debat
                        echo " <span title='Mostra el grau de dispersió d’una conversa asíncrona a través del còmput dels missatges publicats i les respostes rebudes. Una conversa concentrada es aquella on hi ha pocs fils de debat però amb moltes respostes penjant d’elles, mentre que una conversa molt dispersa conté molts fils de debat oberts però amb poques o cap resposta al seu interior.'>Dispersió: </span>";
                            $valor=(round((($numMensajes2-1)/$numMensajes2)-($numRespuestas2/$numMensajes2),2))-(round((($numMensajes1-1)/$numMensajes1)-($numRespuestas1/$numMensajes1),2));
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".($valor*100)."%</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".($valor*100)."%</strong><br/>";
                         }
                        ?>
                        <img src="img/archivos_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        $valor=array_sum($adjuntosPorEstudiante2)-array_sum($adjuntosPorEstudiante1);
						echo " Nombre d’arxius globals:";
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."</strong><br/>";
                         }
                        ?>
                        <img src="img/enlaces_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
                        <?php
                        //Mostrar el número total de mensajes de respuesta
                        $valor=array_sum($enlacesPorEstudiante2)-array_sum($enlacesPorEstudiante1);
						echo " Nombre d’enllaços externs globals:";
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor."</strong><br/>";
                         }

                        ?> 
                       <img src="img/extension.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
                      Extensió mitjana: <strong>
                       <?php
						$valor=(round(array_sum($palabrasPorEstudiante2)/array_sum($mensajesPorEstudiante2),0))-(round(array_sum($palabrasPorEstudiante1)/array_sum($mensajesPorEstudiante1),0));
						if ($valor<0) { 
							echo " <img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor." paraules</strong><br/>";
                         } else {
                                echo " <img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><strong>".$valor." paraules</strong><br/>";
                         }
                       ?>   
                
                	</td>
                </tr>

   </table>
	</div>
















	<div id="panelAlertas" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE ALERTAS -->
    
        <div class="tituloPanel"><p><strong>PANELL D'ALERTES<br/></strong></p></div>
		<table cols="2" align="center" style="font-family:Verdana, Geneva, sans-serif; font-size:11px;" cellspacing="0px" width="860px">
    		<tr>
            	<td align="center" colspan="2" height="50px">
                <div class="tituloAlerta" style="text-align:center;"><strong>- Grau d'acompliment de l'objectiu de dispersió de la conversa -</strong></div>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2" >

				<?php echo "Període 1: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]"; 
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				if ($modelo=="Progressiu") {
					echo "Període 2: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]<br/>"; 
				} elseif ($modelo=="Comparatiu") {
					echo "Període 2: [".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]<br/>";
				}

                $valor1=round((($numMensajes1-1)/$numMensajes1)-($numRespuestas1/$numMensajes1),2);
				$valor1=$valor1*100;
                $valor2=round((($numMensajes2-1)/$numMensajes2)-($numRespuestas2/$numMensajes2),2);
				$valor2=$valor2*100;
				echo "<img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".($valor1/$dispersion*100)."&chl=".$valor1."%&chf=bg,s,ffffff00&chco=00ff00,ffff00,ff0000\">";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".($valor2/$dispersion*100)."&chl=".$valor2."%&chf=bg,s,ffffff00&chco=00ff00,ffff00,ff0000\"><br />";
				echo "Límit fixat: ".$dispersion."%";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "Límit fixat: ".$dispersion."%";
				?>
            	</td>
            </tr>
            <tr>
            	<td width="50px" align="center">
            		<img src="img/alerta_ningun_mensaje.png" width="35px">
            	</td>
                <td width="600px" >
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta d'estudiants per no haver participat encara:</strong></div>
                </td>
             </tr>
             <tr><td></td><td>
             
                <table width="800px" cols="2" style="font-family:Verdana, Geneva, sans-serif; font-size:11px;">
                <tr><td valign="middle" height="50px">
                <strong>
                <?php echo "Període 1: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]"; ?>
                </strong>
				</td><td valign="middle" height="50px">
                <strong>
                <?php if ($modelo=="Progressiu") {
					echo "Període 2: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]<br/>"; 
				} elseif ($modelo=="Comparatiu") {
					echo "Període 2: [".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]<br/>";
				}
				?>
                </strong>
                </td>
                </tr>
                <tr><td valign="top">
                
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 1-->
                <?php
                    $contador=0;
                    foreach ($mensajesPorEstudiante1 as $estudiante => $total) {
                        if ($total==0) {
                            echo "- ";
							if ($mensajesPorEstudiante2[$estudiante]==0) {
								echo "<span style='color:red;'>".$estudiante."</span>";
							} else {
								echo $estudiante;
							}
                            echo "<br/>";
                            $contador++;
                        }
                    }
                    if ($contador==0) {
                            echo "Cap estudiant en aquesta situació...<br/>";
                    }
                ?>
                </td><td valign="top">
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 2-->
                <?php
                    $contador=0;
                    foreach ($mensajesPorEstudiante2 as $estudiante => $total) {
                        if ($total==0) {
                            echo "- ";
							if ($mensajesPorEstudiante1[$estudiante]==0) {
								echo "<span style='color:red;'>".$estudiante."</span>";
							} else {
								echo $estudiante;
							}
                            echo "<br/>";
                            $contador++;
                        }
                    }
                    if ($contador==0) {
                            echo "Cap estudiant en aquesta situació...<br/>";
                    }
                ?>
                </td></tr>
                </table>
                
            </td></tr>
            <tr>
            	<td width="50px" align="center">
            		<img src="img/alerta_pocos_mensajes.png" width="35px">
            	</td>
                <td width="600px">
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta d'estudiants per estar sota el llindar mínim de participació:</strong> (<?php echo $minimo ?> missatges)</div>
                </td>
             </tr>
             
             <tr><td></td><td>
             
                <table width="800px" cols="2" style="font-family:Verdana, Geneva, sans-serif; font-size:11px;">
                <tr><td valign="middle" height="50px">
                <strong>
                <?php echo "Període 1: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]"; ?>
                </strong>
				</td><td valign="middle" height="50px">
                <strong>
                <?php if ($modelo=="Progressiu") {
					echo "Període 2: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]<br/>"; 
				} elseif ($modelo=="Comparatiu") {
					echo "Període 2: [".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]<br/>";
				}
				?>
                </strong>
                </td>
                </tr>
                <tr><td valign="top">
                
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 1-->
                <?php
				$contador=0;
				foreach ($mensajesPorEstudiante1 as $estudiante => $total) {
					if ($total<$minimo) {
						echo "- ".$estudiante;
						if ($listaCorreos1[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos1[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo "<br/>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<em>Cap estudiant en aquesta situació...</em><br/>";
				}
                ?>
                </td><td valign="top">
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 2-->
                <?php
				$contador=0;
				foreach ($mensajesPorEstudiante2 as $estudiante => $total) {
					$total+=$mensajesPorEstudiante1[$estudiante];
					if ($total<$minimo) {
						echo "- ".$estudiante;
						if ($listaCorreos2[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos2[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo "<br/>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<em>Cap estudiant en aquesta situació...</em><br/>";
				}
                ?>
                </td></tr>
                </table>
                
            </td></tr>
             
            <tr>
            	<td width="50px" align="center">
            		<img src="img/alerta_muchos_mensajes.png" width="35px">
            	</td>
                <td width="600px" >
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta per superar el llindar màxim de participació:</strong> (<?php echo $maximo ?> missatges)</div>
                </td>
             </tr>
             <tr><td></td><td>
             
                <table width="800px" cols="2" style="font-family:Verdana, Geneva, sans-serif; font-size:11px;">
                <tr><td valign="middle" height="50px">
                <strong>
                <?php echo "Període 1: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]"; ?>
                </strong>
				</td><td valign="middle" height="50px">
                <strong>
                <?php if ($modelo=="Progressiu") {
					echo "Període 2: [".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]<br/>"; 
				} elseif ($modelo=="Comparatiu") {
					echo "Període 2: [".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]<br/>";
				}
				?>
                </strong>
                </td>
                </tr>
                <tr><td valign="top">
                
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 1-->
                <?php
				$contador=0;
				foreach ($mensajesPorEstudiante1 as $estudiante => $total) {
					if ($total>$maximo) {
						echo "- ".$estudiante;
						if ($listaCorreos1[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos1[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo "<br/>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<em>Cap estudiant en aquesta situació...</em><br/>";
				}
                ?>
                </td><td valign="top">
                <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES DEL PERIODO 2-->
                <?php
				$contador=0;
				foreach ($mensajesPorEstudiante2 as $estudiante => $total) {
					$total+=$mensajesPorEstudiante1[$estudiante];
					if ($total>$maximo) {
						echo "- ".$estudiante;
						if ($listaCorreos2[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos2[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo "<br/>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<em>Cap estudiant en aquesta situació...</em><br/>";
				}
                ?>
                </td></tr>
                </table>
                
            </td></tr>
             
	    </table>
    </div>












	<div id="panelMetricasIndividuales" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE MÉTRICAS INDIVIDUALES -->
        <div class="tituloPanel"><p><strong>PANELL D'INDICADORS I MÈTRIQUES INDIVIDUALS<br/></strong></p></div>

                <table cols="9" width="1090px" style=" border-collapse: collapse; border-spacing: 0px; width:1090px; height:100%; 	margin:10px; padding:0px; font-size:10px; text-align:center; background-color:#CCC; border:1px solid #000000;">
                    <tr>
                        <td style="width:250px;">
                            <img src="img/bandera_verde.png" width="20px" style="vertical-align:middle;"> <strong>Indicadors</strong>
                        </td>
                        <td colspan="2" style="width:175px;">
                            - Participació en la interacció comunicativa -
                        </td>
                        <td colspan="2" style="width:150px;">
                            - Foment del diàleg i de la negociació -
                        </td>
                        <td style="width:75px;">
                            - Estil comunicatiu -
                        </td>
                        <td style="width:280px;">
                            - Constància i regularitat en la interacció grupal -
                        </td>
                        <td colspan="2" style="width:150px;">
                            - Intercanvi  d’informació dins el grup -
                        </td>
					</tr>
                   </table>
<div class="tablaMetricas" >
                <table width="1090px">
                    <tr>
                        <td style="width:250px;">
                            Estudiant
                        </td>
                        <td style="width:75px;">
                            Missatges totals
                        </td>
                        <td style="width:100px;">
                            <span title="Mostra si l’estudiant publica missatges dins els espais de comunicació grupal. S’utilitza per valorar si l’estudiant aporta i intercanvia informació durant el procés de treball col·laboratiu. Es pot valorar, fins a cert grau, si existeix negociació durant la fase de planificació, o intercanvi d’informació durant la fase de desenvolupament del treball. Es mesura calculant el nombre de missatges que ha publicat l’estudiant en base als missatges que han publicat els altres membres en la conversa.">Nivell de participació</span>
                        </td>
                        <td style="width:75px;">
                            Respostes
                        </td>
                        <td style="width:75px;">
                            <span title="És un valor percentual que mostra el nombre de respostes que reben els missatges d’un determinat usuari en base al nombre de missatges resposta totals dins la conversa. Aquesta mètrica mesura l’impacte dels missatges que publica l’estudiant en funció de les respostes que rep.">Popularitat</span>
                        </td>
                        <td style="width:75px;">
                            Paraules promig
                        </td>
                        <td style="width:280px;">
                             <span title="Mostra el temps entre el primer i el darrer missatge, i calcula com l’estudiant reparteix les contribucions durant aquest període de temps.">Distribució temporal de missatges individuals</span>
                        </td>
                        <td style="width:75px;">
                            Adjunts publicats
                        </td>
                        <td style="width:75px;">
                            Enllaços externs
                        </td>
                    </tr>
            
<?php

// Listar la estadística por estudiante
foreach ($mensajesPorEstudiante1 as $estudiante => $total1) {
    $total2=$mensajesPorEstudiante2[$estudiante];
	echo "<tr><td><strong>".$estudiante."</strong><br/>";
	echo "&nbsp;&nbsp;[".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha2)."]<br/>";
	if ($modelo=="Progressiu") {
		echo "&nbsp;&nbsp;[".date("d M Y",$fecha1)."] - [".date("d M Y",$fecha3)."]"; 
	} elseif ($modelo=="Comparatiu") {
		echo "&nbsp;&nbsp;[".date("d M Y",$fecha3)."] - [".date("d M Y",$fecha4)."]";
	}
	echo "</td><td><br/>".round($total1,0);
	
	//Añadimos flechas de promedio
	$promedio=array_sum($mensajesPorEstudiante1)/count($mensajesPorEstudiante1);
	if ($total1<$promedio) {
		echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
	} else {
			if ($total1>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
				
	}
	
	echo "<br/>".round($total2,0);
	//Añadimos flechas de promedio
	$promedio=array_sum($mensajesPorEstudiante2)/count($mensajesPorEstudiante2);
	if ($total2<$promedio) {
		echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
	} else {
			if ($total2>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
				
	}

	echo "</td><td><br/>";
	$valor=round($total1/($numMensajes1/count($mensajesPorEstudiante1)),2);
		if ($valor>1){
			echo "Molt participatiu";
		} elseif ($valor>=0.5) {
			echo "Participatiu";
		} elseif ($valor>0) {
			echo "Poc participatiu";
		} else {
			echo "No participa";
		}
	echo "<br/>";
	$valor=round($total2/($numMensajes2/count($mensajesPorEstudiante2)),2);
		if ($valor>1){
			echo "Molt participatiu";
		} elseif ($valor>=0.5) {
			echo "Participatiu";
		} elseif ($valor>0) {
			echo "Poc participatiu";
		} else {
			echo "No participa";
		}
	
	echo "</td><td><br/>".round($mensajesRespuestaPorEstudiante1[$estudiante],0);

	//Añadimos flechas de promedio
		$promedio=array_sum($mensajesRespuestaPorEstudiante1)/count($mensajesRespuestaPorEstudiante1);
		if ($mensajesRespuestaPorEstudiante1[$estudiante]<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if ($mensajesRespuestaPorEstudiante1[$estudiante]>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}
		echo "<br/>".round($mensajesRespuestaPorEstudiante2[$estudiante],0);
	//Añadimos flechas de promedio
		$promedio=array_sum($mensajesRespuestaPorEstudiante2)/count($mensajesRespuestaPorEstudiante2);
		if ($mensajesRespuestaPorEstudiante2[$estudiante]<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if ($mensajesRespuestaPorEstudiante2[$estudiante]>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}

	echo "</td><td><br/>".round(100*$popularidadPorEstudiante1[$estudiante]/$numRespuestas1,2)."%<br/>".round(100*$popularidadPorEstudiante2[$estudiante]/$numRespuestas2,2)."%</td>";
	
	echo "<td><br/>".round($palabrasPorEstudiante1[$estudiante]/$total1,0);
	
	//Añadimos flechas de promedio
		$promedio=array_sum($palabrasPorEstudiante1)/array_sum($mensajesPorEstudiante1);
		if (round($palabrasPorEstudiante1[$estudiante]/$total1,0)<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><br/> ";
		} else {
			if (round($palabrasPorEstudiante1[$estudiante]/$total1,0)>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /><br/> ";
			}
		}
	
	echo round($palabrasPorEstudiante2[$estudiante]/$total2,0);
	
	//Añadimos flechas de promedio
		$promedio=array_sum($palabrasPorEstudiante2)/array_sum($mensajesPorEstudiante2);
		if (round($palabrasPorEstudiante2[$estudiante]/$total2,0)<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if (round($palabrasPorEstudiante2[$estudiante]/$total2,0)>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}
	

// Calcular y llistar la distribució temporal de missatges del periode 1

echo "</td><td><br/>";

		$min=min($fechasMensajePorEstudiante1[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante1[$estudiante])/86400;
		$periodo=$max-$min;
		echo "<img src=\"img/amplitud.png\" width=\"25px\" title=\"Durada de la participació.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		if (count($fechasMensajePorEstudiante1[$estudiante])==0){
			echo "Cap";
		} else if (count($fechasMensajePorEstudiante1[$estudiante])==1){
			echo "1 dia";
		} else if  (($periodo>0) && ($periodo<1)){
			echo round($periodo*24,1)." hores";
		} else {
			echo round($periodo,1)." dies";

		}
		echo " <img src=\"img/dinamizar.png\" width=\"20px\" title=\"Dates de publicació:\n---------------------------\n";
		
		sort($fechasMensajePorEstudiante1[$estudiante]);
		$contador=0;
		while ($contador<sizeof($fechasMensajePorEstudiante1[$estudiante])) {
			echo date("d M Y G:i:s",$fechasMensajePorEstudiante1[$estudiante][$contador])."\n";
			$contador++;
		}
		
		echo "\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
if (sizeof($fechasMensajePorEstudiante1[$estudiante])==0) {
	echo "Cap";
} else if (sizeof($fechasMensajePorEstudiante1[$estudiante])==1) {
	echo "Missatge únic";
}
else {
	if (sizeof($fechasMensajePorEstudiante1[$estudiante])<=3) {
	echo "Pocs missatges";
	}
	else {
		
		$contador=1;
		unset($fechas);
		$fechas=array();
		while ($contador<sizeof($fechasMensajePorEstudiante1[$estudiante])) {
			$fechas[$contador-1]=($fechasMensajePorEstudiante1[$estudiante][$contador]-$fechasMensajePorEstudiante1[$estudiante][$contador-1])/86400;
			$contador++;
		}
		$dinamització=standard_deviation($fechas);

		if ($dinamització<2) {
			echo "Molt distribuït";
		}
		else {
			if ($dinamització<4) {
			echo "Distribuït";
		}
		else {
			echo "Poc distribuït";
		}
	}
		
}

}


// Calcular y llistar la distribució temporal de missatges del periode 2

echo "<br/>";

		$min=min($fechasMensajePorEstudiante2[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante2[$estudiante])/86400;
		$periodo=$max-$min;
		echo "<img src=\"img/amplitud.png\" width=\"25px\" title=\"Durada de la participació.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		if (count($fechasMensajePorEstudiante2[$estudiante])==0){
			echo "Cap";
		} else if (count($fechasMensajePorEstudiante2[$estudiante])==1){
			echo "1 dia";
		} else if  (($periodo>0) && ($periodo<1)){
			echo round($periodo*24,1)." hores";
		} else {
			echo round($periodo,1)." dies";

		}
		echo " <img src=\"img/dinamizar.png\" width=\"20px\" title=\"Dates de publicació:\n---------------------------\n";
		
		sort($fechasMensajePorEstudiante2[$estudiante]);
		$contador=0;
		while ($contador<sizeof($fechasMensajePorEstudiante2[$estudiante])) {
			echo date("d M Y G:i:s",$fechasMensajePorEstudiante2[$estudiante][$contador])."\n";
			$contador++;
		}
		
		echo "\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
if (sizeof($fechasMensajePorEstudiante2[$estudiante])==0) {
	echo "Cap";
} else if (sizeof($fechasMensajePorEstudiante2[$estudiante])==1) {
	echo "Missatge únic";
}
else {
	if (sizeof($fechasMensajePorEstudiante2[$estudiante])<=3) {
	echo "Pocs missatges";
	}
	else {
		
		$contador=1;
		unset($fechas);
		$fechas=array();
		while ($contador<sizeof($fechasMensajePorEstudiante2[$estudiante])) {
			$fechas[$contador-1]=($fechasMensajePorEstudiante2[$estudiante][$contador]-$fechasMensajePorEstudiante2[$estudiante][$contador-1])/86400;
			$contador++;
		}
		$dinamització=standard_deviation($fechas);

		if ($dinamització<2) {
			echo "Molt distribuït";
		}
		else {
			if ($dinamització<4) {
			echo "Distribuït";
		}
		else {
			echo "Poc distribuït";
		}
	}
		
}

}



echo "</td><td><br/>".round($adjuntosPorEstudiante1[$estudiante],0);
if ($adjuntosPorEstudiante1[$estudiante]>0) {
	echo " <img src=\"img/archivos_adjuntos.png\" width=\"20px\" style=\"vertical-align:middle;\" title=\"LLista d'arxius adjunts:\n---------------------------\n";
	$contador=0;
	while ($contador<sizeof($nombresAdjuntosPorEstudiante1[$estudiante])) {
		echo $nombresAdjuntosPorEstudiante1[$estudiante][$contador];
		$contador++;
	}
	echo "\">";
}

echo "<br/>".round($adjuntosPorEstudiante2[$estudiante],0);
if ($adjuntosPorEstudiante2[$estudiante]>0) {
	echo " <img src=\"img/archivos_adjuntos.png\" width=\"20px\" style=\"vertical-align:middle;\" title=\"LLista d'arxius adjunts:\n---------------------------\n";
	$contador=0;
	while ($contador<sizeof($nombresAdjuntosPorEstudiante2[$estudiante])) {
		echo $nombresAdjuntosPorEstudiante2[$estudiante][$contador];
		$contador++;
	}
	echo "\">";
}


echo "</td><td><br/>".round($enlacesPorEstudiante1[$estudiante],0)."<br/>".round($enlacesPorEstudiante2[$estudiante],0);

echo "</td></tr>";
}
?>
                </table>
            </div>

	</div>
    
    

</td>
</tr>

</table>



<script>

ocultarPanel(false,true,true);

</script>


</div>

</body>
</html>


