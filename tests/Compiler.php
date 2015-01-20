<?php namespace Hip\Tests;
/**
 * Hip Lexer test
 ** 
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 * @group Hip
 * @group Hip_Parser
*/

use Hip\Compiler;
use Hip\Hip;

class Compiler_Test extends \PHPUnit_Framework_TestCase
{
	/**
	 * tests compile
	 */
	public function test_compileSimpleArray()
	{	
		$data = Hip::encode([
			
			'name' => 'foo',
			'age' => 12,
			'active' => true,
			'tags' => [ 'a', 'b', 'c' ]
			
		]);
		
		echo $data; die;
	}
}