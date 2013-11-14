<?php
if(isset($_POST["idbl"])){	
	$idBookLibrary = $_POST["idbl"];
	$json = "";
	require("othersQueries.php");
	require("conection.php");
	$consulta = "SELECT 
                    b.id_book AS idbook,
                    b.title,
                    b.description,
                    b.place,
                    b.publication_date AS publicationdate,
                    b.physical_description AS physicaldescription,
                    b.language,
                    b.image AS image,
                    l.id_library AS idlibrary,
                    bhl.id_book_has_library AS idbooklibrary 
                    FROM book AS b 
                    INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                    INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                    WHERE bhl.id_book_has_library = $idBookLibrary;";
	if ($sentencia = mysqli_prepare($link, $consulta)) {

	    /* ejecutar la sentencia */
	    mysqli_stmt_execute($sentencia) or die("Error " . mysqli_error($link));

	    /* vincular las variables de resultados */
	    mysqli_stmt_bind_result($sentencia, $a, $b, $c, $d, $e, $f, $g, $h, $i, $j);
	    /* obtener los valores */
	    $miLibro = array();	    
	    while (mysqli_stmt_fetch($sentencia)) {	
	        $miLibro["authors"] = getAuthors($a);
                $miLibro["topics"] = getTopics($a);
                $miLibro["idBook"] = $a;
	        $miLibro["title"] = $b;
	        $miLibro["description"] = $c;
	        $miLibro["place"] = $d;
	        $miLibro["publicationDate"] = $e;
                $miLibro["physicalDescription"] = $f;
	        $miLibro["language"] = $g;
	        $miLibro["image"] = $h;
	        $miLibro["library"] = getLibrary2($i);
	        $miLibro["idLibroHasLibrary"] = $j;
	    }//JSON_UNESCAPED_UNICODE
	    $json  = json_encode($miLibro);	    
	    /* cerrar la sentencia */
	    mysqli_stmt_close($sentencia);
	}	        
	mysqli_close($link);       
	echo trim($json);
}
?>