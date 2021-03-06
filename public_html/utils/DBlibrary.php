<?php

function dbconnect() {
	$host = "localhost";

	/*$user = "pgabelli";
	$pass = "bi9UJ9ohCoochei7";
	$db = "pgabelli";
	$user = "agrenden";
	$pass = "EloTeeli0SaePohF";
	$db = "agrenden";*/
	$user = "smarches";
	$pass = "oqu9eim5ookooCei";
	$db = "smarches";
	$conn=new mysqli($host, $user, $pass, $db);
	if($conn -> connect_errno) //invia alla pagina che mostra l'errore
		header('location:errore.php?codmsg=0');
	return $conn;
};

function eseguiQuery($query) {
	$conn = dbconnect();
	$result = $conn->query($query); //se ci sono problemi segnala che c'è un errore, oppure restituisce i risultati nel caso la query sia una SELECT
	$conn->close();
	return $result;
}

function checkCommaSet(&$set) { //metodo di supporto usato durante l'aggiornamento delle entità
	if($set != "") {$set = $set.", ";}
}

function checkAndWhere(&$where) { //metodo di supporto usato durante i check
	if($where != "") {$where = $where." AND ";}
}

function cleanString(&$stringa) {
	$stringa = htmlentities($stringa);
	$stringa = trim($stringa);
}

function checkPrezzo(&$prezzo) {
	if(preg_match("/^[0-9]+([.,][0-9]{1,2})?$/", $prezzo)) {
		if(strpos($prezzo, ',') !== FALSE) {
			$prezzo = str_replace(',', '.', $prezzo);
		}
		return TRUE;
	}
	return FALSE;
}

function standardPrezzo(&$prezzo) { //funzione che aggiunge gli 0 al prezzo quando occorre
	$lunghezzaDec = strrpos($prezzo, '.');
	if($lunghezzaDec === FALSE) //se il punto non c'è allora non ci sono cifre dopo la virgola
		$prezzo = $prezzo.'.00';
	else if($lunghezzaDec == strlen($prezzo)-2) //strrpos parte da 0, invece strlen parte da 1, quindi la differenza di valore è 2 se c'è solo una cifra dopo la virgola
		$prezzo = $prezzo.'0';
}

function checkPercentuale(&$percentuale) {
	if(preg_match("/^[0-9]{1,2}([.,][0-9]+)?$/", $percentuale)) {
		if(strpos($percentuale, ',') !== FALSE) {
			$percentuale = str_replace(',', '.', $percentuale);
		}
		$punto = strpos($percentuale, '.');
		while($punto < strlen($percentuale)-3)
			$percentuale = mb_substr($percentuale, 0, -1); //rimuovo l'ultimo carattere
		return TRUE;
	}
	return FALSE;
}

/*******************MESSAGGI************************/

class Messaggio { //classe che rappresenta un messaggio
	public $codice;
	public $contenuto;
	public $data;
	public $ora;
	public $daLeggere;
	public $email;
	public $telefono;
	public $nome;
	public $cognome;
	
	function __construct($codice, $contenuto, $data, $ora, $daLeggere, $email, $telefono, $nome, $cognome) {
		$this->codice = $codice;
		$this->contenuto = $contenuto;
		$this->data = $data;
		$this->ora = $ora;
		$this->daLeggere = $daLeggere;
		$this->email = $email;
		$this->telefono = $telefono;
		$this->nome = $nome;
		$this->cognome = $cognome;
	}
}

