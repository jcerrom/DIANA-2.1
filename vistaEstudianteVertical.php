<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DIANA 2.1 (DIALOGUE ANALYSIS) - Learning Analytics</title>

</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size:11px; background-color: #EEE;">
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

<?php 
$fecha1=$_GET["fecha1"];
$fecha2=$_GET["fecha2"];
$dias=round((($fecha2-$fecha1)/86400),0);
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

?>
<div id="contenedorPrincipal">


<?php 

// DEFINICIÓN DE VARIABLES GLOBALES -----------------------------------------------------------------------

$carpeta=$_GET["carpeta"];
$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

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

// Declaramos el fichero de configuracion
$ficheroConfiguracion=fopen("conf.txt","r"); 

// Definimos el array con la lista de palabras de la conversación
$palabras = array();

// Declaramos la lista con los nombres de los ficheros adjuntos por usuario
$nombresAdjuntosPorEstudiante=array(array()); 


// CARGAMOS LAS VARIABLES DEL FICHERO DE CONFIGURACION ------------------------------------------------
$severidad=fgets($ficheroConfiguracion);
$minimo=fgets($ficheroConfiguracion);
$maximo=fgets($ficheroConfiguracion);
$dispersion=fgets($ficheroConfiguracion);

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
//$fechasMensajePorEstudiante=$listaEstudiantes; Esta lista no porque es un array(array())
$enlacesPorEstudiante=$listaEstudiantes;
$adjuntosPorEstudiante=$listaEstudiantes;

// CREACIÓN DEL FICHERO DE NODOS Y ARISTAS PARA IMPORTARLO A GEPHI ----------------------------------------------

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
	   
	   // Computamos este mensaje al contador del estudiante
	   if ($mensajesPorEstudiante[$estudiante]>0) {
		   $mensajesPorEstudiante[$estudiante]++;
	   } else {
		   $mensajesPorEstudiante[$estudiante]=1;
	   }
	   
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

// Cerramos el directorio liberando recursos
closedir($directorio);



// VOLVEMOS A RECORRER LOS ARCHIVOS PARA COMPUTAR EL NÚMERO DE PALABRAS PROMEDIO
// Y CONTAR EL NÚMERO DE ENLACES Y ADJUNTOS QUE TIENE EL MENSAJE ---------------------------------------------------

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
// Y TAMBIÉN CREAMOS EL ARRAY CON LA LISTA DE PALABRAS Y EL CÁLCULO
// DE SUS APARICIONES -----------------------------------------------------------------------------------


$directorio = opendir("./".$carpeta."/"); // Cargamos el directorio

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
				
   	    $linea =  utf8_encode(quoted_printable_decode((fgets($mensaje))));
		   } 
		   
	   }
	   else {
	   }
	   
	   // Cerramos el mensaje
	   fclose($mensaje);
    }
}

// Cerramos el directorio liberando recursos
closedir($directorio);



// AHORA COMENZAMOS A VISUALIZAR LOS RESULTADOS ------------------------------------------------------------


$estudiante=$_GET["estudiante"];

?>




<table id="rejillaPantalla" cols="1" cellpadding="0px" cellspacing="0px" align="center" width="545">
<tr>

<td width="250" align="center" style="font-size:12px;">
<p style="text-decoration:underline;"><strong>Missatges totals</strong></p>
<?php

echo "<img src=\"http://chart.apis.google.com/chart?chs=250x150&amp;chd=t:".($mensajesPorEstudiante[$estudiante]/array_sum($mensajesPorEstudiante)).",".(1-($mensajesPorEstudiante[$estudiante]/array_sum($mensajesPorEstudiante)))."&amp;cht=p3&amp;chl=".$mensajesPorEstudiante[$estudiante]."|".(array_sum($mensajesPorEstudiante)-$mensajesPorEstudiante[$estudiante])."&amp;&chco=FF0000,00FF00CC&chdl=Missatges de l'estudiant|Resta&chdlp=b&amp;chf=bg,s,EEEEEE\" width=\"250\" height=\"150\">";
echo "<p style=\"font-style:italic;\">En percentatge: ".$mensajesPorEstudiante[$estudiante]."/".array_sum($mensajesPorEstudiante)."=".round(($mensajesPorEstudiante[$estudiante]/array_sum($mensajesPorEstudiante))*100,2)."%</p>";
?>
</td>
</tr>
<tr>
<td width="250" align="center" style="font-size:12px;">
<p style="text-decoration:underline;"><br /><br /><strong>Missatges resposta</strong></p>
<?php

echo "<img src=\"http://chart.apis.google.com/chart?chs=250x150&amp;chd=t:".($mensajesRespuestaPorEstudiante[$estudiante]/array_sum($mensajesRespuestaPorEstudiante)).",".(1-($mensajesRespuestaPorEstudiante[$estudiante]/array_sum($mensajesRespuestaPorEstudiante)))."&amp;cht=p3&amp;chl=".$mensajesRespuestaPorEstudiante[$estudiante]."|".(array_sum($mensajesRespuestaPorEstudiante)-$mensajesRespuestaPorEstudiante[$estudiante])."&amp;&chco=FF0000,FFFF00CC&chdl=Missatges de l'estudiant|Resta&chdlp=b&amp;chf=bg,s,EEEEEE\" width=\"250\" height=\"150\">";
echo "<p style=\"font-style:italic;\">En percentatge: ".$mensajesRespuestaPorEstudiante[$estudiante]."/".array_sum($mensajesRespuestaPorEstudiante)."=".round(($mensajesRespuestaPorEstudiante[$estudiante]/array_sum($mensajesRespuestaPorEstudiante))*100,2)."%</p>";
?>
</td>
</tr>
<tr>
<td width="250" align="center" style="font-size:12px;">
<p style="text-decoration:underline;"><br /><br /><strong>Paraules escrites</strong></p>
<?php

