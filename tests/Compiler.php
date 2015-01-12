<?php namespace Jane\Tests;
/**
 * Jane Iterator tests
 ** 
 *
 * @package 		Jane
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2014 - 2015 ClanCats GmbH
 *
 * @group Jane
 * @group Jane_Compiler
 */

use Jane\Compiler;
use Jane\Parser;

class Compiler_Test extends \PHPUnit_Framework_TestCase
{
	/**
	 * tests Jane::config
	 */
	public function test_varAssignment()
	{	
		/*$parser = new Parser( '
		myHello = "hello"
		name = "world"
		helloWorld = myHello
		helloWorld .= name' );
		$compiler = new Compiler( $parser->parse() );
		
		var_dump( $compiler->transform() );*/
	}
}