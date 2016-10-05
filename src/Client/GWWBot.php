<?php
/**
 * Class GWWBot
 *
 * @filesource   GWWBot.php
 * @created      03.10.2016
 * @package      GW2Wiki\GWWBot\Client
 * @author       Smiley <smiley@chillerlan.net>
 * @copyright    2016 Smiley
 * @license      MIT
 */

namespace GW2Wiki\GWWBot\Client;

use chillerlan\TinyCurl\Traits\RequestTrait;
use GW2Wiki\GWWBot\Config;
use GW2Wiki\GWWBot\Language\GWWEnglish;

/**
 *
 */
class GWWBot extends ClientAbstract{
	use RequestTrait;

	const REGEX = [
		'dice' => '/(:?[\s]+)?roll(:?[\s]+)?(?P<dice>[\d]{1,3})/i',
		'ytinfo' => '#(?P<url>http(:?s)?\://(:?www.youtube.com|youtu.be)[^\s]*)#isu',
#		'ytsearch' => '/!(:?yt|youtube)\s(?P<q>.*)/i',
		'gw2wde' => '/\[\[\:gw2de\:(?P<article>[^\]]+)\]\]/i',
		'gww' => '/\[\[\:gww\:(?P<article>[^\]]+)\]\]/i',
		'wikipedia' => '/\[\[\:wp\:(?P<article>[^\]]+)\]\]/i',
		'guildwiki' => '/\[\[\:de\:(?P<article>[^\]]+)\]\]/i',
		'gw2wiki' => '/\[\[(?P<article>[^\]]+)\]\]/i',
	];

	/**
	 * @var \GW2Wiki\GWWBot\Language\GWWEnglish
	 */
	protected $lang;

	/**
	 * GWWBot constructor.
	 *
	 * @param \GW2Wiki\GWWBot\Config              $config
	 * @param \GW2Wiki\GWWBot\Language\GWWEnglish $lang
	 */
	public function __construct(Config $config, GWWEnglish $lang){
		parent::__construct($config, $lang);
	}

