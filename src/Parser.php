<?php namespace Hip;
/**
 * Hip Parser
 **
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 */

use Hip\Parser\Exception;
 
class Parser
{	
	/**
	 * The tokens in this code segment
	 *
	 * @var array[Token]
	 */
	protected $tokens = array();
	
	/**
	 * The current index while parsing trough the tokens
	 * 
	 * @var int
	 */
	protected $index = 0;
	
	/**
	 * The number of tokens to parse
	 * 
	 * @var int
	 */
	protected $tokenCount = 0;
	
	/**
	 * The current data already parsed
	 *
	 * @var array
	 */
	protected $result = array();
	
	/**
	 * The constructor
	 * You have to initialize the Parser with an array of lexed tokens.
	 * 
	 *
	 * @var array[Token] 			$tokens
	 * @return void
	 */
	public function __construct( array $tokens )
	{	
		foreach( $tokens as $key => $token )
		{
			// remove all comments
			if ( $token->type === 'comment' )
			{
				unset( $tokens[$key] );
			}
		}
		
		// reset the keys
		$this->tokens = array_values( $tokens );
		
		// count the real number of tokens
		$this->tokenCount = count( $this->tokens );
	}
	
	/**
	 * Adds a value to the current result
	 *
	 * @param Hip\Token 	$token
	 * @param string 		$key
	 * @return void
	 */
	protected function addResult( $token, $key = null )
	{
		// we might also get an array
		if ( is_array( $token ) )
		{
			$value = $token;
		}
		else
		{
			$value = $token->value;
			
			switch ( $token->type ) 
			{
				case 'boolTrue':
					$value = true;
				break;
				
				case 'boolFalse':
					$value = false;
				break;
				
				case 'string':
					$value = substr( $value, 1, -1 );
				break;
				
				case 'number':
					$value = $value+0;
				break;
			} 
		}
		
		if ( is_null( $key ) )
		{
			$this->result[] = $value;
		}
		else
		{
			$this->result[$key] = $value;
		}
	}
	
	/**
	 * Retrives the current token based on the index
	 *
	 * @return Hip\Node
	 */
	protected function currentToken()
	{
		return $this->tokens[ $this->index ];
	}
	
	/**
	 * Get the next token based on the current index
	 * If the token does not exist because its off index "false" is returend.
	 *
	 * @param int 			$i
	 * @return Hip\Node|false
	 */
	protected function nextToken( $i = 1 )
	{
		if ( !isset( $this->tokens[ $this->index + $i ] ) )
		{
			return false;
		}
		
		return $this->tokens[ $this->index + $i ];
	}
	
	/**
	 * Skip the next parser token by updating the index.
	 *
	 * @param int			$times
	 * @return void
	 */
	protected function skipToken( $times = 1 )
	{
		$this->index += $times;
	}

	/**
	 * Check if all tokens have been parsed trough
	 *
	 * @return bool
	 */
	protected function parserIsDone()
	{
		return $this->index >= $this->tokenCount;
	}
	
	/**
	 * Skip all following whitspaces
	 *
	 * @return void
	 */
	protected function skipWhitespaces()
	{
		while ( !$this->parserIsDone() && $this->currentToken()->type === 'whitespace' ) 
		{
			$this->skipToken();
		}
	}	
	
	/**
	 * Skip all following whitspaces, comments and linebreaks
	 *
	 * @return void
	 */
	protected function skipSomeStuff()
	{
		while ( !$this->parserIsDone() && ( $this->currentToken()->type === 'whitespace' || $this->currentToken()->type === 'comment' ||  $this->currentToken()->type === 'linebreak' ) ) 
		{
			$this->skipToken();
		}
	}	

	/**
	 * Check if the current token is the end of a expression
	 *
	 * @return bool
	 */
	protected function isEndOfExpression( $includeComma = false )
	{
		if ( $includeComma ) 
		{
			return $this->parserIsDone() ||
				$this->currentToken()->type === 'linebreak' ||
				$this->currentToken()->type === 'comma';
		}

		return $this->parserIsDone() || $this->currentToken()->type === 'linebreak';
	}

