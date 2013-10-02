<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head profile="http://www.w3.org/2005/10/profile">
	<link href='http://fonts.googleapis.com/css?family=Geo|Lato:400,900,700,300|Ubuntu:400,700,500,300' rel='stylesheet' type='text/css'>
	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
<?php    

$raw_query_string = htmlspecialchars($_POST['q']);
$breaking_news_query = "from:cnnbrk OR from:breakingnews OR from:AP+BREAKING: ";
if (!$raw_query_string) {
    $raw_query_string = htmlspecialchars($_GET['q']);
}
$raw_query_string = str_replace($breaking_news_query, "", $raw_query_string);
if (!$raw_query_string) {
    $raw_query_string = $breaking_news_query ;
    $query_string = $breaking_news_query ;
}
$query_string = $raw_query_string;
$query_string_encoded = urlencode($raw_query_string);
$raw_query_string_prev = htmlspecialchars($_GET['q_prev']);
$raw_query_string_prev = str_replace($breaking_news_query , "", $raw_query_string_prev);

$query_string_prev = $raw_query_string_prev;



if ($query_string_prev){
$query_string_prev_encoded = urlencode($raw_query_string_prev);;
$query_string_full =  $query_string_prev . " " . $query_string;
$query_string_full_encoded = $query_string_prev_encoded . "%20" . $query_string_encoded;
}
else {
	$query_string_full = $query_string;
	$query_string_full_encoded = $query_string_encoded;
}

//Set string variable to keep track of twitter user sources 
$major_mentioned_users = "";
$minor_mentioned_users = "";

//Extract twitter usernames in the query search string
$tw_handle_pattern = "/(@\w+)/";
preg_match_all($tw_handle_pattern, $query_string, $twitter_handles_arr, PREG_SET_ORDER);
foreach($twitter_handles_arr as $result) {
	$major_mentioned_users = $major_mentioned_users . substr($result[0], 1) . ",";
}

require_once ('codebird.php');

//Twitter OAuth Settings, enter your settings here:
$CONSUMER_KEY = 'YOUR_CONSUMER_KEY_FROM_TWITTER';
$CONSUMER_SECRET = 'YOUR_CONSUMER_SECRET_FROM_TWITTER';
$ACCESS_TOKEN = 'YOUR_TOKEN_FROM_TWITTER';
$ACCESS_TOKEN_SECRET = 'YOUR_TOKEN_SECRET_FROM_TWITTER';

//Get authenticated
Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
$cb = Codebird::getInstance();
$cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);


$params = array(
	'q' => $query_string_full . ' exclude:retweets',
	'result_type' => 'popular',
	'lang' => 'en',
	'count' => '10'
);
$data = $cb->search_tweets($params, true);
$data_json = json_encode($data);
$json_output = json_decode($data_json, true);


$params_2 = array(
	'q' => $query_string_full . ' exclude:retweets',
	'result_type' => 'recent',
	'lang' => 'en',	
	'count' => '100'
);
$data_2 = $cb->search_tweets($params_2, true);
$data_json_2 = json_encode($data_2);
$json_output_2 = json_decode($data_json_2, true);



//Output result in JSON, getting it ready for jQuery to process
//echo json_encode($data);




$full_string = " ";
$full_string_user = " ";
//$full_string_links = " ";

$full_string = $full_string . " " . $tw_text;
$related_links = array();
$names_arr = array();
$user_mention_arr = array();

