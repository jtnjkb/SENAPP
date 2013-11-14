<?php
if (isset($_GET["idbl"]) && isset($_GET["idu"]) && isset($_GET["dt"])) {
    $idBookLibrary = $_GET["idbl"];
    $idUser = $_GET["idu"];
    $date = $_GET["dt"];
    $json = "";
    require("conection.php");
    // Crear la reserva
    $consulta1 = "INSERT INTO reservation 
                     (days_paid, id_user, reservation_date) 
                      VALUES('3', $idUser, $date);";
    $sentencia1 = mysqli_prepare($link, $consulta1);
    mysqli_stmt_execute($sentencia1) or die("Error " . mysqli_error($link));
    mysqli_stmt_close($sentencia1);

    $consulta2 = "SELECT MAX(id_reservation) 
                    AS idreservation 
                    FROM reservation;";
    if ($sentencia2 = mysqli_prepare($link, $consulta2)) {

        /* ejecutar la sentencia */
        mysqli_stmt_execute($sentencia2) or die("Error " . mysqli_error($link));

        /* vincular las variables de resultados */
        mysqli_stmt_bind_result($sentencia2, $a);
        /* obtener los valores */
        $miLibro = array();
        $idReservation = 0;
        while (mysqli_stmt_fetch($sentencia2)) {
            $idReservation = $a;
        }
        mysqli_stmt_close($sentencia2);        
        // Realizar la reserva
        $consulta3 = "UPDATE book_has_library 
                     SET reserved = 1 
                     WHERE id_book_has_library = $idBookLibrary;";
        $sentencia3 = mysqli_prepare($link, $consulta3);
        mysqli_stmt_execute($sentencia3) or die("Error " . mysqli_error($link));
        mysqli_stmt_close($sentencia3);
        // Asociar la reserva al libro de esa biblioteca   
        $consulta4 = "INSERT INTO reservation_has_booklibrary 
                      (id_reservation, id_book_has_library) 
                      VALUES($idReservation, $idBookLibrary);";
        $sentencia4 = mysqli_prepare($link, $consulta4);
        mysqli_stmt_execute($sentencia4) or die("Error " . mysqli_error($link));
        mysqli_stmt_close($sentencia4);
        mysqli_close($link);
    }
}
?>