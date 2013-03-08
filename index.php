<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<link href='http://fonts.googleapis.com/css?family=Geo|Lato:400,900,700,300|Ubuntu:400,700,500,300|Averia+Sans+Libre:400,700&subset=latin,latin-ext,greek,cyrillic' rel='stylesheet' type='text/css'>

	<meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>Keepr -  data mining social media chatter</title>
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
<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

<?php    
$raw_query_string = htmlspecialchars($_POST['q']);
if (!$raw_query_string) {
    $raw_query_string = htmlspecialchars($_GET['q']);
}
$raw_query_string = str_replace("breakingnews", "", $raw_query_string);
if (!$raw_query_string) {
    $raw_query_string = "from:@breakingnews";
    $query_string = "from:@breakingnews";
}
$query_string = $raw_query_string;
$query_string_encoded = urlencode($raw_query_string);
$raw_query_string_prev = htmlspecialchars($_GET['q_prev']);
$raw_query_string_prev = str_replace("from:@", "", $raw_query_string_prev);
$raw_query_string_prev = str_replace("breakingnews", "", $raw_query_string_prev);

$query_string_prev = $raw_query_string_prev;

if ($query_string_prev){
$query_string_prev_encoded = urlencode($raw_query_string_prev);;
$query_string_full =  $query_string . " " . $query_string_prev;
$query_string_full_encoded = $query_string_encoded  . "%20" . $query_string_prev_encoded;
}
else {
	$query_string_full = $query_string;
	$query_string_full_encoded = $query_string_encoded;
}


$twitter_api_url = "https://search.twitter.com/search.json?q=" . $query_string_full_encoded . "%20+exclude:retweets&result_type=mixed&include_entities=0&lang=en&locale=US&rpp=3";
$jsonurl = file_get_contents($twitter_api_url);
$json_output = json_decode($jsonurl,true);


$twitter_api_url_2 = "https://search.twitter.com/search.json?q=" . $query_string_full_encoded . "%20+exclude:retweets&result_type=mixed&include_entities=1&lang=en&locale=US&rpp=40";
$jsonurl_2 = file_get_contents($twitter_api_url_2);
$json_output_2 = json_decode($jsonurl_2,true);

$full_string = " ";
$full_string_user = " ";
$full_string_links = " ";

$full_string = $full_string . " " . $tw_text;
$related_links = array();
$names_arr = array();

foreach($json_output_2[results] as $key => $result) {
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
            //echo $result3[screen_name];
            $full_string_user = $full_string_user . " " . $result3[screen_name];
        }
    }   
    
/*    
    if (!empty($links_node)){   
        foreach($links_node  as $key4 => $result4) {
            $url_str = $result4[expanded_url];
            $full_string_links = $full_string_links . " " . $url_str;
            if (strpos($url_str, $raw_query_string) === false) {
                $is_link_good = get_fb_share_count($url_str);
                if ($is_link_good) {
                    $related_links[] = $url_str;
                }
            }
        }
}
*/   

}

$word_freq_array = get_freq($full_string);
$user_freq_array = get_freq($full_string_user);