function listaMessaggi() { //i messaggi verranno già ordinati dal più recente al più vecchio
	$conn = dbconnect();
	$conn->query("DELETE FROM Messaggi WHERE DataOra < (CURDATE() - INTERVAL 2 MONTH)"); //non mi interessa se va a buon fine perché non è una query essenziale, se questa query fallisce ma quella sotto no allora la funzione ha esito positivo
	$result = $conn->query('SELECT CodMessaggi, Contenuto, DataOra, ToRead, Email, Telefono, Nome, Cognome
	FROM Messaggi JOIN Clienti ON Messaggi.CodCliente = Clienti.CodCliente
	ORDER BY DataOra DESC');
	if(!$result) {$messaggi = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$messaggi = array();
		while($messaggio = mysqli_fetch_assoc($result)) {
			$time = strtotime($messaggio['DataOra']);
			$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			$ora = date("H:i", $time); //formato del tipo 23:46
			array_push($messaggi, new Messaggio($messaggio['CodMessaggi'], $messaggio['Contenuto'], $data, $ora, $messaggio['ToRead'], $messaggio['Email'], $messaggio['Telefono'], $messaggio['Nome'], $messaggio['Cognome']));
		}
	}
	$conn->close();
	return $messaggi; //è un array di Messaggi, non viene garantito che $messaggi sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function aggiungiMessaggio($email, $nome, $cognome, $contenuto) { //se ci sono errori in qualche query la funzione restituisce FALSE, altrimenti TRUE
	$conn = dbconnect();
	$errore = FALSE;
	cleanString($email);
	$checkChiocciola = strpos($email, '@');
	$checkPunto = strrpos($email, '.');
	if($email == "" || ($checkChiocciola === FALSE || $checkPunto === FALSE || $checkChiocciola>$checkPunto) || $nome=="" || $cognome=="") {$errore = TRUE;}
	if($errore === FALSE) {
		$cliente = $conn->query("SELECT CodCliente FROM Clienti WHERE Nome='$nome' AND Cognome='$cognome' AND Email='$email'");
		if($cliente && $cliente->num_rows == 0) { //se il cliente è nuovo lo aggiungo al database
			$result = $conn->query("INSERT INTO Clienti(Nome, Cognome, Email) VALUES ('$nome', '$cognome', '$email')");
			//per inserire il messaggio mi serve il codice del cliente, quindi devo eseguire nuovamente la query per ottenerlo
			if($result==1) {$cliente = $conn->query("SELECT MAX(CodCliente) AS CodCliente FROM Clienti");}
			else {$cliente=FALSE;} //se ci sono stati problemi di connessione li segnalo
		}
		if($cliente) {
			cleanString($nome);
			cleanString($cognome);
			cleanString($contenuto);
			$dataora = date("Y-m-d H:i:s", time());
			$codcliente = $cliente->fetch_assoc();
			$codcliente = $codcliente['CodCliente'];
			$result = $conn->query("INSERT INTO Messaggi(CodCliente, Contenuto, DataOra, ToRead) VALUES ($codcliente, '$contenuto', '$dataora', 1)");
			$conn->close();
			if($result) return TRUE;
			return FALSE;
		}
	}
	$conn->close();
	return FALSE;
}

function eliminaMessaggio($codice) {
	return eseguiQuery("DELETE FROM Messaggi WHERE CodMessaggi=$codice");
}

function mostraMessaggio($codice) {
	$messaggio = eseguiQuery("SELECT CodMessaggi, Contenuto, DataOra, ToRead, Email, Telefono, Nome, Cognome
	FROM Messaggi JOIN Clienti ON Messaggi.CodCliente = Clienti.CodCliente
	WHERE CodMessaggi=$codice");
	if(!$messaggio) {return NULL;}
	else {
		$letto = eseguiQuery("UPDATE Messaggi SET ToRead='false' WHERE CodMessaggi=$codice");
		if(!$letto) {return NULL;}
		else {
			$messaggio = mysqli_fetch_assoc($messaggio);
			$time = strtotime($messaggio['DataOra']);
			$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			$ora = date("H:i", $time); //formato del tipo 23:46
			return new Messaggio($messaggio['CodMessaggi'], $messaggio['Contenuto'], $data, $ora, 1, $messaggio['Email'], $messaggio['Telefono'], $messaggio['Nome'], $messaggio['Cognome']);
		}
	}
}

/************************Clienti**************************/

class Cliente {
	public $codice;
	public $nome;
	public $cognome;
	public $telefono;
	public $email;
	public $dataNascita;
	
	function __construct($codice, $nome, $cognome, $telefono, $email, $dataNascita) {
		$this->codice = $codice;
		$this->nome = $nome;
		$this->cognome = $cognome;
		$this->telefono = $telefono;
		$this->email = $email;
		$this->dataNascita = $dataNascita;
	}
}

function listaClienti() {
	$result = eseguiQuery("SELECT * FROM Clienti");
	if(!$result) {$clienti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$clienti = array();
		while($cliente = mysqli_fetch_assoc($result)) {
			if($cliente['DataNascita'] !== NULL) {
				$time = strtotime($cliente['DataNascita']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			}
			else {$data = NULL;}
			array_push($clienti, new Cliente($cliente['CodCliente'], $cliente['Nome'], $cliente['Cognome'], $cliente['Telefono'], $cliente['Email'], $data));
		}
	}
	return $clienti; //è un array di Clienti, non viene garantito che $clienti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function aggiungiCliente($nome, $cognome, $telefono = "", $email = "", $dataNascita = "") {
	if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $dataNascita)) {$data = "'".date_format(date_create_from_format("d/m/Y", $dataNascita), "Y-m-d")."'";}
	else {$data = "NULL";}
	cleanString($nome);
	cleanString($cognome);
	cleanString($telefono);
	$telefono = str_replace(' ', '', $telefono); //elimino gli eventuali spazi in mezzo alla stringa, ad esempio per dividere le prime 3 cifre dal resto
	cleanString($email);
	$checkChiocciola = strpos($email, '@');
	$checkPunto = strrpos($email, '.');
	if(!preg_match("#^[0-9]{9,10}$#", $telefono)) {$telefono="NULL";}
	if($email == "" || ($checkChiocciola === FALSE || $checkPunto === FALSE || $checkChiocciola>$checkPunto)) {$email="NULL";}
	return eseguiQuery("INSERT Clienti(Nome, Cognome, Telefono, Email, DataNascita) VALUES('$nome', '$cognome', '$telefono', '$email', $data)");
}

function eliminaCliente($codice) {
	return eseguiQuery("DELETE FROM Clienti WHERE CodCliente=$codice");
}

function aggiornaCliente($codice, $nome = "", $cognome = "", $telefono = "", $email = "", $dataNascita = "") {
	$set = "";
	cleanString($nome);
	cleanString($cognome);
	cleanString($telefono);
	cleanString($email);
	if($nome != "") {$set = $set."Nome='$nome'";}
	if($cognome != "") {checkCommaSet($set); $set = $set."Cognome='$cognome'";}
	if(preg_match("#^[0-9]{9,10}$#", $telefono)) {
		$telefono = str_replace(' ', '', $telefono); //elimino gli eventuali spazi in mezzo alla stringa, ad esempio per dividere le prime 3 cifre dal resto
		checkCommaSet($set);
		$set = $set."Telefono='$telefono'";
	}
	$checkChiocciola = strpos($email, '@');
	$checkPunto = strrpos($email, '.');
	if($checkChiocciola !== FALSE && $checkPunto !== FALSE && $checkChiocciola<$checkPunto) {checkCommaSet($set); $set = $set."Email='$email'";}
	if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $dataNascita)) {
		$data = "'".date_format(date_create_from_format("d/m/Y", $dataNascita), "Y-m-d")."'";
		checkCommaSet($set);
		$set = $set."DataNascita=$data";
	}
	return eseguiQuery("UPDATE Clienti
	SET $set
	WHERE CodCliente=$codice"); //se $set è vuoto (non viene aggiornato nulla) viene restituito FALSE
}

