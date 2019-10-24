<html
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ESTUDIANTS</title>
</head>

<body>
<?php

$fichero=fopen("./estudiantes.txt","w");
$estudiantes=$_POST["estudiantes"];
fputs($fichero,$estudiantes);

?>

<script language="javascript">
window.open("http://www.paucasals.com/diana/index.php","_self")
</script>

</body>
</html>