echo "<img src=\"http://chart.apis.google.com/chart?chs=250x150&amp;chd=t:".($palabrasPorEstudiante[$estudiante]/array_sum($palabrasPorEstudiante)).",".(1-($palabrasPorEstudiante[$estudiante]/array_sum($palabrasPorEstudiante)))."&amp;cht=p3&amp;chl=".$palabrasPorEstudiante[$estudiante]."|".(array_sum($palabrasPorEstudiante)-$palabrasPorEstudiante[$estudiante])."&amp;&chco=FF0000,0080C0CC&chdl=Paraules de l'estudiant|Resta&chdlp=b&amp;chf=bg,s,EEEEEE\" width=\"250\" height=\"150\">";
echo "<p style=\"font-style:italic;\">En percentatge: ".$palabrasPorEstudiante[$estudiante]."/".array_sum($palabrasPorEstudiante)."=".round(($palabrasPorEstudiante[$estudiante]/array_sum($palabrasPorEstudiante))*100,2)."%</p>";
?>
</td>
</tr>
<tr>
<td width="250" align="center" style="font-size:12px;" valign="top">
<p style="text-decoration:underline;"><br /><br /><strong title="És un valor percentual que mostra el nombre de respostes que reben els missatges d’un determinat usuari en base al nombre de missatges resposta totals dins la conversa. Aquesta mètrica mesura l’impacte dels missatges que publica l’estudiant en funció de les respostes que rep.">Popularitat</strong></p>
<?php

				echo "<img src=\"http://chart.apis.google.com/chart?chs=250x100&cht=gom&chd=t:".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."&chl=".round(100*$popularidadPorEstudiante[$estudiante]/$numRespuestas,2)."%&chf=bg,s,ffffff00&chco=e47fcf,932f9d\" width=\"250\" height=\"100\"><br />";
?>
</td>
</tr>
<tr>
<td align="center" style="font-size:12px;" height="250" valign="bottom">

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Dia', 'Missatges',{ role: 'annotation' }],
<?php
$arrayMensajesPorFecha=array();
$arrayFechas=$fechasMensajePorEstudiante[$estudiante];

foreach ($arrayFechas as $fecha) {
	$hora=date("G",$fecha)*3600;
	$minutos=date("i",$fecha)*60;
	$segundos=date("s",$fecha);
	$fecha=$fecha-$hora-$minutos-$segundos; // Quitamos las horas de la fecha
	if ($arrayMensajesPorFecha[$fecha]==0) {
		$arrayMensajesPorFecha[$fecha]=1;
	} else {
		$arrayMensajesPorFecha[$fecha]++;
	}
	
}

ksort($arrayMensajesPorFecha);
$fecha1=$_GET["fecha1"];
$fecha2=$_GET["fecha2"];
$fechaActual=$fecha1;
while ($fechaActual<=$fecha2) {
	echo "['".date("d M",$fechaActual)."', ".$arrayMensajesPorFecha[$fechaActual].",'".$arrayMensajesPorFecha[$fechaActual]."']";
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
			chartArea:{left:50,top:10,width:"100%",height:"150"},
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

<p style="text-decoration:underline;"><strong><br/><br/>Distribució dels missatges lliurats per l'estudiant</strong></p>
  
    <div id="contenedorGráfica1" style="width:500px; height:200px; overflow-x:auto; overflow-y:hidden;">
       <div id="chart_div" style="width:<?php echo (50*$dias) ?>px; height:200px;"></div>
    </div>

<p>La durada de la participació de l'estudiant <?php echo $estudiante; ?> es de 

<?php
		$min=min($fechasMensajePorEstudiante[$estudiante])/86400;
		$max=max($fechasMensajePorEstudiante[$estudiante])/86400;
		$periodo=$max-$min;
		if (count($fechasMensajePorEstudiante[$estudiante])==0){
			echo "cap dia";
		} else if (count($fechasMensajePorEstudiante[$estudiante])==1){
			echo "1 dia";
		} else if  (($periodo>0) && ($periodo<1)){
			echo round($periodo*24,1)." hores";
		} else {
			echo round($periodo,1)." dies";

		}
?>
, això representa un 

<?php

	echo round($periodo/$dias*100,2)."%";
	
?>

de la durada total de la conversa analitzada.</p>

</td>

</tr>

</table>

<br/>
<p style="text-decoration:underline; font-size:12px;"><strong>Llista d'arxius adjuntats per l'estudiant:</strong></p>

<?php 

if ($adjuntosPorEstudiante[$estudiante]>0) {
	echo " <ul style=\"font-size:12px;\">";
	$contador=0;
	while ($contador<sizeof($nombresAdjuntosPorEstudiante[$estudiante])) {
		echo "<li>".$nombresAdjuntosPorEstudiante[$estudiante][$contador]."</li>";
		$contador++;
	}
	echo "</ul>";
} else {
	echo "Cap";
}

?>

</body>
</html>