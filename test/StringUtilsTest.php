<?
require_once "web/lib/StringUtils.php";

class StringUtilsTest extends PHPUnit_Framework_TestCase {
	public function testGetFeq() {
		$top_words = StringUtils::get_freq("test test test");
		$this->assertEquals(3, $top_words["test"]);
	}

	public function ignore_testGetNameEntities() {
		StringUtils::get_name_entities("test test test");
	}
}


?>