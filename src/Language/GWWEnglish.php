<?php
/**
 * Class GWWEnglish
 *
 * @filesource   GWWEnglish.php
 * @created      04.10.2016
 * @package      GW2Wiki\GWWBot\Language
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot\Language;

/**
 * @property string $roll100
 * @property string $ytresults
 * @property string $ytlink
 * @property string $ytchan
 * @property string $ytnoinfo
 */
class GWWEnglish extends LanguageAbstract{

	public $helloUser      = 'welcome to %1$s, %2$s!';
	public $helloOwner     = 'ohai %1$s! wb in %2$s!';
	public $helloOwnerAway = 'welcome to %1$s, %2$s! [my operator %3$s is currently away]';
	public $helloFromSelf  = 'meow';
	public $helloNotice    = 'WHERE IS WIKICHU';

	public $kickRejoin = 'meow!'; // sent as /me
	public $kickOwner  = '%s!';
	public $kickTaunt  = '¯\_(ツ)_/¯'; // sent as /me

	public $roll100 = '<%1$s> rolls %2$s on a %3$s sided die.';
	public $ytresults = 'youtube search results for "%s"';
	public $ytlink = 'https://www.youtube.com/watch?v=%1$s [%2$s]';
	public $ytchan = 'https://www.youtube.com/channel/%1$s [%2$s]';
	public $ytnoinfo = 'no video info available, probably blocked in germany.';
}
