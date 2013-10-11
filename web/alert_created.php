<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head profile="http://www.w3.org/2005/10/profile">
	<link href='http://fonts.googleapis.com/css?family=Geo|Lato:400,900,700,300|Ubuntu:400,700,500,300|Averia+Sans+Libre:400,700&subset=latin,latin-ext,greek,cyrillic' rel='stylesheet' type='text/css'>
	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>Keepr - data mining social media chatter | alert created</title>
  <link rel="icon" type="image/png" href="/favicon.png">
  <link rel="stylesheet" href="css/normalize.css" />
  <link rel="stylesheet" href="css/foundation.css" />
  <script src="js/vendor/custom.modernizr.js"></script>
</head>
<body style="font-family: 'Lato', sans-serif;">
<style>
a:hover {
	text-decoration:underline;	
}
</style>
	<div class="row">
		<div class="large-12 columns">
			<div class="row" style="background-color:#3B3131;padding-bottom:3px;">
				<div class="large-2 columns text-center" style="padding:2px;"><font style="font-family: 'Geo', sans-serif;font-size:56px;padding-left: 5px;"><a href='/demo/index.php'>Keepr</a></font></div>
				<div class="large-6 columns">
				</div>
			</div>	
			
			<form action="index.php" method="get" onSubmit="_gaq.push(['_trackEvent', 'SearchForm', 'click_submit', '<?php echo $query_string_full ?>']);">			
			  <div class="row" style="padding-top:15px;margin-top:10px;">

				<div class="large-12 columns text-left">
				  <div class="row collapse">
					<div class="large-6 columns">
										<?php
										if ($raw_query_string === $breaking_news_query) {
											echo '<input type="text" name="q" placeholder="e.g. obama" value="" style="color:#666;height:38px;font-size:16px;" />';							    
										}
										else {
											echo '<input type="text" name="q" value="'. $query_string_full .'" style="color:#333;height:38px;font-size:18px;"/>';
										}
										?>
					</div>
					<div class="small-6 columns text-left">
					  <input type="submit" value="Search" class="button prefix" style="width:80px;height:38px;padding-top:6px;font-size:14px;">
					</div>
				  </div>
				</div>
			  </div>							
			</form>			    
			<div class="row" style="background-color:#F7F7F7;padding-top: 12px;padding-bottom:20px;">
				<div class="large-12 columns text-center">

<?php

if (isset($_REQUEST['email']))
//if "email" is filled out, send email
  {
  //send email
  $email = $_REQUEST['email'] ;
  $keywords = $_REQUEST['qt'] ;
  $frequency = $_REQUEST['schedule_type'] ;
$url_static = "http://search.twitter.com/search.rss?q=";
$url_static_keepr = "http://keepr.com/index.php?q=";
$keywords_en = urlencode($keywords);
$url_full = $url_static . $keywords_en ."%20exclude:retweets";
$url_full_keepr = $url_static_keepr . $keywords_en;
$to = "hongjq@gmail.com";
$subject = "$email wants alert for \"$keywords\"";
$message = "Please create a new alert:  $keywords \n\nTwitter RSS feed \n" . $url_full . " \n\nRecipient email \n$email \n\nFrequecy $frequency \n\nKeepr link: $url_full_keepr";
$from = "bot@keepr.com";
$headers = "From: Keepr-REQUEST" ;
mail($to,$subject,$message,$headers);
echo "<h4>Success!</h4> <b>Alert created for \"<i>$keywords</i>\" </b><br><br>Please check your email ($email) for further instructions.<br><br>";
echo "<a href='#' class='button medium' onClick='window.history.back();return false;'>Go back</a><br>";
}
else {
	echo "Sorry... error, something went wrong - alert not created.";
}
?>

				
				</div>
			</div>
		</div>	
	</div>  
  

<div class="row">
<div class="large-12 columns text-center" style="margin-bottom: 25px;font-size:12px;font-weight:400">
<hr>
<a href="http://blog.keepr.com/" style="margin:3px;">About</a>
&nbsp;&nbsp;
<a href="https://hackpad.com/About-Keepr-9Ns5vU6e8V7" style="margin:3px;">Process</a>
&nbsp;&nbsp;
<a href="mailto:contact@keepr.com" style="margin:3px;">Contact</a>
</div>
</div>


<script>document.write('<script src='+('__proto__'in{}?'js/vendor/zepto':'js/vendor/jquery')+'.js><\/script>')</script>
<script src="js/foundation.min.js"></script>
<script src="js/keepr_lib.js"></script>
<script src="js/foundation/foundation.forms.js"></script>
<!--
  
  <script src="js/foundation/foundation.js"></script>
  
  <script src="js/foundation/foundation.alerts.js"></script>
  
  <script src="js/foundation/foundation.clearing.js"></script>
  
  <script src="js/foundation/foundation.cookie.js"></script>
  
  <script src="js/foundation/foundation.dropdown.js"></script>
  
  <script src="js/foundation/foundation.forms.js"></script>
  
  <script src="js/foundation/foundation.joyride.js"></script>
  
  <script src="js/foundation/foundation.magellan.js"></script>
  
  <script src="js/foundation/foundation.orbit.js"></script>
  
  <script src="js/foundation/foundation.placeholder.js"></script>
  
  <script src="js/foundation/foundation.reveal.js"></script>
  
  <script src="js/foundation/foundation.section.js"></script>
  
  <script src="js/foundation/foundation.tooltips.js"></script>
  
  <script src="js/foundation/foundation.topbar.js"></script>
  
  -->
<script>$(document).foundation();</script>
  
</body>
</html>
