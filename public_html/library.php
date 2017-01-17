<?php

/***************************************/
/* FUNZIONI GENERALI PER PAGINE HTML   */
/***************************************/

/* Funzione per iniziare la pagina. In input il titolo */

// inserire variabili per keywords e descrizione, titolo
function page_start($title, $title_meta, $descr, $keywords) {
  $to_print='
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
    <head>
        <title>'.$title.'</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"\/>
        <meta name="title" content="'.$title_meta.'"\/>
        <meta name="description" content="'.$descr.'"\/>
        <meta name="keywords" content="'.$keywords.'"\/>
        <meta name="author" content="Andrea Grendene, Pietro Gabelli, Sebastiano Marchesini"\/>
        <meta name="language" content="italian it"\/>
        <meta name="viewport" content="width=device-width"\/>
        <meta http-equiv="Content-Script-Type" content="application/javascript"\/>
        <link rel="stylesheet" href="css/home_min.css" type="text/css" media="screen and (min-width: 650px)"\/>
        <link rel="stylesheet" type="text/css" href="css/print.css" media="print"\/>
        <link rel="stylesheet" type="text/css" href="css/small-devices.css" media="screen and (max-width: 650px)"\/>
        <!--[if lte IE 8]><link rel="stylesheet" type="text/css" href="css/explorer.css"\/><![endif]-->
        <link rel="icon" href="img/logo2.png" type="image/png"\/>
        <script type="text/javascript" src="script/script.js"></script>
    </head>
    <body>
        <p class="nascosto">
            <a title="salta header" href="#contenitore-menu" tabindex="1" accesskey="a">Salta l&apos;intestazione</a>
        </p>
';
  echo  $to_print;
}

/* Funzione per terminare una pagina */

function page_end() {
  $to_print='
        <div id="footer" class="footer">
            <ul class="nascosto">
                <li><a href="#header" title="vai-a-inizio-pagina" tabindex="100" accesskey="i">Torna all&apos;inizio pagina</a></li>
                <li><a href="#finePagina" title="vai-a-fine-pagina" tabindex="110" accesskey="f">Vai a fine pagina</a></li>
            </ul>
            <div class="footer-left">
                <h3 id="logo_mini"><span>Ggarden</span></h3>
                <p class="footer-menu, testo-footer">
                    <a href="index.html" hreflang="it" xml:lang="en" tabindex="100">Home</a> | 
                    <a href="realizzazioni.html" hreflang="it" tabindex="101">Clienti</a> | 
                    <a href="cgi-bin/checkLog.cgi" hreflang="it" tabindex="102">Prodotti</a> | 
                    <a href="contattaci.html" hreflang="it" tabindex="103">Appuntamenti</a>
                </p>
 
                <p class="footer-nome-azienda">Salone Anna &copy; 2017</p>
            </div>
 
            <div class="footer-center">
                <div>
                    <address class="testo-footer">Via Ludovico Ariosto, 36075 Montecchio Maggiore VI, Italy</address>
                </div>
 
                <div>
                    <p class="testo-footer"><a href="tel:+39 0444 697939">+39 0444 697939</a></p>
                </div>0444 697939
 
                <div>
                    <p xml:lang="en">E-Mail <a href="mailto:salone_anna@gmail.com" accesskey="e" tabindex="104">salone_anna@gmail.com</a></p>
                </div>
                <div class="testo-footer, center">
                    <p class="imgW3C">
                        <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" height="31" width="88" /></a>
                        <a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a>
                    </p>
                </div>
            </div>
 
            <div class="footer-right">
                <p class="footer-company-info" title="motto">
                    <span class="testo-footer">Gg Garden a servizio</span>
                    <span class="testo-footer">L\'erba del tuo vicino &egrave; sempre pi&ugrave; verde. Sii come il tuo vicino,
                        chiama G Garden Group</span>
                </p>
            </div>
        </div>
        <p id="finePagina"></p>
    </body>
</html>
';
  echo $to_print;
};

/*funzione per inserire l'header*/
function insert_header($rifnav, $num) {
  echo<<<END
    <div id="header">
      <h1><span id="logo" xml:lang="en" class="nascosto">GGarden</span></h1>
      <div id="breadcrumbs">
          <span id="rifnav" >Ti trovi in: <strong xml:lang="en">Home</strong></span>
          <form class="headersearch" action="cgi-bin/search.cgi" method="post">
              <fieldset>
                  <legend class="nascosto">Cerca un prodotto o un servizio</legend>
                  <label for="ricerca" class="nascosto">Inserisci i termini da cercare</label>
                  <input type="text" name="ricerca" id="ricerca" class="ricerca" accesskey="s" tabindex="2" />
                  <input type="submit" name="conferma" id="conferma" class="ricerca" value="Cerca" accesskey="d" tabindex="3"/>
              </fieldset>
          </form>
      </div>
END;
  contenitore_menu($num);
  echo "</div>";
}

