<?php
// Sets a PHPSessionCookie to remember user
session_start();
if(isset($_COOKIE['AuthToken']))
{
	$authToken = $_COOKIE['AuthToken'];
}
else{
	$cookieexpiry = time() + (60*60*24*30);
	$authToken = $_SESSION['membershipnumber'] . date("YmdHis") . sprintf("%05d", rand(0, 99999));

	setcookie("AuthToken", $authToken, $cookieexpiry, "/");
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<title>Good Beer Guide - Voting</title>
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

	 		}
			function check_uncheck_checkbox(isChecked) {
				if(isChecked) {
					$('input[id="GBGPubs"]').each(function() {
						this.checked = true;
					});
				} else {
					$('input[id="GBGPubs"]').each(function() {
						this.checked = false;
					});
				}
			}

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
		<h2>Good Beer Guide Voting</h2>
		<p>Simply select the Pub(s) you wish to vote for to appear in the 2020 Good Beer Guide, from the table below. Remember, you can vote for as many Pub(s) as you wish, but you must have visted them within the last 12 months. You can also search using the Filter box also shown below. This will filter the list and any Pub(s) selected will remain selected during this process.</p>
		<p>When you have finished making your selection, simply click "Submit" and your selections will be stored. Once you have submitted your selection, you will recieve an email confirmation of your selection. Should you wish to re-vote, then this can be done at any time, by simply re-visiting this page, any previously Pub(s) submited will be shown below.</p>
		<p>Your registered email address will <i>not</i> be stored, and is only used for confirming your selection.<p>
		<p></p>
		<script type="javascript" src="//code.jquery.com/jquery-1.12.4.min.js"></script>
		<script src='ddtf.js'></script>
		<script>$('#GBGNomination').ddTableFilter();</script>
		<?php
		//load login page...
		include('./login.php');

		// $authlevel = $_SESSION['authlevel'];
		if(isset($_SESSION['membershipnumber']))
		{
			// Only show for South Beds Members
			if ($_SESSION['branch']=="BES")
				{
		  		$_SESSION['timeout'] = time() + $sessionduration;
		  		ShowSessionLogout();
					$memno = $_SESSION['membershipnumber'];
					$query = "SELECT p.PubID, p.Name, p.Town, g.Removed FROM pubdatabase AS p, GBG_Pubs_2019 AS g WHERE p.PubID = g.PubID AND p.RealAle='yes' AND g.Removed IS NULL ORDER BY p.Town";
					$result = mysqli_query($link, $query);
					echo "<p>";

					// If user is an admin, show link to view results.
					global $authlevel;
					if($authlevel == "1"){
						echo "<p>Click <a href='gbg_results.php?auth=1'>here</a> to view the current nomination results.</p>";
					}

					// Retrieves previously submitted pubs by the user.
					$querypubssubmitted = "SELECT MembershipNumber, JSONEnc FROM GBGVotes WHERE MembershipNumber = '$memno' LIMIT 1";

					$queryresult = mysqli_query($link, $querypubssubmitted);
					if (mysqli_num_rows($queryresult)<>0)
					{
						while($row2 = mysqli_fetch_array($queryresult ))
				    {
							$oldjsonid = $row2['JSONEnc'];
							$decodedids = json_decode($row2['JSONEnc'],true);
							$pubcount = count($decodedids);
						}
						echo "<p>You have already nominated $pubcount Pub(s). Your selections have been re-applied to the below table. Simply review your selections below, and then re-submit. Your new changes will then be saved, including any new selections. </p>";
					}
					else {
						$notvoted = 1;
					}
					// Filter the current list by town or Pub Name.
					// Filtering provided by JavaScript function above..
					echo "<div class='input-group'> <span class='input-group-addon'>Filter</span>";
						echo "<input id='filter' name='filter' type='text' class='form-control' placeholder='Search here...'>";
							echo "<div class='input-group-btn'>";
								echo "<button class='btn btn-default eraser' onclick='ClearFields();'>";
									echo "<i class='fa fa-eraser' aria-hidden='true'></i>";
								echo "</button>";
							echo "</div>";
					echo "</div>";
					echo "<p></p>";
					echo "<table id='GBGNomination' class='table table-sm gbgvoting'>
								<tr><td><input type='checkbox' name='checkall' id='checkall' onClick='check_uncheck_checkbox(this.checked);' /> Check/UnCheck All</td></tr>
								</table>";
					echo "<p></p>";

					//Echo Pubs eligible for nomination table created
					echo "<table id='GBGNomination' class='table table-bordered table-sm table-hover beerfest'>
					<tr>
					<thead>
					<th>PubID</th>
					<th>Pub</th>
					<th>Location</th>
					<th class='GBGNomhead'>GBG Nomination<div class='GBGNom-tip'><i class='far fa-question-circle'><p><span class='GBGtiptext'>Tick the box for the Pub(s) that should be entered into the GBG.</span></p></i></div></th>
					</thead>
					</tr>";
					echo "<tbody>";
					echo "<form action='./gbg-submit.php' method='post' name='GBGNom' id='GBGNom'>";
					// Process each pub one by one.
					while($row = mysqli_fetch_array($result))
					{
						echo "<tr id='filter'>";
						echo "<td>" . $row['PubID'] . "</td>";
						$pubid = $row['PubID'];
						echo "<td>" . $row['Name'] . "</td>";
						echo "<td>" . $row['Town'] . "</td>";
						if ($notvoted == 1 )
						{
							echo "<td><input type='checkbox' name='GBGPubs[]' value='$pubid'></td>";
						}
						else {
							// If pub is in the array, automatically select tickbox as the user has already voted for this pub..
							if (in_array($pubid,$decodedids))
							{
								echo "<td><input id='GBGPubs' type='checkbox' name='GBGPubs[]' value='$pubid' checked='checked'></td>";
								echo "<input type='hidden' name='oldjsonid' value='$oldjsonid'>";
							}
							// Else not checked..
							else {
								echo "<td><input id='GBGPubs' type='checkbox' name='GBGPubs[]' value='$pubid'></td>";
							}
						}
						echo "</tr>";
					}
					echo "</tbody>";
					echo "</table>";


					echo "<table class='table table-bordered table-sm bcffeedback'>";
						echo "<tr>";
							echo "<td>";
								echo "<div align='center'>";
									echo "<input class='button' name='Submit' type='submit' value='Submit' style='height:25px; width:75px'>";
								echo "</div>";
							echo "</td>";
						echo "</tr>";
						echo "<tr>";
							echo "<td>";
								echo "<div align='center'>";
									echo "<input id='select-all' class='button' name='Submit2' type='reset' value='Reset' style='height:25px; width:75px'>";
								echo "</div>";
							echo "</td>";
						echo "</tr>";
					echo "</form>";
				echo "</table>";
		}
		// User is not from the South Beds, so is not eligible to vote..
			else {
				ShowSessionLogout();
				echo "</br>";
				echo "<p class='loginerror'>Access Denied</p>";
				echo "<p>Unfortunately, Good Beer Guide & Pub of the Year voting is only open to South Bedfordshire Branch Members only.</p>";
				echo "<p>If you think you are being shown this in error and are a South Beds Member, please contact <a href='mailto:membership@camra.org.uk'>CAMRA Membership Enquiries</a> or the South Beds <a href='mailto:membership@southbeds.camra.org.uk'>Membership Secretary</a> who will be able to assist further.</p>";
			}
		}
		// User is not logged in, so show login page..
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
