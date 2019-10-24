<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DIANA 2.1 (DIALOGUE ANALYSIS) - Learning Analytics</title>
<link rel="stylesheet" type="text/css" href="estilosAnalisis.css" />

<script language="javascript">

function ocultarPanel(primera, segunda, tercera, cuarta, quinta, sexta, septima, octava, novena, decima) {
	document.getElementById('panelMetricasGlobales').hidden=primera;
	document.getElementById('panelAlertas').hidden=segunda;
	document.getElementById('panelMetricasIndividuales').hidden=tercera;
	document.getElementById('panelClasificaciones').hidden=cuarta;
	document.getElementById('panelVistaEstudiante').hidden=quinta;
	document.getElementById('panelVistaComparador').hidden=sexta;
	document.getElementById('panelFeedbackCatalan').hidden=septima;
	document.getElementById('panelFeedbackCastellano').hidden=octava;
	document.getElementById('panelXMLCatalan').hidden=novena;
	document.getElementById('panelXMLCastellano').hidden=decima;

	return 0;
}

</script>
<script src="tagcanvas.js" type="text/javascript"></script>
<script type="text/javascript">
  window.onload = function() {
    try {
	TagCanvas.interval = 20;
	TagCanvas.textFont = 'Impact,Arial Black,sans-serif';
	//TagCanvas.textColour = '#00f';
	TagCanvas.textColour = null;
	TagCanvas.textHeight = 25;
	TagCanvas.outlineColour = '#f96';
	TagCanvas.outlineThickness = 1;
	TagCanvas.maxSpeed = 0.1;
	TagCanvas.minBrightness = 0.1;
	TagCanvas.depth = 0.92;
	TagCanvas.pulsateTo = 0.2;
	TagCanvas.pulsateTime = 0.75;
	TagCanvas.initial = [0.1,-0.1];
	TagCanvas.decel = 0.98;
	TagCanvas.reverse = true;
	TagCanvas.hideTags = true;
	TagCanvas.shadow = '#ccf';
	TagCanvas.shadowBlur = 0;
	TagCanvas.weight = true;
	TagCanvas.weightSize = 0.5;
	TagCanvas.weightMode = 'both';
	TagCanvas.weightFrom = 'peso';
  	TagCanvas.fadeIn = 800;
  	TagCanvas.weightGradient = { 0: "#ff0", 1: "#f00" };
  	TagCanvas.zoom = 1.0;
  	//TagCanvas.shape = 'hring';
    TagCanvas.Start("mycanvas","tags",{
            //textColour: '#ff0000',
            //outlineColour: '#ff00ff',
            //reverse: true,
            //depth: 0.8,
            //maxSpeed: 0.05
          });
    } catch(e) {
      // something went wrong, hide the canvas container
      document.getElementById('nube').style.display = 'none';
    }
  };
</script>

</head>

