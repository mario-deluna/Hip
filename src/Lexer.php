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
		"/^(([1-9]+\.[0-9]*)|([1-9]*\.[0-9]+)|([1-9]+))([eE][-+]?[0-9]+)?/" => 'number',
		
		// bool
		"/^(yes)/" => "boolTrue",
		"/^(no)/" => "boolFalse",
		
		//
		"/^(\r\n|\n|\r)/" 				=> "linebreak",
		"/^(\s+)/" 						=> "whitespace",
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
				if ( $token == 'linebreak' )
				{
					$this->line++;
				}
				
				$this->offset += strlen( $matches[0] );
				
				return array(
					$token,
					$matches[0],
					$this->line,
				);
			}
		}
		
		throw new Exception( sprintf( 'Unexpected character "%s"', $this->code[ $this->offset ] ) );
	}
	
	/**
	 * Lex the tokens from the code
	 * 
	 * @throws Jane\Lexer\Exception
	 * @return array
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