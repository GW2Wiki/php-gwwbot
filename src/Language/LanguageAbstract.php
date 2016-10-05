<?php
/**
 *
 * @filesource   LanguageAbstract.php
 * @created      04.10.2016
 * @package      GW2Wiki\GWWBot\Language
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot\Language;

/**
 * Class LanguageAbstract
 */
abstract class LanguageAbstract implements LanguageInterface{

	public $version         = 'SmileyBot 1.0.0, PHP%1$s/%2$s';
	public $connectionErrMsg = 'could not open connection (#%1$s - %2$s)';
	public $connectionError  = 'connection error';
	public $connected       = 'connected to %1$s:%2$s';
	public $logstart        = '************* ircbot start *************';
	public $stats = 'mem: %skb peak: %skb uptime: %s';

	public $msg_join   = '%1$s joined %2$s';
	public $msg_kick   = '%1$s was kicked from %2$s by %3$s';
	public $msg_quit   = '%1$s has quit IRC: %2$s';
	public $msg_part   = '%1$s has left %2$s';
	public $msg_nick   = '%1$s is now known as %2$s';
	public $msg_notice = '%1$s : %2$s';

	public $helloUser      = '%1$s %2$s';
	public $helloOwner     = '%1$s %2$s';
	public $helloOwnerAway = '%1$s %2$s %3$s';
	public $helloFromSelf  = '';
	public $helloNotice    = '%1$s %2$s';

	public $kickRejoin = '';
	public $kickOwner  = '%1$s';
	public $kickTaunt  = '%1$s';
}