<body>
<?php
// DECLARACIÓN DE FUNCIONES ------------------------------------------------------------------------
function standard_deviation($aValues)
{
    $fMean = array_sum($aValues) / count($aValues);
    //print_r($fMean);
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

<div id="cabecera"><strong>RESULTAT DE LES ANALÍTIQUES DE L'APRENENTATGE - DIANA 2.1<br/>
- Període de <?php 
$fecha1=$_GET["fecha1"];
$fecha2=$_GET["fecha2"];
$fecha2Completa=$fecha2;
$dias=round((($fecha2-$fecha1)/86400),0);
echo $dias." dies"; ?>
 comprès des de [
<?php
	   $hora=date("G",$fecha1)*3600;
	   $minutos=date("i",$fecha1)*60;
	   $segundos=date("s",$fecha1);
	   $fecha1=$fecha1-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
echo date("d M Y",$fecha1);
?>
 ] al [
<?php
	   $hora=date("G",$fecha2)*3600;
	   $minutos=date("i",$fecha2)*60;
	   $segundos=date("s",$fecha2);
	   $fecha2=$fecha2-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
echo date("d M Y",$fecha2);
?>
 ] -</strong> <br/>
Model metodològic aplicat: DESCRIPTIU
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

$carpeta=$_GET["carpeta"];
$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

// Creamos el fichero de salida
$ficheroSalida=fopen("sna.gexf","w"); 
    
// Declaramos la lista de estudiantes para contar mensajes
$mensajesPorEstudiante= array();

// Declaramos la lista de estudiantes para contar mensajes de respuesta
$mensajesRespuestaPorEstudiante= array();

// Declaramos la lista de estudiantes populares para contar respuestas
$popularidadPorEstudiante= array();

// Declaramos la lista de palabras promedio por estudiante
$palabrasPorEstudiante= array();

// Declaramos el contador de mensajes
$numMensajes=0;

// Declaramos el contador de respuestas
$numRespuestas=0;

// Declaramos la lista de fechas (timestamp) de los mensajes de cada estudiante
$fechasMensajePorEstudiante=array(array());

// Declaramos el número de mensajes por día
$mensajesPorDia= array();

// Declaramos la lista con el número de enlaces por usuario
$enlacesPorEstudiante= array(); 

// Declaramos la lista con el número de ficheros adjuntos por usuario
$adjuntosPorEstudiante= array(); 

// Declaramos el fichero de salida con el Feedback de los alumnos IDIOMA CASTELLANO
$ficheroFeedbackCastellano=fopen("feedback_cas_utf8_tab.csv","w"); 
fputs($ficheroFeedbackCastellano,"Usuario".chr(9)."Feedback\r\n");

// Declaramos el fichero de salida con el Feedback de los alumnos IDIOMA CATALÁN
$ficheroFeedbackCatalan=fopen("feedback_cat_utf8_tab.csv","w"); 
fputs($ficheroFeedbackCatalan,"Usuari".chr(9)."Feedback\r\n");

// Declaramos el fichero de configuracion
$ficheroConfiguracion=fopen("conf.txt","r"); 

// Definimos el array con la lista de palabras de la conversación
$palabras = array();

// Declaramos la lista con los nombres de los ficheros adjuntos por usuario
$nombresAdjuntosPorEstudiante=array(array()); 

//Declaramos la lista de estudiantes que participan en la conversacion
$listaParticipantes=array();

//Declaramos la lista de direcciones de correo de cada estudiante que participa en la conversacion
$listaCorreos=array();

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
$mensajesPorEstudiante=$listaEstudiantes;
$mensajesRespuestaPorEstudiante=$listaEstudiantes;
$popularidadPorEstudiante=$listaEstudiantes;
$palabrasPorEstudiante=$listaEstudiantes;
$enlacesPorEstudiante=$listaEstudiantes;
$adjuntosPorEstudiante=$listaEstudiantes;
$listaCorreos=$listaEstudiantes;

// CREACIÓN DEL FICHERO DE NODOS Y ARISTAS PARA IMPORTARLO A GEPHI ----------------------------------------------

// Escribimos la cabecera del archivo GEPHI
fputs($ficheroSalida,"<?xml version='1.0' encoding='UTF-8'?>\r\n<gexf xmlns='http://www.gexf.net/1.2draft' version='1.2'>\r\n<meta lastmodifieddate='2009-03-20'>\r\n<creator>Juan Pedro Cerro Martínez</creator>\r\n        <description>Generador de fitxers GEXF a partir d'espais de comunicació de la UOC en format d'aula nova</description>\r\n</meta>\r\n<graph mode='static' defaultedgetype='directed'>\r\n<nodes>\r\n");

// Nos recorremos todo el directorio para detectar nodos y hacer estadística
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
	   // Cogemos el nombre del estudiante como LABEL del nodo
	   $linea = fgets($mensaje);
	   $estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
	   
	   // De esa misma línea obtenemos la dirección de correo
	   $listaCorreos[$estudiante]=utf8_encode(substr($linea,strpos($linea,"<")+1,strlen($linea)-strpos($linea,"<")-4));
	   
	   // Computamos este mensaje al contador del estudiante
	   if ($mensajesPorEstudiante[$estudiante]>0) {
		   $mensajesPorEstudiante[$estudiante]++;
	   } else {
		   $mensajesPorEstudiante[$estudiante]=1;
	   }
	   
	   // Añadimos el estudiante a la lista de participantes
	   $listaParticipantes[$estudiante]=0;
	   
	   // Cogemos la fecha de envío del mensaje
	   while ((utf8_encode(substr($linea,0,5))!="Date:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $fecha=utf8_encode(substr($linea,6,strlen($linea)-8));
	   $fecha=strtotime($fecha);
	   $fechasMensajePorEstudiante[$estudiante][sizeof($fechasMensajePorEstudiante[$estudiante])]=$fecha;

	// Incrementamos el contador de mensajes por días
	   $hora=date("G",$fecha)*3600;
	   $minutos=date("i",$fecha)*60;
	   $segundos=date("s",$fecha);
	   $fechaConvertida=$fecha-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	   
	   if ($mensajesPorDia[$fechaConvertida]==0){
		   $mensajesPorDia[$fechaConvertida]=1;
	   } else {
		   $mensajesPorDia[$fechaConvertida]++;
	   }
	   
	   // Cogemos el id del mensaje como ID de nodo
	   while ((utf8_encode(substr($linea,0,9))!="X-Uoc-Id:") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	   $id=utf8_encode(substr($linea,10,strlen($linea)-12));

	   // Escribimos la línea en el fichero de nodos
	   fputs($ficheroSalida,"<node id=\"".$id."\" label=\"".$estudiante."\"></node>\r\n");

	   // Miramos si es una respuesta a otro mensaje
	   while ((utf8_encode(substr($linea,0,19))!="X-UOC-PARENT_MAILID") && (!feof($mensaje))) {
		    $linea = fgets($mensaje);
	   }
	
		if (!feof($mensaje)) {
			// Incrementamos el contador de respuestas
			$numRespuestas++;
			
				   // Computamos este mensaje al contador de respuestas del estudiante
	   				if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
		   				$mensajesRespuestaPorEstudiante[$estudiante]++;
	   				} else {
		   				$mensajesRespuestaPorEstudiante[$estudiante]=1;
	   				}

			
			$idRespuesta=utf8_encode(substr($linea,21,strlen($linea)-23));
			
		// Incrementamos el contador de estudiantes populares
			
			// Declaramos la variable de apertura temporal de ficheros respuesta
			$ficheroRespuesta=fopen("./".$carpeta."/".$idRespuesta.".mail", "r");
			// Buscamos el nombre del estudiante al que se le responde
	   		$linea = fgets($ficheroRespuesta);
	   		$estudiante=utf8_encode(substr($linea,6,strpos($linea,"<")-7));
			// Computamos este mensaje de respuesta al estudiante
	   		if ($popularidadPorEstudiante[$estudiante]>0) {
				$popularidadPorEstudiante[$estudiante]++;
	   		} else {
		   		$popularidadPorEstudiante[$estudiante]=1;
	   		}
			fclose($ficheroRespuesta);
			
		}
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Acabamos con los nodos
fputs($ficheroSalida,"</nodes>\r\n");

// Cerramos el directorio liberando recursos
closedir($directorio);

// Empezamos con las aristas
fputs($ficheroSalida,"<edges>\r\n");

$idArista=0; // Definimos el contador de aristas

// Recorremos una segunda vez el directorio para guardar las aristas

$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio de nuevo
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
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
			fputs($ficheroSalida,"<edge source=\"".$id."\" target=\"".$idRespuesta."\" type=\"directed\" id=\"".$idArista++."\" weight=\"1\"></edge>\r\n");

		}
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Escribimos el final del archivo
fputs($ficheroSalida,"</edges>\r\n</graph>\r\n</gexf>");

// Cerramos el fichero de salida
fclose($ficheroSalida);

// Cerramos el directorio liberando recursos
closedir($directorio);

// VOLVEMOS A RECORRER LOS ARCHIVOS PARA COMPUTAR EL NÚMERO DE PALABRAS PROMEDIO
// Y CONTAR EL NÚMERO DE ENLACES Y ADJUNTOS QUE TIENE EL MENSAJE

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes

$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio de nuevo

while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
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
	   			if ($palabrasPorEstudiante[$estudiante]>0) {
					$palabrasPorEstudiante[$estudiante]+=$numPalabras;
	   			} else {
		   			$palabrasPorEstudiante[$estudiante]=$numPalabras;
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
   				if ($enlacesPorEstudiante[$estudiante]>0) {
					$enlacesPorEstudiante[$estudiante]++;
   				} else {
	   				$enlacesPorEstudiante[$estudiante]=1;
   				}
		   }
		   	   
		   $linea =  fgets($mensaje);
	   } 



	   // Buscamos si el mensaje contiene alguna otra sección que indicará si hay ficheros
 	   while (!feof($mensaje)) {
		    $linea = htmlspecialchars_decode(fgets($mensaje));
			if (utf8_encode(substr($linea,0,13))=="Content-Type:") {
			   // Sumamos el número de adjuntos al estudiante
   				if ($adjuntosPorEstudiante[$estudiante]>0) {
					$adjuntosPorEstudiante[$estudiante]++;
   				} else {
	   				$adjuntosPorEstudiante[$estudiante]=1;
   				}
				
				// A continuación, rescatamos el nombre del archivo y lo añadimos la lista del estudiante
				if (strpos($linea," name=")==false) {
					$linea = htmlspecialchars_decode(fgets($mensaje));
				}
					
				$valor=str_replace("\"","",substr($linea,strpos($linea," name=")+6,strlen($linea)-strpos($linea," name=")+6));
				$nombresAdjuntosPorEstudiante[$estudiante][sizeof($nombresAdjuntosPorEstudiante[$estudiante])]=$valor;
			
			}
	   }

   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// A CONTINUACIÓN NOS RECORREMOS EL DIRECTORIO PARA LEER TODOS LOS MENSAJES
// Y CREAR EL FICHERO "TAGCLOUD.TXT" CON EL CONTENIDO TEXTUAL SIN LOS CARACTERES
// DELIMITADORES, Y TAMBIÉN CREAMOS EL ARRAY CON LA LISTA DE PALABRAS Y EL CÁLCULO
// DE SUS APARICIONES -----------------------------------------------------------------------------------


$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

// Creamos el fichero de salida y escribimos la cabecera
$ficheroSalida=fopen("tagcloud.txt","w"); 

$numMensajes=0;

// Definimos los delimitadores
$delimitadores=array("_","[","]","¡","(",")","="," ",",",":","|","-","&",";","?","¿","*","!",".","/","\\","'","\"");

// Nos recorremos todo el directorio para extraer las palabras contenidas en los mensajes
while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)) //Verificamos si es o no un directorio
    {
       $numMensajes++;
	   // Leemos el primer mensaje
	   $mensaje= fopen("./".$carpeta."/".$archivos, "r");
	   
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
					if ($palabras[$palabra]==0) {
					  $palabras[$palabra]=1;
					} else {
					  $palabras[$palabra]++;
					}
				}
			  } 
				
			   fputs($ficheroSalida,utf8_decode($linea)."\r\n");
			   
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
fclose($ficheroSalida);

// Cerramos el directorio liberando recursos
closedir($directorio);
















// AHORA COMENZAMOS A VISUALIZAR LOS RESULTADOS ------------------------------------------------------------

?>




<table id="rejillaPantalla" cols="2" cellpadding="0px" cellspacing="0px" align="center">
<tr>
<td valign="top">
	<div id="metricasGlobales" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(false,true,true,true,true,true,true,true,true,true);">
    <img src="img/equipo.png"><br/>
    <strong>IND./MÈTRIQUES<BR/>GLOBALS</strong>
	</div>
    
	<div id="alertas" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,false,true,true,true,true,true,true,true,true);">
    <img src="img/campana.png" style="margin-top:5px;"><br/>
    <strong>ALERTES</strong>
	</div>
    
	<div id="metricasIndividuales" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,false,true,true,true,true,true,true,true);">
    <img src="img/estudiante.png"><br/>
    <strong>IND./MÈTRIQUES<BR/>INDIVIDUALS</strong>
	</div>
    
	<div id="clasificaciones" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,false,true,true,true,true,true,true);">
    <img src="img/clasificaciones.png" style="margin-top:5px;"><br/>
    <strong>CLASSIFICACIONS</strong>
	</div>
    
	<div id="estudiantes" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,false,true,true,true,true,true);">
    <img src="img/vista_estudiante.png" style="margin-top:5px;"><br/>
    <strong>VISTA ESTUDIANT</strong>
	</div>
    
	<div id="comparador" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,true,false,true,true,true,true);">
    <img src="img/vista_estudiante_espejo.png" style="margin-top:5px;"><img src="img/vista_estudiante.png" style="margin-top:5px;"><br/>
    <strong>COMPARADOR</strong>
	</div>
    
	<div id="feedbackCatalan" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,true,true,false,true,true,true);">
    <strong>FEEDBACK<br />CATALÀ</strong><br/>
    <img src="img/bandera_catalana.png">
	</div>
    
	<div id="feedbackCastellano" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,true,true,true,false,true,true);">
    <strong>FEEDBACK<br />CASTELLÀ</strong><br/>
    <img src="img/bandera_castellana.png">
	</div>

	<div id="xmlCatalan" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,true,true,true,true,false,true);">
    <img src="img/xml.png"><br/><img src="img/bandera_catalana.png" width="20px">

	</div>

	<div id="xmlCastellano" class="solapa" style="cursor: pointer;" onClick="ocultarPanel(true,true,true,true,true,true,true,true,true,false);">
    <img src="img/xml.png"> <br/><img src="img/bandera_castellana.png" width="20px">

	</div>

