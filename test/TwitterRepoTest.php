<?
require_once "web/lib/TwitterRepo.php";

class TwitterRepoTest extends PHPUnit_Framework_TestCase {
	public function testSaveTweets() {
		$twitterRepo = new TwitterRepo();
		$twitterRepo.save("1", "{}");
	}

}

?>