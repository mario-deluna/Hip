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

use Hip\Hip;

class EncondingDecoding_Test extends \PHPUnit_Framework_TestCase
{	
	public static function arrayDataProvider()
	{
		return array( array( 
			
			array( 'A', 'B' ),
			
			array( 'A', 31, true ),
			
			array( 'C', null, 'G' ),
			
			array( 'F', 'D' => null, 'foo', true ),
		));
	}

	/**
	 * Get an expected hip data string
	 *
	 * @dataProvider arrayDataProvider
	 */
	public function testItAndIfAllWorkImHappy( $array )
	{
		$hip = Hip::encode( $array );
		$this->assertEquals( $array, Hip::decode( $hip ) );
	}
}