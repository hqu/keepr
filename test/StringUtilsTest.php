<?
require_once "web/lib/StringUtils.php";

class StringUtilsTest extends PHPUnit_Framework_TestCase {
	public function testGetFeq() {
		$top_words = StringUtils::get_freq("i love a dog and a dog. and only a dog");
		$this->assertEquals(3, $top_words["test"]);
	}

	public function testGetNameEntities() {
		print_r(StringUtils::get_name_entities("Reformist Delhi chief minister Arvind Kejriwal quits after anti-corruption bill is blocked in the state assembly", array()));
	}
}


?>
