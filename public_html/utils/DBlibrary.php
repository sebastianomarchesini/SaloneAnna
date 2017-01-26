<?php
function dbconnect() {
	$host = "localhost";
	$user = "pgabelli";
	$pass = "bi9UJ9ohCoochei7";
	$db = "pgabelli";
	$conn=new mysqli($host, $user, $pass, $db);
	if($conn -> connect_errno)
		echo "Connessione fallita(".$conn -> connect_errno."): ".$conn -> connect_error;
	return $conn;
};

class Messaggio { //classe che rappresenta un messaggio
	public $contenuto;
	public $data;
	public $ora;
	public $daLeggere;
	public $email;
	public $nome;
	public $cognome;
	
	function __construct($contenuto, $data, $ora, $daLeggere, $email, $nome, $cognome) {
		$this->contenuto = $contenuto;
		$this->data = $data;
		$this->ora = $ora;
		$this->daLeggere = $daLeggere;
		$this->email = $email;
		$this->nome = $nome;
		$this->cognome = $cognome;
	}
}

function listaMessaggi() { //i messaggi verranno già ordinati dal più recente al più vecchio
	$conn = dbconnect();
	$query = 'SELECT Contenuto, DataOra, ToRead, Email, Nome, Cognome
	FROM Appuntamenti JOIN Clienti ON Appuntamenti.CodCliente = Clienti.CodCliente
	ORDER BY DataOra DESC';
	$result = $conn->query($query);
	if(!result) {$err = "Errore nella query: ".$conn->error."."; $conn->close();} //intanto segnalo così il caso, è da eliminare se l'errore viene gestito in locale
	else {
		$messaggi = array();
		while($messaggio = mysqli_fetch_assoc($result)) {
			$time = strtotime($messaggio['DataOra']);
			$data = date("d/m/Y", $time); //formato del tipo 05/01/2017
			$ora = date("H:i", $time); //formato del tipo 23:46
			array_push($messaggi, new Messaggio($messaggio['Contenuto'], $data, $ora, $messaggio['toRead'], $messaggio['Email'], $messaggio['Nome'], $messaggio['Cognome']));
		}
		$conn->close();
		return $messaggi; //è un array di Messaggi
	}
}
?>