	/**
	 * @return string
	 */
	protected function chat(){

		foreach(self::REGEX as $m => $r){
			if(preg_match($r, $this->data->message, $match) > 0){
				return call_user_func_array([$this, $m], [$match]);
			}
		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> '.$this->data->message;
	}

	/**
	 * @return string
	 */
	protected function prefix_dm_password(){
		$c = $this->command[0];

		if($c === 'kill' || $c === 'die' || $c === 'stop'){
			$this->log('recived die command from <'.$this->data->nick.'@'.$this->data->channel.'>');
			$this->send('QUIT :'.'forced death ¯\_(ツ)_/¯');

			sleep(1);
			fclose($this->socket);

			exit;
		}
		else if($c === 'op' && isset($this->command[2]) && !empty($this->command[2])){
			$cmd = explode(' ', $this->command[2], 2);

			$this->send('MODE '.$cmd[0].' +o '.(isset($cmd[1]) && !empty($cmd[1])) ? $cmd[1] : $this->config->owner);
		}
		else if($c === 'deop' && isset($this->command[2]) && !empty($this->command[2])){
			$cmd = explode(' ', $this->command[2], 2);

			$this->send('MODE '.$cmd[0].' -o '.(isset($cmd[1]) && !empty($cmd[1])) ? $cmd[1] : $this->config->owner);
		}
		else if($c === 'join' && isset($this->command[2]) && !empty($this->command[2])){
			$this->send('JOIN '.$this->command[2]);
			$this->send('MODE '.$this->command[2]);
		}
		else if($c === 'leave' && isset($this->command[2]) && !empty($this->command[2])){
			$this->send('PART '.$this->command[2].' : leaving');
		}
		else if($c === 'say' && isset($this->command[2]) && !empty($this->command[2])){
			$cmd = explode(' ', $this->command[2], 2);
			$this->say($cmd[0], $cmd[1]);
		}
		else{
			var_dump([__METHOD__, $this->command]);
		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> '.print_r($this->command, true);
	}

	/**
	 * @return string
	 */
	protected function prefix_dm_nopassword(){
		$c = $this->command[0];
		if($c === 'stats'){
			$this->say($this->data->channel, $this->me(sprintf($this->lang->stats, number_format(memory_get_usage() / 1024, 2), number_format(memory_get_peak_usage() / 1024, 2), $this->uptime())));
		}
		else{
			var_dump([__METHOD__, $this->command]);

		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> '.print_r($this->command, true);
	}

	/**
	 * @return string
	 */
	protected function prefix_channel(){
		$c = $this->command[0];

		if(($c === 'yt' || $c === 'youtube') && isset($this->command[2]) && !empty($this->command[2])){
			return $this->ytsearch(['q' => $this->command[2]]);
		}
		else if($c === 'stats'){
			$this->say($this->data->channel, $this->me(sprintf($this->lang->stats, number_format(memory_get_usage() / 1024, 2), number_format(memory_get_peak_usage() / 1024, 2), $this->uptime())));
		}
/*
		// u brave, huh?
		else if($c === 'op'){
			$this->send('MODE '. $this->data->channel.' +o '.$this->config->owner);
		}
		else if($c === 'deop'){
			$this->send('MODE '. $this->data->channel.' -o '.$this->config->owner);
		}
*/
		else{
			var_dump([__METHOD__, $this->command]);

		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> '.print_r($this->command, true);
	}

	/**
	 * @param $wiki
	 * @param $article
	 *
	 * @return string
	 */
	protected function wikilink($wiki, $article){
		$link = $wiki.str_replace(' ', '_', $article);

		$this->say($this->data->channel, $this->me($link));

		return '<'.$this->data->nick.'@'.$this->data->channel.'> wiki link '.$link;
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function dice($match){

		if($match['dice'] > 1 && $match['dice'] <= 100){
			$this->say($this->data->channel, $this->me(sprintf($this->lang->roll100, $this->data->nick, mt_rand(1, $match['dice']), $match['dice'])));
		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> roll '.$match['dice'];
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function ytsearch($match){
		$response = $this->fetch('https://www.googleapis.com/youtube/v3/search', [
			'q'          => $match['q'],
			'part'       => 'snippet',
			'key'        => $this->config->googleapi,
			'maxResults' => 3,
#			'order'      => 'date',
		])->json;

		if(isset($response->items) && is_array($response->items)){
			$this->say($this->data->channel, $this->me(sprintf($this->lang->ytresults, $match['q'])));

			foreach($response->items as $v){
				if(isset($v->id->videoId)){
					$this->say($this->data->channel, $this->me(sprintf($this->lang->ytlink, $v->id->videoId, $v->snippet->title)));
				}
				else if(isset($v->id->channelId)){
					$this->say($this->data->channel, $this->me(sprintf($this->lang->ytchan, $v->id->channelId, $v->snippet->title)));
				}
				else{
					$this->log('[FIXME] '.print_r($v, true));
				}
				sleep(1);
			}

		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> '.sprintf($this->lang->ytresults, $match['q']);
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function ytinfo($match){
		$url = parse_url($match['url']);
		$id  = false;

		if($url['host'] === 'www.youtube.com'){
			parse_str($url['query'], $q);
			$id = isset($q['v']) ? $q['v'] : false;
		}
		elseif($url['host'] === 'youtu.be'){
			$e  = explode('/', $url['path'], 2);
			$id = isset($e[1]) ? $e[1] : false;
		}

		if($id){

			$response = $this->fetch('https://www.googleapis.com/youtube/v3/search', [
				'q'          => $id,
				'part'       => 'snippet',
				'key'        => $this->config->googleapi,
				'maxResults' => 1,
			])->json;

			if(isset($response->items, $response->items[0])){

				if(isset($response->items[0]->id->videoId) && $response->items[0]->id->videoId === $id){
					$this->say($this->data->channel, $this->me('['.$response->items[0]->snippet->title.']'));
				}
				else if(isset($response->items[0]->id->kind) && $response->items[0]->id->kind === 'youtube#playlist'){
					if(isset($response->regionCode) && $response->regionCode === 'DE'){
						$this->say($this->data->channel, $this->me('['.$this->lang->ytnoinfo.']'));
					}
				}
				else{
					$this->log('[FIXME] '.print_r($response, true));
				}

			}

		}

		return '<'.$this->data->nick.'@'.$this->data->channel.'> youtube video '.$match['url'];
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function gww($match){
		return $this->wikilink('https://wiki.guildwars.com/wiki/', $match['article']);
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function guildwiki($match){
		return $this->wikilink('http://www.guildwiki.de/wiki/', $match['article']);
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function gw2wiki($match){
		return $this->wikilink('https://wiki.guildwars2.com/wiki/', $match['article']);
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function gw2wde($match){
		return $this->wikilink('https://wiki-de.guildwars2.com/wiki/', $match['article']);
	}

	/**
	 * @param $match
	 *
	 * @return string
	 */
	protected function wikipedia($match){
		return $this->wikilink('https://en.wikipedia.org/wiki/', $match['article']);
	}

}
