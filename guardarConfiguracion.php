<html
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>CONFIGURACIÓ</title>
</head>

<body>
<?php

$fichero=fopen("./conf.txt","w");
$severidad=$_POST["severidad"];
fputs($fichero,$severidad."\n");
$minimo=$_POST["minimo"];
fputs($fichero,$minimo."\n");
$maximo=$_POST["maximo"];
fputs($fichero,$maximo."\n");
$dispersion=$_POST["dispersion"];
fputs($fichero,$dispersion."\n");
$inactividad=$_POST["inactividad"];
fputs($fichero,$inactividad."\n");
$palabrasClave=$_POST["palabrasClave"];
fputs($fichero,$palabrasClave);

?>

<script language="javascript">
window.open("http://www.paucasals.com/diana/index.php","_self")
</script>

</body>
</html>