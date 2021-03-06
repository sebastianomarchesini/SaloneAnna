<?php
require 'library.php';
require 'utils/DBlibrary.php';
$login=authenticate();
// Controllo accesso
if (!$login) {
    header('location:errore.php?codmsg=1');
    exit;
} 
else {
    $title      = "Seleziona Appuntamento Cliente | Salone Anna";
    $title_meta = "Seleziona Appuntamento Cliente | Salone Anna";
    $descr      = "Pagina di selezione appuntamento del cliente visualizzato tra varie scelte";
    $keywords   = "Appuntamento, Nome, Cognome, Data, Ora, Tipo, Prezzo, Cliente, Seleziona";
    page_start($title, $title_meta, $descr, $keywords, '');
    $rif = '<a href="index.php" xml:lang="en">Home</a> / <a href="Prodotti.php">Prodotti</a>  / <strong>Prodotti-Clienti-Appuntamento</strong>';
    insert_header($rif, 4, true);
    content_begin();
    
    if (isset($_POST['submit']) && isset($_POST['CodCliente'])) {
        $submit     = $_POST["submit"];
        $codCliente = $_POST["CodCliente"];
        
        echo "<h2>Lista degli appuntamenti del cliente selezionato</h2>";
        
        // leggi-se c'è il cliente, prendi gli appuntamenti, stampali sulla lista.
        $result = listaAppuntamentiCliente($codCliente);
        
        if (!$result)
            echo "<p>Non ci sono appuntamenti da mostrare</p>";
        else {
            echo '<form method="post" action="SelezionaProdottiAppuntamento.php">
            <fieldset>';
            $str_to_print = '<table id="tabApp" summary="Appuntamenti successivi alla data corrente">
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
        <tbody>';
            
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
                    <td class=\"tdin\"><input type=\"radio\" name=\"codapp\" id=\"ca" . $appuntamento->codice . "\" value= \"" . $appuntamento->codice . "\"/><label for==\"ca" . $appuntamento->codice . "\">Seleziona</label></td>
                  </tr>";
            }
            
            $str_to_print .= "</tbody></table>";
            echo $str_to_print;
            echo "<input type=\"submit\" name=\"submit\" value=\"Conferma\"/>
          </fieldset>
          </form>";
        }
        unset($result);
    }
    content_end();
    page_end();
}
?>