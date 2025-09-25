<?php
if (!defined('ABSPATH')) exit;

trait AI_Community_Singleton {
    private static array $instances = [];

    public static function get_instance(...$args) {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static(...$args);
        }
        return self::$instances[$class];
    }

    protected function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}
