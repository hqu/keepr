<?php
session_start();
$logout_action = $_GET['logout'];
if ($logout_action === "1") {
	unset($_SESSION['search_term']);
	unset($_SESSION['oauth_token']);
	unset($_SESSION['oauth_token_secret']);
	session_destroy();
}

?>
<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head profile="http://www.w3.org/2005/10/profile">
	<link href='http://fonts.googleapis.com/css?family=Geo|Lato:400,900,700,300|Ubuntu:400,700,500,300' rel='stylesheet' type='text/css'>
	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
<?php    

$raw_query_string = htmlspecialchars($_POST['q']);
$breaking_news_query = "from:cnnbrk OR from:breakingnews OR from:bbcbreaking OR from:AP+BREAKING: OR from:ReutersLive OR from:AJELive OR from:AFP+#BREAKING";
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

$nothomepage = false;
if ($raw_query_string !== $breaking_news_query) {
    $nothomepage = true;
}

if ($query_string_prev){
$query_string_prev_encoded = urlencode($raw_query_string_prev);
$query_string_full =  $query_string_prev . " " . $query_string;
$query_string_full_encoded = $query_string_prev_encoded . "%20" . $query_string_encoded;
}
else {
	$query_string_full = $query_string;
	$query_string_full_encoded = $query_string_encoded;
}

//Create session variable to track search terms
if (empty($_SESSION['search_term'])){
	$_SESSION['search_term'] = array();
}

//Add to session variable to search terms
if ($nothomepage) {
	if (!in_array($query_string, $_SESSION['search_term'])) {
		array_push($_SESSION['search_term'],$query_string);
	}
}
else {
	unset($_SESSION['search_term']);
	//unset($_SESSION['oauth_token']);
	//unset($_SESSION['oauth_token_secret']);
	//session_destroy();
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
require_once ('keys.php');
require_once ('lib/TweetEmbeder.php');
require_once ('lib/FacebookUtils.php');
require_once ('lib/StringUtils.php');

$tweetEmbeder = new TweetEmbeder();

//Get authenticated
Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
$cb = Codebird::getInstance();

if (empty($_SESSION['oauth_token'])) {
	$cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);
}
else {
	$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
}


if ($nothomepage) {
	$params = array(
		'q' => $query_string_full . ' exclude:retweets',
		'result_type' => 'popular',
		'lang' => 'en',
		'count' => '6'
	);
	$data = $cb->search_tweets($params, true);
	$data_json = json_encode($data);
	$json_output = json_decode($data_json, true);

	$params_3 = array(
	'q' => $query_string_full . ' AND (pic.twitter.com OR Twitpic OR Flickr OR instagram.com OR instagram)' . ' exclude:retweets',
	'lang' => 'en',
	'count' => '20'
	);
	$data_3 = $cb->search_tweets($params_3, true);
	$data_json_3 = json_encode($data_3);
	$json_output_3 = json_decode($data_json_3, true);
}

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
$ses_id = session_id();
$m = new MongoClient();
$db = $m->keepr;
$keepr_col = $db->keepr_col;

$saved_tweets = array( "tweet_id" => "1", "saved_id" => $ses_id, 
	"popular" => $data, 
	"recent" => $data_2, 
	"images" => $data_3);
$keepr_col->insert($saved_tweets);

$full_string = " ";
$full_string_user = " ";
//$full_string_links = " ";

$full_string = $full_string . " " . $tw_text;
$related_links = array();
$related_images = array();
$related_images_temp = array();
$names_arr = array();
$user_mention_arr = array();

if (!empty($json_output_3['statuses'])){
foreach($json_output_3['statuses'] as $key9 => $result9) {
    $links_images = $result9[entities][media];
    $tw_text = $result9[text];
    $tw_id = $result9[id];
    if (!empty($links_images)){
	foreach($links_images as $key6 => $result6) {
		$url_str = $result6[media_url];
		if(!in_array($url_str, $related_images_temp)) {
		$related_images_temp[]=$url_str;
		$url_tweet = $result6[expanded_url];
		$temp_img_arr = array();
		$temp_img_arr["img"]=$url_str;
		$temp_img_arr["url"]=$url_tweet;
		$temp_img_arr["text"]=$tw_text;
		$temp_img_arr["id"]=$tw_id;
		$related_images[] = $temp_img_arr;
		}
	}
	}
}
}


