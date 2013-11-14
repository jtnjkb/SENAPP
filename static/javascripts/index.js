var Index = {//"http://appphp.esy.es/"
    SERVER: "http://localhost:60/projects/appPhp/s/",
    filter: 4,
    init: function() {
        Index.setListeners();
        // Existe el usuario del aplicativo?        
        var user = localStorage.getItem("myuser");
        if (user === undefined || user === "" || user === null)
        {
            Lungo.Router.section("splash");
        }
    },
    setListeners: function() { // Aquì se ponen los eventos de los controles                        
        $("input#txtWord").on("focus", function() {
            Index.setVisibleFilter(1)
        });
        $("button#btnSearch").on("click", Index.getBooks);
        $("button#btnSearch2").on("click", Index.getPdfs);
        $("button#btnAuthor").on("click", function() {
            Index.filter = 1;
        });
        $("button#btnTitle").on("click", function() {
            Index.filter = 2;
        });
        $("button#btnTopic").on("click", function() {
            Index.filter = 3;
        });
        $("button#btnAll").on("click", function() {
            Index.filter = 4;
        });
        // Capturo evento clic del botón login        
        $("button#btnLogin").on("click", Index.getUser);
        $("button#goToMain").on("click", function() {
            Lungo.Router.section("searcher");
        });
        $("a#goToMenu").on("click", Index.refreshReserves);
        $("section#detail  header nav a#goToMain").on("click", function() {
            Lungo.Router.section("searcher");
        });
        $("section#reserves").on("load", Index.paintReserves);
        $('#input').on("keyup", Index.searchKeyUp);     
    }, // Método para buscar en una lista de elemento cuando se excriba una letra
    searchKeyUp: function() {
        // get the value from text field
        var input = $(this).val();
        // check wheather the matching element exists
        // by default every list element will be shown
        $(".filter li").show();
        // Non related element will be hidden after input
        $(".filter li").not("[label*=" + input + "]").hide();

        // For Search Variable, total number of lists and number of matched elements
        var total = $(".filter li").length;
        var matched = $(".filter li[label*=" + input + "]").length;
        if (input.length > 0) {
            $('.input').show();
            $('.input').html('Resultado para "' + input + '" (' + matched + ' Coincide de ' + total + ' )');
        } else {
            $('.input').hide();
            $(".filter li").show();
        }
    },
    getBooks: function() {
        // Sin geolocalización
        var latitude = null;
        var longitude = null;
        // Desde bogotá
//        var latitude = 4.63952;        
//         var longitude = -74.0656;
        //Desde el valle
//         var latitude = 3.45064;        
//         var longitude = -76.5488;
        // Obtiene la locaclización
        navigator.geolocation.getCurrentPosition(
                // En caso de que sea correcto
                        function(position) {
                            latitude = position.coords.latitude;
                            longitude = position.coords.longitude;

                        }, // En caso de error
                        function() {
                            console.log("No se ha podido ubicar a tu dispositivo");
                        });
                var value = $("input#txtWord").val();
                if (latitude === null && longitude === null) {
                    $.ajax({
                        "url": Index.SERVER + "getBooks.php",
                        "type": "POST",
                        "data": {"word": value, "filter": Index.filter},
                        "success": Index.paintBooks,
                        "error": function(error) {
                            console.log(error);
                        }
                    });
                } else {
                    $.ajax({
                        "url": Index.SERVER + "getBooks.php",
                        "type": "POST",
                        "data": {"word": value, "filter": Index.filter,
                            "lat": latitude, "lon": longitude},
                        "success": Index.paintBooks,
                        "error": function(error) {
                            console.log(error);
                        }
                    });
                }
            },
    getPdfs: function() {
        var value = $("input#txtWord2").val();
        $.ajax({
            "url": Index.SERVER + "getPdfs.php",
            "type": "POST",
            "data": {"word": value},
            "success": Index.paintPdfs,
            "error": function(error) {
                console.log(error);
            }
        });
    },
    paintBooks: function(data) {
        // Desactivo el filtro
        Index.setVisibleFilter(0);
        var json = JSON.parse(data);
        $("ul#booksList").html('');
        $(json).each(function(i, item) {
            $("ul#booksList").append(
                    $("<li>").append(
                    $("<img>").attr("src", item.image)
                    .css({"width": "100px"})
                    )
                    .append(
                            $("<p>")
                            .css({"float": "right", "width": "40%",
                                "text-align": "left"})
                            .append($("<span>") // Preguntar porque?
                                    .addClass("block text bold")
                                    .attr({"data-action" : "loading"})
                                    .on("click", function() {
                                        Index.getBook(item.idLibroHasLibrary);
                                    })
                                    .text(item.title)
                                    .attr({"data-view-section": "detail", "href": "#"})
                                    )
                            .append($("<div>")
                                    .append($("<span>").text(item.library.formationCenter))
                                    .append($("<br/>"))
                                    .append($("<span>").text(item.library.department.departmentName))
                                    .append($("<br/>"))
                                    .append($("<br/>"))
                                    .append($("<button>").text("Reservar")
                                            .on("click", function() {
                                                Lungo.Notification.confirm({
                                                    icon: 'book',
                                                    title: 'Confirmación',
                                                    description: "¿Está segur@ de \n\
                                                        que desea reservar el libro " + item.title + "?",
                                                    accept: {
                                                        icon: 'checkmark',
                                                        label: 'Aceptar',
                                                        callback: function() {
                                                            Index.toReserveBook2(item.idLibroHasLibrary);
                                                        }
                                                    },
                                                    cancel: {
                                                        icon: 'close',
                                                        label: 'Calcelar',
                                                        callback: function() {
                                                            //alert("No!");
                                                        }
                                                    }
                                                });
                                            })
                                            )
                                    )
                            )
                    .append(
                            $("<p>").append($("<span>")
                            .css({"clear": "both"})
                            )
                            )
                    );
        });
    },
    paintPdfs: function(data) {
        var json = JSON.parse(data);
        $("ul#pdfsList").html('');
        $(json).each(function(i, item) {
            $("ul#pdfsList").append(
                    $("<li>").append(
                    $("<img>").attr("src", item.image)
                    .css({"width": "100px"})
                    )
                    .append(
                            $("<p>")
                            .css({"float": "right", "width": "40%",
                                "text-align": "left"})
                            .append($("<a>") // Preguntar porque?
                                    .on("click", function() {
                                        Index.getBook(item.idLibroHasLibrary);
                                    })
                                    .text(item.title)
                                    .attr({"data-view-section": "detalleEbook"})
                                    )
                            .append($("<section>")
                                    .append($("<button>").text("Reservar")
                                            .on("click", function() {
                                                Lungo.Notification.confirm({
                                                    icon: 'book',
                                                    title: 'Confirmación',
                                                    description: "¿Está segur@ de \n\
                                                        que desea reservar el libro " + item.title + "?",
                                                    accept: {
                                                        icon: 'checkmark',
                                                        label: 'Si',
                                                        callback: function() {
                                                            alert("Yes!");
                                                        }
                                                    },
                                                    cancel: {
                                                        icon: 'close',
                                                        label: 'No',
                                                        callback: function() {
                                                            alert("No!");
                                                        }
                                                    }
                                                });
                                            })
                                            )
                                    )
                            )
                    .append(
                            $("<p>").append($("<span>")
                            .css({"clear": "both"})
                            )
                            )
                    );
        });
    },
    getBook: function(idBookLibrary) {
        $.ajax({
            "url": Index.SERVER + "getBook.php",
            "type": "POST",
            "data": {"idbl": idBookLibrary},
            "success": function(data) {
                Index.getBookInfo(data);
            },
            "error": function(error) {
                console.log("" + error);
            }
        });
    },
    getUser: function() {
        var user = $("input#txtUser").val();
        var password = $("input#txtPassword").val();

        $.ajax({
            "url": Index.SERVER + "getUser.php",
            "type": "POST",
            "data": {"u": user,
                "p": password},
            "success": function(data) {
                localStorage.setItem("myuser", data);
                 Lungo.Router.section("home");

            },
            "error": function(error) {
                console.log("" + error);
            }
        });
    },
    getBookInfo: function(myBook) {
        var json = JSON.parse(myBook);
        // Ubicar el detalle
        var detail = $("section#detail article#detail-article");
        // Limpio el anterior contenido
        detail.html("");
        // Creo los elementos de la lista
        var unorderedList = $("<ul>");
        $(json).each(function(i, item) {
            // Formateo los autores
            var autors = "";
            $(item.authors).each(function(i, item) {
                autors += item.authorName + ",";
            });
            // Formateo los temas
            var topics = "";
            $(item.topics).each(function(i, item) {
                topics += item.topicName + ",";
            });
            //.append($("<p>").text("Fecha de reservacion: "))
            // Crear el li
            /*
            unorderedList.append($("<li>").css({"text-align": "center"})
                    .append($("<p>")
                            .append($("<b>").text("Centro de formacion: "))
                            .append(item.library.formationCenter)
                            )
                    .append($("<p>").text("Direccion de la biblioteca: " + item.library.adress))
                    .append($("<p>").text("Titulo: " + item.title))
                    .append($("<p>").text("Autores: " + autors))
                    .append($("<p>").text("Temas: " + topics))
                    .append($("<p>").text("Descripcion: " + item.description))
                    .append($("<p>").text("Lugar editorial: " + item.place))
                    .append($("<p>").text("Fecha de publicación: " + item.publicationDate))
                    .append($("<p>").text("Descripción física: " + item.physicalDescription))
                    .append($("<p>").text("Idioma: " + item.language))
                    );*/
            unorderedList
                    .append($("<li>").addClass("feature")
                        .append($("<div>").addClass("on-right")
                            .text("Centro de formación")
                        )
                        .append($("<strong>").addClass("text bold")
                            .text(item.library.formationCenter)
                        )
                        .append($("<small>").text(item.library.adress))
                    )
                    .append($("<li>").addClass("feature")
                        .append($("<div>").addClass("on-right")
                            .text("Título")
                        )
                        .append($("<div>").addClass("text small")
                            .text(item.title)
                        )                    
                    )
                    .append($("<li>").addClass("feature")
                        .append($("<div>").addClass("on-right")
                            .text("Autores")
                        )
                        .append($("<small>")
                            .text(autors)
                        ) 
                    )
                    .append($("<li>").addClass("feature")
                        .append($("<div>").addClass("on-right")
                            .text("Temas")
                        )
                        .append($("<div>").addClass("text small")
                            .text(topics)
                        ) 
                    )
                    .append($("<li>").addClass("feature")
                        .append($("<div>").addClass("on-right")
                            .text("Descripción")
                        )
                        .append($("<div>").addClass("text small")
                            .text(item.description)
                        ) 
                    )
                    .append($("<li>").addClass("feature")
                        .append($("<p>").text("Lugar editorial: " + item.place))
                        .append($("<p>").text("Fecha de publicación: " + item.publicationDate))
                        .append($("<p>").text("Descripción física: " + item.physicalDescription))
                        .append($("<p>").text("Idioma: " + item.language))
                    );
        });
        // Agrego la lista llenada
        detail.append(unorderedList);
        // Creo el botón de reseva
        var button = $("<button>").addClass("anchor margin-bottom")
                .text("Reservar")
                .on("click", function() {
                    Lungo.Notification.confirm({
                        icon: 'book',
                        title: 'Confirmación',
                        description: "¿Está segur@ de \n\
                                que desea reservar el libro " + json.title + "?",
                        accept: {
                            icon: 'checkmark',
                            label: 'Si',
                            callback: Index.toReserveBook
                        },
                        cancel: {
                            icon: 'close',
                            label: 'No',
                            callback: function() {
                                alert("No!");
                            }
                        }
                    });
                });
        // Agrego el botón
        detail.append(button);
    },
    toReserveBook: function() {
        var rBooks = localStorage.getItem("reservedBooks");
        if (rBooks === null || rBooks === undefined || rBooks === "") {
            localStorage.setItem("reservedBooks", JSON.stringify([]));
        }
        var rBooks = JSON.parse(localStorage.getItem("reservedBooks"));
        var selectedBook = JSON.parse(localStorage.getItem("selectedbook"));
        var myuser = JSON.parse(localStorage.getItem("myuser"));
        var date = (new Date()).getTime();
        rBooks.push(selectedBook);
        localStorage.setItem("reservedBooks", JSON.stringify(rBooks));
        $.ajax({
            "url": Index.SERVER + "toReserve.php",
            "type": "POST",
            "data": {"idbl": selectedBook.idLibroHasLibrary,
                "idu": myuser.idUser,
                "dt": date},
            "success": function() {
                Lungo.Notification.success('Reserva exitosa',
                        'Encontrará sus reservas en el menú principal', 'ok', 5);
                Lungo.Router.section("searcher");
            },
            "error": function(error) {
                console.log("" + error);
            }
        });
    },
    toReserveBook2: function(idBookLibrary) {
        // Solicitud ajax para obtener los datos del libro
        $.ajax({
            "url": Index.SERVER + "getBook.php",
            "type": "POST",
            "data": {"idbl": idBookLibrary},
            "success": function(data) {
                var rBooks = localStorage.getItem("reservedBooks");
                if (rBooks === null || rBooks === undefined || rBooks === "") {
                    localStorage.setItem("reservedBooks", JSON.stringify([]));
                }
                var rBooks = JSON.parse(localStorage.getItem("reservedBooks"));
                var selectedBook = JSON.parse(data);
                var myuser = JSON.parse(localStorage.getItem("myuser"));
                var date = (new Date()).getTime();
                rBooks.push(selectedBook);
                localStorage.setItem("reservedBooks", JSON.stringify(rBooks));
                $.ajax({
                    "url": Index.SERVER + "toReserve.php",
                    "type": "POST",
                    "data": {"idbl": selectedBook.idLibroHasLibrary,
                        "idu": myuser.idUser,
                        "dt": date
                    },
                    "success": function() {
                        Lungo.Notification.success('Reserva exitosa',
                                'Encontrará sus reservas en el menú principal', 'ok', 5);
                        Lungo.Router.section("searcher");

                    },
                    "error": function(error) {
                        console.log("" + error);
                    }
                });
            },
            "error": function(error) {
                console.log("" + error);
            }
        });

    },
    paintReserves: function() {
        // Obtener las reservas
        var reserves = JSON.parse(localStorage.getItem("reservedBooks"));
        $("ul#reservedBooksList").html("");
        $(reserves).each(function(i, item) {
            $("ul#reservedBooksList").append(
                    $("<li>")
                    .append($("<strong>").text(item.title)
                            .css({"float": "left"})
                            )
                    .append($("<div>").css({"float": "right",
                        "text-align": "rigth"})
                            .append($("<button>")
                                    .text("Cancelar reserva")
                                    )
                            )
                    .append($("<div>").css({"clear": "both"}))
                    );
        });
    },
    refreshReserves: function() {
        var reservedBooksLength = JSON.parse(localStorage.getItem("reservedBooks")).length;
        $("aside#menu article ul li div").text(reservedBooksLength);
    },
    setVisibleFilter: function(state) {
        if (state === 1) {
            $("div#filterReserves").css({"display": "inline"});
        } else if (state === 0) {
            $("div#filterReserves").css({"display": "none"});
        }
    }
};


