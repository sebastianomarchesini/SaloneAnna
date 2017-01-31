<?php

session_start();
session_regenerate_id(TRUE);

// Controllo accesso
if (!isset($_SESSION['username'])) {
    header('location:index.php');
    exit;
} else {
    require 'library.php';
    include 'utils/DBlibrary.php';
    if (!$_GET["codmsg"]) {
        $err = "<p class=\"errorSuggestion\">Non è stato selezionato alcun messaggio, torna a <a href=\"Messaggi.php\" e riprova</p>";
    } else {
        $cmsg = $_GET["codmsg"];
        
        $result = mostraMessaggio($cmsg);
        
        if (is_null($result)) {
            echo "<p class=\"info\">Non è presente il messaggio richiesto</p>";
        } else {
            $to_print = '
            <div id="messaggio">
                <ul>
                    <li>
                        <p id="nomecognome">
                            <em>Messaggio inviato da:  </em>' . $result->nome . ' ' . $result->cognome . '
                        </p>
                    </li>
                    <li>
                        <p id="dataora">
                            <em>Ricevuto:  </em>' . $result->data . ' ' . $result->ora . '
                        </p>
                    </li>
                    <li>
                        <p id="telefono">
                            <em>Telefono cliente: </em><a href="tel:' . $result->telefono . '">' . $result->telefono . '</a>
                        </p>
                        <p id="mail">
                            <em xml:lang="en">email: </em><a href="mailto:' . $result->email . '"">' . $result->email . '</a>
                        </p>
                    </li>
                    <li>
                        <p id="contenuto">
                            <em>Contenuto:</em>' . $result->contenuto . '
                        </p>
                    </li>
                </ul>
            </div>
                ';
        }
    }
    
    $title      = "Messaggi: Salone Anna";
    $title_meta = "Messaggi: Salone Anna";
    $descr      = "";
    $keywords   = "Messaggi, Parrucchiere, Montecchio, Vicenza, Taglio, Colorazioni, Donna";
    
    page_start($title, $title_meta, $descr, $keywords, '');
    $rif = '<a href="index.php" xml:lang="en">Home</a> / <a href="Messaggi.php">Messaggi</a> / <strong>Leggi Messaggio</strong>';
    insert_header($rif, 7, true);
    content_begin();
    if (isset($err))
        echo $err;
    if (isset($to_print))
        echo $to_print;
    content_end();
    page_end();
}

?>