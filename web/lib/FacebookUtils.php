<?

class FacebookUtils {

	public static function is_good_link_by_fb($url) {
	    $JSON = file_get_contents("http://graph.facebook.com/{$url}");
	    $JSON_Data = json_decode($JSON,true);
	    $num_shares = $JSON_Data["shares"]; 
	    $good_link = ($num_shares > 25 AND strpos($url, "youtube.com") === false AND strpos($url, "youtu.be") === false);
	    if ($good_link) {
	        //echo "<br>$url <b> $num_shares</b> shares";
	    }
	    return $good_link;
	}

}

?>