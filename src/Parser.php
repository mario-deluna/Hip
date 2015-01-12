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
	 * @var array[array]
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
	 * All these token types implement a custom parser mehtod
	 * 
	 * @var array
	 */
	protected $customParserActionTokens = array(
		'variable',
		'identifier',
		'function'
	);
	
	/**
	 * The constructor
	 * You have to initialize the Parser with an array of lexed tokens.
	 * 
	 * example tokens:
	 *     {
	 *         // { type, value, line }
	 *         { identifier, foo, 1 },
	 *         { equal, =, 1 },	 
	 *         { string, =, 'bar' },	 
	 *     }
	 *
	 * @var array[array] 			$tokens
	 * @return void
	 */
	public function __construct( array $tokens )
	{	
		$this->tokens = $tokens;
		
		foreach( $this->tokens as $key => $token )
		{
			// it might already have been converted to an node
			if ( is_object( $token ) )
			{
				continue;
			}
			
			// we skip all whitespaces
			if ( $token[0] === 'whitespace' )
			{
				unset( $this->tokens[$key] ); continue;
			}
			
			// replace the token with a node 
			$this->tokens[$key] = new Node( $token );
		}
		
		// reset the keys
		$this->tokens = array_values( $this->tokens );
		
		// count the real number of tokens
		$this->tokenCount = count( $this->tokens );
	}

	/**
	 * Retrives the current token based on the index
	 *
	 * @return Jane\Node
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
	 * @return Jane\Node|false
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
	 * Check if the given string is a valid dataType
	 *
	 * @param string 			$string
	 * @return bool
	 */
	protected function isValidDataType( $string )
	{
		return in_array( $string, array(
			"primitiveInt",
			"primitiveFloat",
			"primitiveDouble",
			"primitiveString",
			"primitiveArray",
			"primitiveBool",
		));
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
	 * @param Jane\Node 				$token
	 * @return Jane\Parser\Exception;
	 */
	protected function errorUnexpectedToken( $token )
	{
		return new Exception( 'unexpected "'.$token->type.'" given at line '.$token->line );
	}
	
	/**
	 * Start the code parser and return the result  
	 *
	 * @param Jane\Scope 		$scope
	 * 
	 * @return Jane\Scope
	 */
	public function parse( $scope = null )
	{
		// if there is already a scope given
		if ( !is_null( $scope ) )
		{
			if ( !( $scope instanceof Scope ) )
			{
				throw new Exception( 'The given scope is not an instance of Jane\\Scope' );
			}
		}
		else
		{
			$scope = new Scope;
		}
		
		// set the current scope
		$this->currentScope = $scope;
		
		// start parsing trought the tokens
		for( $this->index = 0; $this->index < $this->tokenCount; $this->index++ )
		{
			// add the recived code node to the current scope
			$this->currentScope->addNode( $this->next() );
		}
		
		return $this->currentScope;
	}
	
	/**
	 * Parse the next token
	 *
	 * @return Jane\Node
	 */
	protected function next()
	{
		$node = $this->currentToken();
		
		// if we have a primitve dataType it might be a variable
		// or a function declaration coming 
		if ( $this->nextToken() && substr( $node->type, 0, strlen( 'primitive' ) ) === 'primitive' )
		{
			// function definition incoming?
			if ( $this->nextToken()->type === 'function' )
			{
				$this->skipToken();
				return $this->praseFunction( $node->type );
			}
			
			// var declaration
			elseif ( $this->nextToken()->type === 'identifier' )
			{
				$this->skipToken();
				return $this->parseVarDeclaration( $node->type );
			}
		}
		
		// default parser action
		elseif ( in_array( $node->type, $this->customParserActionTokens ) )
		{
			$node = call_user_func( array( $this, 'parse'.ucfirst( $node->type ) ) );
		}
		
		return $node;
	}
	
	/**
	 * Parse an incoming function declaration
	 *
	 * @param Jane\Node			$node
	 * @return Jane\Node
	 */
	protected function parseFunction( $node )
	{
		if ( $this->nextToken()->type !== 'identifier' )
		{
			throw new Exception( 'no identifier given for function on line:'.$node->line );
		}
		
		$name = $this->nextToken()->value;
		$arguments = array();
		
		// check if the function implements arguments
		if ( $this->nextToken(2)->type === 'seperator' )
		{
			$tokenIndex = 3;
			$nextToken = $this->nextToken( $tokenIndex );
			$argumentIndex = 0;
			
			// until the scope get opend
			while ( $nextToken->type !== 'scopeOpen' ) 
			{
				if ( !isset( $arguments[$argumentIndex] ) )
				{
					$arguments[$argumentIndex] = array(
						'dataType' => null,
						'name' => null,
						'default' => null,
					);
				}
				
				// primitive dataType
				if ( $nextToken->isPrimitiveDefinition() )
				{
					// if the dataType has already been set
					if ( isset( $arguments[$argumentIndex]['dataType'] ) )
					{
						throw new Exception( 'the data type for this argument has already been set on line '.$nextToken->line );
					}
					
					$arguments[$argumentIndex]['dataType'] = $nextToken->type;
				}
				
				// is the name ( identifier )
				elseif ( $nextToken->type === 'identifier' )
				{
					// if the identifier has already been set
					if ( isset( $arguments[$argumentIndex]['name'] ) )
					{
						throw new Exception( 'the identifier for this argument has already been set on line '.$nextToken->line );
					}
					
					$arguments[$argumentIndex]['name'] = $nextToken->value;
				}
				
				// next argument
				elseif ( $nextToken->type === 'comma' )
				{
					$argumentIndex++;
				}
				
				// default value
				elseif ( $nextToken->type === 'equal' )
				{
					$tokenIndex++;
					$nextToken = $this->nextToken( $tokenIndex );
					
					if ( !$nextToken->isAssignableValue() )
					{
						throw new Exception( 'unexpected "'.$nextToken->type.'" given at line '.$nextToken->line );
					}
					
					$arguments[$argumentIndex]['default'] = $nextToken->value;
				}
				
				// something else? nope
				else
				{
					throw new Exception( 'unexpected "'.$nextToken->type.'" given at line '.$nextToken->line );
				}
				
				// set next token
				$tokenIndex++;
				$nextToken = $this->nextToken( $tokenIndex );
			}
			
			$this->skipToken( $tokenIndex-2 );
		}
		
		$this->skipToken(2);
		
		return new FunctionDefinition( null, $name, $arguments, $this->parseScopeBlock() );
	}
	
	/**
	 * Parse an scope block of code
	 *
	 * @return Jane\Node\ScopeBlock
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
	
	/**
	 * Parse an incoming identifier
	 *
	 * @param Jane\Node			$node
	 * @return Jane\Node
	 */
	protected function parseIdentifier()
	{
		if ( !$this->nextToken() )
		{
			throw new Exception( 'unexpected "'.$this->currentToken()->type.'" given at line '.$this->currentToken()->line );
		}
		
		// check if var assignment
		if ( $this->nextToken()->isAssignNode() )
		{
			return $this->parseVarAssignment();
		}
		
		// check if identifier list followed by an assignment
		if ( $this->nextToken()->type === 'comma' )
		{
			$inListAssignment = true;
			$nodeIndex = 1;
			
			while( $inListAssignment )
			{
				$nodeIndex++;
				
				if ( $this->nextToken( $nodeIndex )->type === 'comma' || 
					$this->nextToken( $nodeIndex )->type === 'identifier' || 
					$this->nextToken( $nodeIndex )->isAssignNode() )
				{
					if ( $this->nextToken( $nodeIndex )->isAssignNode() )
					{
						// if there is no other token than comma or identifiers until
						// an equal appears we have a list assignment.
						return $this->parseVarListAssignment( $this->currentToken );
					}
				}
				else
				{
					$inListAssignment = false;
				}
			}
		}
		
		return $node;
	}

	/**
	 * Parse a var declaration
 	 *
 	 * @return Jane\Node\VarAssignment
	 */
	protected function parseVariable()
	{
		$this->skipToken();
		return $this->parseVarDeclaration();
	}

	/**
	 * Parse a var declaration
	 * 
 	 * @param string						$dataType
 	 *
 	 * @return Jane\Node\VarAssignment
	 */
	protected function parseVarDeclaration( $dataType = null )
	{
		// if there is a dataType check if its valid
		if ( !is_null( $dataType ) )
		{
			if ( !$this->isValidDataType( $dataType ) )
			{
				throw new Exception( 'Invalid data type '.$dataType.' given at line'.$this->currentToken()->line );
			}
		}

		$vars = array();
		
		// current token has to bee an identifier!
		if ( $this->currentToken()->type !== 'identifier' )
		{
			throw $this->errorUnexpectedToken( $this->currentToken() );
		}

		while ( !$this->isEndOfExpression() )
		{
			// we have now all needed data to declar the var
			if ( $this->currentToken()->type === 'identifier' )
			{
				$identifier = $this->currentToken()->value;

				// check for overwrite
				if ( $this->currentScope->hasVar( $identifier ) )
				{
					if ( !Jane::config( 'allow_var_overwrite' ) )
					{
						throw new Exception( 'Variable "'.$identifier.'" has already been declared. line: '.$this->currentToken()->line );
					}
				}

				// create a var object by adding it to the current scope
				$var = $this->currentScope->addVar( $identifier, $dataType );

				$vars[] = new VarDeclaration( $var );

				// if there is a next token
				if ( $nextToken = $this->nextToken() )
				{
					// if the next node is some assign thingy
					if ( $nextToken->isAssignNode() )
					{
						$vars = array_merge( $vars, $this->parseVarAssignment() );
					}
				}
			}

			// there might also follow another declartation
			elseif ( $this->currentToken()->type === 'comma' )
			{		
				$this->skipToken(); // skip the comma
				$vars = array_merge( $vars, $this->parseVarDeclaration( $dataType ) );
				break; // break the loop
			}

			// if something else syntax error
			else
			{
				throw $this->errorUnexpectedToken( $this->currentToken() );
			}

			$this->skipToken();
		}

		return $vars;
	}
	
	/**
	 * Parse an var assignment
 	 * 
 	 * @return Jane\Node\VarAssignment
	 */
	protected function parseVarAssignment()
	{
		// no identifier?
		if ( $this->currentToken()->type !== 'identifier' )
		{
			return errorUnexpectedToken( $this->currentToken() );
		}

		// check if next token is an assigner
		if ( !$this->nextToken()->isAssignNode() )
		{
			return errorUnexpectedToken( $this->nextToken() );
		}

		// check if the var is declared
		if ( !$var = $this->currentScope->getVar( $this->currentToken()->value ) )
		{
			throw new Exception( 'Assignement to undeclared identifier "'.$this->currentToken()->value.'" on line: '.$this->currentToken()->line );
		}

		$assigner = $this->nextToken(1)->type; 
		$this->skipToken(2); // skip the identifier and the assigner

		$values = array();

		// now parse everything until the end of the expression or the next comma
		while ( !$this->isEndOfExpression( true ) )
		{
			$values[] = $this->currentToken();
			$this->skipToken();
		}
		
		// create assignment node
		return array( new VarAssignment( $var, $assigner, $this->parseExpression( $values ) ) );
	}

	/**
	 * Parse an var assignment
 	 * 
 	 * @param Jane\Node
 	 * @return Jane\Node\VarAssignment
	 */
	protected function parseVarListAssignment( $node )
	{	
		$identifier = $node->value;
		
		$node = new VarAssignment();

		return $node;
	}
}