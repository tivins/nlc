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

/**
 * Internal Messaging System (IMS).
 *
 * @todo Add an "emiting channel".
 */
class Msg
{
	const failure = 1; /// @todo Rename to `exit_failure`.
	const success = 0; /// @todo Rename to `exit_success`.

	static private $history = [];
	static private $store_history = false;
	static private $output = true;
	static private $file = false;

	// ----- getters / setters -----

	static public function set_output(bool $value) { self::$output = $value; }
	static public function set_store_history(bool $value) { self::$store_history = $value; }
	static public function get_history() { return self::$history; }
	static public function reset_history() { self::$history = []; }


	// ----- public emit functions -----

	static public function error($msg, $file = '', $line = 0, $column = 0)
	{
		self::emit(Output::write_error('error'),
				   Output::write_text_error($msg),
				   $file, $line, $column);
		return self::failure;
	}
	static public function exception($ex)
	{
		self::emit(Output::write_error('exception'),
				   $ex->getMessage(), $ex->getFile(), $ex->getLine());
		return self::failure;
	}
	static public function fatal($msg)
	{
		self::emit(Output::write_error('fatal'), Output::write_text_error($msg));
		die(self::failure);
	}
    static public function debug($msg)
    {
        self::emit(Output::write(Output::dim, 'debug'), Output::write(Output::dim, $msg));
        return self::success;
    }
	static public function warn($msg, $file = '', $line = 0, $col = 0)
	{
		self::emit(self::fmt_warn('warning'), $msg, $file, $line, $col);
		return self::success;
	}
	static public function info($msg)
	{
		self::emit('info', $msg);
		return self::success;
	}
	static public function time($name = null, $time = null)
	{
		self::emit('chrono', "$name during ".round($time,4)."s");
		return self::success;
	}
	static public function funcline($function, $line)
	{
		return self::debug("$function ($line)");
	}
	static public function test(bool $status)
	{
		$msg = self::fmt_success("Test OK");
		if (!$status) $msg = self::fmt_error("Test failed");
		self::emit('[test result]', $msg);
		return self::success;
	}

	/**
	 * Main (internal) function used to emit a message.
	 */
	static private function emit($type, $msg, $file = '', $line = 0, $column = 0)
	{
		if (self::$store_history) {
			self::$history[] = [
				'date' => time(),
				'type' => $type,
				'msg' => $msg,
				'file' => $file,
				'line' => $line,
				'column' => $column,
			];
		}
		if (self::$output) {
			// echo '['.date('Y-m-d H:i:s'). '] ' ;
			echo '  ' .$type . ': ' . $msg . "\n";
			if ($file) {
				echo "\t".'file: '.$file. ', line: '.$line.', column: '.$column."\n";
			}
		}
	}

	// --- Formatting functions ---

	const pty_error = "\e[1;4;97;41m"; /// @todo Rename to `tty_`.
	const pty_warn = "\e[1;97;43m"; /// @todo Rename to `tty_`.
	const pty_dim = "\e[2m"; /// @todo Rename to `tty_`.
	const pty_success = "\e[1;97;42m"; /// @todo Rename to `tty_`.
	const pty_reset = "\e[0m"; /// @todo Rename to `tty_`.

	static public function fmt_error($text) {
		return self::pty_error . $text . self::pty_reset;
	}
	static public function fmt_texterror($text) {
		return "\e[33m" . $text . self::pty_reset;
	}
	static public function fmt_high($text) {
		return "\e[1m" . $text . self::pty_reset;
	}
	static public function fmt_success($text) {
		return self::pty_success . $text . self::pty_reset;
	}
	static public function fmt_dim($text) {
		return self::pty_dim . $text . self::pty_reset;
	}
	static public function fmt_warn($text) {
		return self::pty_warn . $text . self::pty_reset;
	}
}