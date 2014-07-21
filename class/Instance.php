<?php
/**
 * Description of Instance
 *
 * @author sparks
 * @version 0.0.1
 */
class Instance {
	
	public static $_version = '0.0.1';
	
	public static $_INSTANCES_ = array();
	
	const _INSTANCE_ = 'instance';
	const _TYPE_ = 'type';
	
	function __construct() {
		self::$_INSTANCES_[] = array(
			self::_TYPE_ => get_class($this),
			self::_INSTANCE_ => $this,
		);
	}
	
	public static function getInstances($type = null) {
		$instances = array();
		if(!empty(self::$_INSTANCES_)) {
			foreach(self::$_INSTANCES_ as $instance) {
				if(!$type || $type && $instance[self::_TYPE_] == $type) {
					$instances[] = $instance[self::_INSTANCE_];
				}
			}
		}
		return $instances;
	}
	
	public function __toArray() {
		return (array) $this;
	}
	
}