foreach($json_output_2['statuses'] as $key => $result) {
    $tweet_id = $result[id];
    //$num_retweets = $result[metadata][recent_retweets];
    $tw_screenname = $result[from_user];
    $tw_screenname_display = $result[from_user_name];
    $tw_screenname_url = $result[profile_image_url];
    $tw_text = $result[text];
    $hash_tags_node = $result[entities][hashtags];
    $links_node = $result[entities][urls];
    $full_string = $full_string . " " . $tw_text;    
    $names_arr = get_name_entities($tw_text, $names_arr);
    
    if (!empty($hash_tags_node)){      
        foreach($hash_tags_node  as $key2 => $result2) {
            $full_string = $full_string . " " . $result2[text]  . " " . $result2[text] ;
        }
    }
    $user_mention_node = $result[entities][user_mentions];
    if (!empty($user_mention_node)){      
        foreach($user_mention_node  as $key3 => $result3) {
            //echo $result3[screen_name] . " ";
            $related_users_array[] = $result3[screen_name];
        }
    }   
    
   
    if (!empty($links_node)){   
        foreach($links_node  as $key4 => $result4) {
            $url_str = $result4[expanded_url];
            //$full_string_links = $full_string_links . " " . $url_str;
            if (strpos($url_str, $raw_query_string) === false) {
		$related_links[] = $url_str;
            }
        }
    }
   

}

foreach($json_output['statuses'] as $key2 => $result2) {
    $pop_user_mention_node = $result2[entities][user_mentions];
    if (!empty($pop_user_mention_node)){      
        foreach($pop_user_mention_node  as $key6 => $result6) {
            //echo $result3[screen_name] . " ";
            $related_users_array[] = $result6[screen_name];
            $related_users_array[] = $result6[screen_name];
        }
    }    
    $pop_links_node = $result2[entities][urls];  
    if (!empty($pop_links_node)){   
	foreach($pop_links_node  as $key5 => $result5) {
	    $url_str = $result5[expanded_url];
	    //$full_string_links = $full_string_links . " " . $url_str;
	    if (strpos($url_str, $raw_query_string) === false) {
		$related_links[] = $url_str;
		$related_links[] = $url_str;
	    }
	}
    }
}


$word_freq_array = get_freq($full_string);
$user_freq_array = get_freq($full_string_user);

$related_queries_array = gen_new_query_string($word_freq_array, $raw_query_string);
//$related_users_array = gen_user_string($user_freq_array);    
$names_array_counted = array_count_values($names_arr);
if (!empty($related_users_array)){
    $user_mention_arr = array_count_values($related_users_array);
    arsort($user_mention_arr);
}
if (!empty($related_links)){
    $related_links_arr_counted = array_count_values($related_links);
    arsort($related_links_arr_counted);
}





?>
  <title>Keepr data mining social media chatter -  <?php echo $query_string_full; ?></title>
  <link rel="icon" type="image/png" href="/favicon.png">
  <link rel="stylesheet" href="css/normalize.css" />
  <link rel="stylesheet" href="css/foundation.css" />
  <script src="js/vendor/custom.modernizr.js"></script>
  <script src="http://widgets.twimg.com/j/2/widget.js"></script>	
