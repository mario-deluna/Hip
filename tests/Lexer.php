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
 * @group Hip_Lexer
 */

use Hip\Lexer;

class Lexer_Test extends \PHPUnit_Framework_TestCase
{
	protected function pickTokenTypes( array $tokens )
	{
		$types = array();
		
		foreach( $tokens as $token )
		{
			$types[] = $token->type;
		}
		
		return $types;
	}
	
	protected function pickTokenLine( array $tokens )
	{
		$lines = array();
		
		foreach( $tokens as $token )
		{
			$lines[] = $token->line;
		}
		
		return $lines;
	}
	
	/**
	 * tests Lexer
	 */
	public function test_consturct()
	{	
		$lexer = new Lexer( 'foo' );
		
		$this->assertInstanceOf( 'Hip\\Lexer', $lexer );
		$this->assertEquals( 3, $lexer->length() );
	}
	
	/**
	 * tests Lexer
	 */
	public function test_tokenString()
	{	
		// string
		$lexer = new Lexer( '"hello world"' );
		$this->assertEquals( array( 'string' ), $this->pickTokenTypes( $lexer->tokens() ) );
		
		// string singlequotes
		$lexer = new Lexer( "'hello world'" );
		$this->assertEquals( array( 'string' ), $this->pickTokenTypes( $lexer->tokens() ) );
		
		// string singlequotes escaped
		$lexer = new Lexer( "'hello \'world'" );
		$this->assertEquals( array( 'string' ), $this->pickTokenTypes( $lexer->tokens() ) );
	}
	
	/**
	 * tests Lexer
	 */
	public function test_tokenNumber()
	{	
		// int
		$lexer = new Lexer( '124' );
		$this->assertEquals( array( 'number' ), $this->pickTokenTypes( $lexer->tokens() ) );
		
		// float
		$lexer = new Lexer( "12.3112" );
		$this->assertEquals( array( 'number' ), $this->pickTokenTypes( $lexer->tokens() ) );
		
		// float in string
		$lexer = new Lexer( "'12.31'" );
		$this->assertEquals( array( 'string' ), $this->pickTokenTypes( $lexer->tokens() ) );
	}
	
	/**
	 * tests Lexer
	 */
	public function test_tokenEqual()
	{	
		// simple
		$lexer = new Lexer( 'foo:"bar"' );
		$this->assertEquals( 
			array( 'identifier', 'equal', 'string' ), 
			$this->pickTokenTypes( $lexer->tokens() )
		);
	}
	
	/**
	 * tests Lexer
	 */
	public function test_linenum()
	{	
		$lexer = new Lexer( "yes\nyes" );
		$this->assertEquals( array(1,2,2), $this->pickTokenLine( $lexer->tokens() ) );
	}
	
	/**
	 * tests Lexer error
	 *
	 * @expectedException \Hip\Lexer\Exception
	 */
	public function test_unknownToken()
	{	
		$lexer = new Lexer( "*" ); $lexer->tokens();
	}
}