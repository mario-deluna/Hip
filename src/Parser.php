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
		// reset the keys
		$this->tokens = array_values( $tokens );
		
		// count the real number of tokens
		$this->tokenCount = count( $this->tokens );
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
		for( $this->index = 0; $this->index < $this->tokenCount; $this->index++ )
		{
			$this->next();
		}
		
		// return the result after the loop is done
		return $this->result;
	}
	
	/**
	 * Parse the next token
	 *
	 * @return mixed
	 */
	protected function next()
	{
		$token = $this->currentToken();
		
		// Identifier? we have a key! :)
		if ( $token->type === 'identifier' )
		{
			
		}
		
		// if nothing matches throw a parser error
		else
		{
			throw $this->errorUnexpectedToken( $token );
		}
		
		return $node;
	}

	
	/**
	 * Parse an scope block of code
	 *
	 * @return Hip\Node\ScopeBlock
	 */
	protected function parseScopeBlock()
	{
		if ( $this->currentToken()->type !== 'scopeOpen' )
		{
			throw new Exception( 'unexpected "'.$this->currentToken()->type.'" given at line '.$this->currentToken()->line );
		}
		
		$code = array( $this->currentToken() );
		
		$scope = 1;
		$tokenIteration = 1;
		
		while ( $scope > 0 ) 
		{
			if ( !$nextToken = $this->nextToken( $tokenIteration ) )
			{
				throw new Exception( 'unexpected end of code at line '.$this->nextToken( $tokenIteration-1 )->line );
			}
			
			if ( $nextToken->type === 'scopeOpen' )
			{
				$scope++;
			}
			elseif ( $nextToken->type === 'scopeClose' )
			{
				$scope--;
			}
			
			$code[] = $nextToken;
			
			$tokenIteration++;
		}
		
		$this->skipToken( $tokenIteration );
		
		// parse the code
		// first we have to remove the open and close tokens 
		$code = array_slice( $code, 1, -1 );
		
		// create a new parser
		$parser = new static( $code );
		
		// return the parsed block content
		return $parser->parse();
	}
}