$related_queries_array = gen_new_query_string($word_freq_array, $raw_query_string);
$related_users_array = gen_user_string($user_freq_array);    
$names_array_counted = array_count_values($names_arr);
arsort($names_array_counted);
?>

	<div class="row">
		<div class="large-12 columns">
			<div class="row" style="background-color:#3B3131;padding-bottom:3px;">
				<div class="large-2 columns text-center" style="padding:2px;"><font style="font-family: 'Geo', sans-serif;font-size:68px;padding-left: 5px;font-color:#000;"><a href='/demo/'>Keepr</a></font></div>
				<div class="large-6 columns">
					<div class="row" style="margin-top:12px;">
						<form action="index.php" method="get">
						    <div class="large-12 columns">
						      <div class="row collapse">
							<div class="small-10 columns">
							  <input type="text" name="q" value="<?php echo $raw_query_string ?>" />
							</div>
							<div class="small-2 columns">
							  <input type="submit" value="search" class="postfix xsmall button" style="height:32px;padding-top:1px">
							</div>
						      </div>
						 <div class="row collapse">     
						<div class="large-12 columns" style="margin-top:-10px;margin-bottom: 10px;">
							<div style="font-size:13px;font-weight:700;">
								&nbsp;<a href="index.php?q=pope">pope</a> 
								&nbsp;&nbsp;<a href="index.php?q=sequester">sequester</a>
								&nbsp;&nbsp;<a href="index.php?q=north+korea">north korea</a>
								&nbsp;&nbsp;<a href="index.php?q=kenya">kenya</a>
								&nbsp;&nbsp;<a href="index.php?q=chavez">chavez</a>
							</div>
						</div>
						 </div>
						    </div>	
						</form>
					</div>

				</div>
			</div>	
			<div class="row" style="background-color:#F7F7F7;padding-top: 12px;">
				<div class="large-12 columns text-center">
				<h4 style="font-family: 'Ubuntu', sans-serif;">
				<?php	
					if ($query_string_prev){
						echo "<a href='index.php?q={$query_string_prev}'>$query_string_prev</a> &rarr; ";
					}
					
					echo "<a href='index.php?q={$query_string_encoded}'>$raw_query_string</a>";
				?>
				</h4>
				
				</div>
			</div>
		</div>	
	</div>

	
	<div class="row">
		<div class="large-12 columns">
			<div class="row"  style="background-color:#F7F7F7;padding-bottom: 6px;">
				<div class="small-12 columns">
					<?php
					if (!empty($names_array_counted)){
					
					foreach ($names_array_counted as $name_string => $name_freq) {
						if (stristr($raw_query_string, $name_string) ===FALSE AND stristr($query_string_full, $name_string) ===FALSE) {	
						    $full_url_string = "index.php?q=" . urlencode($name_string) . "&q_prev=" . $query_string_full_encoded;
						    if ($name_freq > 2) {
							echo "<a href='$full_url_string' class='large button text-left' style='margin:3px;background-color: '>{$name_string}</a>";
						    }
						    elseif ($name_freq > 1) {
							echo "<a href='$full_url_string' class='medium button text-left' style='margin:3px;background-color: '>{$name_string}</a>";
						    }
						    else {
							echo "<a href='$full_url_string' class='small button text-left' style='margin:3px;background-color: '>{$name_string}</a>";
						    }					    
						    
						    }	
						}
					}
					if (!empty($related_queries_array)){
					foreach ($related_queries_array as $key => $text_string) {
					    if (++$i == 5) break;
					    $full_url_string = "index.php?q=" . urlencode($text_string) . "&q_prev=" . $query_string_full_encoded;
					    echo "<a href='$full_url_string' class='small button text-left' style='margin:3px;'>#{$text_string}</a>";
					    }
					}
					echo "";
					if (!empty($related_users_array)){
						foreach ($related_users_array as $key => $text_string) {
							if ("@".$text_string !== $raw_query_string) {	
							    if (++$j == 3) break;
							    $user_query = "@" . $text_string;
							    $full_url_string = "index.php?q=" . urlencode($user_query) . "&q_prev=" . $query_string_full_encoded;
							    echo "<a href='$full_url_string' class='small secondary button' style='margin:3px;'>@{$text_string}</a></a>";
							}
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>	
	
	

	<div class="row">
		<div class="large-6 columns">
			<!-- Grid Example -->
			<div class="row" style="background-color:#FFF;">
				<?php
				foreach($json_output[results] as $key => $result) {
				    if (++$k == 4) break;
				    $tweet_id = $result[id];
				    $embed_html = get_tweet_embed($tweet_id);
				    echo "<div class='large-12 columns'>";
				    echo $embed_html;
				    echo "</div>";
				}
				?>
			</div>
		</div>
		<div class="large-6 columns">
			<div class="row">
<?php
if ($query_string_full === "@breakingnews") {	
	echo '<a class="twitter-timeline" height="2150" href="https://twitter.com/BreakingNews" data-widget-id="309712090464133120">Tweets by @BreakingNews</a>';
	echo '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.async=true;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
}


else {
$tumblr_api_url_tmb = "https://api.tumblr.com/v2/tagged?tag=" . $query_string_full_encoded . "&api_key=BkzWzYlui6v6NsztxOUUtBKsecof21fhvzff85BYU0wf6IuAic&limit=3";
$jsonurl_tmb = file_get_contents($tumblr_api_url_tmb);
$json_output_tmb = json_decode($jsonurl_tmb,true);	
foreach($json_output_tmb[response] as $key => $result) {
    if (++$i == 5) break;
    $post_id = $result[id];
    $post_url = $result[post_url];
    $post_type = $result[type];
    $post_caption = $result[caption];
    $user_name = $result[blog_name];
    $photo_url = $result[photos][0][original_size][url];
    $vid_embed_code = $result[player][2][embed_code];
    if ($post_type == "photo") {
        echo "<div class='large-12 columns' style='text-align: center; margin-bottom:20px;'>";      
        echo "<br><a href='$post_url'><img src='$photo_url'></a>";
        echo "<div style='width:100%;border: 0px #EEE solid;padding:2px;background-color: #EFEFEF;font-weight:300;font-size:12px;'><blockquote style='width:100%;text-align:left;'>$post_caption</blockquote></div>";
        echo "<div style='font-size:11px;text-align:center;margin-top:5px;'><a href='http://{$user_name}.tumblr.com/'>$user_name</a>  &nbsp;&nbsp;&rarr; <a href='$post_url' target='_blank'>see post</a></div>"; 
        echo "</div>";        
    }
    if ($post_type == "video") {
        echo "<div class='large-12 columns' style='text-align: center; margin-bottom:20px;'>";      
        echo "<div style='text-align:center'>" . $vid_embed_code ."</div>";
        echo "<div style='width:100%;border: 0px #EEE solid;padding:2px;background-color: #EFEFEF;font-weight:300;font-size:12px;'><blockquote style='width:100%;text-align:left;'>$post_caption</blockquote></div>";
        echo "<div style='font-size:11px;text-align:center;margin-top:5px;'><a href='http://www.tumblr.com/$username'>$user_name</a> &nbsp;&nbsp;&rarr; <a href='$post_url' target='_blank'>see post</a></div>"; 
        echo "</div>";    	   
    }	
}    
    
}    
?>
			</div>
		</div>	
	</div>
	<div class="row">
		<div class="large-12 columns text-center"  style="margin-bottom: 25px;font-size:12px;font-weight:400">
			<hr>
			<a href="http://blog.keepr.com/" style="margin:3px;">About</a>
			&nbsp;&nbsp;
			<a href="https://hackpad.com/About-Keepr-9Ns5vU6e8V7" style="margin:3px;">Process</a>
		</div>
	</div>
	

	
	

	
	
  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
  <script src="js/foundation.min.js"></script>
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

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-38496892-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
  

<?php

function get_tweet_embed($tw_id) {
    //echo $tw_id."URL: https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=center&omit_script=true<br>";
    $JSON = file_get_contents("https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=left&omit_script=true&maxwidth=400");
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
    $stopword_name = array("The ", "THE ", "A ", "An ", "Hey ", "This ","That ","These ", "You ", "RT ", " RT", "RSS ", "CNN", "Breaking News", "BREAKING NEWS","My ", "We ", "Not ","Into ","Is ", "Only ", " Is", "If ", "So ", "THIS ", "His ","It ", "Of ", "Will ","Please ","Can ","In ", "On ", "And ", " And" , "Why","MUST", "From", "Now", "Some ", "After ", "With ", "Latest ", "Jokes" , "FUCK", " The", " Can","Here ", "When", "How ", "Are ", "I ","Can ", " An", "As ", "All ", "Although ");
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
            $related_queries[] = $topWord;
        }
    }
    return $related_queries;
}


