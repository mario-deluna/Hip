<?php namespace Hip;
/**
 * Hip Compiler
 **
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 */
class Compiler
{	
	/**
	 * The data to be compiled
	 *
	 * @var string
	 */
	protected $data = null;

	/**
	 * The constructor
	 *
	 * @var string 		$code
	 * @return void
	 */
	public function __construct( $data )
	{
		$this->data = $data;
	}
	
	/**
	 * Is the current array associative
	 *
	 * @param array 			$data
	 * @return bool
	 */
	protected function isAssoc( array $data )
	{
		return array_keys( $data ) !== range( 0, count( $data ) - 1 );
	}
	
	/**
	 * Check if an array contains other arrays
	 *
	 * @param array 			$data
	 * @return bool
	 */
	protected function containsArrays( array $data )
	{
		foreach ( $data as $value ) 
		{
			if ( is_array( $value ) )
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check if an array only contains other arrays
	 *
	 * @param array 			$data
	 * @return bool
	 */
	protected function onlyContainsArrays( array $data )
	{
		foreach ( $data as $value ) 
		{
			if ( !is_array( $value ) )
			{
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Compiles the current data 
	 *
	 * @return string
	 */
	public function transform()
	{
		$buffer = '';
		
		// If this stuff is true we assume that we have an collection
		if ( is_array( $this->data ) && !$this->isAssoc( $this->data ) && $this->onlyContainsArrays( $this->data ) )
		{
			return $this->transformCollection( $this->data );
		}
		
		foreach( $this->data as $key => $value )
		{
			// first of all we have to check if our value is 
			// an array then we might have an short comma list
			if ( is_array( $value ) && !$this->isAssoc( $value ) && !$this->containsArrays( $value ) )
			{
				$value = $this->transformCommaSeperatedArray( $value );
			}
			
			// or just an normal array 
			elseif ( is_array( $value ) )
			{
				if ( empty( $value ) )
				{
					$value = "--";
				}
				else
				{
					$value = ( is_string( $key ) ? "\n" : '' ). $this->compileNextLevel( $value );
				}
			}	
			
			// everyting else should be a normal value
			else
			{
				$value = $this->transformValue( $value );
			}
			
			// if we have an string as key we assume that its a associative
			// item and not a sequential one @toDo: there has to be a better solution??
			if ( is_string( $key ) )
			{
				$buffer .= $key.": ";
			}
			
			$buffer .= $value."\n";
		}
		
		return $buffer;
	}
	
	/**
	 * Compiles an array on an higher level
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function compileNextLevel( $data )
	{
		$compiler = new static( $data );
		
		$buffer = "";
		
		foreach ( explode( "\n", $compiler->transform() ) as $line ) 
		{
			$buffer .= '  '.$line."\n";	
		}
		
		return '  '.trim( $buffer );
	}
	
	/**
	 * Transforms an array into an comma seperated list 
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function transformCommaSeperatedArray( array $data )
	{
		return implode( ', ', array_map( function( $value ) 
		{
			return $this->transformValue( $value );
		}, $data ));	
	}
	
	/**
	 * Transforms collection list 
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function transformCollection( array $data )
	{
		$buffer = "";
		
		$count = count( $data );
		
		foreach( $data as $key => $array )
		{
			$compiler = new static( $array );
			
			$buffer .= "-\n".$compiler->transform()."-";
			
			// if its the last item we don't need to add a break
			if ( $key !== $count - 2 )
			{
				$buffer .= "\n";
			}
		}
		
		return $buffer;
	}
	
	/**
	 * Tranform a value
	 *
	 * @param mixed 			$value
	 * @return string
	 */
	protected function transformValue( $value )
	{
		// strings
		if ( is_string( $value ) )
		{
			$value = '"'.str_replace( '"', '\\"', $value ).'"';
		}
		
		// bool true
		elseif ( $value === true )
		{
			$value = 'yes';
		}
		
		// bool false
		elseif ( $value === false )
		{
			$value = 'no';
		}
		
		// nothing null
		elseif ( $value === null )
		{
			$value = 'nil';
		}
		
		return $value;
	}
}