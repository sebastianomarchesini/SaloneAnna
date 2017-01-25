<?php
session_start();
session_regenerate_id(TRUE);

// Controllo accesso
if (!isset($_SESSION['username'])) {
    header('location:index.php');
    exit;
} else {
    require 'library.php';
    $title = "Prodotti Max: Salone Anna";
    $title_meta = "Prodotti Max: Salone Anna";
    $descr = "";
    $keywords = "Gestione, Prodotti, Parrucchiere, Montecchio, Vicenza, Taglio, Colorazioni, Donna";
    
    page_start($title, $title_meta, $descr, $keywords, '');
    $rif = '<a href="index.php" xml:lang="en">Home</a> / <a href="Prodotti.php">Prodotti</a> / <strong>Prodotti Max</strong>';
    insert_header($rif, 3, true);
    content_begin();
    
    
    
    include("DBlibrary.php");
    $conn = dbconnect();
    
    $query  = "
      SELECT CodProdotto, Nome, Marca, Tipo, Quantita
  FROM Prodotti
  WHERE quantita<6 AND quantita!=0;";
    $result = mysqli_query($conn, $query);
    
    
    $number_cols = mysqli_num_fields($result);
    
    echo "<strong>I prodotti in esaurimento sono:</strong>";
    
    $num_rows = mysqli_num_rows($result);
    if (!$num_rows)
        echo "<p>Non ci sono prodotti da mostrare</p>";
    else {
        $th = '<table id="ProdottiMagazzino" summary="Prodotti in esaurimento">
            <caption>Prodotti presenti in numero inferiore a 6</caption>
            <thead>
                <tr>
                    <th scope="col">CodProdotto</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Quantità</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th scope="col">CodProdotto</th>
                    <th scope="col">Nome</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Quantità</th>
            </tfoot>

            <tbody>
            ';
        $tb = "";
        //corpo tabella
        while ($row = mysqli_fetch_row($result)) {
            
            $tb .= "<tr>\n";
            for ($i = 0; $i < $number_cols; $i++) {
                $tb .= "<td>";
                if (!isset($row[$i]))
                    $tb .= " ";
                else {
                    $tb .= $row[$i];
                }
                $tb .= "</td>\n";
            }
            $tb .= "</tr>\n";
        }
        
        $tf = "</tbody></table>";
        $to_print = $th . $tb . $tf;
        echo $to_print;
    }
    content_end();
    page_end();
}
mysqli_close($conn);
?>
