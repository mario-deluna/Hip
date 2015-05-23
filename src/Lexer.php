<?php namespace Hip;
/**
 * Hip Lexer
 **
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 */

use Hip\Lexer\Exception;

class Lexer
{	
	/**
	 * The current code we want to iterate trough
	 *
	 * @var string
	 */
	protected $code = null;

	/**
	 * The code lenght to iterate
	 *
	 * @var int
	 */
	protected $length = 0;

	/**
	 * The current string offset in the code
	 *
	 * @var int
	 */
	protected $offset = 0;

	/**
	 * The current line
	 *
	 * @var int
	 */
	protected $line = 0;

	/**
	 * Token map 
	 *
	 * @var array
	 */
	protected $tokenMap = array(

		// strings
		'/^"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"/' => 'string',
		"/^'[^'\\\\]*(?:\\\\.[^'\\\\]*)*'/" => 'string',

		// numbers
		"/^(([1-9][0-9]*\.?[0-9]*)|(\.[0-9]+))([Ee][+-]?[0-9]+)?/" => 'number',

		// bool
		"/^(yes)/" => "boolTrue",
		"/^(no)/" => "boolFalse",

		// null
		"/^(nil)/" => "null",

		// comments
		"/^\#.*/" => "comment",

		//
		"/^(\r\n|\n|\r)/" 				=> "linebreak",
		"/^(\s)/" 						=> "whitespace",
		"/^(-)/" 						=> "seperator",
		"/^(:)/" 						=> "equal",
		"/^(,)/" 						=> "comma",
		"/^(\w+)/" 						=> "identifier",

	);

	/**
	 * The constructor
	 *
	 * @var string 		$code
	 * @return void
	 */
	public function __construct( $code )
	{
		// we have to convert all tabs to whitespaces to
		$code = trim( str_replace( array( "  ", "\t" ), " ", $code ) );

		$this->code = $code;
		$this->length = strlen( $code );
	}

	/**
	 * Get the codes lenght
	 *
	 * @return int
	 */
	public function length()
	{
		return $this->length;
	}

	/**
	 * Lex the next word
	 * Return false everything has been parsed
	 *
	 * @return string|false
	 */
	protected function next()
	{
		if ( $this->offset >= $this->length )
		{
			return false;
		}

		foreach( $this->tokenMap as $regex => $token ) 
		{
			if ( preg_match( $regex, substr( $this->code, $this->offset ), $matches ) ) 
			{	
				if ( $token === 'linebreak' )
				{
					$this->line++;
				}

				$this->offset += strlen( $matches[0] );

				return new Token( array( $token, $matches[0], $this->line+1 ) );
			}
		}

		throw new Exception( sprintf( 'Unexpected character "%s" on line %s', $this->code[ $this->offset ], $this->line ) );
	}

	/**
	 * Lex the tokens from the code
	 * 
	 * @throws Hip\Lexer\Exception
	 * @return array[Hip\Token]
	 */
	public function tokens() 
	{
		$tokens = array();

		while( $token = $this->next() )
		{
			$tokens[] = $token;
		}

		return $tokens;
	}
}