</head>
<body style="font-family: 'Lato', sans-serif;">
<style>
a:hover {
	text-decoration:underline;	
}
</style>
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

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
		</div>	
	</div>

	<div class="row">
		<div class="large-12 columns">
			<div class="row"  style="background-color:#E2F2F2;padding-bottom: 6px;padding-top: 10px; margin-top:-18px;">
				<div class="small-12 columns">
					<?php
					if (!empty($names_array_counted)){
					foreach ($names_array_counted as $name_string => $name_freq) {
					    if (strpos(strtolower($name_string),strtolower($query_string_full)) === false) {
						if (stristr($raw_query_string, $name_string) === false) {	
						    $full_url_string = "index.php?q=" . urlencode($name_string) . "&q_prev=" . $query_string_full_encoded;
						    if ($name_freq > 1) {
								echo "<a href='$full_url_string' class='large awesome' style='margin:3px;background-color: '>{$name_string}</a>";
								$subtopics_entities[]=$name_string . " " . $query_string_full;
								//echo " E3 $name_freq";
						    }
						    }	
						}
					    }
					}
					
					if (!empty($related_queries_array)){
					    foreach ($related_queries_array as $key_freq => $text_string) {
						$full_url_string = "index.php?q=" . urlencode($text_string) . "&q_prev=" . $query_string_full_encoded;
						if (strpos(strtolower($query_string_full),$text_string) === false) {
						    if ($key_freq > 20) {
							echo "<a href='$full_url_string' class='large awesome' style='margin:3px;'>{$text_string}</a>";
							$subtopics_entities[]=$text_string . " " . $query_string_full;
							//echo " K40 $key_freq";
						    }					    
						}    
					    }
					}					
					    
					if (!empty($names_array_counted)){
					foreach ($names_array_counted as $name_string => $name_freq) {
					    if (strpos(strtolower($name_string),strtolower($query_string_full)) === false) {
						if (stristr($raw_query_string, $name_string) ===FALSE AND stristr($query_string_full, $name_string) ===FALSE) {	
						    $full_url_string = "index.php?q=" . urlencode($name_string) . "&q_prev=" . $query_string_full_encoded;
						    if ($name_freq < 2) {
							echo "<a href='$full_url_string' class='medium awesome' style='margin:3px;background-color: '>{$name_string}</a>";
							//echo " E2 $name_freq";
						    }					        
						    }	
						}
					    }
					}
				
					if (!empty($related_queries_array)){
					    foreach ($related_queries_array as $key_freq => $text_string) {
						$full_url_string = "index.php?q=" . urlencode($text_string) . "&q_prev=" . $query_string_full_encoded;
						if (strpos(strtolower($query_string_full),$text_string) === false) {
						    if ($key_freq < 26) {
							echo "<a href='$full_url_string' class='small awesome' style='margin:3px;'>{$text_string}</a>";
							//echo " K00 $key_freq";
						    }						    
						}    
					    }
					}
					?>		
				</div>
			</div>
		</div>
	</div>	


	<div class="row" style="background-color:#E2F2F2;">
			<div class="large-4 columns">
				<div class="row" style="margin-top:8px; margin-left:-12px;">
				
				<?php
				
				if (!empty($related_links_arr_counted)){
					
					foreach (array_slice($related_links_arr_counted, 0, 3) as $url_string => $url_freq) {
						if ($url_freq > 1) {	
						    $full_url_string = "index.php?q=" . urlencode($url_string);
						    $display_url_string = substr($url_string,0,32);
						    $embed_link = "http://api.embed.ly/1/oembed?key=0c08c75737b3425db32d30a364884d07&url={$url_string}";
							$jsonurl_link = file_get_contents($embed_link);
							$json_output_link = json_decode($jsonurl_link,true);
							$link_url = $json_output_link[url];
							$news_source = strtoupper($json_output_link[provider_name]);
							$link_type = $json_output_link[type];
							if ($news_source !== "ADF") {							
								echo "<div style='width:318px;margin-bottom:12px;margin-left:0px;background-color:#FFF;padding:10px 10px 10px 10px;'>";
								echo "<div style='margin-bottom:6px;'>";
								if ($link_type !== "video") {
									echo "<div><a href='$link_url' target='_blank'><img src='" . $json_output_link[thumbnail_url] . "' width='100%'></a><br></div>";
								}
								else {
									echo "<div class='flex-video'>";
									echo $json_output_link[html];
									echo "</div>";
								}								
								echo "<a href='$link_url' target='_blank' style='font-size:20px;color:#666;font-weight:800'>" . $json_output_link[title] . "</a></div>";
								echo "<div style='margin-bottom:6px;'><span style='font-size:13px;color:#70707D'>" . $json_output_link[description] . "<br> source: {$news_source} </span></div>";
								echo "</div>";
							}						  
						}
					}
				}
				
				?>			
				</div>
			</div>
		
		<div class="large-4 columns">
			<div class="row">
		
				<?php
				if (!empty($json_output['statuses'])) {
					foreach(array_slice($json_output['statuses'], 0, 5) as $key => $result)  {
						$tweet_id = $result[id];
						$embed_html = get_tweet_embed($tweet_id);
						echo "<div style='margin-left:6px;'>";
						echo $embed_html;
						echo "</div>";	    
					}
				}
				?>
				
				<?php
					foreach(array_slice($json_output_2['statuses'], 0, 20) as $key => $result)  {
						$tweet_id = $result[id];
						$embed_html = get_tweet_embed($tweet_id);
						echo "<div>";
						echo $embed_html;
						echo "</div>";
					}
				?>	

			</div>
		</div>
	


	
		<div class="large-4 columns">
			<div class="row">
				<div class="large-12 columns text-left" style="margin-left:8px;">
					<?php    

					    if (!empty($user_mention_arr)){
						    foreach ($user_mention_arr  as $text_string => $user_freq) {
						    if (stristr($raw_query_string, $text_string) === false) {
							    if ("@".$text_string !== $raw_query_string AND $user_freq > 3 AND $text_string !== "YouTube" AND $text_string !== "ShareThis") {	
								//$user_query = "@" . $text_string;
								$major_mentioned_users = $major_mentioned_users . $text_string . ",";
								//$full_url_string = "index.php?q=" . urlencode($user_query) . "&q_prev=" . $query_string_full_encoded;
								//echo "<a href='$full_url_string' class='medium grey awesome' style='margin:3px;'>$user_query</a>";
								$subtopics[]=$text_string;
							    }
							}
						    }
					    }



					    if (!empty($user_mention_arr)){
						    foreach ($user_mention_arr  as $text_string => $user_freq) {
						    if (stristr($raw_query_string, $text_string) === false) {
							    if ("@".$text_string !== $raw_query_string AND $user_freq < 4 AND $user_freq > 1 AND $text_string !== "YouTube" AND $text_string !== "ShareThis") {	
								//$user_query = "@" . $text_string;
								$minor_mentioned_users = $minor_mentioned_users . $text_string . ",";
								//$full_url_string = "index.php?q=" . urlencode($user_query) . "&q_prev=" . $query_string_full_encoded;
								//echo "<a href='$full_url_string' class='small grey awesome' style='margin:3px;'>$user_query</a>";
								$subtopics[]=$text_string;
							    }
							}
						    }
					    }

					
					//echo $major_mentioned_users;
					$major_sources = substr($major_mentioned_users, 0, -1);
					if (trim($major_mentioned_users) !== "") {
					$params = array(
						'screen_name' => $major_sources,
					);
					$data_profiles = $cb->users_lookup($params, true);
					$data_json_profiles = json_encode($data_profiles);
					$json_output_profiles = json_decode($data_json_profiles, true);
					foreach(array_slice($json_output_profiles,0,-1) as $key => $result) {
						$user_name_disp = $result["name"];
						$user_desc_disp = $result["description"];
						$user_username = $result["screen_name"];
						$user_followers = $result["followers_count"];
						$user_image_url = $result["profile_image_url"];
						$user_location_dis = $result["location"];
						$user_tweet_id = $result["status"]["id"];
						echo "<div style='width:306px;margin-top:10px;margin-left:-10px;background-color:#FFF;padding:6px 6px 6px 6px;'><div style='float:left;margin-right:5px;'>";
						echo "<a class='th radius' href='http://www.twitter.com/{$user_username}' target='_blank'><img src='$user_image_url' width='72'></a></div>";
						echo "<div style='float:left;width:200px;margin-bottom:3px;'>";
						echo "<a href='http://www.twitter.com/{$user_username}' target='_blank'>" . $user_name_disp . "</a>";
						echo "<span style='font-size:12px;color:#70707D'>";
						if ($user_location_dis !== ""){
						echo " $user_location_dis";
						}
						echo "<div style='margin:3px 0px 4px 0px;line-height: 120%;'>" . $user_desc_disp . "</div>";
						$full_screenname_string = "@" . $user_username;
						$full_url_string = "index.php?q=" . urlencode($full_screenname_string) . "&q_prev=" . $query_string_full_encoded;
						echo "<a href='$full_url_string' class='medium grey awesome' style='margin:3px;'>$full_screenname_string</a>";						
						echo "</span></div>";
						echo "<div style='clear:both'></div>";
						//$embed_html_profile = get_tweet_embed_small($user_tweet_id);
				    	//echo "<div>";
				    	//echo $embed_html_profile;
				    	//echo "</div>";					
						echo "</div>";
						//$user_verified = $result["verified"];
						//$user_geo_on = $result["geo_enabled"];
						//echo $user_verified . "<br>";
					}
					}
				?>
				</div>
			</div>
		
		
			<div class="row">
			    <div class="large-12 columns text-left" style="padding-bottom:6px;margin-left:10px;">
			<?php    


	
				if (!empty($user_mention_arr)){
				    echo "<br>";
				    $minor_sources = substr($minor_mentioned_users, 0, -1);
				    if (trim($minor_mentioned_users) !== "") {
					$params = array(
						'screen_name' => $minor_sources,
					);
					$data_profiles = $cb->users_lookup($params, true);
					$data_json_profiles = json_encode($data_profiles);
					$json_output_profiles = json_decode($data_json_profiles, true);
					foreach(array_slice($json_output_profiles,0,-1) as $key => $result) {
						$user_name_disp = $result["name"];
						$user_desc_disp = $result["description"];
						$user_username = $result["screen_name"];
						$user_followers = $result["followers_count"];
						$user_image_url = $result["profile_image_url"];
						$user_location_dis = $result["location"];
						$user_tweet_id = $result["status"]["id"];
						echo "<div style='width:306px;margin-bottom:10px;margin-left:-10px;background-color:#FFF;padding:6px 6px 6px 6px;'><div style='float:left;margin-right:5px;'>";
						echo "<a class='th radius' href='http://www.twitter.com/{$user_username}' target='_blank'><img src='$user_image_url' width='72'></a></div>";
						echo "<div style='float:left;width:200px;margin-bottom:3px;'>";
						echo "<a href='http://www.twitter.com/{$user_username}' target='_blank'>" . $user_name_disp . "</a>";
						echo "<span style='font-size:12px;color:#70707D'>";
						if ($user_location_dis !== ""){
						echo " $user_location_dis";
						}
						echo "<div style='margin:3px 2px 3px 2px;line-height: 120%;'>" . $user_desc_disp . "</div>";
						$full_screenname_string = "@" . $user_username;
						$full_url_string = "index.php?q=" . urlencode($full_screenname_string) . "&q_prev=" . $query_string_full_encoded;
						echo "<a href='$full_url_string' class='medium grey awesome' style='margin:3px;'>$full_screenname_string</a>";						
						echo "</span></div>";
						echo "<div style='clear:both'></div>";
						//$embed_html_profile = get_tweet_embed_small($user_tweet_id);
				    	//echo "<div>";
				    	//echo $embed_html_profile;
				    	//echo "</div>";					
						echo "</div>";
						//$user_verified = $result["verified"];
						//$user_geo_on = $result["geo_enabled"];
						//echo $user_verified . "<br>";
					}				
					}		    		

			    }
				?>
				</div>
			</div>
			</div>

			</div>	
		</div>

	<div class="row">
	    <div class="large-12 columns text-center"  style="margin-bottom: 2px;">
	
		<hr>
		    <?php
		    if ($raw_query_string === $breaking_news_query) {
			//echo "<h6 style='color:#666;'>Keepr curates realtime news stories</h6>";
		    }
		    else {
			if ($query_string_prev){
				echo "<a class='small awesome' href='index.php?q={$query_string_prev}'>$query_string_prev</a> &nbsp; &rarr; ";
			    }
			    echo "<a class='small awesome' href='index.php?q={$query_string_encoded}'>$raw_query_string</a>";
		    }    
		    ?>		
	    </div>
	</div>
	
	
	<div class="row">
		<div class="large-12 columns text-center"  style="margin-bottom: 25px;font-size:12px;font-weight:400">
			<hr>
			<a href="http://blog.keepr.com/" style="margin:3px;">About</a>
			&nbsp;&nbsp;
			<a href="https://hackpad.com/About-Keepr-9Ns5vU6e8V7" style="margin:3px;">Process</a>

			&nbsp;&nbsp;
			<a href="mailto:contact@keepr.com" style="margin:3px;">Contact</a>
		</div>
	</div>
	

	
	

	
	
  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
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
  

  
  <script>
    $(document).foundation();
  </script>



