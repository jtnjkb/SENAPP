<?php
if(isset($_POST["word"])){	
	$word = $_POST["word"];
	$json = "";
	require("othersQueries.php");
	require("conection.php");
	$consulta = "SELECT 
                    b.id_book AS idbook,
                    b.title,
                    b.image AS image,
                    b.route 
                    FROM book AS b 
                    INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
                    INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
                    WHERE b.title LIKE '%$word%' OR a.author_name LIKE '%$word%' 
                    AND b.type=2";
	if ($sentencia = mysqli_prepare($link, $consulta)) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia) or die("Error " . mysqli_error($link));

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b, $c, $d);
	    /* obtener los valores */
	    $misLibros = array();	    
	    while (mysqli_stmt_fetch($sentencia)) {	 
	    	$miLibro = array(); 	    	      
	        $miLibro["idBook"] = $a;
                $miLibro["authors"] = getAuthors($a);
                $miLibro["topics"] = getTopics($a);
	        $miLibro["title"] = $b;
	        $miLibro["image"] = $c;
	        $miLibro["route"] = $d;
	        array_push($misLibros, $miLibro);
	    }//JSON_UNESCAPED_UNICODE
	    $json  = json_encode($misLibros);	    
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	        
	mysqli_close($link);       
	echo trim($json);
}
?>