<?php
/*
index.php:
This is the main page. It switch every page of the website.
In this page I setup the not-logged user details and I create every page sending data to template.

Fantamanager

To Do:
-Require meta.lang.php
-Setup sessions


Included library:
 * Savant2.php that add the library for the template system
 * config.inc.php that contain the general configuration of the website
 * dblib.inc.php that defines database access function
 * authlib.inc.php that includes function to define the authorization
 * langlib.inc.php that defines functions for lang array

*/

$session_name = 'fantamanajer';
@session_name($session_name);
// strictly, PHP 4 since 4.4.2 would not need a verification
if (version_compare(PHP_VERSION, '5.1.2', 'lt') && isset($_COOKIE[$session_name]) && eregi("\r|\n", $_COOKIE[$session_name])) 
	die('attacked');

if (!isset($_COOKIE[$session_name])) 
{
	ob_start();
	$old_display_errors = ini_get('display_errors');
	$old_error_reporting = error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$r = session_start();
	ini_set('display_errors', $old_display_errors);
	error_reporting($old_error_reporting);
	unset($old_display_errors, $old_error_reporting);
	$session_error = ob_get_contents();
	ob_end_clean();
	if ($r !== TRUE || ! empty($session_error)) 
	{
		setcookie($session_name, '', 1);
		die('sessionError');
	}
}
else
	@session_start();
	
/*
 * Prevent XSS attach
 */

foreach($_POST as $key=>$val)
{
	if(is_array($val))
	{
		foreach($val as $key2=>$val2)
			$_POST[$key][$key2] = stripslashes(addslashes(htmlspecialchars($val2)));
	}
	else
		$_POST[$key] = stripslashes(addslashes(htmlspecialchars($val)));
}	
foreach($_GET as $key=>$val)
{
	if(is_array($val))
	{
		foreach($val as $key2=>$val2)
			$_GET[$key][$key2] = stripslashes(addslashes(htmlspecialchars($val2)));
	}
	else
		$_GET[$key] = stripslashes(addslashes(htmlspecialchars($val)));
}
require_once('config/config.inc.php');
require_once('config/Savant3.php');
require_once('config/pages.inc.php');
require_once(INCDIR . 'db.inc.php');
require_once(INCDIR . 'auth.inc.php');
require_once(INCDIR . 'strings.inc.php');
require_once(INCDIR . 'links.inc.php');



//Creating a new db istance
$dbLink = new db;
$dbLink->dbConnect();

//Creating object for pages
$layouttpl = new Savant3();
$headertpl = new Savant3();
$footertpl = new Savant3();
$contenttpl = new Savant3();
$operationtpl = new Savant3();
$navbartpl = new Savant3();

//Creating linksObj in object pages
$linksObj = new links();
$headertpl->assign('linksObj',$linksObj);
$footertpl->assign('linksObj',$linksObj);
$contenttpl->assign('linksObj',$linksObj);
$operationtpl->assign('linksObj',$linksObj);
$navbartpl->assign('linksObj',$linksObj);

//If no page have been required give the default page (home.php and home.tpl.php)
if (isset($_GET['p']))
	$p = $_GET['p'];
else
	$p = 'home';

//Adding the language

if (!isset($_SESSION['lang']))
	$_SESSION['lang'] = 'it';

//Try login if POSTDATA exists
require_once(CODEDIR . 'login.code.php');

if(isset($_POST['username']) && $_SESSION['logged'])
	header('Location: '. str_replace('&amp;','&',$linksObj->getLink('dettaglioSquadra',array('squadra'=>$_SESSION['idSquadra']))));

//Setting up the default user data
if (!isset($_SESSION['logged'])) {
	$_SESSION['userid'] = 1000;
	$_SESSION['roles'] = -1;
	$_SESSION['usertype'] = 'guest';
	$_SESSION['logged'] = FALSE;
	$_SESSION['idSquadra'] = FALSE;
	$_SESSION['idLega'] = 1;
	$_SESSION['legaView'] = 1;
}

require_once(INCDIR . 'lega.db.inc.php');
$legaObj = new lega();

/**
 * Eseguo i controlli per sapere se ci sono messaggi da comunicare all'utente e setto in sessione i dati di lega
 */

if ($_SESSION['logged'])
{
	require_once(INCDIR . 'giocatore.db.inc.php');
	require_once(INCDIR . 'trasferimento.db.inc.php');
	
	$giocatoreObj = new giocatore();
	$trasferimentoObj = new trasferimento();
	$_SESSION['datiLega'] = $legaObj->getLegaById($_SESSION['idLega']);
	if($giocatoreObj->getGiocatoriTrasferiti($_SESSION['idSquadra']) != FALSE && count($trasferimentoObj->getTrasferimentiByIdSquadra($_SESSION['idSquadra'])) < $_SESSION['datiLega']['numTrasferimenti'] )
		$layouttpl->assign('generalMessage','Un tuo giocatore non è più nella lista! Vai alla pagina trasferimenti');
}

