<?php
function getLibrary($idLibrary){	
	require("conection.php");
	$miArreglo = array();		
	$consulta = "SELECT  
                formation_center AS library,
                id_department AS iddepartment 
                FROM library 
                WHERE id_library = $idLibrary;";
	if ($sentencia = mysqli_prepare($link, $consulta) or die("Error " . mysqli_error($link))) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia) or die("Error " . mysqli_error($link));

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b) or die("Error " . mysqli_error($link));

	    /* obtener los valores */	    
	    while (mysqli_stmt_fetch($sentencia)) {	        
	        $miArreglo["formationCenter"] = $a;
	        $miArreglo["department"] = getDepartment($b);
	    }	   
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	
	mysqli_close($link);
	return $miArreglo;
}	

function getDepartment($idDepartment){
	require("conection.php");	
	$miArreglo = array();
	$consulta = "SELECT 
			id_department AS iddepartment,
			department_name AS department 
			FROM department 
			WHERE id_department = $idDepartment;";
	if ($sentencia = mysqli_prepare($link, $consulta) or die("Error " . mysqli_error($link))) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia);

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b);

	    /* obtener los valores */	    
	    while (mysqli_stmt_fetch($sentencia)) {	        
	        $miArreglo["idDepartment"] = $a;
	        $miArreglo["departmentName"] = $b;
	    }
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	
	mysqli_close($link);
	return $miArreglo;
}

function getAuthors($idBook){
	require("conection.php");	
	$miArreglo = array();
	$consulta = "SELECT 
                    author_id_author,
                    author_name 
                    FROM book_has_author 
                    INNER JOIN author ON id_author = author_id_author 
                    WHERE book_id_book = $idBook;";
	if ($sentencia = mysqli_prepare($link, $consulta) or die("Error " . mysqli_error($link))) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia);

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b);

	    /* obtener los valores */	    
	    while (mysqli_stmt_fetch($sentencia)) {
                $miAutor = array();
	        $miAutor["idAutor"] = $a;
	        $miAutor["authorName"] = $b;
                array_push($miArreglo, $miAutor);
	    }
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	
	mysqli_close($link);
	return $miArreglo;
}

function getTopics($idBook){
	require("conection.php");	
	$miArreglo = array();
	$consulta = "SELECT 
                    t.id_topic AS idtopic,
                    t.topic_name AS topicname 
                    FROM topic t 
                    INNER JOIN book_has_topic bht ON bht.topic_id_topic = t.id_topic 
                    WHERE bht.book_id_book = $idBook;";
	if ($sentencia = mysqli_prepare($link, $consulta) or die("Error " . mysqli_error($link))) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia);

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b);

	    /* obtener los valores */	    
	    while (mysqli_stmt_fetch($sentencia)) {
                $miTema = array();
	        $miTema["idTopic"] = $a;
	        $miTema["topicName"] = $b;
                array_push($miArreglo, $miTema);
	    }
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	
	mysqli_close($link);
	return $miArreglo;
}

function getLibrary2($idLibrary){
	require("conection.php");	
	$miArreglo = array();
	$consulta = "SELECT  
                    id_library AS idlibrary,
                    formation_center AS formationCenter,
                    adress,
                    dates_loan AS datesloan,
                    id_department AS iddepartment 
                    FROM library 
                    WHERE id_library = $idLibrary;";
	if ($sentencia = mysqli_prepare($link, $consulta) or die("Error " . mysqli_error($link))) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia);

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b, $c, $d, $e);

	    /* obtener los valores */	    
	    while (mysqli_stmt_fetch($sentencia)) {
	        $miArreglo["idLibrary"] = $a;
	        $miArreglo["formationCenter"] = $b;
                $miArreglo["adress"] = $c;
                $miArreglo["datesLoan"] = $d;
                $miArreglo["department"] = getDepartment($e);
	    }
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	
	mysqli_close($link);
	return $miArreglo;
}
?>