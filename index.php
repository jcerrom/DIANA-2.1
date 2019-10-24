<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DIANA 2.1 (DIALOGUE ANALYSIS) - Learning Analytics</title>
<link rel="stylesheet" href="estilosInicio.css" />
</head>

<body>

<div id="contenedorPrincipal">

<div id="cabecera"><strong>- DIANA 2.1 - <br />Eina per a l'anàlisi de la interacció comunicativa (GENER 2019)<br />Adaptat a les bústies de missatges del campus de la UOC</strong>
</div>

<br />

<div align="center" id="titulo">

<p>&copy; 2015-2019 Cerro, J.P.; Guitert, M.; Romeu, T. Tots els drets reservats pels autors.<br />
Resolució mínima recomanada: 1.366 x 768 píxels.</p>
</div>

<table id="rejillaPantalla" cols="2" cellpadding="5px" align="center">
<tr>
<td>

<form action="guardarConfiguracion.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">

<div class="solapa" style="width:600px;">
<strong>CONFIGURACIÓ DE L'EINA</strong> - <input type="image" src="img/disco.png" width="20px" style="vertical-align:middle;"> Desar
</div>

<div class="cuadroInferior" style="width:600px;">
<table id="tablaConfiguracion" cols="2" cellpadding="5px">
<tr>
<td><strong>Llista de paraules clau (1 per línia)<br/>o camp semàntic de la conversa:</strong>
<br/>
<textarea name="palabrasClave" cols="30" rows="9" style="resize:none"  />
<?php
$ficheroConfiguracion=fopen("conf.txt","r"); 
$severidad=fgets($ficheroConfiguracion);
$minimo=fgets($ficheroConfiguracion);
$maximo=fgets($ficheroConfiguracion);
$dispersion=fgets($ficheroConfiguracion);
$inactividad=fgets($ficheroConfiguracion);
$linea=fgets($ficheroConfiguracion);
while (!feof($ficheroConfiguracion)) {
	echo $linea;
	$linea=fgets($ficheroConfiguracion);
}

while (!feof($ficheroConfiguracion)) {
	$linea=fgets($ficheroConfiguracion);
	echo $linea;
}

fclose($ficheroConfiguracion);
?>
</textarea>
</td>

<td>
- Grau de severitat al control semàntic: <input name="severidad" type="text" maxlength="3" size="3" <?php echo "value='$severidad'"; ?> > %.<br/><br/>
- Mínim de participació: <input name="minimo" type="text" maxlength="2" size="2" <?php echo "value='$minimo'"; ?> > missatges.<br/><br/>
- Màxim de participació: <input name="maximo" type="text" maxlength="2" size="2" <?php echo "value='$maximo'"; ?> > missatges.<br/><br/>
- Grau màxim de dispersió de la conversa: <input name="dispersion" type="text" maxlength="3" size="3" <?php echo "value='$dispersion'"; ?> > %.
<br/><br/>
- Temps màxim d'inactivitat: <input name="inactividad" type="text" maxlength="3" size="3" <?php echo "value='$inactividad'"; ?> > dies.
</td>

</tr>
</table>
</div>
</form>

</td>

<td style="vertical-align:top;">

<form action="guardarEstudiantes.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">

<div class="solapa" style="width:300px;">
<strong>LLISTAT D'ESTUDIANTS</strong> - <input type="image" src="img/disco.png" width="20px" style="vertical-align:middle;"> Desar</div>

<div class="cuadroInferior" style="width:300px; height:181px; alignment-adjust:central">
<textarea name="estudiantes" cols="39" rows="11" style="resize:none; font-size:12px;"  />
<?php
$ficheroEstudiantes=fopen("estudiantes.txt","r"); 
$linea=fgets($ficheroEstudiantes);
while (!feof($ficheroEstudiantes)) {
	echo $linea;
	$linea=fgets($ficheroEstudiantes);
}
fclose($ficheroEstudiantes);
?>
</textarea>
</div>
</form>


