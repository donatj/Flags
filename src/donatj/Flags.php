<?php

namespace donatj;

use donatj\Exceptions\InvalidFlagParamException;
use donatj\Exceptions\InvalidFlagTypeException;

class Flags {

	private $defined_flags = array();
	private $defined_short_flags = array();
	private $arguments = array();

	public function arg( $index ) {
		return isset($this->arguments[$index]) ? $this->arguments[$index] : null;
	}

	/**
	 * @return string[]
	 */
	public function args() {
		return $this->arguments;
	}

	public function &short( $letter, $usage = '' ) {
		$this->defined_short_flags[$letter[0]] = array(
			'value' => 0,
			'usage' => $usage,
		);

		return $this->defined_short_flags[$letter[0]]['value'];
	}

	public function &bool( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('bool', $name, $value, $usage);
	}

	public function &float( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('float', $name, $value, $usage);
	}

	public function &int( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('int', $name, $value, $usage);
	}

	public function &uint( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('uint', $name, $value, $usage);
	}

	public function &string( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('string', $name, $value, $usage);
	}

	private function &_storeFlag( $type, $name, $value, $usage ) {

		$this->defined_flags[$name] = array(
			'type'     => $type,
			'usage'    => $usage,
			'required' => $value === null,
			'value'    => $value,
		);

		return $this->defined_flags[$name]['value'];

	}

	public function getDefaults() {

		$output = '';
		$final  = array();
		$max    = 0;

		foreach( $this->defined_short_flags as $char => $data ) {
			$final["-{$char}"] = $data['usage'];
		}

		foreach( $this->defined_flags as $flag => $data ) {
			$final["--{$flag}"] = $data['usage'];
			$max                = max($max, strlen($flag));
		}

		foreach( $final as $flag => $usage ) {
			$output .= sprintf('%' . ($max + 5) . 's', $flag) . "   {$usage}" . PHP_EOL;
		}

		return $output;

	}

	/**
	 * @param array $args
	 * @throws Exceptions\InvalidFlagParamException
	 * @throws Exceptions\InvalidFlagTypeException
	 */
	public function parse( array $args = null ) {

		if( $args === null ) {
			$args = $GLOBALS['argv'];
		}

		$longParams  = array();
		$shortParams = array();
		$startArgs   = false;

		$cmd = array_shift($args);

		$get_val = false;
		foreach( $args as $arg ) {
			if( $arg == '--' ) {
				$startArgs = true;
			} elseif( $arg[0] == '-' && !$startArgs ) {
				$cleanArg = ltrim($arg, '- ');

				$get_val = false;

				if( $arg[1] == '-' ) {
					$split = explode('=', $arg, 2);

					if( count($split) > 1 ) {
						$longParams[ltrim(reset($split), '- ')] = end($split);
					} else {
						$get_val = $cleanArg;
					}
				} else {
					$split = str_split($cleanArg);
					foreach( $split as $char ) {
						$shortParams[$char] = isset($shortParams[$char]) ? $shortParams[$char] + 1 : 1;
					}
				}
			} elseif( $get_val !== false && !$startArgs ) {
				$longParams[$get_val] = $arg;
				$get_val              = false;
			} else {
				$this->arguments[] = $arg;
			}
		}

		foreach( $longParams as $name => $value ) {
			if( !isset($this->defined_flags[$name]) ) {
				throw new InvalidFlagParamException('Unknown option: --' . $name);
			} else {
				$defined_flag =& $this->defined_flags[$name];

				if( $this->validateType($defined_flag['type'], $value) ) {
					$defined_flag['value']  = $value;
					$defined_flag['parsed'] = true;
				} else {
					throw new InvalidFlagTypeException('Option --' . $name . ' expected type: "' . $defined_flag['type'] . '"');
				}
			}
		}

		foreach( $shortParams as $char => $value ) {
			if( !isset($this->defined_short_flags[$char]) ) {
				throw new InvalidFlagParamException('Unknown option: -' . $char);
			} else {
				$this->defined_short_flags[$char]['value'] = $value;
			}
		}

	}

	/**
	 * @param string $type
	 * @param mixed  $value
	 * @return bool
	 */
	private function validateType( $type, &$value ) {
		$validate = array(
			'bool'   => function ( &$val ) {
				$val = strtolower((string)$val);
				if( $val == "0" || $val == 'f' || $val == 'false' ) {
					$val = false;

					return true;
				} elseif( $val == "1" || $val == 't' || $val == 'true' ) {
					$val = true;

					return true;
				}

				return false;
			},
			'uint'   => function ( &$val ) {
				if( ctype_digit((string)$val) ) {
					$val = intval($val);

					return true;
				}

				return false;
			},
			'int'    => function ( &$val ) {
				if( is_numeric($val) ) {
					$val = intval($val);

					return true;
				}

				return false;
			},
			'float'  => function ( &$val ) {
				if( is_numeric($val) ) {
					$val = floatval($val);

					return true;
				}

				return false;
			},
			'string' => function ( &$val ) {
				$val = (string)$val;

				return true;
			},
		);

		$test = $validate[$type];

		return $test($value);
	}

}