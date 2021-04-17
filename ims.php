<?php

IMS::set_output('tty');
IMS::set_log_level(LogLevel::Debug);
IMS::emit(['chann' => 'PHP', 'level' => LogLevel::Debug, 'text' => 'Some thing went wrong...', 'file' => __file__]);
IMS::emit(['chann' => 'PHP', 'level' => LogLevel::Error, 'text' => 'Some thing went wrong...', 'file' => __file__]);
IMS::emit(['chann' => 'Compiler', 'level' => LogLevel::Error, 'text' => 'Some thing went wrong...', 'file' => __file__]);

class LogLevel
{
    const Emergency = 7;
    const Alert     = 6;
    const Critical  = 5;
    const Error     = 4;
    const Warning   = 3;
    const Notice    = 2;
    const Info      = 1;
    const Debug     = 0;

    static public function get_name(int $val) {
        switch ($val) {
            case self::Debug: return 'Debug';
            case self::Info: return 'Info';
            case self::Notice: return 'Notice';
            case self::Warning: return 'Warning';
            case self::Error: return 'Error';
            case self::Critical: return 'Critical';
            case self::Alert: return 'Alert';
            case self::Emergency: return 'Emergency';
        }
    }
}

class Out
{
    static public function writetty($infos)
    {
        echo '  ['.$infos['chann'].'] ';
        echo LogLevel::get_name($infos['level']) . ': ';
        echo $infos['text'] . PHP_EOL;
        if ($infos['file']||$infos['line']||$infos['col']) {
            echo "\t";
            if ($infos['file']) echo "file:".$infos['file'].', ';
            if ($infos['line']) echo "line: ".$infos['line'].', ';
            if ($infos['col']) echo "column: ".$infos['col'].'.' ;
            echo PHP_EOL;
        }
    }
}

/**
 * Internal Messaging System
 */
class IMS
{
    static private $storage    = [];
    static private $do_storage = false;
    static private $output     = 'tty';
    static private $log_level  = LogLevel::Info;

    static public function get_data() {
        return self::$storage;
    }

    static public function set_log_level(int $level = LogLevel::Info) {
        self::$log_level = $level;
    }

    static public function set_output($type) {
        self::$output = $type;
    }

    /**
     * Emit a message into the system.
     *
     * @param infos
     *      An assiociative array which may contain the following keys:
     *      - 'chann':  Emitter channel,
     *      - 'level':  Log level,
     *      - 'text':   The message,
     *
     * @return void
     */
    static public function emit(string $channel, array $infos)
    {
        $infos += [
            'chann' => $channel,
            'level' => LogLevel::Info,
            'text'  => '',
            'file'  => '',
            'line'  => 0,
            'col'   => 0,
        ];

        if ($infos['level'] < self::$log_level) {
            return;
        }

        if (self::$do_storage) {
            self::$storage[] = $infos ;
        }

        if (self::$output) {
            Out::{"write".self::$output}($infos);
        }
    }
}