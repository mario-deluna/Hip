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
		// we might also get an array wich already
		// is converted
		if ( is_array( $token ) )
		{
			$value = $token;
		}
		else
		{
			$value = $token->getValue();
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
	 * Get all tokens until the next linebreak
	 *
	 * @return array
	 */
	protected function getTokensUntilLinebreak()
	{
		$tokens = array();
		
		while( !$this->parserIsDone() && $this->currentToken()->type !== 'linebreak' )
		{
			$tokens[] = $this->currentToken(); $this->skipToken();
		}
		
		return $tokens;
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
			$this->skipWhitespaces();
			
			// if the current token is now a linebreak we
			// have to parse the tokens on the next level
			if ( $this->currentToken()->type === 'linebreak' )
			{
				$this->skipToken();
				$this->addResult( $this->parseTokensOnNextLevel(), $token->value );
			}
			// otherwise parse the value
			else
			{
				$this->parseValue( $token->value );
			}
		}
		
		// when we directly have a value parse it
		elseif ( $token->isValue() )
		{
			$this->parseValue();
		}
		
		// a new array begins
		elseif ( $token->type === 'seperator' )
		{
			$this->addResult( $this->parseArray() );
		}
		
		// if we have a whitespace we have an upper layer
		elseif ( $token->type === 'whitespace' )
		{
			$this->addResult( $this->parseTokensOnNextLevel() );
		}
		
		// we can skip linebreaks and unused whitespaces
		elseif ( $token->type === 'linebreak' )
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
	 * @return void
	 */
	protected function parseValue( $key = null )
	{
		$tokens = $this->getTokensUntilLinebreak();
		
		$commaSeperated = false;
		
		// filter whitespaces and check if there is a comma
		foreach( $tokens as $i => $token )
		{
			if ( $token->type === 'whitespace' )
			{
				unset( $tokens[$i] ); continue;
			}
			
			// check for a comma so we know if we have 
			// a comma seperated list
			elseif ( $token->type === 'comma' )
			{
				$commaSeperated = true;
			}
		}
		
		// reset the keys
		$tokens = array_values( $tokens );
		
		// if there is just one token directly add it
		if ( count( $tokens ) === 1 )
		{
			$this->addResult( reset( $tokens ), $key ); return;
		}
		
		$values = array(); 
		$done = false; 
		$index = 0;
		
		while( !$done )
		{
			// check if the current token is a value
			if ( !$tokens[$index]->isValue() )
			{
				throw $this->errorUnexpectedToken( $tokens[$index] );
			}
			
			// the next token isset it has to be a comma
			if ( isset( $tokens[$index+1] ) && $tokens[$index+1] !== 'comma' )
			{
				throw $this->errorUnexpectedToken( $tokens[$index+1] );
			}
			
			// if no next token is there we are doen
			if ( !isset( $tokens[$index+2] ) )
			{
				$done = true;
			}
			
			$values[] = $tokens[$index]->getValue();
		}
		
		$this->addResult( $values, $key );
	}
	
	/**
	 * Parse an incomming array
	 *
	 * @return array
	 */
	protected function parseArray()
	{
		if ( $this->currentToken()->type !== 'seperator' )
		{
			throw $this->errorUnexpectedToken();
		}
		
		$isArrayEnd = false;
		
		$tokens = array();
		
		while( !$this->parserIsDone() && !$isArrayEnd )
		{
			if ( $this->nextToken() && $this->currentToken()->type === 'linebreak' && $this->nextToken()->type === 'seperator' )
			{
				$isArrayEnd = true; $this->skipToken();
			}
			
			$tokens[] = $this->currentToken();
			
			$this->skipToken();
		}
		
		// remove the seperators
		$tokens = array_slice( $tokens, 1, -1 );
		
		// create a new parser
		$parser = new static( $tokens );
		
		// add the result
		return $parser->parse();
	}
	
	/**
	 * Parses all following token on a higher level
	 *
	 * @return array[mixed]
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
		
		// create a new parser
		$parser = new static( $tokens );
		
		// add the result
		return $parser->parse();
	}
}