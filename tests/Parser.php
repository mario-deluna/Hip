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
use Hip\Parser;
use Hip\Lexer;

class Parser_Test extends \PHPUnit_Framework_TestCase
{
	/**
	 * tests Parser
	 */
	public function test_consturct()
	{	
		$lexer = new Lexer( 'foo: "foo"' );
		$parser = new Parser( $lexer->tokens() );
		
		$this->assertInstanceOf( 'Hip\\Parser', $parser );
	}
	
	/**
	 * test parser shortcut
	 */
	public function test_dataTypes()
	{	
		// string doublequotes
		$value = Hip::decode( '"foo"' );
		$this->assertInternalType( 'string', reset( $value ) );
		$this->assertEquals( 'foo', reset( $value ) );
		
		// string singlequotes
		$value = Hip::decode( "'foo'" );
		$this->assertInternalType( 'string', reset( $value ) );
		$this->assertEquals( 'foo', reset( $value ) );
		
		// int
		$value = Hip::decode( "42" );
		$this->assertInternalType( 'int', reset( $value ) );
		$this->assertEquals( 42, reset( $value ) );
		
		// float
		$value = Hip::decode( "3.14" );
		$this->assertInternalType( 'float', reset( $value ) );
		$this->assertEquals( 3.14, reset( $value ) );
		
		// bool yes
		$value = Hip::decode( "yes" );
		$this->assertInternalType( 'bool', reset( $value ) );
		$this->assertEquals( true, reset( $value ) );
		
		// bool false
		$value = Hip::decode( "no" );
		$this->assertInternalType( 'bool', reset( $value ) );
		$this->assertEquals( false, reset( $value ) );
		
		// null
		$value = Hip::decode( "nil" );
		$this->assertInternalType( 'null', reset( $value ) );
		$this->assertEquals( null, reset( $value ) );
		
		// array
		$value = Hip::decode( "--" );
		$this->assertInternalType( 'array', reset( $value ) );
		$this->assertEquals( array(), reset( $value ) );
	}
	
	/**
	 * Test commnets
	 */
	public function test_comments()
	{
		// just normal comments
		$data = Hip::decode( "#some comment\n'Foo'\n#next comment\n'Bar'" );
		$this->assertEquals( array( 'Foo', 'Bar' ), $data );
		
		// comment after key
		$data = Hip::decode( "foo:\n\t# comment\n\t'Bar'" );
		$this->assertEquals( array( 'foo' => array( 'Bar' ) ), $data );
	}
	
	/**
	 * Test comma seperated
	 */
	public function test_commaSeperated()
	{
		// just normal comments
		$data = Hip::decode( "1,2,3" );
		$this->assertEquals( array( array( 1,2,3 ) ), $data );	
		
		// just normal comments
		$data = Hip::decode( "genres: 'HipHop', 'Rock'" );
		$this->assertEquals( array( 'genres' => array( 'HipHop', 'Rock' ) ), $data );	
	}
	
	/**
	 * Test comma seperated
	 *
	 * @expectedException \Hip\Parser\Exception
	 */
	public function test_commaSeperatedErrorCommaExpected()
	{
		// just normal comments
		$data = Hip::decode( "1,2 2" );
	}
	
	/**
	 * Test comma seperated
	 *
	 * @expectedException \Hip\Parser\Exception
	 */
	public function test_commaSeperatedErrorValueExpected()
	{
		// just normal comments
		$data = Hip::decode( "1,2,," );
	}
	
	/**
	 * Test higher level
	 */
	public function test_higherLevel()
	{
		// simple array
		$data = Hip::decode( "-'Foo'\n\t'Bar'\n\t'Bat'-" );
		$this->assertEquals( array( array( 'Foo', array( 'Bar', 'Bat' ) ) ), $data );	
	}
	
	/**
	 * Test wrong syntax
	 *
	 * @expectedException \Hip\Parser\Exception
	 */
	public function test_wrongSyntax()
	{
		$data = Hip::decode( "," );
	}
	
	/**
	 * Test arrays
	 */
	public function test_arrays()
	{
		// simple array
		$data = Hip::decode( "-\n1\n2\n-" );
		$this->assertEquals( array( array( 1, 2 ) ), $data );		
	}
	
	/**
	 * Test multiple arrays
	 */
	public function test_multipleArrays()
	{
		// simple array
		$data = Hip::decode( "'foo'\n\tyes\n'foo'\n\tyes\n" );
		$this->assertEquals( array( 'foo', array( true ), 'foo', array( true ) ), $data );		
	}
}