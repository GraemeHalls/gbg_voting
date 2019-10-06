<?php
session_start();
if(isset($_COOKIE['AuthToken']))
{
	$authToken = $_COOKIE['AuthToken'];
	// echo "<p>" . $_COOKIE['AuthToken'] . "</p>;";
}
else{
	$cookieexpiry = time() + (60*60*24*30);
	$authToken = $_SESSION['membershipnumber'] . date("YmdHis") . sprintf("%05d", rand(0, 99999));
	// $_SESSION['authlevel'] == "0";
	// echo "<p>" . $authToken . "</p>";
	setcookie("AuthToken", $authToken, $cookieexpiry, "/");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Good Beer Guide - Results</title>
	<script type="text/javascript">
	</script>
	<script type="text/javascript" src="//code.jquery.com/jquery-1.12.4.min.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous" type="text/css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no"/>
	<link rel="stylesheet" href="/css/style.css" type="text/css" />
	<link media="(max-width: 900px)" href="/css/mobile-style.css" type="text/css" rel="stylesheet" />
    <link rel="icon" type="image/ico" href="/images/ico/sbeds favicon.ico" />
	<script type="text/javascript">
	 var _gaq = _gaq || [];
	 _gaq.push(['_setAccount', 'UA-19926804-1']);
	 _gaq.push(['_trackPageview']);

	 (function() {
	   var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	 })();
	</script>
	<script src="ddtf.js"></script>
	<script>
	$(document).ready(function () {
		$('#GBGNominations').ddTableFilter();
	});
	</script>
	<script type="text/javascript">
	(function(d, s, id) {
	   var js, fjs = d.getElementsByTagName(s)[0];
	   if (d.getElementById(id)) return;
	   js = d.createElement(s); js.id = id;
	   js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.5";
	   fjs.parentNode.insertBefore(js, fjs);
	   }(document, 'script', 'facebook-jssdk'));
		</script>
		<script type="text/javascript">
		$(document).ready(function () {
		    (function ($) {
		        $('#filter').keyup(function () {
		            var rex = new RegExp($(this).val(), 'i');
		            $('tr#filter').hide();
		            $('tr#filter').filter(function () {
		                return rex.test($(this).text());
		            }).show();
		            $('.hidden').filter(function () {
		                return rex.test($(this).text());
		            }).show();
		        })
		    }(jQuery));
			});
			function ClearFields() {
     		document.getElementById("filter").value = "";
				jQuery('tr#filter').show();
				//$('.hideRow tr').hide();
	 		}
			//document.getElementById("hideRow").hide();
			//document.getElementById("hideRow").style.display = 'none';
	</script>
</head>
<body>
	<div id="container">
		<div id="header">
			<?php include("../includes/header.html"); ?>
		</div>
		<div class="menu" id="menu">
			<?php include("../includes/menu.html"); ?>
		</div>
		<div class="cushycms" id="content">
		<h2>Good Beer Guide Results</h2>
    <p>Here are the current results of the 2020 Good Beer Guide Voting</p>
		<?php
		//load login page...
		include('./login.php');
		$authlevel = $_GET['auth'];
		 // $authlevel;
		global $authlevel;
    if(isset($_SESSION['membershipnumber']))
    {
			// Only show to South Beds members who are admins...
      if ($_SESSION['branch']=="BES" && $authlevel == "1")
        {
          $_SESSION['timeout'] = time() + $sessionduration;
          ShowSessionLogout();
          $memno = $_SESSION['membershipnumber'];
          $query = "SELECT JSONEnc FROM GBGVotes";
          $queryresult = mysqli_query($link, $query);
					$memcount = mysqli_num_rows($queryresult);

          // loop through all results in the DB..
          $votedpubs = array();
          while($row2 = mysqli_fetch_assoc($queryresult ))
          {
            // put all JSON results into a string, ready to be decoded.
            $thisjson = $row2['JSONEnc'];
						// Decode JSON into array..
            $thisarray = json_decode($thisjson, true);
            foreach ( $thisarray as $thispubid )
            {
							// Searches through $votedpubs array to see if it has been voted for before..
              $key = array_search($thispubid, array_column($votedpubs, 'pubid'));
							// If it hasnt been voted for, push PubID to the array..
              if ($key === FALSE)
              {
                array_push( $votedpubs, array ('pubid' => $thispubid, 'count' => 1));
								$sqlbuild .= "\"" . $thispubid . "\",";
              }
              else
              {
                $votedpubs[$key]['count']++;
              }
            }
          }

          $colpubid = array_column($votedpubs, 'pubid');
          $colcount = array_column($votedpubs, 'count');
					// Sort pubs in the array ready to be shown on the page..
          array_multisort ( $colcount, SORT_DESC, $colpubid, SORT_ASC, $votedpubs );

					$pubcount = count($colpubid);

					$sqlbuild = str_replace('/','\/',substr($sqlbuild,0,-1));

          $pubidquery = "SELECT PubID, Name, Town FROM pubdatabase WHERE PubID IN (" . $sqlbuild . ")";
					$pubidresult = mysqli_query($link, $pubidquery);

					// Display stats..
					echo "<p>";
					echo "<p>The running stats from this years Good Beer Guide Nominations are as follows:</p>";
					echo "<ul>";
					echo "<li>Total number of members who have voted: " . $memcount . "</li>";
					echo "<li>Total number of pubs nominated: " . $pubcount . "</li>";
					echo "</ul>";

					if (!$pubidresult)
					{
						echo "<p><strong>No votes yet recieved. Please check back later!</strong></p>\n";
					}
          echo "<table class='table table-bordered table-sm'>\n<tr><th>PubID</th><th>Count</th><th>Pub Name</th><th>Town</th></tr>\n";
					$votedpubs = array_column($votedpubs, null, 'pubid');
					while ($row3 = mysqli_fetch_array($pubidresult))
					{
					    $votedpubs[$row3['PubID']]['Name'] = $row3['Name'];
					    $votedpubs[$row3['PubID']]['Town'] = $row3['Town'];
					}
					// For each of the pubs voted for, show more information..
          foreach($votedpubs as $thispub )
          {
          		echo "<tr><td>" . $thispub['pubid'] . "</td><td>" . $thispub['count'] . "</td><td>" . $thispub['Name'] . "</td><td>" . $thispub['Town'] . "</td></tr>\n";
					}
          echo "</table>\n";

      }
      else {
				//user does not have permission to view this page..
        ShowSessionLogout();
        echo "</br>";
        echo "<p class='loginerror'>Access Denied</p>";
				echo "<p>You do not have access to view the results.</p>";
			}
    }
		else
		{
			ShowSessionLogin();
		}

  mysqli_close($link);
  ?>
  </div>
  <div id="right">
    <?php include("../includes/right.html"); ?>
  </div>
  <div style="clear:both;"></div>
  <div id="footer">
    <?php include("../includes/footer.html"); ?>
  </div>
</div>
</body>
</html>
