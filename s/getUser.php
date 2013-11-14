<?php
if(isset($_POST["u"]) && isset($_POST["p"])){	
	$json = "";
	$user = $_POST["u"];
	$password = $_POST["p"];	
	$lu = strlen($user);
	$lp = strlen($user);
	if($lu < 22 &&  $lp < 22){
		$user = strtoupper(hash("SHA512",$user));
		$password = strtoupper(hash("SHA512",$password));
	}
	require_once("conection.php");
	$consulta = "SELECT iduser,
		user_name AS user,
		user_password AS password 
		FROM user 
		WHERE user.user_name = '$user' AND 
		user.user_password = '$password' ;";
	if ($sentencia = mysqli_prepare($link, $consulta)) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia);

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b, $c);

	    /* obtener los valores */
	    $miArreglo = array();
	    while (mysqli_stmt_fetch($sentencia)) {	        
	        $miArreglo["idUser"] = $a;
	        $miArreglo["user"] = $b;
	        $miArreglo["password"] = $c;
	    }
	    $json  = json_encode($miArreglo);	    
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);	 
	     
	}		
	mysqli_close($link);
	echo trim($json);  
}
?>