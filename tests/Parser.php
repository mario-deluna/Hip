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
		$lexer = new Lexer( 'var foo' );
		$parser = new Parser( $lexer->tokens() );
		
		$this->assertInstanceOf( 'Hip\\Parser', $parser );
	}
	
	/**
	 * test parser shortcut
	 */
	public function test_parseSimple()
	{	
		$data = Hip::decode( '
name: "Mario"
age: 21
active: yes
hasCamera: no
		' );

		print_r( $data ); die;
		
		$data = Hip::decode( '
		
name: "Mario"
age: 21
active: yes
hasCamera: no

groups:
	"Admins"
	"Users"
	
emails
	primary: "mario@clancats.com"
	secondary: "info@example.com"

bands
	-
	name: "La Dispute"
	genre: 
		"Hardcore"
		"Post-Hardcore"
	--
	name: "Antilopen Gang"
	genre: "HipHip", "Rap"
	-		
		' );
		
		print_r( $data ); die;
	}
}