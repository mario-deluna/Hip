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
	public function test_compileSequentiale()
	{
		$data = Hip::encode( array( 'get up', 'stand up' ) );
		
		$this->assertEquals( $this->getExpectedHip( 'sequential' ), $data );
		
		// more layers
		$data = Hip::encode( array( 'get up', 'stand up', array( 'stand up', 'for your right', array( 'don\'t give up', 'the fight' ) ) ) );
		
		$this->assertEquals( $this->getExpectedHip( 'sequential3D' ), $data );
		
		// natives
		$data = Hip::encode( array( 'string', 42, 3.14, true, false, null ) );
		
		$this->assertEquals( $this->getExpectedHip( 'sequentialNatives' ), $data );
	}
	
	/**
	 * tests compile
	 */
	public function test_compileAssociative()
	{
		$data = Hip::encode( array( 'who' => 'let the dogs out', 'theAnswerToLifeTheUniverseAndEverything' => 42 ) );
		
		$this->assertEquals( $this->getExpectedHip( 'associative' ), $data );
		
		// more layers
		$data = Hip::encode( array( 
			'this' => array( 
				'is' => array(
					'pretty' => array(
						'high' => 'doe'
					)
				),
			)
		));
		
		$this->assertEquals( $this->getExpectedHip( 'associative3D' ), $data );
		
		// natives
		$data = Hip::encode( array(
			'string' => 'string',
			'int' => 42,
			'float' => 3.14,
			'true' => true,
			'false' => false,
			'null' => null 
		));
		
		$this->assertEquals( $this->getExpectedHip( 'associativeNatives' ), $data );
	}
}