</td>
<td rowspan="8" valign="top">





















	<div id="panelMetricasGlobales" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE MÉTRICAS GLOBALES -->
    
        <div class="tituloPanel"><p><strong>PANELL D'INDICADORS I MÈTRIQUES GLOBALS<br/></strong></p></div>
			<table cols="4" width="860px" align="center">
            <tr>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="400px">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Participació en la interacció comunicativa:</strong></div>
            </td>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="400px">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Foment del diàleg i de la negociació:</strong></div>
            </td>
            </tr>
            <tr>
            <td></td>
            <td style="font-size:12px; height:120px;">
            
            <img src="img/usuarios.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
<?php
//Mostrar el número total de usuarios participantes
echo " Usuaris participants: <strong>".count($listaParticipantes)." de ".count($mensajesPorEstudiante)." (".(round(count($listaParticipantes)/count($mensajesPorEstudiante)*100,2))."% del total)</strong><br/>";
?>
<img src="img/mensajes.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" />
<?php
//Mostrar el núnmero total de mensajes
echo " Missatges totals analitzats: <strong>".$numMensajes."</strong><br/>";
?>
<img src="img/homogeneidad.png" width="20px" style="vertical-align:middle; margin:0px 3px 3px 3px;" />
<?php
$valor=1-(standard_deviation($mensajesPorEstudiante)/((max($mensajesPorEstudiante)+min($mensajesPorEstudiante))/2));
$valor*=100;
$valor=round($valor,2);
echo " <span title='Mostra el grau d’igualtat en la participació dels usuaris dins els espais de comunicació asíncrona. Un 100% indicaria que tots els estudiants han participat amb el mateix nombre de missatges. El grau d’homogeneïtat mesura la dispersió mitja del nombre de missatges publicats per cada usuari en base a la mitja global.'>Grau d'homogeneïtat participativa:</span> <strong>".$valor."%</strong>";
?>
            </td>
            <td>
            </td>
            <td style="font-size:12px;">
            
<img src="img/responder.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo " Missatges de resposta totals: <strong>".$numRespuestas."</strong><br/>";
?>
<img src="img/dialogo.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo " <span title='Mostra el grau de reciprocitat en el lliurament de missatges dins la conversa, mitjançant la relació entre el número total de missatges resposta i els missatges totals publicats.'>Nivell de diàleg (respostes vs. missatges):</span> <strong>".(round($numRespuestas/($numMensajes-1),4)*100)."%</strong>";
?> 
            </td>
            </tr>
            
            <tr>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="400px">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Tipus de comunicació:</strong></div>
            </td>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="400px">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Intercanvi  d’informació dins el grup:</strong></div>
            </td>
            </tr>
 
            <tr>
            <td></td>
            <td style="font-size:12px; height:100px;">

<img src="img/expandir.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
<?php
//Mostrar el nivell de dispersió del debat
echo " <span title='Mostra el grau de dispersió d’una conversa asíncrona a través del còmput dels missatges publicats i les respostes rebudes. Una conversa concentrada es aquella on hi ha pocs fils de debat però amb moltes respostes penjant d’elles, mentre que una conversa molt dispersa conté molts fils de debat oberts però amb poques o cap resposta al seu interior.'>Grau de dispersió: </span><strong>";
	$valor=round((($numMensajes-1)/$numMensajes)-($numRespuestas/$numMensajes),2);
		if ($valor>0.55){
		echo "Conversa dispersa";
	} elseif ($valor>=0.45) {
		echo "Conversa equilibrada";
	} else {
		echo "Conversa concentrada";
	}
echo " (".$valor*100 ."%)</strong><br>";
?>
   <img src="img/grafo_nodos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 

 Graf de nodes .gexf (GEPHI): <a href="sna.gexf" target="_blank">sna.gexf</a>
            
            </td>
            <td>
            </td>
            <td style="font-size:12px;">
            
