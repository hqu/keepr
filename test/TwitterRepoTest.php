<?
require_once "web/lib/TwitterRepo.php";

class TwitterRepoTest extends PHPUnit_Framework_TestCase {

    private $keepr_col;

    public function setUp() {
        $m = new MongoClient();
        $db = $m->keepr;
        $db->keepr_col->drop();
        $this->keepr_col = $db->keepr_col;
    }

	public function testSaveTweets() {
		$twitterRepo = new TwitterRepo($this->keepr_col);
		$twitterRepo->save("user_screenname", "{}", "{}", "{}");

        $saved_tweets = $this->keepr_col->findOne(array("twitter_name" => "user_screenname"));
        $twitter_name = $saved_tweets["twitter_name"];
        $popular_tweets = $saved_tweets["saved_tweets"][0]["popular"];
        $recent_tweets = $saved_tweets["saved_tweets"][0]["recent"];
        $images_tweets = $saved_tweets["saved_tweets"][0]["images"];

        $this->assertEquals("user_screenname", $twitter_name);
        $this->assertEquals("{}", $popular_tweets);
        $this->assertEquals("{}", $recent_tweets);
        $this->assertEquals("{}", $images_tweets);
	}

    public function testSaveTwoTweets() {
        $twitterRepo = new TwitterRepo($this->keepr_col);
        $twitterRepo->save("user_screenname", "{}", "{}", "{}");
        $twitterRepo->save("user_screenname", "1", "1", "1");

        $saved_tweets_cursor = $this->keepr_col->find(array("twitter_name" => "user_screenname"));
        foreach($saved_tweets_cursor as $saved_tweet) {
            $size_of_saved_tweets = count($saved_tweet["saved_tweets"]);
            $this->assertEquals(2, $size_of_saved_tweets);
        }

    }

    public function testSaveTwoTweetsDifferentUsers() {
        $twitterRepo = new TwitterRepo($this->keepr_col);
        $twitterRepo->save("user_screenname", "{}", "{}", "{}");
        $twitterRepo->save("user_screenname1", "1", "1", "1");

        $saved_tweets_cursor = $this->keepr_col->find(array("twitter_name" => "user_screenname"));
        foreach($saved_tweets_cursor as $saved_tweet) {
            $size_of_saved_tweets = count($saved_tweet["saved_tweets"]);
            $this->assertEquals(1, $size_of_saved_tweets);
        }

    }

    public function testGetSavedTweetIds() {
        $twitterRepo = new TwitterRepo($this->keepr_col);
        $twitterRepo->save("user_screenname", "{}", "{}", "{}");
        $twitterRepo->save("user_screenname", "{}", "{}", "{}");

        $ids = $twitterRepo->get_saved_tweet_ids("user_screenname");
        $this->assertEquals(2, count($ids));

    }

}

?>