<?

require_once "web/lib/FacebookUtils.php";

class FacebookUtilsTest extends PHPUnit_Framework_TestCase {

	public function testGetFacebookShareCount() {
		$good_link = FacebookUtils::is_good_link_by_fb("http://www.google.com");
		$this->assertEquals($good_link, "1");
		
	}

}


?>