/**
 * SETTO NEL CONTENTTPL LA GIORNATA
 */
require_once(INCDIR . 'giornata.db.inc.php');

$giornataObj = new giornata();
$giornata = $giornataObj->getGiornataByDate();

define("GIORNATA",$giornata['idGiornata']);
define("PARTITEINCORSO",$giornata['partiteInCorso']);
define("STAGIONEFINITA",$giornata['stagioneFinita']);


$leghe = $legaObj->getLeghe();
$layouttpl->assign('leghe',$leghe);
if(!isset($_SESSION['legaView']))
	$_SESSION['legaView'] = $leghe[0]['idLega'];
if(isset($_POST['legaView']))
	$_SESSION['legaView'] = $_POST['legaView'];
/**
 * INIZIALIZZAZIONE VARIABILI CONTENT
 * Questo Switch discrimina tra i vari moduli di codice quello che deve
 * essere caricato per visualizzare la pagina corretta
 *
 */

if(!isset($pages[$p])) 
{
	$message['level'] = 1;
	$message['text'] = "La pagina " . $p . " non esiste. Sei stato mandato alla home";
	$p = 'home';
}
elseif($pages[$p]['roles'] > $_SESSION['roles']) 
{
	$message['level'] = 1;
	$message['text'] = "Non hai l'autorizzazione necessaria per vedere la pagina " . strtolower($pages[$p]['title']) . ". Sei stato mandato alla home";
	$p = 'home';
}
if(isset($_SESSION['message']))
{
	$message = $_SESSION['message'];
	unset($_SESSION['message']);
}
if(!empty($message))
	$layouttpl->assign('message',$message);

//INCLUDE IL FILE DI CODICE PER LA PAGINA
if (file_exists(CODEDIR . $p . '.code.php'))
	require(CODEDIR . $p . '.code.php');
//definisce il file di template utilizzato per visualizzare questa pagina
$tplfile = TPLDIR . $p . '.tpl.php';


//ASSEGNO ALLA NAVBAR LA PAGINA IN CUI SIAMO
$navbartpl->assign('p',$p);
$navbartpl->assign('pages',$pages);
/**
 *
 * INIZIALIZZAZIONE VARIABILI HEAD (<html><head>...</head><body>
 *
 */
$layouttpl->assign('title',$pages[$p]['title']);
$layouttpl->assign('p',$p);
if(isset($pages[$p]['css']))
 	$layouttpl->assign('css', $pages[$p]['css']);
if(isset($pages[$p]['js']))
	$layouttpl->assign('js', $pages[$p]['js']);

/**
 * GENERAZIONE LAYOUT
 */

/**
 * PRODUZIONE HEADER
 * il require include il file con il codice per l'header, incluso il nome del file template
 */
$header = $headertpl->fetch(TPLDIR . 'header.tpl.php');

/**
 * PRODUZIONE FOOTER
 * il require include il file con il codice per il'footer, incluso il nome del file del file template
 */
//$footertpl->assign('p',$p);
$footer = $footertpl->fetch(TPLDIR . 'footer.tpl.php');

/**
 * PRODUZIONE MENU
 * il require include il file con il codice per il menu, incluso il nome del file del file template
 */

// $navbartpl->assign('p',$p);
$navbar = $navbartpl->fetch(TPLDIR . 'navbar.tpl.php');
/**
 * PRODUZIONE CONTENT
 * Esegue la fetch del template per l'area content
 */
$content = $contenttpl->fetch($tplfile);
$operation = "";
if($_SESSION['logged'])
	$operation .= $operationtpl->fetch(TPLDIR . "operazioni.tpl.php");
if(file_exists(TPLDIR . "operazioni/" . $p . ".tpl.php"))
		$operation .= $operationtpl->fetch(TPLDIR . "operazioni/" . $p . ".tpl.php");

/**
 * COMPOSIZIONE PAGINA
 */

$layouttpl->assign('header', $header);
$layouttpl->assign('footer', $footer);
$layouttpl->assign('content', $content);
$layouttpl->assign('operation', $operation);
$layouttpl->assign('navbar', $navbar);

/**
 * Output Pagina
 */

$result = $layouttpl->display(TPLDIR . 'layout.tpl.php');
// now test the result of the display() call.  if there was an
// error, this will tell you all about it.
if ($layouttpl->isError($result)) {
	echo "There was an error displaying the template. <pre>";
	print_r($result,1);
	echo "</pre>";
}

$dbLink->dbClose();
//echo "<pre>".print_r($_SESSION,1)."</pre>";
?>