</td>
</tr>

<tr>
<td>

<div class="solapa" style="width:600px;">
<strong>SELECCIÓ DE LA CONVERSA A ANALITZAR</strong></div>

<div class="cuadroInferior" style="width:600px; font-size:12px;">

<br />
    <form action="upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
         <strong><em>CÀRREGA DE CONVERSES:</em></strong><br/><blockquote>
         1.- Selecciona el fitxer comprimit amb els missatges (Màx: <?php echo ini_get('post_max_size')."b"; ?>)<br />
        <blockquote><input type="file" name="userfile[]" /></blockquote>
        2.- i prem el botó 
        <input type="submit" value="Carregar al servidor" /></blockquote>
    </form>

<strong><em>CONVERSES CARREGADES:</em> (Selecciona una conversa per ser analitzada)</strong><br/><br/>

<form action="descomprimir.php" method="post" enctype="application/x-www-form-urlencoded" name="debates" target="precarga">

<table style="margin:0px 0px 10px 20px;">

<?php 

$directorio = opendir("./debates/"); // Cargamos el directorio

// VAMOS A INSPECCIONAR LOS DIRECTORIOS CON LOS DEBATES CARGADOS

while ($archivos = readdir($directorio)) //obtenemos un archivo y luego otro sucesivamente
{
	if (!is_dir($archivos)  && (strtolower(end(explode(".",$archivos)))=="zip")) //Verificamos si es o no un directorio y un ZIP
    {
		echo "<tr><td style=\"width:auto; font-size:12px;\"> <input style=\"vertical-align:middle; margin:4px 4px 4px 4px;\" type=\"radio\" name=\"fichero\" value=\"".$archivos."\" required> ".$archivos."</td><td style=\"width:100px;\">&nbsp;<a href=\"eliminar_fichero.php?fichero=".$archivos."\"><img src=\"img/papelera.png\" width=\"20\"></a></td></tr>";
	}
}
?>

</table>

</td>

<td style="vertical-align:top;">


<div class="solapa" style="width:300px;">
<strong>PARÀMETRES DE L'ANÀLISI</strong></div>

<div class="cuadroInferior" style="width:300px; height:auto;  font-size:12px;">
<br/>Introdueix les dates per a l'anàlisi (1 per línia) i selecciona el model metodològic a utilitzar:<br/><br/>
<table width="300" cols="2" style="font-size:12px;">
<tr>
<td align="center" width="45%" style="vertical-align:top;"><strong>DATES</strong><br/></td>
<td align="center" width="55%" style="vertical-align:top;"><strong>MODEL</strong><br/></td>
</tr>
<tr>
<td align="center" width="45%" style="vertical-align:top;"><textarea name="fechas" cols="11" rows="5" style="resize:none; font-size:12px;"  required placeholder="dd/mm/aaaa"/></textarea></td>
<td align="left" width="55%" style="vertical-align:top;">
<input type="radio" name="modelo" value="Descriptiu" required /> Descriptiu (2 dates)<br/>
<input type="radio" name="modelo" value="Progressiu" required /> Progressiu (3 dates)<br/>
<input type="radio" name="modelo" value="Comparatiu" required /> Comparatiu (4 dates)<br/>
</td>
</tr>
<tr>
<td align="center" colspan="2" height="150"><br/><input type="image" src="img/play.png" width="100px" onClick="document.getElementById('contenedorPrecarga').style.visibility='visible';"><br/><strong>ANALITZAR</strong></td>
</tr>
</table>

</div>
</form>


</td>


</tr>
</table>


</div>

<div id="contenedorPrecarga" style="visibility:hidden;">
<iframe name="precarga" width="500" height="275" style="background-color:#FFF;" marginwidth="0px" marginheight="0px" frameborder="0" scrolling="no">

</iframe>

</div>


</body>
</html>