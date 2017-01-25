<?php
  session_start();
  session_regenerate_id(TRUE);

  // Controllo accesso
  if (!isset($_SESSION['username'] ) )
  {
    header('location:index.php');
    exit;
  }
  else
  {
    require 'library.php';
    $title="Clienti: Salone Anna";
    $title_meta="Clienti: Salone Anna";
    $descr="";
    $keywords="Clienti, Parrucchiere, Montecchio, Vicenza, Taglio, Colorazioni, Donna";
    
    page_start($title, $title_meta, $descr, $keywords,'');
    $rif='<a href="index.php" xml:lang="en">Home</a> / <strong>GestioneProdotti</strong>';
    insert_header($rif, 1, true);
    content_begin();
    echo "Benvenuto ".$_SESSION['username'];
    echo"<form method=post action=\"confermaGestProd.php\">
    <input type=submit name=\"submit\" value=\"Conferma\"><br>";
    
    
    include ("utils/dbconnect.php");
    $conn=dbconnect();
    
    $query = "SELECT * FROM Prodotti p WHERE p.Quantita is not NULL AND p.Quantita>0 ";
    $result = mysqli_query($conn, $query);

    $number_cols = mysqli_num_fields($result);
    
    echo "<b><h2>Lista Prodotti</h2></b>";
    echo "<table border = 1>\n";
    echo "<tr align=center>\n";
    for($i=0; $i<$number_cols+1; $i++)
      {
      if($i==$number_cols) echo"<th> Elimina </th>\n";
      else echo "<th>" . mysqli_field_seek ($result, $i). "</th>\n";
      }
    echo "</tr>\n";
    
    //intestazione tabella
    
    //corpo tabella
    while ($row = mysqli_fetch_row($result))
    {
    
    echo "<tr align=left>\n";
      for ($i=0; $i<$number_cols+1; $i++)
      {
      echo "<td align=center>";
        if(!isset($row[$i])) echo " ";
        else if($i==4) echo "<input type=\"text\" name=\"quantita\" value=\"$row[$i]\" />";
        else
          {echo $row[$i];} 
      if($i==$number_cols)echo "<input type=checkbox name=prod >";
    
     
        echo "</td>\n";
      }
      echo "</tr>\n";
    }
    
    echo "</table>";
    
    echo"<input type=submit name=\"submit\" value=\"Conferma\">
    </td>
    </form>";
    content_end();
  page_end();
}

?>