if (!empty($json_output_2['statuses'])){
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
    $names_arr = StringUtils::get_name_entities($tw_text, $names_arr);
    
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
            //echo $url_str;
				$related_links[] = $url_str;
            }
        }
    }
}
}

if (!empty($json_output['statuses'])){
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
	    if (strpos($url_str, $raw_query_string) === false AND !in_array($url_str, $related_links)) {
			$related_links[] = $url_str;
	    }
	}
    }
}
}


$word_freq_array = StringUtils::get_freq($full_string);
$user_freq_array = StringUtils::get_freq($full_string_user);

$related_queries_array = StringUtils::gen_new_query_string($word_freq_array, $raw_query_string);
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

$related_images = array_map("unserialize", array_unique(array_map("serialize", $related_images)));
//$related_images = array_reverse($related_images);
/*
if (!empty($related_images)){
    $related_images_arr_counted = array_count_values($related_images);
    arsort($related_images_arr_counted);
}
*/


?>
  <title>Keepr - news search engine - <?php echo $query_string_full; ?></title>
  <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="stylesheet" href="css/normalize.css" />
    <link rel="stylesheet" href="http://foundation.zurb.com/docs/assets/docs.css" />
    <script src="http://foundation.zurb.com/docs/assets/vendor/custom.modernizr.js"></script>

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
				<div class="large-2 columns text-left" style="padding:2px;"><font style="font-family: 'Geo', sans-serif;font-size:56px;padding-left: 5px;"><a href='/index.php'>Keepr</a></font></div>
				<div class="large-6 columns">
				</div>
			</div>	
			<?php
			if (!empty($json_output_2['statuses'])){
				if (empty($_SESSION['oauth_token'])) {
			?>
				<div class="row">
					<div class="large-12 columns text-right" style="padding-top:5px;">
							<a href="oauth/index.php?prev_page=<?php echo $_SERVER['REQUEST_URI'];?>"><img src="imgs/sign-in-with-twitter-gray.png" align="right"></a>
					</div>
				</div>
				<?php
				}
				else {
				?>
					<div class="row">
						<div class="large-12 columns text-right" style="padding-top:5px;">
								<a href="index.php?logout=1">Log out</a>
						</div>
					</div>
				<?php

				}
				?>
				<form action="index.php" id="searchForm" method="get" onSubmit="_gaq.push(['_trackEvent', 'SearchForm', 'click_submit', '<?php echo $query_string_full ?>']);">
				  <div class="row" style="padding-top:10px;margin-top:3px;margin-bottom:13px;">
					<div class="large-12 columns text-left">
					  <div class="row collapse">
						<div class="large-5 columns">
											<?php
											if (!$nothomepage) {
												echo '<input type="text" name="q" id="q" placeholder="search for news..." value="" style="color:#666;height:50px;font-size:22px;" />';
											}
											else {
												echo '<input type="text" name="q" id="q" value="'. $query_string_full .'" style="color:#333;height:50px;font-size:22px;"/>';
											}
											?>
						</div>
						<div class="large-7 columns text-left">
							<div style="float:left;">
						<?php
						//if ($raw_query_string_prev) {
							//echo '<input type="hidden" name="unset_var" value="1">'; 	
						//}
						?> 
							  <input type="submit" value="Search" class="button prefix" style="width:90px;height:50px;padding-top:8px;font-size:20px;">

							</div>

							<div style="float:left;text-align:right;width:68%;padding-top:0px;padding-left:5px;">
							<?php
							if (!empty($_SESSION['search_term'])){
								foreach($_SESSION['search_term'] as $key=>$value) {
									$value_encoded = urlencode($value);
									echo "<a class='xsmall awesome' onmouseout='reset_q();' onmouseover='preview_q_new(this.textContent);' style='border-color: #d0d0d0;background-color:#E9E9E9;color:#333333' href='index.php?q=$value_encoded&unset_var=1'>$value</a> &nbsp;";
								}
							}
							?>
							</div>
						</div>
					  </div>
					</div>
				  </div>
				</form>
			<?php
			}
			?>
		</div>
	</div>

	<div class="row">
		<div class="large-12 columns">
			<div class="row"  style="background-color:#E2F2F2;padding-bottom: 6px;padding-top: 10px;">
				<div class="small-12 columns">
					<?php
					if (!empty($names_array_counted)){
					foreach ($names_array_counted as $name_string => $name_freq) {
					    if (strpos(strtolower($name_string),strtolower($query_string_full)) === false) {
						if (stristr($raw_query_string, $name_string) === false) {	
						    $full_url_string = "index.php?q=" . urlencode($name_string) . "&q_prev=" . $query_string_full_encoded;
						    if ($name_freq > 1) {
								echo "<a href='$full_url_string' class='large awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;background-color: '>{$name_string}</a>";
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
							echo "<a href='$full_url_string' class='large awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;'>{$text_string}</a>";
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
							echo "<a href='$full_url_string' class='medium awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;background-color: '>{$name_string}</a>";
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
							echo "<a href='$full_url_string' class='small awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;'>{$text_string}</a>";
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


	<div class="row" style="background-color:#E2F2F2;padding-top:8px;">
		<div class="large-12 columns">
			<?php
				if (!empty($related_images) AND $nothomepage){
					foreach (array_slice($related_images, 0, 6) as $url_string => $url_freq) {
						$tweet_id = $url_freq["id"];
						echo '<div id="m' . $tweet_id . '" class="reveal-modal small">';
						echo '<iframe src="embed_iframe.php?tid=' . $tweet_id . '" width="300" height="550" frameborder="0" scrolling="no"></iframe>';
						echo '<a class="close-reveal-modal">&#215;</a>';
						echo '</div>';
						//echo '<div style="width:318px;margin-bottom:10px;background-color:#FFF;padding:10px 10px 10px 10px;"><a href="' . $url_freq["url"] . '" target="_blank"><img class="th" src="' . $url_freq["img"] . '" width="100%" align="center"></a><div style="line-height: 120%;width:96%;margin-top:5px;font-size:13px;color:#70707D">' . $url_freq["text"] . '</div></div>';
						echo '<div class="large-2 columns" style="padding-bottom:10px;"><a class="th" href="#" data-reveal-id="m'. $tweet_id .'"><img data-caption="' . $url_freq["text"] . '" src="'. $url_freq["img"] . '"></a></div>';
					}
				}
			?>
		</div>
	</div>

	<div class="row" style="background-color:#E2F2F2;">
			<div class="large-4 columns">
				<div class="row" style="padding-top:8px; margin-left:-12px;">
				
				<?php
	    if ($nothomepage) {

				if (!empty($related_links_arr_counted)){
					foreach (array_slice($related_links_arr_counted, 0, 2) as $url_string => $url_freq) {
						if ($url_freq > 1) {	
						    $full_url_string = "index.php?q=" . urlencode($url_string);
						    $display_url_string = substr($url_string,0,32);
						    $embed_link = "http://api.embed.ly/1/oembed?key=0c08c75737b3425db32d30a364884d07&url={$url_string}";
						    if (file_get_contents($embed_link)) {
								$jsonurl_link = file_get_contents($embed_link);
								$json_output_link = json_decode($jsonurl_link,true);
								$link_url = $json_output_link[url];
								$news_source = strtoupper($json_output_link[provider_name]);
								$link_type = $json_output_link[type];
								if ($news_source !== "ADF") {
									echo "<div style='width:318px;margin-bottom:12px;background-color:#FFF;padding:10px 10px 10px 10px;'>";
									echo "<div style='margin-bottom:3px;'>";
									if ($link_type !== "video") {
										echo "<div style='padding-bottom:3px;'><a href='$link_url' target='_blank'><img src='" . $json_output_link[thumbnail_url] . "' width='100%'></a><br></div>";
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
				}
			}
				?>
				</div>
			</div>


		<div class="large-4 columns">
			<div class="row">
		
				<?php

			if ($nothomepage) {				
				if (!empty($json_output['statuses'])) {
					foreach(array_slice($json_output['statuses'], 0, 5) as $key => $result)  {
						$tweet_id = $result[id];
						$embed_html = $tweetEmbeder->get_tweet_embed($tweet_id);
						echo "<div style='margin-left:6px;'>";
						echo $embed_html;
						echo "</div>";	    
					}
				}
			}
				?>
				
				<?php
				if (!empty($json_output_2['statuses'])){
					foreach(array_slice($json_output_2['statuses'], 0, 12) as $key => $result)  {
						$tweet_id = $result["id"];
						$embed_html = $tweetEmbeder->get_tweet_embed($tweet_id);
						echo "<div>";
						echo $embed_html;
						echo "</div>";
					}
				}
				else {
					echo '<h5>Please sign in your Twitter account</h5> <a href="oauth/index.php"><img src="imgs/sign-in-with-twitter-gray.png"></a><br><br>';
				}


				?>
			</div>
<!--		<div class="row">
				<div class="large-12 columns text-center" style="padding-top:6px;padding-bottom:3px;"><a class="medium button" href="http://topsy.com/s?q=<? echo $query_string_encoded;?>&type=tweet" target="_blank">More tweets</a></div>
			</div>
-->
		</div>



	
		<div class="large-4 columns">
			<div class="row">
				<div class="large-12 columns text-left" style="margin-left:8px;">
					<?php    
					if ($nothomepage) {		
					    if (!empty($user_mention_arr)){
						    foreach ($user_mention_arr  as $text_string => $user_freq) {
						    if (stristr($raw_query_string, $text_string) === false) {
							    if ("@".$text_string !== $raw_query_string AND $user_freq > 3 AND $text_string !== "Po_st" AND $text_string !== "YouTube" AND $text_string !== "ShareThis") {	
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
							    if ("@".$text_string !== $raw_query_string AND $user_freq < 4 AND $user_freq > 1 AND $text_string !== "Po_st" AND $text_string !== "YouTube" AND $text_string !== "ShareThis") {	
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
						echo "<a href='$full_url_string' class='medium grey awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;'>$full_screenname_string</a>";						
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
						echo "<a href='$full_url_string' class='medium grey awesome' onmouseout='reset_q();' onmouseover='preview_q(this.textContent);' style='margin:3px;'>$full_screenname_string</a>";						
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
				<div class="large-3 columns">&nbsp;</div>
				<div class="large-6 columns text-center">
					<?php	
					if ($nothomepage) {
					?>

						<div class="row">
							<div class="large-12 columns">
							<form onSubmit="return checkEmail();" class="custom" action="alert_created.php" method="post">
							<fieldset style="background-color:#333;width:100%;">
								<legend style="font-size:20px;padding: 5px 5px 5px 5px;">Sign up for email alerts</legend> 
								  <label style="color:#FFF">Twitter conversations about</label>
								  <input type="text" name="qt" class="input-text" value="<?php echo $query_string_full ?>" style="height:35px;margin-top:5px;font-size:15px;color:#00" />
								  <label style="color:#FFF">Email</label>
								  <input name="email" type="email" id="email" class="input-text" placeholder="Enter email address" value="" style="height:40px;margin-top:5px;font-size:18px;"/>
								  <input type="submit" value="Create alert" class="medium button" style="width:100%;margin-top:2px;color:#FFF;">
							</fieldset>
							</form>
							</div>
						</div>
						<div class="row">
							<div class="large-12 columns text-center">
							<div style="float:center">
								<span style="font-weight:500;font-size:16px;"><a href="http://keepr.com/rss/searchrss.php?q=<? echo $query_string_encoded;?>&rt=popular" target="_blank">RSS</a>&nbsp;&nbsp;</span>
							</div>
							</div>
						</div>
						<div class="row">
							<div class="large-12 columns">
							<div style="float:left;margin-top:6px;">
								<h6 style="color:#3B3131;font-weight:500">Share&nbsp;</h6>
							</div>
							<div style="float:left;width:45%;margin-top:12px;">
								<input onClick="this.select();" type="text" value="http://keepr.com/index.php?q=<? echo $query_string_full_encoded;?>" style="height:25px;width:100%;background-color:#FFF;font-size:11px;color:#000;">
							</div>
							<div style="float:left;width:20%;margin-top:12px;margin-left:5px">
							<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.keepr.com/index.php?q=<?php echo $query_string_full_encoded;?>" data-text="Search for news about <? echo $query_string_full?> via @keepr" data-size="large" data-hashtags="keepr">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
							</div>
							</div>
						</div>
					<?php
					}
					?>
				</div>
				<div class="large-3 columns">&nbsp;</div>
			</div>	
	<div class="row">
		<div class="large-12 columns text-center"  style="margin-bottom:20px;font-size:12px;font-weight:400">
			<hr>
			<a href="http://blog.keepr.com/" style="margin:3px;">About</a>
			&nbsp;&nbsp;
			<a href="https://hackpad.com/About-Keepr-9Ns5vU6e8V7" style="margin:3px;">Process</a>

			&nbsp;&nbsp;
			<a href="mailto:contact@keepr.com" style="margin:3px;">Contact</a>
		</div>
	</div>

<script>
perm_query = document.forms['searchForm'].q.value;

</script>
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

</body>
</html>
