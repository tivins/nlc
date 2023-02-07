<?php


abstract class IMSChannel {
	abstract function get_name():string ;
	abstract function debug():bool ;
}

class IMSDefault {
	public function get_name():string {
		return '';
	}
	public function debug():bool {
		return false;
	}
}

class IMS
{
	static private $channels_instances = [];

	static public function register_channel(IMSChannel $channel) {
		self::$channels_instances[$channel->get_name()] = $channel;
	}

	static public function get(string $channel_name) {
		if (!isset(self::$channels_instances[$channel_name])) {
			self::$channels_instances[$channel_name] = new IMSDefault();
		}
		return self::$channels_instances[$channel_name] ;
	}
}
