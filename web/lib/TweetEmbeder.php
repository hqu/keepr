<?

class TweetEmbeder {
	public function get_tweet_embed($tw_id) {
		//echo $tw_id."URL: https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=center&omit_script=true<br>";
		$JSON = file_get_contents("https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=left&omit_script=true&hide_media=false");
		return $this->get_html($JSON);
	}

	public function get_tweet_embed_small($tw_id) {
		//echo $tw_id."URL: https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=center&omit_script=true<br>";
		$JSON = file_get_contents("https://api.twitter.com/1/statuses/oembed.json?id={$tw_id}&align=left&omit_script=true&hide_media=false&hide_thread=false");
		return $this->get_html($JSON);
	}

	public function get_tweet_embed_embedly($tw_id) {
	    //echo "<br>http://api.embed.ly/1/oembed?url=http%3A%2F%2Ftwitter.com%2Fembedly%2Fstatus%2F{$tw_id}&omit_script=true&maxwidth=300<br>";
	    $JSON = file_get_contents("https://api.embed.ly/1/oembed?key=0c08c75737b3425db32d30a364884d07&url=http%3A%2F%2Ftwitter.com%2Fembedly%2Fstatus%2F{$tw_id}&omit_script=true&maxwidth=300");
		$JSON_Data = json_decode($JSON,true);
		$tw_embed_code = $JSON_Data["html"];
		return $tw_embed_code;
	}

	private function get_html($embed_json) {
		$JSON_Data = json_decode($embed_json,true);
		$tw_embed_code = $JSON_Data["html"];
		return $tw_embed_code;
	}

}
?>