function checkCliente($nome, $cognome, $telefono = "", $email = "", $data = "") {
	$where = "Nome='$nome' AND Cognome='$cognome'"; //nome e cognome sono campi obbligatori
	if($telefono != "") {checkAndWhere($where); $where = $where."Telefono='$telefono'";}
	if($email != "") {checkAndWhere($where); $where = $where."Email='$email'";} //non controllo il formato dell'email perché se è errato non verranno trovati clienti
	if($data != "") {
		$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
		if($data !== FALSE) {
			checkAndWhere($where);
			$where = $where."DataNascita='$data'";
		}
	}
	$result = eseguiQuery("SELECT * FROM Clienti WHERE $where");
	if(!isset($result)) {return NULL;}
	else {
		$clienti = array();
		while($cliente = mysqli_fetch_assoc($result)) {
			if($cliente['DataNascita'] !== NULL) {
				$time = strtotime($cliente['DataNascita']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			}
			else {$data = NULL;}
			array_push($clienti, new Cliente($cliente['CodCliente'], $cliente['Nome'], $cliente['Cognome'], $cliente['Telefono'], $cliente['Email'], $data));
		}
		return $clienti;
	}
}

function mostraCliente($codice) {
	$cliente = eseguiQuery("SELECT * FROM Clienti WHERE CodCliente=$codice");
	if(!$cliente) {return NULL;}
	else {
		$cliente = mysqli_fetch_assoc($cliente);
		if($cliente['DataNascita'] !== NULL) {
			$data = strtotime($cliente['DataNascita']);
			$data = date("d/m/Y", $data); //formato del tipo 05/01/2017
		}
		else {$data = NULL;}
		return new Cliente($cliente['CodCliente'], $cliente['Nome'], $cliente['Cognome'], $cliente['Telefono'], $cliente['Email'], $data);
	}
}

/**********************TIPO APPUNTAMENTI***********************/

class TipoAppuntamento {
	public $codice;
	public $nome;
	public $costo;
	public $sconto;
	
	function __construct($codice, $nome, $costo, $sconto) {
		standardPrezzo($costo);
		standardPrezzo($sconto);
		
		$this->codice = $codice;
		$this->nome = $nome;
		$this->costo = $costo;
		$this->sconto = $sconto;
	}
}

function listaTipoAppuntamenti() {
	$result = eseguiQuery("SELECT * FROM TipoAppuntamento");
	if(!$result) {$tipi = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$tipi = array();
		while($tipo = mysqli_fetch_assoc($result)) {
			array_push($tipi, new TipoAppuntamento($tipo['CodTipoAppuntamento'], $tipo['NomeTipo'], $tipo['Costo'], $tipo['Sconto']));
		}
	}
	return $tipi; //è un array di TipoAppuntamento, non viene garantito che $tipi sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function aggiungiTipoAppuntamento($nome, $costo=0, $sconto=0) {
	cleanString($nome);
	if($nome === "") {return FALSE;} //un TipoAppuntamento senza nome non ha senso
	if(!checkPrezzo($costo)) {$costo=0;} //per sicurezza faccio questi controlli, anche se non dovrebbero servire, non dovrebbe essere possibile immettere come valore una stringa vuota
	if(!checkPercentuale($sconto)) {$sconto=0;}
	return eseguiQuery("INSERT TipoAppuntamento(NomeTipo, Costo, Sconto) VALUES('$nome', $costo, $sconto)");
}

function eliminaTipoAppuntamento($codice) {
	return eseguiQuery("DELETE FROM TipoAppuntamento WHERE CodTipoAppuntamento=$codice");
}

function aggiornaTipoAppuntamento($codice, $nome = "", $costo = -1, $sconto = -1) {
	$set = "";
	cleanString($nome);
	if($nome != "") {$set = $set."NomeTipo='$nome'";}
	if($costo > -1) {checkCommaSet($set); $set = $set."Costo=$costo";} //uso -1 come valore nullo perché 0 potrebbe essere un numero valido, soprattutto per sconto
	if($sconto > -1) {checkCommaSet($set); $set = $set."Sconto=$sconto";}
	return eseguiQuery("UPDATE TipoAppuntamento
	SET $set
	WHERE CodTipoAppuntamento=$codice"); //se $set è vuoto (non viene aggiornato nulla) viene restituito FALSE
}

/***********************APPUNTAMENTI***********************/

class Appuntamento {
	public $codice;
	public $data;
	public $ora;
	public $tipo;
	public $prezzo;
	public $nome;
	public $cognome;
	public $telefono;
	public $email;
	
	function __construct($codice, $data, $ora, $tipo, $prezzo, $nome, $cognome, $telefono, $email) {
		standardPrezzo($prezzo);
		
		$this->codice = $codice;
		$this->data = $data;
		$this->ora = $ora;
		$this->tipo = $tipo;
		$this->prezzo = $prezzo; //inteso come costo già scontato
		$this->nome = $nome;
		$this->cognome = $cognome;
		$this->telefono = $telefono;
		$this->email = $email;
	}
}

function listaAppuntamenti() {
	$result = eseguiQuery("SELECT CodAppuntamento, DataOra, NomeTipo, Costo, Sconto, Nome, Cognome, Telefono, Email
	FROM Appuntamenti JOIN TipoAppuntamento ON Appuntamenti.CodTipoAppuntamento=TipoAppuntamento.CodTipoAppuntamento JOIN Clienti ON Appuntamenti.CodCliente=Clienti.CodCliente
	ORDER BY DataOra DESC");
	if(!$result) {$appuntamenti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$appuntamenti = array();
		while($appuntamento = mysqli_fetch_assoc($result)) {
			if($appuntamento['DataOra'] !== NULL) {
				$time = strtotime($appuntamento['DataOra']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
				$ora = date("H:i", $time); //formato del tipo 23:46
			}
			else {$data = NULL; $ora = NULL;}
			$prezzo = round($appuntamento['Costo'] * ((100-$appuntamento['Sconto'])/100), 2); //round arrotonda il numero alla seconda cifra decimale
			array_push($appuntamenti, new Appuntamento($appuntamento['CodAppuntamento'], $data, $ora, $appuntamento['NomeTipo'], $prezzo, $appuntamento['Nome'], $appuntamento['Cognome'], $appuntamento['Telefono'], $appuntamento['Email']));
		}
	}
	return $appuntamenti; //è un array di Appuntamenti, non viene garantito che $appuntamenti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function checkOrarioLibero($data, $ora) { //il controllo della quantità di tempo a disposizione per eseguire la richiesta del cliente è responsabilità dell'amministratore
	if($ora !== "") {
		$ora = strtotime($ora);
		if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data) && $ora !== FALSE) {
			$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
			$dataora = "'$data ".date("H:i:s", $ora)."'";
			$result = eseguiQuery("SELECT CodAppuntamento FROM Appuntamenti WHERE DataOra=$dataora");
			if(!isset($result) || $result->num_rows > 0) {return FALSE;} //se c'è un errore restituisco FALSE perché il problema non dipende dall'input dell'utente
		}
	}
	return TRUE;
}

function aggiungiAppuntamento($codCliente, $data, $ora, $codTipo) {
	if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data)) {
		$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
	}
	else {return FALSE;}
	$ora = strtotime($ora);
	if($ora !== FALSE) {
		$dataora = "'$data ".date("H:i:s", $ora)."'";
		return eseguiQuery("INSERT Appuntamenti(CodCliente, DataOra, CodTipoAppuntamento) VALUES ($codCliente, $dataora, $codTipo)");
	}
	else {return FALSE;}
}

