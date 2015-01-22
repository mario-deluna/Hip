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
		return array( 
		
			array( array( 'A', 'B' ) ),
			
			array( array( 'A', 31, true ) ),
			
			array( array( 'C', null, 'G' ) ),
			
			array( array( 'F', 'D' => null, 'foo', true ) ),
			
			array( array( array(), 'foo' => 'bar' ) ),
			
			array( array( array(), 'foo' => array(), true ) ),
		
		);
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