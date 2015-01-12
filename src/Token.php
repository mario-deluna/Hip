<?php namespace Hip;
/**
 * Hip Token
 **
 *
 * @package 		Hip
 * @author			Mario Döring <mario@clancats.com>
 * @version			1.0
 * @copyright 		2015 ClanCats GmbH
 *
 */

class Token
{	
	/**
	 * The type of this token
	 *
	 * @var string
	 */
	public $type = null;

	/**
	 * The value of this token
	 *
	 * @var int
	 */
	public $value = null;

	/**
	 * The line this token is in the code
	 *
	 * @var int
	 */
	public $line = 0;

	/**
	 * The constructor
	 *
	 * @var array 		$token
	 * @return void
	 */
	public function __construct( array $token )
	{
		list( $this->type, $this->value, $this->line ) = $token;
	}
	
	/**
	 * Is this a value token?
	 *
	 * @return bool
	 */
	public function isValue()
	{
		return 
			$this->type === 'string' || 
			$this->type === 'number' ||
			$this->type === 'null' ||
			$this->type === 'boolTrue' ||
			$this->type === 'boolFalse';
	}
}