function eliminaAppuntamento($codice) {
	return eseguiQuery("DELETE FROM Appuntamenti WHERE CodAppuntamento=$codice");
}

function aggiornaAppuntamento($codice, $codCliente=0, $data="", $ora="", $codTipo=0) {
	$conn = dbconnect();
	$set = "";
	if($codCliente > 0) {$set = $set."CodCliente=$codCliente";}
	if($codTipo > 0) {checkCommaSet($set); $set = $set."CodTipoAppuntamento=$codTipo";}
	if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data) && $ora != "") {
		$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
		$ora = strtotime($ora);
		if($ora !== FALSE) {
			checkCommaSet($set);
			$set = $set."DataOra='$data ".date("H:i:s", $ora)."'";
		}
	}
	else if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data) || $ora != "") {
		$dataora = $conn->query("SELECT DataOra FROM Appuntamenti WHERE CodAppuntamento=$codice");
		$dataora = mysqli_fetch_assoc($dataora);
		if($dataora) {
			$dataora = strtotime($dataora['DataOra']);
			if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data)) {
				$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
				checkCommaSet($set);
				$set = $set."DataOra='$data ".date("H:i:s", $dataora)."'";
			}
			else {
				$ora = strtotime($ora);
				if($ora !== FALSE) {
					checkCommaSet($set);
					$set = $set."DataOra='".date("Y-m-d", $dataora)." ".date("H:i:s", $ora)."'";
				}
			}
		}
	}
	$result = $conn->query("UPDATE Appuntamenti SET $set WHERE CodAppuntamento=$codice"); //se $set è vuoto (non viene aggiornato nulla) viene restituito FALSE
	$conn->close();
	return $result;
}