<?php

function get_tweet_embed($tw_id) {
    //echo $tw_id."URL: https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=center&omit_script=true<br>";
    $JSON = file_get_contents("https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=left&omit_script=true&hide_media=false");
    $JSON_Data = json_decode($JSON,true);
    $tw_embed_code = $JSON_Data[html];
    return $tw_embed_code;
}

function get_tweet_embed_small($tw_id) {
    //echo $tw_id."URL: https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=center&omit_script=true<br>";
    $JSON = file_get_contents("https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=left&omit_script=true&hide_media=false&hide_thread=false");
    $JSON_Data = json_decode($JSON,true);
    $tw_embed_code = $JSON_Data[html];
    return $tw_embed_code;
}

function get_tweet_embed_embedly($tw_id) {
    //echo "<br>http://api.embed.ly/1/oembed?url=http%3A%2F%2Ftwitter.com%2Fembedly%2Fstatus%2F{$tw_id}&omit_script=true&maxwidth=300<br>";
    $JSON = file_get_contents("https://api.embed.ly/1/oembed?key=0c08c75737b3425db32d30a364884d07&url=http%3A%2F%2Ftwitter.com%2Fembedly%2Fstatus%2F{$tw_id}&omit_script=true&maxwidth=300");
    $JSON_Data = json_decode($JSON,true);
    $tw_embed_code = $JSON_Data[html];
    return $tw_embed_code;
}

