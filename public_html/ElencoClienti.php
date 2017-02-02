
<?php
require 'library.php';
include 'utils/DBlibrary.php';

$login=authenticate();

// Controllo accesso
if (!checkLog()) {
    header('location:index.php');
    exit;
} else {
    $title      = "Elenco clienti: Salone Anna";
    $title_meta = "Elenco clienti: Salone Anna";
    $descr      = "";
    $keywords   = "Elenco, Clienti, Parrucchiere, Montecchio, Vicenza, Taglio, Colorazioni, Donna";
    page_start($title, $title_meta, $descr, $keywords, '');
    $rif = '<a href="index.php" xml:lang="en">Home</a> / <a href="Clienti.php">Clienti</a> / <strong>Elenco clienti</strong>';
    insert_header($rif, 5, true);
    content_begin();
    echo "<h2>Elenco Clienti</h2>";
    
    $ris = listaClienti();
    
    $num_rows = count($ris);
    
    
    if ($ris) {
        $str_to_print = '<table id="clientiTab" summary="Elenco clienti">
            <caption class="nascosto">Elenco clienti</caption>
            <thead>
                <tr>
                	<th scope="col">Codice</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Cognome</th>
                    <th scope="col">Telefono</th>
                    <th scope="col" xml:lang="en">E-mail</th>
                    <th scope="col">Data nascita</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($ris as $cliente) {
            $str_to_print .= "
            	<tr>
            		<td>" . $cliente->codice . "</td>
            		<td>" . $cliente->nome . "</td>
            		<td>" . $cliente->cognome . "</td>
            		<td>" . $cliente->telefono . "</td>
            		<td>" . $cliente->email . "</td>
            		<td>" . $cliente->dataNascita . "</td>
            	</tr>";
        }
        $str_to_print .= "</tbody></table>";
        unset($cliente); //fortemente consigliato perché altrimenti l'oggetto $cliente rimane in memoria
    } else
        $str_to_print = "<p class=\"inforesult\">Non sono presenti clienti nel database</p>";
    unset($ris);
    echo $str_to_print;
    content_end();
    page_end();
}

?>

