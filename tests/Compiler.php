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
	 * Expected hip data string 
	 *
	 * @var array[string]
	 */
	protected $expectedHipData = null;
	
	/**
	 * Get an expected hip data string
	 *
	 * @param string 			$key
	 * @return string
	 */
	public function getExpectedHip( $key )
	{
		if ( is_null( $this->expectedHipData ) )
		{
			$data = file_get_contents( __DIR__.'/ExpectedCompilerOutput.hip' );
			
			$currentKey = null;
			
			foreach ( explode( "\n", $data ) as $line ) 
			{
				if ( substr( $line, 0, 1 ) === '@' )
				{
					$currentKey = substr( $line, 1 ); continue;
				}
				
				if ( !isset( $this->expectedHipData[ $currentKey ] ) )
				{
					$this->expectedHipData[ $currentKey ] = "";
				}
				
				$this->expectedHipData[ $currentKey ] .= $line."\n";
			}
		}
		
		return trim( $this->expectedHipData[ $key ] );
	}		
	
	/**
	 * tests compile
	 */
	public function test_compileSequentialeArray()
	{
		$data = Hip::encode( array( 'foo', 'bar' ) );
		
		$this->assertEquals( $this->getExpectedHip( 'sequentialArray' ), $data );
	}
	
	/**
	 * tests compile
	 */
	public function test_compileAssociativeArray()
	{
		$data = Hip::encode( array( 'foo' => 'bar', 'number' => 42 ) );
		
		$this->assertEquals( $this->getExpectedHip( 'associativeArray' ), $data );
	}
}