function get_name_entities($tweet_string, $names_array) {
    $stopword_name = array("BBC ","Another ", "BREAKING ", "The ", "THE ", "Former ","An ", "New Post","TOP STORIES","BBC " ,"Which ","Hey ", "This ","That ","These ", "Would ","You ", "RT ", " RT", "RSS ", "CNN", "Breaking News", "BREAKING NEWS","My ", "Did ", "About ", "We ", "Not ","Into ","Is ", "Only ", "If ", "So ", "THIS ", "His ","It ", "Of ", "Will ","Please ","Can ","In ", "On ", "And ", "Why","MUST", "From", "Some ", "After ", "With ", "Latest ", "Jokes" , "FUCK", " The", "LUNCH ", "Watching", " Can ", "For ","Was ","Get ","Very ", "What ","Does ","Here ", "Have ","DTN " , "How ", "Are ", " I ","Can ", "As ", "All ", "Although ");
    $rexSafety = "/^([A-Z][\w-]*(\s+[A-Z][\w-]*)+)/";
    $strings2 = preg_split($rexSafety, $tweet_string, 0, PREG_SPLIT_OFFSET_CAPTURE);
    if (count($strings2) > 0) {
    $len_str_1 = strlen($strings2[0][0]);
    $len_name_1 = $strings2[1][1] - $len_str_1;
    $name_string_1 = substr($tweet_string, $len_str_1, $len_name_1);
    $name_string_final  = str_replace($stopword_name, "", $name_string_1);

    if (substr_count($name_string_final, ' ') > 0 AND substr_count($name_string_final, ' ') < 3) {
	$add_name_1 = true;
	$words_arr = explode(" ", $name_string_final);
	foreach ($words_arr as $key => $value){
		if (strlen($value) <3) {
			$add_name_1 = false;
		}
	}
	if ($add_name_1 === true) {
		$names_array[] = $name_string_final;
	}
    }
    $num_array_len = count($strings2);
        if (count($strings2) > 1) {
            $len_str_2 = strlen($strings2[0][0]) + $len_name_1 + strlen($strings2[1][0]);
            $len_name_2 = $strings2[2][1] - $len_str_2;
            $name_string_2 = substr($tweet_string, $len_str_2, $len_name_2);
            $name_string_final_2  = str_replace($stopword_name, " ", $name_string_2);
	    if (substr_count($name_string_final_2, ' ') > 0 AND substr_count($name_string_final_2, ' ') < 3) {
		$add_name_2 = true;
		$words_arr_2 = explode(" ", $name_string_final_2);
		foreach ($words_arr_2 as $key => $value){
			if (strlen($value) <3) {
				$add_name_2 = false;
			}
		}
		if ($add_name_2 === true) {
			$names_array[] = $name_string_final_2;
		}
	    }
        }
    }
    return $names_array;
}


