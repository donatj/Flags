<?php

namespace donatj;

use donatj\Exceptions\InvalidFlagParamException;
use donatj\Exceptions\InvalidFlagTypeException;
use donatj\Exceptions\MissingFlagParamException;

class Flags {

	private $defined_flags = array();
	private $defined_short_flags = array();
	private $arguments = array();

	/**
	 * Returns the n'th command-line argument. `arg(0)` is the first remaining argument after flags have been processed.
	 *
	 * @param int $index
	 * @return string
	 */
	public function arg( $index ) {
		return isset($this->arguments[$index]) ? $this->arguments[$index] : null;
	}

	/**
	 * Returns the non-flag command-line arguments.
	 *
	 * @return string[] Array of argument strings
	 */
	public function args() {
		return $this->arguments;
	}

	/**
	 * Returns an array of long-flag values indexed by flag name
	 *
	 * @return array
	 */
	public function longs() {
		$out = array();
		foreach( $this->defined_flags as $key => $data ) {
			$out[$key] = $data['value'];
		}

		return $out;
	}

	/**
	 * Returns an array of short-flag call-counts indexed by character
	 *
	 * `-v` would set the 'v' index to 1, whereas `-vvv` will set the 'v' index to 3
	 *
	 * @return array
	 */
	public function shorts() {
		$out = array();
		foreach( $this->defined_short_flags as $key => $data ) {
			$out[$key] = $data['value'];
		}

		return $out;
	}

	/**
	 * Defines a short-flag of specified name, and usage string.
	 * The return value is a reference to an integer variable that stores the number of times the short-flag was called.
	 *
	 * This means the value of the reference for v would be the following.
	 *
	 *     -v => 1
	 *     -vvv => 3
	 *
	 * @param string $letter The character of the short-flag to define
	 * @param string $usage The usage description
	 * @return int
	 */
	public function &short( $letter, $usage = '' ) {
		$this->defined_short_flags[$letter[0]] = array(
			'value' => 0,
			'usage' => $usage,
		);

		return $this->defined_short_flags[$letter[0]]['value'];
	}

	/**
	 * Defines a bool long-flag of specified name, default value, and usage string.
	 * The return value is a reference to a variable that stores the value of the flag.
	 *
	 * Examples:
	 *
	 * Truth-y:
	 *
	 *      --mybool=[true|t|1]
	 *      --mybool [true|t|1]
	 *      --mybool
	 *
	 * False-y:
	 *
	 *      --mybool=[false|f|0]
	 *      --mybool [false|f|0]
	 *        [not calling --mybool and having the default false]
	 *
	 * @param string $name The name of the long-flag to define.
	 * @param mixed  $value The default value - usually false.
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &bool( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('bool', $name, $value, $usage);
	}

	/**
	 * Defines a float long-flag of specified name, default value, and usage string.
	 * The return value is a reference to a variable that stores the value of the flag.
	 *
	 * Examples:
	 *
	 *     --myfloat=1.1
	 *     --myfloat 1.1
	 *
	 * @param string $name The name of the long-flag to define.
	 * @param mixed  $value The default value
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &float( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('float', $name, $value, $usage);
	}

	/**
	 * Defines an integer long-flag of specified name, default value, and usage string.
	 * The return value is a reference to a variable that stores the value of the flag.
	 *
	 * Note: Float values trigger an error, rather than casting.
	 *
	 * Examples:
	 *
	 *     --myinteger=1
	 *     --myinteger 1
	 *
	 * @param string $name The name of the long-flag to define.
	 * @param mixed  $value The default value
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &int( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('int', $name, $value, $usage);
	}

	/**
	 * Defines a unsigned integer long-flag of specified name, default value, and usage string.
	 * The return value is a reference to a variable that stores the value of the flag.
	 *
	 * Note: Negative values trigger an error, rather than casting.
	 *
	 * Examples:
	 *
	 *     --myinteger=1
	 *     --myinteger 1
	 *
	 * @param string $name The name of the long-flag to define.
	 * @param mixed  $value The default value
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &uint( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('uint', $name, $value, $usage);
	}

	/**
	 * Defines a string long-flag of specified name, default value, and usage string.
	 * The return value is a reference to a variable that stores the value of the flag.
	 *
	 * Examples
	 *
	 *     --mystring=vermouth
	 *     --mystring="blind jazz singers"
	 *     --mystring vermouth
	 *     --mystring "blind jazz singers"
	 *
	 * @param string $name The name of the long-flag to define.
	 * @param mixed  $value The default value
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &string( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag('string', $name, $value, $usage);
	}


	/**
	 * @param string $type
	 * @param string $name
	 * @param mixed  $value
	 * @param string $usage
	 * @return mixed
	 */
	private function &_storeFlag( $type, $name, $value, $usage ) {

		$this->defined_flags[$name] = array(
			'type'     => $type,
			'usage'    => $usage,
			'required' => $value === null,
			'value'    => $value,
		);

		return $this->defined_flags[$name]['value'];

	}