function gen_user_string($top_user_array) {
    $related_users = array();
    foreach ($top_user_array as $topWord => $frequency) {
        if ($frequency > 0) {
        //echo "<BR>$topWord FREQ $frequency";
        $related_users[] = $topWord;
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
       return 		!preg_match("/^(.|youtube|about|About|a||A|an|An|as|As|only|thing|next|say|says||every|there||should|how|cause|gets|where|down|reuters|live|into|before|after|sharethis|just|things|must|more|white|house|would|bring|against|him|Him|because|tgif|look|looking|will|from|under|over|they|with|have|he|He|and|like|And|you|You|it|It|the|The|this|This|what|What|that|That|at|At|on|On|in|In|amp|via|Via|or|of|is|Is|are|Are|for|to|co|tco|http)$/",$x);
     });     
     
/* get associative array of values from $filteredArray as keys and their frequency count as value */
$wordFrequencyArray = array_count_values($filteredArray);
 
/* Sort array from higher to lower, keeping keys */
arsort($wordFrequencyArray);
 
/* grab Top 10, huh sorted? */
$top_words = array_slice($wordFrequencyArray,0,8);
 
/* display them 
foreach ($top_words as $topWord => $frequency)
    echo "<br>$top_words --  $frequency<br/>";
*/

return ($top_words);
}
?>
  
</body>
</html>