function gen_new_query_string($top_word_array, $q_string_orig) {
    //echo "<br>$q_string_orig";
    $related_queries = array();
    foreach ($top_word_array as $topWord => $frequency) {
        if (strpos($q_string_orig, $topWord) === false AND $frequency > 2) {
            $related_queries[$frequency] = $topWord;   
        }
    }
    return $related_queries;
}


function gen_user_string($top_user_array) {
    $related_users = array();
    foreach ($top_user_array as $topWord => $frequency) {
        if ($frequency > 1) {
        echo "<BR>$topWord FREQ $frequency";
        $related_users[$frequency] = $topWord;
        }
    }
    return $related_users;
}

function get_fb_share_count($url) {
    $JSON = file_get_contents("http://graph.facebook.com/{$url}");
    $JSON_Data = json_decode($JSON,true);
    $num_shares = $JSON_Data[shares]; 
    $good_link = ($num_shares > 25 AND strpos($url, "youtube.com") === false AND strpos($url, "youtu.be") === false);
    if ($good_link) {
        //echo "<br>$url <b> $num_shares</b> shares";
    }
    return $good_link;
}


function get_freq($txt_string){
/* split $content into array of substrings of $content i.e wordwise */
$txt_string_lower = strtolower($txt_string);
$wordArray = preg_split('/[^a-zA-Z]/', $txt_string_lower, -1, PREG_SPLIT_NO_EMPTY);

 
/* "stop words", filter them  */
$filteredArray = array_filter($wordArray, function($x){
        if (strlen($x)>3) {
        return $x;
        }
     });

$filteredArray = array_filter($filteredArray, function($x){
       return 		!preg_match("/^(.|youtube|about|since|About|a||A|an|An|as|As|least|only|thing|next|say|also|other|their|ast|very|when|Former|during|says||every|those|news|update|https|there|take|near|then|than|ever|should|how|cause|gets|your|where|said|been|does|come|thanks|down|reuters|live|into|before|after|sharethis|that|didn|could|want|wants|just|things|must|more|white|house|would|bring|against|him|Him|because|tgif|look|looking|will|from|under|over|they|with|have|he|He|and|like|And|you|You|it|It|the|The|this|This|what|What|that|That|at|At|on|On|in|In|amp|via|Via|or|of|is|Is|are|Are|for|to|co|tco|http)$/",$x);
     });     
     
/* get associative array of values from $filteredArray as keys and their frequency count as value */
$wordFrequencyArray = array_count_values($filteredArray);
 
/* Sort array from higher to lower, keeping keys */
arsort($wordFrequencyArray);
 
/* grab Top 10, huh sorted? */
$top_words = array_slice($wordFrequencyArray,0,10);
 
/* display them 
foreach ($top_words as $topWord => $frequency)
    echo "<br>$top_words --  $frequency<br/>";
*/

return ($top_words);
}

$ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $twitter_api_url_2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
$out = curl_exec($ch);

// close cURL resource, and free up system resources
curl_close($ch);
$time_unix = time();
$query_string_encoded_file = $query_string_encoded;
if ($raw_query_string === $breaking_news_query) $query_string_encoded_file = "breakingnews";
$filename_json = $time_unix . "_" . $query_string_encoded_file . "". ".json";
$url_dest = "keepr_dataset_3/" . $filename_json;
$fp = fopen($url_dest, 'w');
fwrite($fp, $out);
fclose($fp);

?>
  
</body>
</html>
