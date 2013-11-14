<?php

if (isset($_POST["word"]) && isset($_POST["filter"])) {
    $word = $_POST["word"];
    $filter = $_POST["filter"];
    $json = "";
    $consulta = "";
    require("othersQueries.php");
    require("conection.php");

    if (isset($_POST["lat"]) && isset($_POST["lon"])) {
        $latitude = $_POST["lat"];
        $longitude = $_POST["lon"];
        $consulta = getConsultaConGeolocalizacion($word, $filter, $latitude, $longitude);
    } else {
        $consulta = getConsultaSinGeolocalizacion($word, $filter);
    }
    if ($sentencia = mysqli_prepare($link, $consulta)) {

        /* ejecutar la sentencia */
        mysqli_stmt_execute($sentencia) or die("Error " . mysqli_error($link));

        /* vincular las variables de resultados */
        mysqli_stmt_bind_result($sentencia, $a, $b, $c, $d, $e);
        /* obtener los valores */
        $misLibros = array();
        while (mysqli_stmt_fetch($sentencia)) {
            $miLibro = array();
            $miLibro["idBook"] = $a;
            $miLibro["title"] = $b;
            $miLibro["image"] = $c;
            $miLibro["library"] = getLibrary($d);
            $miLibro["idLibroHasLibrary"] = $e;
            array_push($misLibros, $miLibro);
        }//JSON_UNESCAPED_UNICODE
        $json = json_encode($misLibros);
        /* cerrar la sentencia */
        mysqli_stmt_close($sentencia);
    }
    mysqli_close($link);
    echo trim($json);
}

function getConsultaSinGeolocalizacion($word, $filter) {
    $consulta = "";
    switch ($filter) {
        case 1: //Consulta por autor     
            $consulta = "SELECT 
				b.id_book AS idbook,
				b.title,
				b.image AS image,
				l.id_library AS idlibrary,
				bhl.id_book_has_library AS idbooklibrary 
				FROM book AS b 
				INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
				INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
				INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
				INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
				WHERE a.author_name LIKE '%$word%' 
				AND bhl.reserved = 0 AND b.type = 1 
		 		GROUP BY l.id_library;";
            break;
        case 2: // Consulta por título
            $consulta = "SELECT 
				b.id_book AS idbook,
				b.title,
				b.image AS image,
				l.id_library AS idlibrary,
				bhl.id_book_has_library AS idbooklibrary 
				FROM book AS b 
				INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
				INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
				INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
				INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
				WHERE b.title LIKE '%$word%'  
				AND bhl.reserved = 0 AND b.type = 1 
		 		GROUP BY l.id_library;";
            break;
        case 3: // Conculta por tema
            $consulta = "SELECT 
                        b.id_book AS idbook,
                        b.title,
                        b.image AS image,
                        l.id_library AS idlibrary,
                        bhl.id_book_has_library AS idbooklibrary 
                        FROM book AS b 
                        INNER JOIN book_has_topic AS bht ON bht.book_id_book = b.id_book 
                        INNER JOIN topic AS t ON t.id_topic = bht.topic_id_topic 
                        INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                        INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                        WHERE t.topic_name LIKE '%$word%' 
                        AND bhl.reserved = 0 AND b.type = 1 
                        GROUP BY l . id_library";
            break;
        case 4: // Consulta general
            $consulta = "SELECT 
                        b.id_book AS idbook,
                        b.title,
                        b.image AS image,
                        l.id_library AS idlibrary,
                        bhl.id_book_has_library AS idbooklibrary 
                        FROM book AS b 
                        INNER JOIN book_has_topic AS bht ON bht.book_id_book = b.id_book 
                        INNER JOIN topic AS t ON t.id_topic = bht.topic_id_topic 
                        INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
                        INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
                        INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                        INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                        WHERE b.title LIKE '%$word%' OR a.author_name LIKE '%$word%' 
                                        OR t.topic_name LIKE '%$word%' 
                        AND bhl.reserved = 0 AND b.type = 1 
                        GROUP BY l.id_library;";
            break;
    }
    return $consulta;
}

