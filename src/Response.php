<?php
/**
 * Class Response
 *
 * @filesource   Response.php
 * @created      03.10.2016
 * @package      GW2Wiki\GWWBot
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot;

/**
 *
 */
class Response{

	public $_raw;
	public $_command;
	public $_body;
	public $_matches;

	public $nick;
	public $ident;
	public $host;
	public $action;
	public $channel;
	public $param1;
	public $param2;
	public $message;

}