function mostraAppuntamento($codice) {
	$appuntamento = eseguiQuery("SELECT CodAppuntamento, DataOra, NomeTipo, Costo, Sconto, Nome, Cognome, Telefono, Email
	FROM Appuntamenti JOIN TipoAppuntamento ON Appuntamenti.CodTipoAppuntamento=TipoAppuntamento.CodTipoAppuntamento JOIN Clienti ON Appuntamenti.CodCliente=Clienti.CodCliente
	WHERE CodAppuntamento=$codice");
	if(!$appuntamento) {return NULL;}
	else {
		$appuntamento = mysqli_fetch_assoc($appuntamento);
		if($appuntamento['DataOra'] !== NULL) {
			$time = strtotime($appuntamento['DataOra']);
			$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			$ora = date("H:i", $time); //formato del tipo 23:46
		}
		else {$data = NULL; $ora = NULL;}
		$prezzo = round($appuntamento['Costo'] * ((100-$appuntamento['Sconto'])/100), 2); //round arrotonda il numero alla seconda cifra decimale
		return new Appuntamento($appuntamento['CodAppuntamento'], $data, $ora, $appuntamento['NomeTipo'], $prezzo, $appuntamento['Nome'], $appuntamento['Cognome'], $appuntamento['Telefono'], $appuntamento['Email']);
	}
}

function listaAppuntamentiCliente($codCliente) {
	$result = eseguiQuery("SELECT CodAppuntamento, DataOra, NomeTipo, Costo, Sconto, Nome, Cognome, Telefono, Email
	FROM Appuntamenti JOIN TipoAppuntamento ON Appuntamenti.CodTipoAppuntamento=TipoAppuntamento.CodTipoAppuntamento JOIN Clienti ON Appuntamenti.CodCliente=Clienti.CodCliente
	WHERE Appuntamenti.CodCliente='$codCliente'
	ORDER BY DataOra DESC");
	if(!$result) {$appuntamenti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$appuntamenti = array();
		while($appuntamento = mysqli_fetch_assoc($result)) {
			if($appuntamento['DataOra'] !== NULL) {
				$time = strtotime($appuntamento['DataOra']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
				$ora = date("H:i", $time); //formato del tipo 23:46
			}
			else {$data = NULL; $ora = NULL;}
			$prezzo = round($appuntamento['Costo'] * ((100-$appuntamento['Sconto'])/100), 2); //round arrotonda il numero alla seconda cifra decimale
			array_push($appuntamenti, new Appuntamento($appuntamento['CodAppuntamento'], $data, $ora, $appuntamento['NomeTipo'], $prezzo, $appuntamento['Nome'], $appuntamento['Cognome'], $appuntamento['Telefono'], $appuntamento['Email']));
		}
	}
	return $appuntamenti; //è un array di Appuntamenti, non viene garantito che $appuntamenti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

/**********************TIPO PRODOTTI*********************/

class Prodotto {
	public $codice;
	public $nome;
	public $marca;
	public $tipo;
	public $quantita;
	public $prezzo;
	public $prezzoRiv;
	
	function __construct($codice, $nome, $marca, $tipo, $quantita, $prezzo, $prezzoRiv) {
		standardPrezzo($prezzo);
		standardPrezzo($prezzoRiv);
		
		$this->codice = $codice;
		$this->nome = $nome;
		$this->marca = $marca;
		$this->tipo = $tipo;
		$this->quantita = $quantita;
		$this->prezzo = $prezzo;
		$this->prezzoRiv = $prezzoRiv;
	}
}

function listaProdotti() {
	$result = eseguiQuery("SELECT * FROM Prodotti");
	if(!$result) {$prodotti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodotti = array();
		while($prodotto = mysqli_fetch_assoc($result)) {
			array_push($prodotti, new Prodotto($prodotto['CodProdotto'], $prodotto['Nome'], $prodotto['Marca'], $prodotto['Tipo'], $prodotto['Quantita'], $prodotto['Prezzo'], $prodotto['PRivendita']));
		}
	}
	return $prodotti; //è un array di Prodotti, non viene garantito che $prodotti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function aggiungiProdotto($nome, $marca, $tipo, $quantita, $prezzo=0, $prezzoRiv=0) { //marca e tipo possono eventualmente essere lasciati vuoti, quantita, prezzo e prezzoRiv di base sono 0
	cleanString($nome);
	if($nome === "") {return FALSE;} //un Prodotto senza nome, marca e tipo non ha senso
	cleanString($marca);
	if($marca === "") {return FALSE;}
	cleanString($tipo);
	if($tipo === "") {return FALSE;}
	if($quantita === "" || $quantita <= 0) {return FALSE;}
	if(!checkPrezzo($prezzo)) {$prezzo=0;}
	if(!checkPrezzo($prezzoRiv)) {$prezzoRiv=0;}
	return eseguiQuery("INSERT Prodotti(Nome, Marca, Tipo, Quantita, Prezzo, PRivendita) VALUES('$nome', '$marca', '$tipo', $quantita, $prezzo, $prezzoRiv)");
}

function eliminaProdotto($codice) {
	return eseguiQuery("DELETE FROM Prodotti WHERE CodProdotto=$codice");
}

function cambiaQuantitaProdotto($codice, $quantita) {
	if($quantita === "") {$quantita = 0;} //controllo di sicurezza che non dovrebbe mai essere eseguito
	return eseguiQuery("UPDATE Prodotti SET Quantita=$quantita WHERE CodProdotto=$codice");
}

function mostraProdotto($codice) {
	$prodotto = eseguiQuery("SELECT * FROM Prodotti WHERE CodProdotto=$codice");
	if(!$prodotto) {return NULL;}
	else {
		$prodotto = mysqli_fetch_assoc($prodotto);
		return new Prodotto($prodotto['CodProdotto'], $prodotto['Nome'], $prodotto['Marca'], $prodotto['Tipo'], $prodotto['Quantita'], $prodotto['Prezzo'], $prodotto['PRivendita']);
	}
}

/********************PRODOTTI APPUNTAMENTO********************/

class ProdottoAppuntamento {
	public $codProdotto;
	public $codAppuntamento;
	public $nome;
	public $marca;
	public $tipo;
	public $utilizzo; //numero prodotti usati durante l'appuntamento
	
	function __construct($codProdotto, $codAppuntamento, $nome, $marca, $tipo, $utilizzo) {
		$this->codProdotto = $codProdotto;
		$this->codAppuntamento = $codAppuntamento;
		$this->nome = $nome;
		$this->marca = $marca;
		$this->tipo = $tipo;
		$this->utilizzo = $utilizzo;
	}
}

function listaTotaleProdottiAppuntamento($codAppuntamento) {
	$result = eseguiQuery("
		SELECT Prodotti.CodProdotto as CodProdotto, Nome, Marca, Tipo, coalesce(Utilizzo,0) as Utilizzo, $codAppuntamento as CodAppuntamento
		FROM Prodotti LEFT JOIN (select * from ProdApp pa WHERE pa.CodAppuntamento=$codAppuntamento) AS pa 
		ON Prodotti.CodProdotto=pa.CodProdotto");
	if(!$result) {$prodottiApp = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodottiApp = array();
		while($prodottoApp = mysqli_fetch_assoc($result)) {
			array_push($prodottiApp, new ProdottoAppuntamento($prodottoApp['CodProdotto'], $prodottoApp['CodAppuntamento'], $prodottoApp['Nome'], $prodottoApp['Marca'], $prodottoApp['Tipo'], $prodottoApp['Utilizzo']));
		}
	}
	return $prodottiApp; //è un array di ProdottoAppuntamento, non viene garantito che $prodottiApp sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function listaProdottiAppuntamento($codAppuntamento) {
	$result = eseguiQuery("SELECT ProdApp.CodProdotto, CodAppuntamento, Nome, Marca, Tipo, Utilizzo
	FROM ProdApp JOIN Prodotti ON ProdApp.CodProdotto=Prodotti.CodProdotto
	WHERE CodAppuntamento=$codAppuntamento");
	if(!$result) {$prodottiApp = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodottiApp = array();
		while($prodottoApp = mysqli_fetch_assoc($result)) {
			array_push($prodottiApp, new ProdottoAppuntamento($prodottoApp['CodProdotto'], $prodottoApp['CodAppuntamento'], $prodottoApp['Nome'], $prodottoApp['Marca'], $prodottoApp['Tipo'], $prodottoApp['Utilizzo']));
		}
	}
	return $prodottiApp; //è un array di ProdottoAppuntamento, non viene garantito che $prodottiApp sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function listaProdottiAppuntamentoMax() {
	$result = eseguiQuery("SELECT ProdApp.CodProdotto, CodAppuntamento, Nome, Marca, Tipo, Utilizzo
	FROM ProdApp JOIN Prodotti ON ProdApp.CodProdotto=Prodotti.CodProdotto
	ORDER BY Utilizzo DESC LIMIT 10");
	if(!$result) {$prodottiApp = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodottiApp = array();
		while($prodottoApp = mysqli_fetch_assoc($result)) {
			array_push($prodottiApp, new ProdottoAppuntamento($prodottoApp['CodProdotto'], $prodottoApp['CodAppuntamento'], $prodottoApp['Nome'], $prodottoApp['Marca'], $prodottoApp['Tipo'], $prodottoApp['Utilizzo']));
		}
	}
	return $prodottiApp; //è un array di ProdottoAppuntamento, non viene garantito che $prodottiApp sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function cambiaUtilizzoProdottiAppuntamento($codAppuntamento, $codProdotto, $utilizzo) {
	$conn = dbconnect();
	$result = $conn->query("SELECT CodProdotto FROM ProdApp WHERE CodProdotto=$codProdotto AND CodAppuntamento=$codAppuntamento");
	if(!isset($result)) {$fatto = FALSE;} //c'è stato un errore
	else if($result->num_rows == 0) { //il prodotto non c'è
		$fatto = $conn->query("INSERT INTO ProdApp(CodProdotto, CodAppuntamento, Utilizzo) VALUES ($codProdotto, $codAppuntamento, $utilizzo)");
	}
	else { //il prodotto c'è già
		$fatto = $conn->query("UPDATE ProdApp SET Utilizzo=$utilizzo WHERE CodAppuntamento=$codAppuntamento AND CodProdotto=$codProdotto");
	}
	$conn->close();
	return $fatto;
}

function getAppuntamentiData($date){
	if(preg_match("/([0-9]{1,2})[-]([0-9]{2})[-]([0-9]{4})/", $date)){
		$ts = date_format(date_create_from_format("d-m-Y", $date), "Y-m-d");
		$result = eseguiQuery("SELECT CodAppuntamento, DataOra, NomeTipo, Costo, Sconto, Nome, Cognome, Telefono, Email
		FROM Appuntamenti JOIN TipoAppuntamento ON Appuntamenti.CodTipoAppuntamento=TipoAppuntamento.CodTipoAppuntamento JOIN Clienti ON Appuntamenti.CodCliente=Clienti.CodCliente
		WHERE DATE(DataOra)='$ts'
		ORDER BY DataOra DESC");
		if(!$result) {$appuntamenti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
		else {
			$appuntamenti = array();
			while($appuntamento = mysqli_fetch_assoc($result)) {
				if($appuntamento['DataOra'] !== NULL) {
					$time = strtotime($appuntamento['DataOra']);
					$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
					$ora = date("H:i", $time); //formato del tipo 23:46
				}
				else {$data = NULL; $ora = NULL;}
				$prezzo = round($appuntamento['Costo'] * ((100-$appuntamento['Sconto'])/100), 2); //round arrotonda il numero alla seconda cifra decimale
				array_push($appuntamenti, new Appuntamento($appuntamento['CodAppuntamento'], $data, $ora, $appuntamento['NomeTipo'], $prezzo, $appuntamento['Nome'], $appuntamento['Cognome'], $appuntamento['Telefono'], $appuntamento['Email']));
			}
		}
	return $appuntamenti;
	}	
	else return false;
}

function giorno($day)
{
	$giorni = array(0=>'Domenica',
	'Lunedì',
	'Martedì',
	'Mercoledì',
	'Giovedì',
	'Venerdì',
	'Sabato');
	return $giorni[$day];
}

function appuntamentiSettimana($date) {
	if(preg_match("/([0-9]{1,2})[-]([0-9]{2})[-]([0-9]{4})/", $date)){
		$ts = strtotime($date);
	    $start = strtotime('monday this week', $ts);
	    $end=strtotime('sunday this week', $start);
		$result= array();
		//start date
		$date = date('Y-m-d',$start);
		// End date
		$end_date = date('Y-m-d',$end);

		while (strtotime($date) <= strtotime($end_date)) {
	        $i=date('d-m-Y',strtotime($date));
	        $app_date=getAppuntamentiData($i);
	        $p_date=giorno(date('w', strtotime($date))) . " ". date('d/m/Y',strtotime($date));
	        array_push($result, array($p_date, $app_date ));
	        $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}
		return $result;
	}	
	else{
		//echo "La data non soddisfa il formato richiesto";
	 return false;
	}
}


/*****************************IMMAGINI***************************/

class Immagine {
	public $codice;
	public $nome;
	public $descrizione;
	
	function __construct($codice, $nome, $descrizione) {
		$this->codice = $codice;
		$this->nome = $nome;
		$this->descrizione = $descrizione;
	}
}

function mostraImmagine($codice) {
	$immagine = eseguiQuery("SELECT * from Images where Img_title='$codice'");
	if(!$immagine) {return NULL;}
	else {
		$immagine = mysqli_fetch_assoc($immagine);
		return new Immagine($immagine['Img_title'], $immagine['Img_filename'], $immagine['Img_desc']);
	}
}

function listaImmagini() {
	$result = eseguiQuery("SELECT * FROM Images");
	if(!$result) {$immagini = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$immagini = array();
		while($immagine = mysqli_fetch_assoc($result)) {
			array_push($immagini, new Immagine($immagine['Img_title'], $immagine['Img_filename'], $immagine['Img_desc']));
		}
	}
	return $immagini; //è un array di Immagini, non viene garantito che $immagini sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function aggiungiImmagine($img_desc, $userfile) { //dovrebbe bastarmi sapere la descrizione e il nome HTML
	if (($userfile["type"] == "image/gif" || $userfile["type"] == "image/jpeg" || $userfile["type"] == "image/jpg" || $userfile["type"] == "image/pjpeg"|| $userfile["type"] == "image/png" && $userfile["size"] < 20000)) {
            if ($userfile["error"] > 0) {
                //echo "<p class=\"inforesult\">Un errore si è presentato durante il caricamento: <span lang=\"en\">" . $userfile["error"] . "</span></p>";
                return FALSE;
            } else {

                $conn         = dbconnect();
                $i            = 1;
                $success      = false;
                $new_img_name = str_replace(' ', '', $userfile['name']);
                while (!$success) {
                    if (file_exists("uploads/" . $new_img_name)) {
                        $i++;
                        $new_img_name = "$i" . $new_img_name;
                    } else {
                        $success = true;
                    }
                }
                $ris=move_uploaded_file($userfile["tmp_name"], "uploads/" . $new_img_name);
                if (!$ris){
                	$conn->close();
                	return false;
                }
				cleanString($img_desc);
                $qry = "INSERT INTO Images(Img_desc,Img_filename) VALUES('$img_desc','$new_img_name')";
                if (!mysqli_query($conn, $qry)) {
                	$conn->close();
                    return false;
                } else {
                	$conn->close();
                    return true;
                }
            }
        }
}

function eliminaImmagine($codice) {
	$conn = dbconnect();
	$nome = $conn->query("SELECT Img_filename FROM Images WHERE Img_title=$codice");
	if($nome) {
		$nome = mysqli_fetch_assoc($nome);
		$nome = $nome['Img_filename'];
		if (file_exists("uploads/$nome")){
			$esito = unlink("uploads/$nome"); //elimina il file, restituisce TRUE se l'operazione ha esito positivo, FALSE altrimenti
			if(!$esito){
				$conn->close();
				return FALSE;
			}
		}
		$esito = $conn->query("DELETE FROM Images WHERE Img_title=$codice");
		$conn->close();
		if($esito) {
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	$conn->close();
	return FALSE;
}

function modificaImmagine($codice, $descrizione, $userfile="") {
	if($userfile!=""){
		if (!is_uploaded_file($userfile['tmp_name'])) { //controllo che non siano avvenuti errori
			return FALSE;
		}
		if (($userfile["type"] == "image/gif" || $userfile["type"] == "image/jpeg" || $userfile["type"] == "image/jpg" || $userfile["type"] == "image/pjpeg"|| $userfile["type"] == "image/png" && $userfile["size"] < 20000)) {
            if ($userfile["error"] > 0) {
                return false;
            } else {
                $conn = dbconnect();
                $i = 1;
                $success = false;
                $new_img_name = $userfile['name'];
                while (!$success) {
                    if (file_exists("uploads/" . $new_img_name)) {
                        $i++;
                        $new_img_name = "$i" . $img_name;
                    } else {
                        $success = true;
                    }
                }
                $ris=move_uploaded_file($userfile["tmp_name"], "uploads/" . $new_img_name);
                if (!$ris) {
                	$conn->close();
                	return false;
                }
                else {
                    if(!eliminaImmagine($codice)){
                    	$conn->close();
                    	return false;
                    }
					cleanString($descrizione);
                    $qry = "INSERT INTO Images(Img_title, Img_desc, Img_filename) VALUES('$codice', '$descrizione','$new_img_name')";
                    return eseguiQuery($qry);
                }
            }
        }

	} 
	else {
		cleanString($descrizione);
		return eseguiQuery("UPDATE Images SET Img_desc='$descrizione' WHERE Img_title='$codice'");
	}

}

/*******************************QUERY****************************/

function prodottiInEsaurimento() {
	$result = eseguiQuery("SELECT * FROM Prodotti WHERE quantita<6 AND quantita!=0");
	if(!$result) {$prodotti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodotti = array();
		while($prodotto = mysqli_fetch_assoc($result)) {
			array_push($prodotti, new Prodotto($prodotto['CodProdotto'], $prodotto['Nome'], $prodotto['Marca'], $prodotto['Tipo'], $prodotto['Quantita'], $prodotto['Prezzo'], $prodotto['PRivendita']));
		}
	}
	return $prodotti; //è un array di Prodotti, non viene garantito che $prodotti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function elencoClientiCompleanni() {
	$result = eseguiQuery("
		SELECT *
		FROM Clienti 
		WHERE MONTH(DataNascita)*100+DAY(DataNascita) BETWEEN MONTH(CURDATE())*100+DAY(CURDATE()) AND MONTH(ADDDATE(CURDATE(), INTERVAL 31 DAY))*100+DAY(ADDDATE(CURDATE(), INTERVAL 31 DAY))
		");
	if(!$result) {$clienti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$clienti = array();
		while($cliente = mysqli_fetch_assoc($result)) {
			if($cliente['DataNascita'] !== NULL) {
				$time = strtotime($cliente['DataNascita']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			}
			else {$data = NULL;}
			array_push($clienti, new Cliente($cliente['CodCliente'], $cliente['Nome'], $cliente['Cognome'], $cliente['Telefono'], $cliente['Email'], $data));
		}
	}
	return $clienti; //è un array di Clienti, non viene garantito che $clienti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

class StatisticaTipoAppuntamento {
	public $quantita;
	public $percentuale;
	public $nomeTipo;
	
	function __construct($quantita, $percentuale, $nomeTipo) {
		$this->quantita = $quantita;
		$this->percentuale = $percentuale;
		$this->nomeTipo = $nomeTipo;
	}
}

function listaAppuntamentiPerTipo() {
	$result = eseguiQuery("SELECT p.Parziali AS Quantità, CONCAT((TRUNCATE((p.Parziali/(SELECT SUM(Parziali) FROM Contatori))*100, 2)), ' %')  AS Percentuale, NomeTipo AS 'Tipo Appuntamento' FROM Contatori p JOIN TipoAppuntamento ta on p.Tipo =ta.CodTipoAppuntamento GROUP BY p.Tipo");
	if(!$result) {$tipi = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$tipi = array();
		while($tipo = mysqli_fetch_assoc($result)) {
			array_push($tipi, new StatisticaTipoAppuntamento($tipo['Quantità'], $tipo['Percentuale'], $tipo['Tipo Appuntamento']));
		}
	}
	return $tipi; //è un array di StatisticaTipoAppuntamento, non viene garantito che $tipi sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

class ProdottoAppuntamentoDatato extends ProdottoAppuntamento {
	public $data;
	public $ora;
	
	function __construct($codProdotto, $codAppuntamento, $nome, $marca, $tipo, $utilizzo, $data, $ora) {
		parent::__construct($codProdotto, $codAppuntamento, $nome, $marca, $tipo, $utilizzo);
		$this->data = $data;
		$this->ora = $ora;
	}
}

function listaProdottiAppuntamentoDatato($codCliente) {
	$result = eseguiQuery("SELECT p.CodProdotto as CodProdotto, Nome, Marca, Tipo, coalesce(Utilizzo,0) as Utilizzo, pa.CodAppuntamento AS CodAppuntamento, DataOra
	FROM Prodotti p JOIN ProdApp pa ON p.CodProdotto=pa.CodProdotto JOIN Appuntamenti a ON a.CodAppuntamento=pa.CodAppuntamento
	WHERE CodCliente = '$codCliente'");
	if(!$result) {$prodottiAppDat = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$prodottiAppDat = array();
		while($prodottoAppDat = mysqli_fetch_assoc($result)) {
			if($prodottoAppDat['DataOra'] !== NULL) {
				$time = strtotime($prodottoAppDat['DataOra']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
				$ora = date("H:i", $time); //formato del tipo 23:46
			}
			else {$data = NULL; $ora = NULL;}
			array_push($prodottiAppDat, new ProdottoAppuntamentoDatato($prodottoAppDat['CodProdotto'], $prodottoAppDat['CodAppuntamento'], $prodottoAppDat['Nome'], $prodottoAppDat['Marca'], $prodottoAppDat['Tipo'], $prodottoAppDat['Utilizzo'], $data, $ora));
		}
	}
	return $prodottiAppDat; //è un array di ProdottoAppuntamentoDatato, non viene garantito che $prodottiAppDat sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}

function cambiaPassword($username, $nuovaPassword) {
	cleanString($username);
	cleanString($nuovaPassword);
	return eseguiQuery("UPDATE Account SET password='$nuovaPassword' WHERE username='$username'");
}

function AppuntamentiDataCliente($codCliente="", $data="", $ora="") {
	$query = "SELECT CodAppuntamento, DataOra, NomeTipo, Costo, Sconto, Nome, Cognome, Telefono, Email
		FROM Appuntamenti JOIN TipoAppuntamento ON Appuntamenti.CodTipoAppuntamento=TipoAppuntamento.CodTipoAppuntamento JOIN Clienti ON Appuntamenti.CodCliente=Clienti.CodCliente";
	$where = "";
	if($codCliente != "") {
		$where.= "Appuntamenti.CodCliente=$codCliente";
	}
	if(preg_match("#^[0-9]{2}[/]{1}[0-9]{2}[/]{1}[0-9]{4}$#", $data)) {
		$data = date_format(date_create_from_format("d/m/Y", $data), "Y-m-d");
		checkAndWhere($where);
		$where .= "DATE(DataOra)='$data' ";
		if($ora!="" AND preg_match("/[0-9]{1,2}[:][0-9]{2}/", $ora)) {
			$hh = substr($ora, 0, 2);
			$where .= "AND HOUR(DataOra)>='$hh'";
		}
	}
	$result = eseguiQuery("$query WHERE $where ORDER BY DataOra DESC");
	if(!$result) {$appuntamenti = NULL;} //il valore NULL segnala che c'è stato un errore nella connessione o nell'esecuzione della query
	else {
		$appuntamenti = array();
		while($appuntamento = mysqli_fetch_assoc($result)) {
			if($appuntamento['DataOra'] !== NULL) {
				$time = strtotime($appuntamento['DataOra']);
				$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
				$ora = date("H:i", $time); //formato del tipo 23:46
			}
			else {$data = NULL; $ora = NULL;}
			$prezzo = round($appuntamento['Costo'] * ((100-$appuntamento['Sconto'])/100), 2); //round arrotonda il numero alla seconda cifra decimale
			array_push($appuntamenti, new Appuntamento($appuntamento['CodAppuntamento'], $data, $ora, $appuntamento['NomeTipo'], $prezzo, $appuntamento['Nome'], $appuntamento['Cognome'], $appuntamento['Telefono'], $appuntamento['Email']));
		}
	}
	return $appuntamenti; //è un array di Appuntamenti, non viene garantito che $appuntamenti sia stato effettivamente istanziato perché potrebbero esserci stato un errore
}
?>