<?
require_once "web/lib/TweetEmbeder.php";

class TweetEmbederTest extends PHPUnit_Framework_TestCase
{
    public function testGetTweetEmbed() {
    	$tweetEmbeder = new TweetEmbeder;
    	$embed_code = $tweetEmbeder->get_tweet_embed("389460805844217856");
    	$found_embed_code = strpos($embed_code, "389460805844217856");
    	$this->assertTrue($found_embed_code !== false);
    }

    public function testGetTweetEmbedSmall() {
    	$tweetEmbeder = new TweetEmbeder;
    	$embed_code = $tweetEmbeder->get_tweet_embed_small("389460805844217856");
    	$found_embed_code = strpos($embed_code, "389460805844217856");
    	$this->assertTrue($found_embed_code !== false);
    }

    public function ignore_testGetTweetEmbedEmbedly() {
    	$tweetEmbeder = new TweetEmbeder;
    	$embed_code = $tweetEmbeder->get_tweet_embed_embedly("389460805844217856");
    	$found_embed_code = strpos($embed_code, "389460805844217856");
    	$this->assertTrue($found_embed_code !== false);
    }

}

?>