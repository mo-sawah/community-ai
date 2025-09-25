<?php
/**
 * Singleton Trait
 * Provides a standard singleton pattern for classes.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

trait AI_Community_Singleton {

    private static array $instances = [];

    /**
     * Get the singleton instance of the class.
     */
    public static function get_instance(...$args) {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static(...$args);
        }
        return self::$instances[$class];
    }

    /**
     * Protected constructor to prevent direct creation.
     */
    protected function __construct() {}

    /**
     * Prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance.
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize a singleton.");
    }
}