/* funzione per inserire il menu
num serve ad evidenziare l'elemento del menu in cui si è

*/
function contenitore_menu($num) {
  $to_print='
    <div id="contenitore-menu">
      <p class="nascosto">
          <a href="#content" title="salta al contenuto principale">Salta menu navigazione</a>
      </p>
      <ul class="menu">

          <li><a href="index.php" id="home" class='.(($num == 0) ? ("vnav"):("nav")).' xml:lang="en" accesskey="h" tabindex="10">Home </a><li>
          <li><a href="Clienti.php" id="real" class='.(($num == 1) ? ("vnav"):("nav")).' accesskey="c" tabindex="11">Clienti</a></li>
          <li><a href="Prodotti.php" id="vend" class='.(($num == 2) ? ("vnav"):("nav")).' accesskey="p" tabindex="12">Prodotti </a></li>
          <li><a href="Appuntamenti.php" id="cont" class='.(($num == 3) ? ("vnav"):("nav")).' accesskey="a" tabindex="13">Appuntamenti</a></li>
      </ul>
    </div>
';
echo $to_print;
};

function select_class_menu($num1, $num2){
  if(num1 == $num2)
    return "vnav";
  else
    return "nav";
}

/*funzione per inserire l'inizio del content*/
function content_begin() {
  echo<<<END
    <div id="content">
      <p class="nascosto">
          <a title="saltare galleria immagini" href="#footer" tabindex="30" accesskey="b">Salta la galleria immagini</a>
      </p>
END;
}

function content_end() {
  echo "</div>";
}

/* funzione per il sottotitolo */

function subtitle($str) {
  echo "<h3>$str</h3>";
};

/* Funzione che ritorna un link, associato ad una URL. */

function hyperlink($str, $url) {
  return "<a href=\"$url\">$str</a>";
};

/***************************************/
/* FUNZIONI PER LA GESTIONE DI TABELLE */
/***************************************/

/* Funzione per iniziare una tabella html. In input l'array degli
   header delle colonne */
function table_start($row) {
  echo "<table border=\"1\">";
  echo "<tr>";
  foreach ($row as $field) 
    echo "<th>$field</th>";
  echo "</tr>";
};
  
/* funzione per stampare un array, come riga di tabella html */
function table_row($row) {
  echo "<tr>";
  foreach ($row as $field) 
    if ($field)
      echo "<td>$field</td>\n";
    else
      echo "<td>---</td>\n";
  echo "</tr>";
}

/* funzione per chiudere una tabella html */
function table_end() {
  echo "</table>";
};



/***************************************/
/* CONNESSIONE AL DATABASE             */
/***************************************/

/* Si connette e seleziona il database */

function dbconnect()
  {
    $host = "localhost";
    $user = "pgabelli";
    $pass = "bi9UJ9ohCoochei7";
    $db = "pgabelli";
    $conn=new mysqli($host, $user, $pass, $db);
    if($conn -> connect_errno)
      echo "Connessione fallita(".$conn -> connect_errno."): ".$conn -> connect_error;
    return $conn;
    //exit();
  };



/***************************************/
/* FUNZIONI PER AUTENTICAZIONE         */
/***************************************/

function new_user($login, $password) {

  /* si connette e seleziona il database da usare */
  $dbname="login-ES";
  $conn = dbConnect($dbname);

  /* preparazione dello statement */

  $query= sprintf("INSERT INTO Eser5Users VALUES (\"%s\", \"%s\")", 
		  $login, SHA1($password));
  
  /* Stampa la query a video ... utile per debug */
  /* echo "<B>Query</B>: $query <BR />"; */
  
  mysqli_query($conn, $query)
    or die("Query fallita" . mysqli_error($conn));
}


function get_pwd($login) {
    $conn = dbConnect();
    $query= "SELECT * FROM Login WHERE username='$login'";
    $result=mysqli_query($conn, $query)
      or die("Query fallita" . mysql_error($conn));
    $output=mysqli_fetch_assoc($result);
    if ($output)
      return $output['password'];
    else 
      return FALSE;
  }


/* inizia la sessione e verifica che l'utente sia autenticato */
function authenticate() {
  session_start();
  // session_regenerate_id(TRUE);
  $login=$_SESSION['logged'];
  if (! $login) {
    return FALSE;
  } else {
    return $login;
  }
}

?>
