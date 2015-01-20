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
	 * Compiles the current data 
	 *
	 * @return string
	 */
	public function transform()
	{
		$buffer = '';
		
		foreach( $this->data as $key => $value )
		{
			// first of all we have to check if our value is 
			// an array then we might have an short comma list
			if ( is_array( $value ) && !$this->isAssoc( $value ) && !$this->containsArrays( $value ) )
			{
				$value = $this->transformCommaSeperatedArray( $value );
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
	 * Transforms an array into an comma seperated list 
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function transformCommaSeperatedArray( array $data )
	{
		return implode( ', ', array_map( function( &$value ) 
		{
			
			$value = $this->transformValue( $value );var_dump( $value );
		}, $data ));	
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
	
	/**
	 * Compile an associative array
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function transformAssocArray( array $data )
	{
		$buffer = '';
		return $buffer;
	}
	
	/**
	 * Compile an sequential array
	 *
	 * @param array 			$data
	 * @return string
	 */
	protected function transformSequentialArray( array $data )
	{
		$buffer = '';
		
		foreach ( $data as $value ) 
		{
			$buffer .= $this->transformValue( $value )."\n";
		}
		
		return $buffer;
	}
}