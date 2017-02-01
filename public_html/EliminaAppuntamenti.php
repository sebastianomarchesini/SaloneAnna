
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
    
    if (isset($_POST['submit']) && isset($_POST['codApp'])) {
        $ids   = $_POST['codApp'];
        $n_el  = 0;
        $n_err = 0;
        foreach ($ids as $codice) {
            $ris = eliminaAppuntamento($codice);
            if ($ris)
                ++$n_el;
            else
                ++$n_err;
        }
        
        if ($n_el > 0)
            if ($n_el == 1)
                $msg = "<p class=\"inforesult\">È stato cancellato $n_el appuntamento</p>";
            else
                $msg = "<p class=\"inforesult\">Sono stati cancellati $n_el appuntamenti</p>";
        if ($n_err > 0)
            $msg = "<p class=\"errorSuggestion\">Durante la cancellazione si sono verificati $n_err errori</p>";
    }
    
    $title      = "Elimina appuntamenti: Salone Anna";
    $title_meta = "Elimina appuntamenti: Salone Anna";
    $descr      = "";
    $keywords   = "Elimina, Appuntamenti, Parrucchiere, Montecchio, Vicenza, Taglio, Colorazioni, Donna";
    
    page_start($title, $title_meta, $descr, $keywords, '');
    $rif = '<a href="index.php" xml:lang="en">Home</a> / <a href="Appuntamenti.php">Appuntamenti</a> / <strong>Elimina Appuntamenti</strong>';
    insert_header($rif, 6, true);
    content_begin();
    
    $result = listaAppuntamenti();
    
    echo "<h2>Lista degli appuntamenti da oggi in poi</h2>";
    
    if (!$result)
        echo "<p class=\"info\">Non ci sono appuntamenti da mostrare</p>";
    else {
        form_start("post", "EliminaAppuntamenti.php");
        echo '<fieldset>
            <legend>Seleziona gli appuntamenti che vuoi eliminare</legend>';
        $str_to_print = '
            <table id="topProd" summary="Appuntamenti successivi alla data corrente">
            	<caption>Appuntamenti successivi alla data corrente</caption>
            	<thead>
                	<tr>
                	<th scope="col">Id</th>
                	<th scope="col">Nome</th>
                	<th scope="col">Cognome</th>
                	<th scope="col">Data</th>
                	<th scope="col">Ora</th>
                	<th scope="col">Tipo</th>
                	<th scope="col">Prezzo</th>
                	<th scope="col">Seleziona</th>
                	</tr>
                </thead>
                <tbody>
                ';
        
        foreach ($result as $appuntamento) {
            $str_to_print .= "
            <tr>
                <td>" . $appuntamento->codice . "</td>
                <td>" . $appuntamento->nome . "</td>
                <td>" . $appuntamento->cognome . "</td>
                <td>" . $appuntamento->data . "</td>
                <td>" . $appuntamento->ora . "</td>
                <td>" . $appuntamento->tipo . "</td>
                <td>" . $appuntamento->prezzo . "</td>
                <td><input type=\"checkbox\" name=\"codApp[]\" value= \"" . $appuntamento->codice . "\" /></td>
            </tr>
            ";
        }
        
        $str_to_print .= "</tbody></table>";
        $str_to_print .= "<input type=\"submit\" name=\"submit\" value=\"Conferma\">
    	</td>
        </fieldset>
	</form>";
    }
    $result->free();
    if (isset($msg))
        echo $msg;
    if (isset($str_to_print))
    echo $str_to_print;
    content_end();
    page_end();
}

?>