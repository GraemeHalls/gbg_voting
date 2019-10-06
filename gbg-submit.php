<?php
session_start();
if(isset($_SESSION['timeout']))
{
  if(time() > $_SESSION['timeout'])
  {
    DestroySession();
  }
}

function DestroySession()
{
  global $link;

  //    echo "<p><b>##### DESTROYING THE SESSION #####</b></p>\n";
    unset($_SESSION['title']);
    unset($_SESSION['name']);
    unset($_SESSION['firstname']);
    unset($_SESSION['surname']);
    unset($_SESSION['membershipnumber']);
    unset($_SESSION['branch']);
    unset($_SESSION['branchdesc']);
    unset($_SESSION['blo']);
    unset($_SESSION['email']);
    unset($_SESSION['region']);

    $_SESSION['timeout'] = time() - 1;
    $_SESSION['privilege'] = (string)"0";
    unset($_SESSION['privilege']);

    session_unset();
    session_destroy();
    $_SESSION = array();

}

function ShowSessionLogin()
{
  global $referer;
  global $authresultmessage;

  if(isset($_SESSION['membershipnumber'])) // double-check that we're logged in (or not!)
  {
    ShowSessionLogout();
  }
  else
  {
    echo $authresultmessage;
    echo "<form method='post' action='" . str_replace('&', '&amp;', $_SERVER['REQUEST_URI']) . "' id='login' name='sessionlogin'>\n";
    echo "Some pages on this site require you to log in with your CAMRA Member Login:&nbsp;<br />";
    echo "<div><b>Member No.:</b> <input type='text' name='username' size='10' /> ";
    echo "<b>Password:</b> <input type='password' name='password' size='10' /> ";
    echo "<input type='hidden' name='Referer' value='$referer' />\n";
    echo "<input type='hidden' name='do' value='login' />\n";
    echo "<input type='submit' id='loginButton' value='Log in' />";
    echo "</div>";
    echo "</form>\n";
  }

}

// $today = date('Y-m-d');
$unixtime = time();
$thankyou = "./thankyou.php";
$gbgvotingclosing = "17-01-2019";

$today = date('d-m-Y');
// printf("<p>$gbgvotingclosing</p><p>$today</p>");

if ($today >= $gbgvotingclosing) {
  printf("Unfortunately the voting for the 2020 Good Beer Guide has now closed! Please contact the Branch Secretary for more informtaion");
  exit;
}
// Creates confirmation email to sent to the member confirming their selection.
function sendemail($email,$unixtime,$GBGPubs)
{
  global $conn;
  global $dbhost;
  global $dbuser;
  global $dbpass;
  global $dbname;

	$sendemail = FALSE;
  // Send email to users email address.
	$to = "$email";
	$subject = "Good Beer Guide Voting Auto-Response";
	$headers = "From: no-reply@xxxx.org.uk\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

	$emailoutput .= "<html><body>";
	$emailoutput .= "<p>Hi " . $_SESSION['firstname'] . ",</p>";
	$emailoutput .= "<p>Thankyou for your Good Beer Guide nomination(s). This email is to confirm that you have voted for the following pub(s):</p>";
  $pubcount = 0;
  foreach ($_POST['GBGPubs'] as $pub)
  {
    $pubcount++;

    // lookup PubID in DB to pull back name & location..
    $escapedpub = str_replace('/', '\/', $pub);
    $query = "SELECT p.PubID, p.Name, p.Town FROM pubdatabase AS p WHERE p.PubID = '$escapedpub'" or die (mysql_error());

    $result = mysqli_query($conn, $query);

    while($row = mysqli_fetch_array($result))
    {
      $emailoutput .= "<p><b><u>Pub $pubcount</b></u></p>";
      $emailoutput .= "<p>Pub Name: " . $row['Name']. "</p>";
      $emailoutput .= "<p>Location: " . $row['Town']. "</p>";
      $emailoutput .= "<p></p>";
    }
  }

	$emailoutput .= "<p>Should you need to change your mind, you can re-submit at anypoint until the closing date. </p>";
	$emailoutput .= "<p>****PLEASE NOTE: This email was sent from an un-monitored account****</p>";
	$emailoutput .= "</body></html>";


	// Send the email
	if(mail($to, $subject, $emailoutput, $headers))
	{
	  printf("<p>Email sent successfully.</p>\n");
	}
	else
	{
	  printf("<p>Email send failed.</p>\n");
	}

}

require_once($_SERVER["DOCUMENT_ROOT"] . '../../../Inc/config.inc.php');

function starts_with_upper($str) {
	$chr = mb_substr ($str, 0, 11, "UTF-8");
    return mb_strtolower($chr, "UTF-8") != $chr;
}

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (mysqli_connect_errno()) {
    printf("Connect failed: %s<br />", mysqli_connect_error());
    exit();
}

if(isset($_SESSION['membershipnumber']))
{
  if(isset($_POST['GBGPubs'])){

  	$firstname = $_SESSION['firstname'];
  	$GBGPubs = $_POST['GBGPubs'];
    $email = $_SESSION['email'];

    $jsonid = mysqli_real_escape_string($conn,json_encode($GBGPubs, JSON_UNESCAPED_SLASHES));
    // Stores selection in DB
  	$sql = "REPLACE INTO GBGVotes (MembershipNumber, JSONEnc, TimeSubmitted) VALUES ( ";
  	$sqlvals = "\"" . $_SESSION['membershipnumber']. "\", ";
  	$sqlvals .= "\"" . $jsonid . "\", ";
    $sqlvals .= "now() );";
  }
  else {
    printf("You have not selected any pubs. Please try again. <br />");
    exit();
  }
  // combine into sql statement.
	$sql .= $sqlvals;
  // mysqli_real_escape_string($conn, $sql);

	if (!mysqli_query($conn, $sql)) {
	    printf("Error Entering Data: %s<br />", mysqli_sqlstate($conn));
			printf($sql);
	}
  else
  {
    sendemail($email,$unixtime,$GBGPubs);
  	//sendemail($outputfields);
    mysqli_close($conn);
    header('Location: '.$thankyou);
  }
}
?>