	/**
	 * Create new unexpected token exception
	 *
	 * @param Hip\Node 				$token
	 * @return Hip\Parser\Exception;
	 */
	protected function errorUnexpectedToken( $token )
	{
		return new Exception( 'unexpected "'.$token->type.'" given at line '.$token->line );
	}
	
	/**
	 * Start the code parser and return the result  
	 * 
	 * @return array
	 */
	public function parse()
	{		
		// reset the result
		$this->result = array();	
	
		// start parsing trought the tokens
		while( !$this->parserIsDone() )
		{
			$this->next();
		}
		
		// return the result after the loop is done
		return $this->result;
	}
	
	/**
	 * Parse the next token
	 *
	 * @return void
	 */
	protected function next()
	{		
		$token = $this->currentToken();
		
		// Identifier followed by equal? we have a key! :)
		if ( $token->type === 'identifier' && $this->nextToken()->type === 'equal' )
		{
			$this->skipToken(2);
			$this->parseValue( $token->value );
		}
		
		// we can skip linebreaks and unused whitespaces
		elseif ( $token->isValue() )
		{
			$this->skipToken();
			
			// skip all whitespaces and check if the next
			// token is a linebreak
			$this->skipWhitespaces();
			
			if ( !$this->parserIsDone() && $this->currentToken()->type !== 'linebreak' )
			{
				throw $this->errorUnexpectedToken( $this->currentToken() );
			}
			
			$this->addResult( $token );
			
			$this->skipToken();
		}
		
		// we can skip linebreaks and unused whitespaces
		elseif ( $token->type === 'linebreak' || $token->type === 'whitespace' )
		{
			$this->skipToken();
		}
		
		// if nothing matches throw a parser error
		else
		{
			throw $this->errorUnexpectedToken( $token );
		}
	}
	
	/**
	 * Parse a value for the index
	 *
	 * @param string 			$key
	 */
	protected function parseValue( $key = null )
	{
		// at this point we ignore whitespaces
		$this->skipWhitespaces();
		
		$token = $this->currentToken();
		
		// if a line break follows we have to get all values on the new
		// level and parse them on its own
		if ( $token->type === 'linebreak' )
		{
			// skip the linebreak
			$this->skipToken();
			
			// get the higher level tokens and parse them
			$parser = new static( $this->parseTokensOnNextLevel() );
			
			// we keep the 
			
			// add the result
			$this->addResult( $parser->parse(), $key );
		}
		
		// there might follow a value
		elseif ( $token->isValue() )
		{
			$this->addResult( $token, $key );
			
			// skip the value
			$this->skipToken();
			
			// if no linebreak follow hurray syntax error
			if ( !$this->parserIsDone() && $this->currentToken()->type !== 'linebreak' )
			{
				throw $this->errorUnexpectedToken( $this->currentToken() );
			}
		}
		
		// if nothing matches we have an syntax error
		else
		{
			throw $this->errorUnexpectedToken( $token );
		}
	}
	
	/**
	 * Parses all following token on a higher level
	 *
	 * @return array[Token]
	 */
	protected function parseTokensOnNextLevel()
	{
		$onHigherLevel = true; $tokens = array();
		
		while ( $onHigherLevel && !$this->parserIsDone() ) 
		{
			// if the token is not a whitespace we are done
			if ( $this->currentToken()->type !== 'whitespace' )
			{
				$onHigherLevel = false;
			}
			else
			{
				// remove on level by skipping one token
				$this->skipToken();
				
				// add all tokens until the next linebreak
				while( !$this->isEndOfExpression() )
				{
					$tokens[] = $this->currentToken();
					$this->skipToken();
				}
				
				// also add the end of expression token and skip it
				// if the parser is not done yet
				if ( !$this->parserIsDone() )
				{
					$tokens[] = $this->currentToken();
					$this->skipToken();
				}
			}
		}
		
		return $tokens;
	}
}