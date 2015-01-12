<?php namespace Hip;
/**
 * Hip main
 **
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 */
class Hip
{	
	/**
	 * Ecnode array to hip data string
	 *
	 * 
	 */
	public static function encode()
	{
		
	}
	
	/**
	 * Decode hip data string to an array
	 *
	 * @throws Hip\Exception
	 *
	 * @param string			$hip
	 * @return array
	 */
	public static function decode( $hip )
	{
		$lexer = new Lexer( $hip );
		$parser = new Parser( $lexer->tokens() );
		
		return $parser->parse(); 
	}
	
	/**
	 * Parse jane code into an array
	 *
	 * @param string			$code
	 * @return string
	 */
	public static function read( $code )
	{
		$lexer = new Lexer( $code );
		$parser = new Parser( $lexer->tokens() );
		
		return $parser->parse(); 
	}
	
	/**
	 * Transform jane code to the target language
	 *
	 * @param string 			$code
	 * @return string
	 */
	public static function write( $code )
	{
		$compiler = new Compiler( static::parse( $code ) );
		return $compiler->transform();
	}
}