	/**
	 * Returns the default values of all defined command-line flags as a formatted string.
	 *
	 * Example:
	 *
	 *               -v   Output in verbose mode
	 *      --testsuite   [string] Which test suite to run.
	 *      --bootstrap   [string] A "bootstrap" PHP file that is run before the specs.
	 *           --help   Display this help message.
	 *        --version   Display this applications version.
	 *
	 * @return string
	 */
	public function getDefaults() {

		$output = '';
		$final  = array();
		$max    = 0;

		foreach( $this->defined_short_flags as $char => $data ) {
			$final["-{$char}"] = $data['usage'];
		}

		foreach( $this->defined_flags as $flag => $data ) {
			$key         = "--{$flag}";
			$final[$key] = ($data['required'] ?
					"<{$data['type']}> " :
					($data['type'] == 'bool' ?
						'' :
						"[{$data['type']}] "
					)) . $data['usage'];
			$max         = max($max, strlen($key));
		}

		foreach( $final as $flag => $usage ) {
			$output .= sprintf('%' . ($max + 5) . 's', $flag) . "   {$usage}" . PHP_EOL;
		}

		return $output;

	}

	/**
	 * Parses flag definitions from the argument list, which should not include the command name.
	 * Must be called after all flags are defined and before flags are accessed by the program.
	 *
	 * Will throw exceptions on Missing Require Flags, Unknown Flags or Incorrect Flag Types
	 *
	 * @param array $args The arguments to parse, defaults to $GLOBALS['argv']
	 * @param bool  $ignoreExceptions
	 * @throws Exceptions\MissingFlagParamException
	 * @throws Exceptions\InvalidFlagParamException
	 * @throws Exceptions\InvalidFlagTypeException
	 */
	public function parse( array $args = null, $ignoreExceptions = false ) {

		if( $args === null ) {
			$args = $GLOBALS['argv'];
		}

		$longParams  = array();
		$shortParams = array();
		$startArgs   = false;

		$cmd = array_shift($args);

		$forceValue = false;
		$getValue   = false;
		foreach( $args as $arg ) {
			if( $arg[0] == '-' && !$startArgs && !$forceValue ) {
				$cleanArg = ltrim($arg, '- ');

				if( $getValue ) {
					$longParams[$getValue] = true;
				}

				$getValue = false;

				if( $arg == '--' ) {
					$startArgs = true;
				} elseif( $arg[1] == '-' ) {
					$split = explode('=', $arg, 2);

					if( count($split) > 1 ) {
						$longParams[ltrim(reset($split), '- ')] = end($split);
					} else {
						$getValue = $cleanArg;

						if( isset($this->defined_flags[$cleanArg]) && $this->defined_flags[$cleanArg]['type'] != 'bool' ) {
							$forceValue = true;
						}

					}
				} else {
					$split = str_split($cleanArg);
					foreach( $split as $char ) {
						$shortParams[$char] = isset($shortParams[$char]) ? $shortParams[$char] + 1 : 1;
					}
				}
			} elseif( ($getValue !== false && !$startArgs) || $forceValue ) {
				$longParams[$getValue] = $arg;
				$getValue              = false;
				$forceValue            = false;
			} else {
				$this->arguments[] = $arg;
			}
		}

		if( $getValue ) {
			$longParams[$getValue] = true;
		}

		foreach( $longParams as $name => $value ) {
			if( !isset($this->defined_flags[$name]) ) {
				if( !$ignoreExceptions ) {
					throw new InvalidFlagParamException('Unknown option: --' . $name);
				}
			} else {
				$defined_flag =& $this->defined_flags[$name];

				if( $this->validateType($defined_flag['type'], $value) ) {
					$defined_flag['value']  = $value;
					$defined_flag['parsed'] = true;
				} else {
					if( !$ignoreExceptions ) {
						throw new InvalidFlagTypeException('Option --' . $name . ' expected type: "' . $defined_flag['type'] . '"');
					}
				}
			}
		}

		foreach( $shortParams as $char => $value ) {
			if( !isset($this->defined_short_flags[$char]) ) {
				if( !$ignoreExceptions ) {
					throw new InvalidFlagParamException('Unknown option: -' . $char);
				}
			} else {
				$this->defined_short_flags[$char]['value'] = $value;
			}
		}

		foreach( $this->defined_flags as $name => $data ) {
			if( $data['value'] === null ) {
				if( !$ignoreExceptions ) {
					throw new MissingFlagParamException('Expected option --' . $name . ' missing.');
				}
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
					if( abs(floatval($val)) == intval($val) ) {
						$val = intval($val);

						return true;
					}

					return false;
				},
			'int'    => function ( &$val ) {
					if( is_numeric($val) && floatval($val) == intval($val) ) {
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
					if( $val !== true ) {
						$val = (string)$val;

						return true;
					}

					return false;
				},
		);

		$test = $validate[$type];

		return $test($value);
	}

}