function getConsultaConGeolocalizacion($word, $filter, $latitude, $longitude) {
    $consulta = "";
    switch ($filter) {
        case 1: //Consulta por autor     
            $consulta = "SELECT 
                        b.id_book AS idbook,
                        b.title,
                        b.image AS image,
                        l.id_library AS idlibrary,
                        bhl.id_book_has_library AS idbooklibrary 
                        FROM book AS b 
                        INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
                        INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
                        INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                        INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                        WHERE a.author_name LIKE '%$word%' 
                        AND bhl.reserved = 0 AND b.type = 1 
                        GROUP BY l.id_library 
                        ORDER BY (ROUND((ACOS(SIN(RADIANS($latitude)) * SIN(RADIANS(l.latitude)) + 
                        COS(RADIANS($latitude)) * COS(RADIANS(l.latitude)) * 
                        COS(RADIANS($longitude) - RADIANS(l.longitude))) * 6378))+1);";
            break;
        case 2: // Consulta por título
            $consulta = "SELECT 
				b.id_book AS idbook,
				b.title,
				b.image AS image,
				l.id_library AS idlibrary,
				bhl.id_book_has_library AS idbooklibrary 
				FROM book AS b 
				INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
				INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
				INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
				INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
				WHERE b.title LIKE '%$word%'  
				AND bhl.reserved = 0 AND b.type = 1 
		 		GROUP BY l.id_library 
                                ORDER BY (ROUND((ACOS(SIN(RADIANS($latitude)) * SIN(RADIANS(l.latitude)) + 
                                COS(RADIANS($latitude)) * COS(RADIANS(l.latitude)) * 
                                COS(RADIANS($longitude) - RADIANS(l.longitude))) * 6378))+1);";
            break;
        case 3: // Conculta por tema
            $consulta = "SELECT 
                        b.id_book AS idbook,
                        b.title,
                        b.image AS image,
                        l.id_library AS idlibrary,
                        bhl.id_book_has_library AS idbooklibrary 
                        FROM book AS b 
                        INNER JOIN book_has_topic AS bht ON bht.book_id_book = b.id_book 
                        INNER JOIN topic AS t ON t.id_topic = bht.topic_id_topic 
                        INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                        INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                        WHERE t.topic_name LIKE '%$word%' 
                        AND bhl.reserved = 0 AND b.type = 1 
                        GROUP BY l . id_library 
                        ORDER BY (ROUND((ACOS(SIN(RADIANS($latitude)) * SIN(RADIANS(l.latitude)) + 
                        COS(RADIANS($latitude)) * COS(RADIANS(l.latitude)) * 
                        COS(RADIANS($longitude) - RADIANS(l.longitude))) * 6378))+1);";
            break;
        case 4: // Consulta general
            $consulta = "SELECT 
                        b.id_book AS idbook,
                        b.title,
                        b.image AS image,
                        l.id_library AS idlibrary,
                        bhl.id_book_has_library AS idbooklibrary 
                        FROM book AS b 
                        INNER JOIN book_has_topic AS bht ON bht.book_id_book = b.id_book 
                        INNER JOIN topic AS t ON t.id_topic = bht.topic_id_topic 
                        INNER JOIN book_has_author AS bha ON b.id_book = bha.book_id_book 
                        INNER JOIN author AS a ON  bha.author_id_author = a.id_author 
                        INNER JOIN book_has_library AS bhl ON bhl.book_id_book = b.id_book 
                        INNER JOIN library AS l ON bhl.library_id_library = l.id_library 
                        WHERE b.title LIKE '%$word%' OR a.author_name LIKE '%$word%' 
                                        OR t.topic_name LIKE '%$word%' 
                        AND bhl.reserved = 0 AND b.type = 1 
                        GROUP BY l.id_library 
                        ORDER BY (ROUND((ACOS(SIN(RADIANS($latitude)) * SIN(RADIANS(l.latitude)) + 
                        COS(RADIANS($latitude)) * COS(RADIANS(l.latitude)) * 
                        COS(RADIANS($longitude) - RADIANS(l.longitude))) * 6378))+1);";
            break;
    }
    return $consulta;
}
?>