<img src="img/archivos_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo " Nombre d’arxius globals publicats/adjuntats al grup: <strong>".array_sum($adjuntosPorEstudiante)."</strong><br/>";
?>
<img src="img/enlaces_adjuntos.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;"  />
<?php
//Mostrar el número total de mensajes de respuesta
echo " Nombre d’enllaços externs globals publicats: <strong>".array_sum($enlacesPorEstudiante)."</strong>";
?> 

            </td>
            </tr>

            <tr>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="830px" colspan="3">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Constància i regularitat en la interacció grupal:</strong></div>
            </td>
			</tr>
            <tr>
            <td colspan="4" align="center" style="font-size:12px;">
            <br/>
			Distribució temporal i grupal dels missatges
            
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Dia', 'Missatges',{ role: 'annotation' }],
<?php
ksort($mensajesPorDia);
$fecha1=$_GET["fecha1"];
$fecha2=$_GET["fecha2"];
$fechaActual=$fecha1;
while ($fechaActual<=$fecha2) {
	echo "['".date("d M",$fechaActual)."', ".$mensajesPorDia[$fechaActual].",'".$mensajesPorDia[$fechaActual]."']";
	$fechaActual=$fechaActual+86400; // Sumamos un día en segundos
	   $hora=date("G",$fechaActual)*3600;
	   $minutos=date("i",$fechaActual)*60;
	   $segundos=date("s",$fechaActual);
	   $fechaActual=$fechaActual-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	   if ($fechaActual<=$fecha2) {
		   echo ",";
	   }
}
?>
        ]);
        var options = {
			legend: { 'position': "none" },
			hAxis: {textStyle:  {fontSize: 10,bold: false}},
			vAxis: {textStyle:  {fontSize: 14,bold: false}},
			annotations: { textStyle: { fontSize: 12}},
			chartArea:{left:50,top:10,width:"100%",height:250},
			tooltip: {textStyle:  {fontSize: 12,bold: true}},
			backgroundColor: '#EEE'
			};
		/* var options = {
          title: 'Balance de la Compañía',
          hAxis: {title: 'Año', titleTextStyle: {color: 'red'}}
        }; */
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
    
       <div id="contenedorGráfica1" style="width:800px; height:300px; overflow-x:auto; overflow-y:hidden;">

       <div id="chart_div" style="width:<?php echo (50*$dias) ?>; height:300px;"></div>
    </div>
  
   </td>
   </tr>
   
            <tr>
            <td aling="center" width="30px"><img src="img/bandera_verde.png" width="20px">
            </td>
            <td width="400px">
            	<div class="tituloAlerta" style="text-align:left;"><strong>Estil comunicatiu i llenguatge utilitzat:</strong></div>
            </td>
            <td width="430px" colspan="2">
            	<div style="text-align:left; color:#000; font-family:Verdana, Geneva, sans-serif; font-size:12px;"><strong>Detall de l'aplicació del camp semàntic:</strong></div>
            </td>
            </tr>
            <tr>
			<td>
            </td>
   <td style="font-size:12px; height:250px;">
   <img src="img/nube_etiquetas.gif" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
  Contingut textual (núvol d'etiquetes): <a href="tagcloud.txt" target="_blank">tagcloud.txt</a><br/>
   <img src="img/extension.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
  Extensió mitjana en la comunicació: <strong>
   <?php
   echo round(array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante),0);
   ?>   
    paraules</strong><br />
   <img src="img/semantica.png" width="20px" style="vertical-align:middle; margin:3px 3px 3px 3px;" /> 
  <span title="Percentatge de la conversa que conté les paraules clau definides pel professor i que defineixen el camp semàntic desitjat per a la comunicació.">Grau d'adequació del discurs al camp semàntic:</span><br />
 	<?php
	
	$totalPalabras=array_sum($palabras);
 	$totalPalabrasClave=0;
 	foreach ($palabrasClave as $palabra=>$valor) {
		if ($palabra!=NULL) {
			$totalPalabrasClave+=$palabras[$palabra];
	 	}
 	}

	$valor=(($totalPalabrasClave/$totalPalabras)*100)*100/$severidad;
	echo "<div style=\"text-align:center;\"><img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".$valor."&chl=".round(($totalPalabrasClave/$totalPalabras)*100,2)."%&chf=bg,s,ffffff00&chco=ff0000,ffff00,00ff00\"><br />";
				echo "Grau de severitat establert: ".$severidad."%";
				echo "</div>";

	
	?>
    
 			</td>
            <td width="430px" colspan="2" style="font-size:12px;" valign="top">
            <br />
 <?php
 
 foreach ($palabrasClave as $palabra=>$valor) {
	 if ($palabra!=NULL) {
		 echo "&nbsp;&nbsp;&nbsp;- ".$palabra." (<span style=\"color:#F00\">".round((($palabras[$palabra]/$totalPalabras)*100),2)."%</span>)<br />";
	 }
 }

 
 ?>
 			</td>
            </tr>
            
            <tr>
            <td colspan="4" style="font-size:12px;">
            
                <a name="nube">
                
                <p align="center">
                <strong>Núvol d’etiquetes dels espais de conversa:</strong> Esfèric <a href="#nube" onClick=" TagCanvas.Start('mycanvas','tags',{weightSize:1.0,shape:'sphere'});">100%</a> | 
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.75,shape:'sphere'});">75%</a> | 
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.5,shape:'sphere'});">50%</a> | 
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.25, shape:'sphere'});">25%</a>
                 &nbsp;&nbsp;&nbsp; Cilíndric <a href="#nube" onClick=" TagCanvas.Start('mycanvas','tags',{weightSize:1.0,shape:'hcylinder'});">100%</a> |
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.75,shape:'hcylinder'});">75%</a> |
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.5,shape:'hcylinder'});">50%</a> |
                <a href="#nube" onClick="TagCanvas.Start('mycanvas','tags',{weightSize:0.25, shape:'hcylinder'});">25%</a>
                </p>
                
                <div id="nube" align="center">
                 <canvas width="800" height="500" id="mycanvas" style="background-color:#EEE;">
                  <p>Anything in here will be replaced on browsers that support the canvas element</p>
                 </canvas>
                
                </div>
                <br />
                <div id="tags">
                <ul>
                <?php
                arsort($palabras);
                if (count($palabras)>75) {
                    array_splice($palabras,75,count($palabras)-75);
                }
                foreach ($palabras as $palabra => $valor) {
                    $palabra=mb_strtolower($palabra,'UTF-8');
                    echo "<li><a href=\"#nube\" peso=\"$valor\">".$palabra."</a></li>";
                }
                ?>
                </ul>
                </div>
            
            
            
            </td>
            </tr>
            <tr>
            <td colspan="4" style="font-size:12px;" align="center">
				<br/><p align="center"><strong>Llistat amb les 75 paraules més freqüents</strong></p>

                <div class="tablaMetricas" style="width:375px;" align="center">
                                <table style="width:375px;">
                                    <tr>
                                        <td style="width:75px;">
                                            Posició
                                        </td>
                                        <td style="width:200px;">
                                            Paraula
                                        </td>
                                        <td style="width:100px;">
                                            Freqüència
                                        </td>
                                    </tr>
                <?php
                $contador=0;
                foreach ($palabras as $palabra => $valor) {
                    echo "<tr><td>".++$contador."</td><td>$palabra</td><td>$valor</td></tr>";
                }
                
                
                ?>
                </table>
                
                </div>

			</td>
            </tr>   
   
   </table>
	</div>












	<div id="panelAlertas" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE ALERTAS -->
    
        <div class="tituloPanel"><p><strong>PANELL D'ALERTES<br/></strong></p></div>
		<table cols="2" align="center" style="font-family:Verdana, Geneva, sans-serif; font-size:11px;" cellspacing="0px" width="860px">
    		<tr>
            	<td align="center" colspan="2" height="35px">
                

                <div class="tituloAlerta" style="text-align:center;"><strong>- Grau d'acompliment de l'objectiu de dispersió de la conversa -</strong></div>
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2" height="140px">
				<?php
                $valor=round((($numMensajes-1)/$numMensajes)-($numRespuestas/$numMensajes),2);
				$valor=$valor*100;
				echo "<img src=\"http://chart.apis.google.com/chart?chs=200x110&cht=gom&chd=t:".($valor/$dispersion*100)."&chl=".$valor."%&chf=bg,s,ffffff00&chco=00ff00,ffff00,ff0000\"><br />";
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
             <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES -->
			<?php
				$contador=0;
				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					if ($total==0) {
						echo "<tr><td></td><td height=\"25px\">- ".$estudiante;
						echo "</td></tr>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<tr><td></td><td height=\"25px\"><em>Cap estudiant en aquesta situació...</em></td></tr>";
				}
			?>
            <tr >
            	<td width="50px" align="center">
            		<img src="img/alerta_inactivo.png" width="35px">
            	</td>
                <td width="600px" >
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta d'estudiants per inactivitat: </strong>(<?php echo $diasInactividad<2?$diasInactividad."dia":$diasInactividad."dies"; echo " enrere des de la data ".date("d/m/Y G:i:s",$fecha2Completa); ?>)</div>
                </td>
             </tr>
             <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES -->
			<?php
				$contador=0;
				sort($fechasMensajePorEstudiante[$estudiante]);
				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					if (((count($fechasMensajePorEstudiante[$estudiante])!=0) && (end($fechasMensajePorEstudiante[$estudiante])<($fecha2Completa-(86400*$diasInactividad)))) || (count($fechasMensajePorEstudiante[$estudiante])==0)) {
						echo "<tr><td></td><td height=\"25px\">- ".$estudiante;
						if ($listaCorreos[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo "</td></tr>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<tr><td></td><td height=\"25px\"><em>Cap estudiant en aquesta situació...</em></td></tr>";
				}
			?>
            <tr>
            	<td width="50px" align="center">
            		<img src="img/alerta_pocos_mensajes.png" width="35px">
            	</td>
                <td width="600px">
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta d'estudiants per estar sota el llindar mínim de participació:</strong> (<?php echo $minimo ?> missatges)</div>
                </td>
             </tr>
             <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES -->
			<?php
				$contador=0;
				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					if (($total>0) && ($total<$minimo)) {
						echo "<tr><td></td><td height=\"25px\">- ".$estudiante;
						if ($listaCorreos[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo " <span style=\"color:#C00;\"><em>(".($minimo-$total)." missatge/s per superar el llindar)<br/><br/></em></span></td></tr>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<tr><td></td><td height=\"25px\"><em>Cap estudiant en aquesta situació...</em></td></tr>";
				}
			?>
            <tr>
            	<td width="50px" align="center">
            		<img src="img/alerta_muchos_mensajes.png" width="35px">
            	</td>
                <td width="600px" >
                	<div class="tituloAlerta" style="text-align:left;"><strong>Alerta per superar el llindar màxim de participació:</strong> (<?php echo $maximo ?> missatges)</div>
                </td>
             </tr>
             <!-- AQUI COMIENZA EL BUCLE PARA MOSTRAR LOS ESTUDIANTES -->
			<?php
				$contador=0;
				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					if ($total>$maximo) {
						echo "<tr><td></td><td height=\"25px\">- ".$estudiante;
						if ($listaCorreos[$estudiante]!=NULL) {
							echo "<span style=\"color:#00F;\"> &lt;".$listaCorreos[$estudiante]."&gt;</span>";
						} else {
							echo "<span style=\"color:#00F;\"> &lt;Sense informació&gt;</span>";
						}
						echo " <span style=\"color:#C00;\"><em>(".($total-$maximo)." missatge/s per sobre del llindar)<br/><br/></em></span></td></tr>";
						$contador++;
					}
				}
				if ($contador==0) {
						echo "<tr><td></td><td height=\"25px\"><em>Cap estudiant en aquesta situació...</em></td></tr>";
				}
			?>
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
                            - Participació en la interacció comunicativa
                        </td>
                        <td colspan="2" style="width:150px;">
                            - Foment del diàleg i de la negociació
                        </td>
                        <td style="width:75px;">
                            - Estil comunicatiu
                        </td>
                        <td style="width:280px;">
                            - Constància i regularitat en la interacció grupal
                        </td>
                        <td colspan="2" style="width:150px;">
                            - Intercanvi  d’informació dins el grup
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
foreach ($mensajesPorEstudiante as $estudiante => $total) {
    echo "<tr><td><strong>".$estudiante."</strong><br/>";
	if ($listaCorreos[$estudiante]!=NULL) {
		echo "&lt;".$listaCorreos[$estudiante]."&gt;";
	} else {
		echo "&lt;Sense informació&gt;";
	}
	echo "</td><td>".$total;
	
	//Añadimos flechas de promedio
	$promedio=array_sum($mensajesPorEstudiante)/count($mensajesPorEstudiante);
	if ($total<$promedio) {
		echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
	} else {
			if ($total>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
				
	}

	echo "</td><td>";
	$valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
		if ($valor>1){
			echo "Molt participatiu";
		} elseif ($valor>=0.5) {
			echo "Participatiu";
		} elseif ($valor>0) {
			echo "Poc participatiu";
		} else {
			echo "No participa";
		}
	
	echo "</td><td>".$mensajesRespuestaPorEstudiante[$estudiante];

	//Añadimos flechas de promedio
		$promedio=array_sum($mensajesRespuestaPorEstudiante)/count($mensajesRespuestaPorEstudiante);
		if ($mensajesRespuestaPorEstudiante[$estudiante]<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if ($mensajesRespuestaPorEstudiante[$estudiante]>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}

	echo "</td><td>".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."%</td>";
	
	echo "<td>".round($palabrasPorEstudiante[$estudiante]/$total,0);
	
	//Añadimos flechas de promedio
		$promedio=array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante);
		if (round($palabrasPorEstudiante[$estudiante]/$total,0)<$promedio) {
			echo "<img src=\"img/abajo.png\" width=\"15px\" title=\"Per sota del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		} else {
			if (round($palabrasPorEstudiante[$estudiante]/$total,0)>$promedio) {
				echo "<img src=\"img/arriba.png\" width=\"15px\" title=\"Per damunt del promig.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
			}
		}
	
	

// Calcular y llistar la distribució temporal de missatges

echo "<td>";

		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;
		echo "<img src=\"img/amplitud.png\" width=\"25px\" title=\"Durada de la participació.\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
		if (count($fechasMensajePorEstudiante[$estudiante])==0){
			echo "Cap";
		} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
			echo "1 dia";
		} else if  (($periodo>0) && ($periodo<1)){
			echo round($periodo*24,1)." hores";
		} else {
			echo round($periodo,1)." dies";

		}
		echo " <img src=\"img/dinamizar.png\" width=\"20px\" title=\"Dates de publicació:\n---------------------------\n";
		
		sort($fechasMensajePorEstudiante[$estudiante]);
		$contador=0;
		while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
			echo date("d M Y G:i:s",$fechasMensajePorEstudiante[$estudiante][$contador])."\n";
			$contador++;
		}
		
		echo "\" style=\"vertical-align:middle; margin:1px 1px 1px 1px;\" /> ";
if (sizeof($fechasMensajePorEstudiante[$estudiante])==0) {
	echo "Cap";
} else if (sizeof($fechasMensajePorEstudiante[$estudiante])==1) {
	echo "Missatge únic";
}
else {
	if (sizeof($fechasMensajePorEstudiante[$estudiante])<=3) {
	echo "Pocs missatges";
	}
	else {
		
		$contador=1;
		unset($fechas);
		$fechas=array();
		while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
			$fechas[$contador-1]=($fechasMensajePorEstudiante[$estudiante][$contador]-$fechasMensajePorEstudiante[$estudiante][$contador-1])/86400;
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

echo "</td><td>".round($adjuntosPorEstudiante[$estudiante],0);
if ($adjuntosPorEstudiante[$estudiante]>0) {
	echo " <img src=\"img/archivos_adjuntos.png\" width=\"20px\" style=\"vertical-align:middle;\" title=\"LLista d'arxius adjunts:\n---------------------------\n";
	$contador=0;
	while ($contador<sizeof($nombresAdjuntosPorEstudiante[$estudiante])) {
		echo $nombresAdjuntosPorEstudiante[$estudiante][$contador];
		$contador++;
	}
	echo "\">";
}

echo "</td><td>".round($enlacesPorEstudiante[$estudiante],0);

echo "</td></tr>";
}
?>
                </table>
            </div>

	</div>
    
    









	<div id="panelClasificaciones" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE CLASIFICACIONES -->
    
        <div class="tituloPanel"><p><strong>PANELL DE CLASSIFICACIONS PER MÈTRIQUES<br/></strong></p></div>
			<table cols="2" width="700px" align="center" >
			<tr>
            <td valign="top" align="center">
            <div class="tablaClasificaciones">
            	<table cols="3">
                <tr><td colspan="3">MISSATGES LLIURATS PER ESTUDIANT</td></tr>
                
				<?php
                $contador=0;
                $copiaVector=$mensajesPorEstudiante;
				arsort($mensajesPorEstudiante);
                foreach ($mensajesPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".$total."</td></tr>";
                }
				$mensajesPorEstudiante=$copiaVector; // Restauramos el array como estaba antes de reordenarlo
                ?>
                
				</table>
			</div>
            </td>
            <td valign="top" align="center">
            <div class="tablaClasificaciones">
            	<table cols="3">
                <tr><td colspan="3">MISSATGES RESPOSTA PER ESTUDIANT</td></tr>

				<?php
                $contador=0;
                arsort($mensajesRespuestaPorEstudiante);
                foreach ($mensajesRespuestaPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".$total."</td></tr>";
                }
                ?>

				</table>
             </div>
            </td>
            </tr>
            
            
			<tr>
            <td valign="top" align="center">
            <div class="tablaClasificaciones">
            	<table cols="3">
                <tr><td colspan="3">PROMIG DE PARAULES PER ESTUDIANT</td></tr>
                
				<?php
                foreach ($mensajesPorEstudiante as $estudiante => $total) {
                    $promedio=round($palabrasPorEstudiante[$estudiante]/$total,0);
                    $promedioPalabrasPorEstudiante[$estudiante]=$promedio;
                }
                
                $contador=0;
                arsort($promedioPalabrasPorEstudiante);
                foreach ($promedioPalabrasPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".$total."</td></tr>";
                }
                ?>
                
				</table>
			</div>
            </td>
            <td valign="top" align="center">
            <div class="tablaClasificaciones" style="width:450px;">
            	<table cols="3">
                <tr><td colspan="3">PARTICIPACIÓ EN LA INTERACCIÓ COMUNICATIVA</td></tr>

				<?php
                $contador=0;
                $copiaVector=$mensajesPorEstudiante;
                arsort($mensajesPorEstudiante);
                foreach ($mensajesPorEstudiante as $estudiante => $total) {
                    $valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='150'>".$valor." (";
                    if ($valor>1){
                        echo "Molt participatiu";
                    } elseif ($valor>=0.5) {
                        echo "Participatiu";
                    } elseif ($valor>0) {
                        echo "Poc participatiu";
                    } else {
                        echo "No participa";
                    }
                    echo ")</td></tr>";
                }
				$mensajesPorEstudiante=$copiaVector; // Restauramos el array como estaba antes de reordenarlo
                ?>

				</table>
             </div>
            </td>
            </tr>
            
            
            
			<tr>
            <td valign="top" align="center">
            <div class="tablaClasificaciones">
            	<table cols="3">
                <tr><td colspan="3">POPULARITAT PER CADA ESTUDIANT</td></tr>
                
				<?php
                $contador=0;
                arsort($popularidadPorEstudiante);
                foreach ($popularidadPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".round(100*$total/$numRespuestas,2)."%</td></tr>";
                }
                
                ?>

                
				</table>
			</div>
            </td>
            <td valign="top" align="center">
            <div class="tablaClasificaciones" style="width:450px;">
            	<table cols="3">
                <tr><td colspan="3">ARXIUS ADJUNTS PER CADA ESTUDIANT</td></tr>

				<?php
                $contador=0;
                arsort($adjuntosPorEstudiante);
                foreach ($adjuntosPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".$total."</td></tr>";
                }
                
                ?>

				</table>
             </div>
            </td>
            </tr>
            
            <tr>
            <td colspan="2" valign="top" align="center">

            <div class="tablaClasificaciones" style="width:450px;">
            	<table cols="3">
                <tr><td colspan="3">ENLLAÇOS EXTERNS PUBLICATS PER ESTUDIANT</td></tr>

				<?php
                $contador=0;
                arsort($enlacesPorEstudiante);
                foreach ($enlacesPorEstudiante as $estudiante => $total) {
                    echo "<tr><td align='center' width='50'>".++$contador."</td><td width='250' align='left'>".$estudiante."</td><td align='center' width='50'>".$total."</td></tr>";
                }
                
                ?>

				</table>
             </div>

            
            </td>
            </tr>
            
			</table>
	</div>











	<div id="panelVistaEstudiante" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL VISTA DE ESTUDIANTE -->
        <div class="tituloPanel"><p><strong>PANELL VISTA D'ESTUDIANT<br/></strong></p></div>
		<p style="font-size:12px;">Selecciona l'estudiant que vols visualitzar:
        <?php
			echo "<select name=\"alumno\" onChange=\"document.getElementById('vistaEstudiante').src=this.options[this.selectedIndex].value;\">";
			echo "<option value=\"http://www.paucasals.com/missatgesUOC/vistaEstudianteVacio.php\">(Selecciona)</option>";
			foreach ($listaEstudiantes as $estudiante=>$valor){
				echo "<option value=\"http://www.paucasals.com/diana/vistaEstudiante.php?carpeta=debates/seleccionado&amp;fecha1=".$fecha1."&amp;fecha2=".$fecha2."&amp;estudiante=".$estudiante."\">".$estudiante."</option>";
				//echo "<option value=\"".$estudiante."\">".$estudiante."</option>";
			}
			echo "</select>";
        ?>
        </p>
        
        <iframe id="vistaEstudiante" width="1090" height="<?php echo(520+(max($adjuntosPorEstudiante)*25)); ?>" style="background-color:#EEE;" marginwidth="0" marginheight="0" frameborder="0" scrolling="no"></iframe>
        
	</div>


















	<div id="panelVistaComparador" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL VISTA DE COMPARADOR DE ESTUDIANTE -->
        <div class="tituloPanel"><p><strong>PANELL VISTA COMPARADOR D'ESTUDIANTS<br/></strong></p></div>
		<table width="100%" cols="2">
        <tr>
        <td style="width:545px;">
        <p style="font-size:12px;">Selecciona el primer estudiant a comparar:
        <?php
			echo "<select name=\"alumno\" onChange=\"document.getElementById('estudiante1').src=this.options[this.selectedIndex].value;\">";
			echo "<option value=\"http://www.paucasals.com/diana/vistaEstudianteVacio.php\">(Selecciona)</option>";
			foreach ($listaEstudiantes as $estudiante=>$valor){
				echo "<option value=\"http://www.paucasals.com/diana/vistaEstudianteVertical.php?carpeta=debates/seleccionado&amp;fecha1=".$fecha1."&amp;fecha2=".$fecha2."&amp;estudiante=".$estudiante."\">".$estudiante."</option>";
				//echo "<option value=\"".$estudiante."\">".$estudiante."</option>";
			}
			echo "</select>";
        ?>
        </p>
        
        <iframe id="estudiante1" width="545" height="<?php echo(1170+(max($adjuntosPorEstudiante)*25)); ?>" style="background-color:#EEE;" marginwidth="0" marginheight="0" frameborder="0" scrolling="no">

		</iframe>
        
        </td>

        <td style="width:545px;">
        <p style="font-size:12px;">Selecciona el segon estudiant a comparar:
        <?php
			echo "<select name=\"alumno\" onChange=\"document.getElementById('estudiante2').src=this.options[this.selectedIndex].value;\">";
			echo "<option value=\"http://www.paucasals.com/diana/vistaEstudianteVacio.php\">(Selecciona)</option>";
			foreach ($listaEstudiantes as $estudiante=>$valor){
				echo "<option value=\"http://www.paucasals.com/diana/vistaEstudianteVertical.php?carpeta=debates/seleccionado&amp;fecha1=".$fecha1."&amp;fecha2=".$fecha2."&amp;estudiante=".$estudiante."\">".$estudiante."</option>";
				//echo "<option value=\"".$estudiante."\">".$estudiante."</option>";
			}
			echo "</select>";
        ?>
        </p>
        
        <iframe id="estudiante2" width="545" height="<?php echo(1170+(max($adjuntosPorEstudiante)*25)); ?>" style="background-color:#EEE;" marginwidth="0" marginheight="0" frameborder="0" scrolling="no">

		</iframe>
        
        </td>
        </tr>
        </table>
        
	</div>

















	<div id="panelFeedbackCatalan" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL FEEDBACK CATALAN -->
        <div class="tituloPanel"><p><strong>PANELL DE FEEDBACK EN LLENGUA CATALANA<br/></strong></p></div>

        <p align="center"><br /><u><strong>Descarregar el feedback en català: <a href="feedback_cat_utf8_tab.csv">feedback_cat_utf8_tab.csv</a></strong></u></p>
        <br />
        
        <div class="tablaMetricas" style="width:1000px;" align="center">
                        <table style="width:1000px;">
                            <tr>
                                <td style="width:250px;">
                                    Estudiant
                                </td>
                                <td style="width:750px;">
                                    Feedback
                                </td>
                            </tr>
        <?php
        
        foreach ($mensajesPorEstudiante as $estudiante => $total) {

		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;
        
        echo "<tr><td>".$estudiante."<br/>";
		if ($listaCorreos[$estudiante]!=NULL) {
			echo "&lt;".$listaCorreos[$estudiante]."&gt;";
		} else {
			echo "&lt;correu no disponible&gt;";
		}
		echo "</td><td>";
        $linea_cat= "A l'espai de comunicació has realitzat un total de ".$total." aportacions";
        
        if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
            $linea_cat=$linea_cat.", de les quals ".$mensajesRespuestaPorEstudiante[$estudiante]." eren respostes a fils ja oberts";
        }
        
		if (count($fechasMensajePorEstudiante[$estudiante])==0){
			$linea_cat=$linea_cat.". No vas participar en la conversa cap dia.";
		} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
			$linea_cat=$linea_cat.". La teva participació es va portar a terme durant un període d'un dia.";
		} else if  (($periodo>0) && ($periodo<1)){
			$linea_cat=$linea_cat.". La teva participació es va portar a terme durant un període de ".round($periodo*24,1)." hores.";
		} else {
			$linea_cat=$linea_cat.". La teva participació es va portar a terme durant un període de ".round($periodo,1)." dies.";
		}
		
		$linea_cat= $linea_cat." Has utilitzat una mitjana de ".round($palabrasPorEstudiante[$estudiante]/$total,0)." paraules, ";
        
        if ($enlacesPorEstudiante[$estudiante]==0) {
            $linea_cat= $linea_cat."sense fer servir cap enllaç extern i ";
        } else {
            $linea_cat= $linea_cat."fent servir ".$enlacesPorEstudiante[$estudiante]." enllaç/os externs i ";
        }
        
        if ($adjuntosPorEstudiante[$estudiante]==0){
            $linea_cat= $linea_cat."cap";
        } else {
            $linea_cat= $linea_cat."utilitzant ".$adjuntosPorEstudiante[$estudiante];
        }
        
        $linea_cat= $linea_cat." arxiu/s adjunt/s. A més, en base a les respostes rebudes a les teves aportacions, has aconseguit assolir un ";
        
        if (round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)==0) {
            $linea_cat= $linea_cat."0";
        } else {
            $linea_cat= $linea_cat.round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2);
        }
        
        $linea_cat= $linea_cat."% de popularitat dins de la discussió. Finalment, basant-nos només en els missatges publicats a la conversa, la teva actitud es pot definir com ";

                    $valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
                    if ($valor>1){
                         $linea_cat= $linea_cat."molt participativa";
                    } elseif ($valor>=0.5) {
                         $linea_cat= $linea_cat."participativa";
                    } elseif ($valor>0) {
                         $linea_cat= $linea_cat."poc participativa";
                    } else {
                         $linea_cat= $linea_cat."gens participativa";
                    }
        
        $linea_cat= $linea_cat.".\r\n";
        echo $linea_cat."</td></tr>";
		$linea_cat=$estudiante.chr(9).$linea_cat;
        fputs($ficheroFeedbackCatalan, utf8_decode($linea_cat));
        }
        
        ?>
        </table>
        
        </div>

	</div>
















	<div id="panelFeedbackCastellano" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL FEEDBACK CASTELLANO -->
        <div class="tituloPanel"><p><strong>PANELL DE FEEDBACK EN LLENGUA CASTELLANA<br/></strong></p></div>

        <p align="center"><br /><u><strong>Descarregar el feedback en castellà: <a href="feedback_cas_utf8_tab.csv">feedback_cas_utf8_tab.csv</a></strong></u></p>
        <br />
        
        <div class="tablaMetricas" style="width:1000px;" align="center">
                        <table style="width:1000px;">
                            <tr>
                                <td style="width:250px;">
                                    Estudiant
                                </td>
                                <td style="width:750px;">
                                    Feedback
                                </td>
                            </tr>
        <?php
        
        foreach ($mensajesPorEstudiante as $estudiante => $total) {

		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;
        
        echo "<tr><td>".$estudiante."<br/>";
		if ($listaCorreos[$estudiante]!=NULL) {
			echo "&lt;".$listaCorreos[$estudiante]."&gt;";
		} else {
			echo "&lt;correo no disponible&gt;";
		}
		echo "</td><td>";
		
        $linea_cat= "En el espacio de comunicación has realizado un total de ".$total." aportaciones";
        
        if ($mensajesRespuestaPorEstudiante[$estudiante]>0) {
            $linea_cat=$linea_cat.", de las cuales ".$mensajesRespuestaPorEstudiante[$estudiante]." eran respuestas a hilos ya abiertos";
        }
        
		if (count($fechasMensajePorEstudiante[$estudiante])==0){
			$linea_cat=$linea_cat.". No participaste en la conversación ningún día.";
		} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
			$linea_cat=$linea_cat.". Tu participación se llevó a cabo durante un período de un día.";
		} else if  (($periodo>0) && ($periodo<1)){
			$linea_cat=$linea_cat.". Tu participación se llevó a cabo durante un período de ".round($periodo*24,1)." horas.";
		} else {
			$linea_cat=$linea_cat.". Tu participación se llevó a cabo durante un período de ".round($periodo,1)." días.";
		}
		
		$linea_cat= $linea_cat." Has utilizado una media de ".round($palabrasPorEstudiante[$estudiante]/$total,0)." palabras, ";
        
        if ($enlacesPorEstudiante[$estudiante]==0) {
            $linea_cat= $linea_cat."sin emplear ningún enlace externo y ";
        } else {
            $linea_cat= $linea_cat."empleando ".$enlacesPorEstudiante[$estudiante]." enlace/s externos y ";
        }
        
        if ($adjuntosPorEstudiante[$estudiante]==0){
            $linea_cat= $linea_cat."ningún";
        } else {
            $linea_cat= $linea_cat."utilizando ".$adjuntosPorEstudiante[$estudiante];
        }
        
        $linea_cat= $linea_cat." archivo/s adjunto/s. Además, en base a las respuestas recibidas a tus aportaciones, has alcanzado un ";
        
        if (round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)==0) {
            $linea_cat= $linea_cat."0";
        } else {
            $linea_cat= $linea_cat.round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2);
        }
        
        $linea_cat= $linea_cat."% de popularidad en la discusión. Finalmente, basándonos sólo en los mensajes publicados en la conversación, tu actitud puede ser definida como ";

                    $valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
                    if ($valor>1){
                         $linea_cat= $linea_cat."muy participativa";
                    } elseif ($valor>=0.5) {
                         $linea_cat= $linea_cat."participativa";
                    } elseif ($valor>0) {
                         $linea_cat= $linea_cat."poco participativa";
                    } else {
                         $linea_cat= $linea_cat."nada participativa";
                    }
        
        $linea_cat= $linea_cat.".\r\n";
        echo $linea_cat."</td></tr>";
		$linea_cat=$estudiante.chr(9).$linea_cat;
        fputs($ficheroFeedbackCastellano, utf8_decode($linea_cat));
        }
        
        ?>
        </table>
        
        </div>

	</div>













	<div id="panelXMLCatalan" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE EXPORTACIÓN XML EN CATALAN -->
    
        <div class="tituloPanel"><p><strong>PANELL D'EXPORTACIÓ DE DADES EN FORMAT XML VERSIÓ CATALÀ<br/></strong></p></div>
			<table cols="1" width="800px" align="center" style="background-color:#FFFFFF; padding:10px;">
            <tr>
            <td aling="left" >
            <pre><?php
            	echo "&lt;?xml version=\"1.0\" encoding=\"UTF-8\"?&gt;<br/>"; 
				echo "&lt;analitiquesAprenentatgeDIANA2.1&gt;";
				echo "<blockquote>&lt;metriquesGlobals&gt;";
				echo "<blockquote>&lt;estudiantsTotals&gt;".count($mensajesPorEstudiante)."&lt;/estudiantsTotals&gt;<br/>";
				echo "&lt;nombreEstudiantsParticipants&gt;".count($listaParticipantes)."&lt;/nombreEstudiantsParticipants&gt;<br/>";
				echo "&lt;nombreTotalMissatgesPublicats&gt;".$numMensajes."&lt;/nombreTotalMissatgesPublicats&gt;<br/>";
				$valor=1-(standard_deviation($mensajesPorEstudiante)/((max($mensajesPorEstudiante)+min($mensajesPorEstudiante))/2));
				$valor*=100;
				$valor=round($valor,2);
				echo "&lt;grauHomogeneitatParticipacio&gt;".$valor."%&lt;/grauHomogeneitatParticipacio&gt;<br/>";
				echo "&lt;respostesTotalsPublicades&gt;".$numRespuestas."&lt;/respostesTotalsPublicades&gt;<br/>";
				echo "&lt;nivellDialeg&gt;".(round($numRespuestas/($numMensajes-1),4)*100)."%&lt;/nivellDialeg&gt;<br/>";
				echo "&lt;grauDispersio&gt;";
				$valor=round((($numMensajes-1)/$numMensajes)-($numRespuestas/$numMensajes),2);
				if ($valor>0.55){
					echo "Conversa dispersa";
				} elseif ($valor>=0.45) {
					echo "Conversa equilibrada";
				} else {
					echo "Conversa concentrada";
				}
				echo " (".($valor*100)."%)&lt;/grauDispersio&gt;<br/>";
				echo "&lt;nombreArxiusGlobalsPublicats&gt;".array_sum($adjuntosPorEstudiante)."&lt;/nombreArxiusGlobalsPublicats&gt;<br/>";
				echo "&lt;nombreGlobalVinclesExternsPublicats&gt;".array_sum($enlacesPorEstudiante)."&lt;/nombreGlobalVinclesExternsPublicats&gt;<br/>";
				echo "&lt;extensioMitjanaGlobalComunicacio&gt;".round(array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante),0)." paraules&lt;/extensioMitjanaGlobalComunicacio&gt;<br/>";
				echo "&lt;grauAdequacioCampSemantic&gt;";
				$totalPalabras=array_sum($palabras);
				$totalPalabrasClave=0;
				foreach ($palabrasClave as $palabra=>$valor) {
					if ($palabra!=NULL) {
						$totalPalabrasClave+=$palabras[$palabra];
					}
				}
				$valor=($totalPalabrasClave/$totalPalabras)*100;
				echo round($valor,2)."%&lt;/grauAdequacioCampSemantic&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/metriquesGlobals&gt;<br/>";
				echo "&lt;metriquesIndividuals&gt;<blockquote>";

				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					echo "&lt;estudiant&gt;<br/>";
					echo "<blockquote>";
					echo "&lt;nom&gt;".$estudiante."&lt;/nom&gt;<br/>";
					echo "&lt;correu&gt;";
					if ($listaCorreos[$estudiante]!=NULL) {
						echo $listaCorreos[$estudiante];
					} else {
						echo "Sense informació";
					}
					echo"&lt;/correu&gt;<br/>";
					echo "&lt;nombreMissatgesPublicats&gt;".$total."&lt;/nombreMissatgesPublicats&gt;<br/>";
					echo "&lt;nivellParticipacio&gt;";
					$valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
						if ($valor>1){
							echo "Molt participatiu";
						} elseif ($valor>=0.5) {
							echo "Participatiu";
						} elseif ($valor>0) {
							echo "Poc participatiu";
						} else {
							echo "No participa";
						}
					echo "&lt;/nivellParticipacio&gt;<br/>";					
					echo "&lt;respostesPublicades&gt;".$mensajesRespuestaPorEstudiante[$estudiante]."&lt;/respostesPublicades&gt;<br/>";
				
					echo "&lt;popularitat&gt;".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."%&lt;/popularitat&gt;<br/>";
					echo "&lt;extensioMitjanaComunicacio&gt;".round($palabrasPorEstudiante[$estudiante]/$total,0)." paraules&lt;/extensioMitjanaComunicacio&gt;<br/>";
					echo "&lt;duradaComunicacio&gt;";
					$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
					$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
					$periodo=$max-$min;
					if (count($fechasMensajePorEstudiante[$estudiante])==0){
						echo "Cap";
					} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
						echo "1 dia";
					} else if  (($periodo>0) && ($periodo<1)){
						echo round($periodo*24,1)." hores";
					} else {
						echo round($periodo,1)." dies";
			
					}
					echo "&lt;/duradaComunicacio&gt;<br/>";
					echo "&lt;distribucioTemporalMissatgesIndividuals&gt;";
					sort($fechasMensajePorEstudiante[$estudiante]);
					if (sizeof($fechasMensajePorEstudiante[$estudiante])==0) {
						echo "Cap";
					} else if (sizeof($fechasMensajePorEstudiante[$estudiante])==1) {
						echo "Missatge únic";
					}
					else {
						if (sizeof($fechasMensajePorEstudiante[$estudiante])<=3) {
						echo "Pocs missatges";
						}
						else {
							
							$contador=1;
							unset($fechas);
							$fechas=array();
							while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
								$fechas[$contador-1]=($fechasMensajePorEstudiante[$estudiante][$contador]-$fechasMensajePorEstudiante[$estudiante][$contador-1])/86400;
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
					echo "&lt;/distribucioTemporalMissatgesIndividuals&gt;<br/>";
					echo "&lt;nombreArxiusPublicats&gt;".($adjuntosPorEstudiante[$estudiante]==0?"0":$adjuntosPorEstudiante[$estudiante])."&lt;/nombreArxiusPublicats&gt;<br/>";
					echo "&lt;nombreVinclesPublicats&gt;".($enlacesPorEstudiante[$estudiante]==0?"0":$enlacesPorEstudiante[$estudiante])."&lt;/nombreVinclesPublicats&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/estudiant&gt;<br/>";
				}
			
				
				echo "</blockquote>&lt;/metriquesIndividuals&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/analitiquesAprenentatgeDIANA2.1&gt;<br/>";
				
			?>
            </pre>
            </td>
            </tr>
		</table>
	</div>













	<div id="panelXMLCastellano" class="panelDerecho"> <!-- AQUÍ EMPIEZA EL PANEL DE EXPORTACIÓN XML EN CASTELLANO -->
    
        <div class="tituloPanel"><p><strong>PANELL D'EXPORTACIÓ DE DADES EN FORMAT XML VERSIÓ CASTELLÀ<br/></strong></p></div>
			<table cols="1" width="800px" align="center" style="background-color:#FFFFFF; padding:10px;">
            <tr>
            <td aling="left" >
            <pre><?php
            	echo "&lt;?xml version=\"1.0\" encoding=\"UTF-8\"?&gt;<br/>"; 
				echo "&lt;analiticasAprendizajeDIANA2.1&gt;";
				echo "<blockquote>&lt;metricasGlobales&gt;";
				echo "<blockquote>&lt;estudiantesTotales&gt;".count($mensajesPorEstudiante)."&lt;/estudiantesTotales&gt;<br/>";
				echo "&lt;numeroEstudiantesParticipantes&gt;".count($listaParticipantes)."&lt;/numeroEstudiantesParticipantes&gt;<br/>";
				echo "&lt;numeroTotalMensajesPublicados&gt;".$numMensajes."&lt;/numeroTotalMensajesPublicados&gt;<br/>";
				$valor=1-(standard_deviation($mensajesPorEstudiante)/((max($mensajesPorEstudiante)+min($mensajesPorEstudiante))/2));
				$valor*=100;
				$valor=round($valor,2);
				echo "&lt;gradoHomogeneidadParticipacion&gt;".$valor."%&lt;/gradoHomogeneidadParticipacion&gt;<br/>";
				echo "&lt;respuestasTotalesPublicadas&gt;".$numRespuestas."&lt;/respuestasTotalesPublicadas&gt;<br/>";
				echo "&lt;nivelDialogo&gt;".(round($numRespuestas/($numMensajes-1),4)*100)."%&lt;/nivelDialogo&gt;<br/>";
				echo "&lt;gradoDispersion&gt;";
				$valor=round((($numMensajes-1)/$numMensajes)-($numRespuestas/$numMensajes),2);
				if ($valor>0.55){
					echo "Conversación dispersa";
				} elseif ($valor>=0.45) {
					echo "Conversación equilibrada";
				} else {
					echo "Conversación concentrada";
				}
				echo " (".($valor*100)."%)&lt;/gradoDispersion&gt;<br/>";
				echo "&lt;numeroArchivosGlobalesPublicados&gt;".array_sum($adjuntosPorEstudiante)."&lt;/numeroArchivosGlobalesPublicados&gt;<br/>";
				echo "&lt;numeroEnlacesExternosGlobalesPublicados&gt;".array_sum($enlacesPorEstudiante)."&lt;/numeroEnlacesExternosGlobalesPublicados&gt;<br/>";
				echo "&lt;extensionMediaGlobalComunicacion&gt;".round(array_sum($palabrasPorEstudiante)/array_sum($mensajesPorEstudiante),0)." palabras&lt;/extensionMediaGlobalComunicacion&gt;<br/>";
				echo "&lt;gradoAdecuacionCampoSemantico&gt;";
				$totalPalabras=array_sum($palabras);
				$totalPalabrasClave=0;
				foreach ($palabrasClave as $palabra=>$valor) {
					if ($palabra!=NULL) {
						$totalPalabrasClave+=$palabras[$palabra];
					}
				}
			
				$valor=($totalPalabrasClave/$totalPalabras)*100;
				echo round($valor,2)."%&lt;/gradoAdecuacionCampoSemantico&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/metricasGlobales&gt;<br/>";
				echo "&lt;metricasIndividuales&gt;<blockquote>";

				foreach ($mensajesPorEstudiante as $estudiante => $total) {
					echo "&lt;estudiante&gt;<br/>";
					echo "<blockquote>";
					echo "&lt;nombre&gt;".$estudiante."&lt;/nombre&gt;<br/>";
					echo "&lt;correo&gt;";
					if ($listaCorreos[$estudiante]!=NULL) {
						echo $listaCorreos[$estudiante];
					} else {
						echo "Sin información";
					}
					echo"&lt;/correo&gt;<br/>";
					echo "&lt;numeroMensajesPublicados&gt;".$total."&lt;/numeroMensajesPublicados&gt;<br/>";
					echo "&lt;nivelParticipacion&gt;";
					$valor=round($total/($numMensajes/count($mensajesPorEstudiante)),2);
						if ($valor>1){
							echo "Muy participativo";
						} elseif ($valor>=0.5) {
							echo "Participativo";
						} elseif ($valor>0) {
							echo "Poco participativo";
						} else {
							echo "No participa";
						}
					echo "&lt;/nivelParticipacion&gt;<br/>";					
					echo "&lt;respuestasPublicadas&gt;".$mensajesRespuestaPorEstudiante[$estudiante]."&lt;/respuestasPublicadas&gt;<br/>";
				
					echo "&lt;popularidad&gt;".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."%&lt;/popularidad&gt;<br/>";
					echo "&lt;extensionMediaComunicacion&gt;".round($palabrasPorEstudiante[$estudiante]/$total,0)." palabras&lt;/extensionMediaComunicacion&gt;<br/>";
					echo "&lt;duracionComunicacion&gt;";
					$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
					$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
					$periodo=$max-$min;
					if (count($fechasMensajePorEstudiante[$estudiante])==0){
						echo "Ninguna";
					} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
						echo "1 día";
					} else if  (($periodo>0) && ($periodo<1)){
						echo round($periodo*24,1)." horas";
					} else {
						echo round($periodo,1)." días";
			
					}
					echo "&lt;/duracionComunicacion&gt;<br/>";
					echo "&lt;distribucionTemporalMensajesIndividuales&gt;";
					sort($fechasMensajePorEstudiante[$estudiante]);
					if (sizeof($fechasMensajePorEstudiante[$estudiante])==0) {
						echo "Ninguno";
					} else if (sizeof($fechasMensajePorEstudiante[$estudiante])==1) {
						echo "Mensaje único";
					}
					else {
						if (sizeof($fechasMensajePorEstudiante[$estudiante])<=3) {
						echo "Pocos mensajes";
						}
						else {
							
							$contador=1;
							unset($fechas);
							$fechas=array();
							while ($contador<sizeof($fechasMensajePorEstudiante[$estudiante])) {
								$fechas[$contador-1]=($fechasMensajePorEstudiante[$estudiante][$contador]-$fechasMensajePorEstudiante[$estudiante][$contador-1])/86400;
								$contador++;
							}
							$dinamització=standard_deviation($fechas);
					
							if ($dinamització<2) {
								echo "Muy distribuido";
							}
							else {
								if ($dinamització<4) {
								echo "Distribuido";
							}
							else {
								echo "Poco distribuido";
							}
						}
							
					}
					}
					echo "&lt;/distribucionTemporalMensajesIndividuales&gt;<br/>";
					echo "&lt;numeroArchivosPublicados&gt;".($adjuntosPorEstudiante[$estudiante]==0?"0":$adjuntosPorEstudiante[$estudiante])."&lt;/numeroArchivosPublicados&gt;<br/>";
					echo "&lt;numeroEnlacesPublicados&gt;".($enlacesPorEstudiante[$estudiante]==0?"0":$enlacesPorEstudiante[$estudiante])."&lt;/numeroEnlacesPublicados&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/estudiante&gt;<br/>";
				}
			
				
				echo "</blockquote>&lt;/metricasIndividuales&gt;<br/>";
				echo "</blockquote>";
				echo "&lt;/analiticasAprendizajeDIANA2.1&gt;<br/>";
				
			?>
            </pre>
            </td>
            </tr>
		</table>
	</div>








</td>
</tr>

</table>



<script>

ocultarPanel(false,true,true,true,true,true,true,true,true,true);

</script>


</div>

</body>
</html>