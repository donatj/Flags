<?php

namespace donatj;

use donatj\Exceptions\InvalidFlagParamException;
use donatj\Exceptions\InvalidFlagTypeException;
use donatj\Exceptions\MissingFlagParamException;

class Flags {

	/** @var array | null */
	protected $args;

	/** @var bool */
	protected $skipFirstArgument;

	private $definedFlags = [];
	private $definedShortFlags = [];
	private $arguments = [];
	private $parsed = false;

	/** @access private */
	private const DEF_TYPE = 'type';
	/** @access private */
	private const DEF_USAGE = 'usage';
	/** @access private */
	private const DEF_REQUIRED = 'required';
	/** @access private */
	private const DEF_VALUE = 'value';
	/** @access private */
	private const DEF_PARSED = 'parsed';

	/** @access private */
	private const TYPE_BOOL = 'bool';
	/** @access private */
	private const TYPE_UINT = 'uint';
	/** @access private */
	private const TYPE_INT = 'int';
	/** @access private */
	private const TYPE_FLOAT = 'float';
	/** @access private */
	private const TYPE_STRING = 'string';

	/**
	 * Flags constructor.
	 *
	 * @param array $args The arguments to parse, defaults to $_SERVER['argv']
	 * @param bool  $skipFirstArgument Setting to false causes the first argument to be parsed as an parameter rather
	 *     than the command.
	 */
	public function __construct( array $args = null, $skipFirstArgument = true ) {
		if( $args === null && isset($_SERVER['argv']) ) {
			$args = (array)$_SERVER['argv'];
		}

		$this->args              = $args;
		$this->skipFirstArgument = $skipFirstArgument;
	}

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
	 * Returns an array of short-flag call-counts indexed by character
	 *
	 * `-v` would set the 'v' index to 1, whereas `-vvv` will set the 'v' index to 3
	 *
	 * @return array
	 */
	public function shorts() {
		$out = [];
		foreach( $this->definedShortFlags as $key => $data ) {
			$out[$key] = $data[self::DEF_VALUE];
		}

		return $out;
	}

	/**
	 * Returns an array of long-flag values indexed by flag name
	 *
	 * @return array
	 */
	public function longs() {
		$out = [];
		foreach( $this->definedFlags as $key => $data ) {
			$out[$key] = $data[self::DEF_VALUE];
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
		$this->definedShortFlags[$letter[0]] = [
			self::DEF_VALUE => 0,
			self::DEF_USAGE => $usage,
		];

		return $this->definedShortFlags[$letter[0]]['value'];
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
	 * @param string $name The name of the long-flag to define
	 * @param mixed  $value The default value - usually false for bool - which if null marks the flag required
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &bool( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag(self::TYPE_BOOL, $name, $value, $usage);
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
	 * @param string $name The name of the long-flag to define
	 * @param mixed  $value The default value which if null marks the flag required
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &float( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag(self::TYPE_FLOAT, $name, $value, $usage);
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
	 * @param string $name The name of the long-flag to define
	 * @param mixed  $value The default value which if null marks the flag required
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &int( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag(self::TYPE_INT, $name, $value, $usage);
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
	 * @param string $name The name of the long-flag to define
	 * @param mixed  $value The default value which if null marks the flag required
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &uint( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag(self::TYPE_UINT, $name, $value, $usage);
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
	 * @param string $name The name of the long-flag to define
	 * @param mixed  $value The default value which if null marks the flag required
	 * @param string $usage The usage description
	 * @return mixed A reference to the flags value
	 */
	public function &string( $name, $value = null, $usage = '' ) {
		return $this->_storeFlag(self::TYPE_STRING, $name, $value, $usage);
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param mixed  $value
	 * @param string $usage
	 * @return mixed
	 */
	private function &_storeFlag( $type, $name, $value, $usage ) {

		$this->definedFlags[$name] = [
			self::DEF_TYPE     => $type,
			self::DEF_USAGE    => $usage,
			self::DEF_REQUIRED => $value === null,
			self::DEF_VALUE    => $value,
		];

		return $this->definedFlags[$name][self::DEF_VALUE];
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
		$final  = [];
		$max    = 0;

		foreach( $this->definedShortFlags as $char => $data ) {
			$final["-{$char}"] = $data[self::DEF_USAGE];
		}

		foreach( $this->definedFlags as $flag => $data ) {
			$key         = "--{$flag}";
			$final[$key] = ($data[self::DEF_REQUIRED] ?
					"<{$data[self::DEF_TYPE]}> " :
					($data[self::DEF_TYPE] == self::TYPE_BOOL ?
						'' :
						"[{$data[self::DEF_TYPE]}] "
					)) . $data[self::DEF_USAGE];
			$max         = max($max, strlen($key));
		}

		foreach( $final as $flag => $usage ) {
			$output .= sprintf('%' . ($max + 5) . 's', $flag) . "   {$usage}" . PHP_EOL;
		}

		return $output;
	}

	/**
	 * Parses flag definitions from the argument list, which should include the command name.
	 * Must be called after all flags are defined and before flags are accessed by the program.
	 *
	 * Will throw exceptions on Missing Require Flags, Unknown Flags or Incorrect Flag Types
	 *
	 * @param array $args The arguments to parse. Defaults to arguments defined in the constructor.
	 * @param bool  $ignoreExceptions Setting to true causes parsing to continue even after an exception has been
	 *     thrown.
	 * @param bool  $skipFirstArgument Option to parse the first argument as an parameter rather than the command.
	 *     Defaults to constructor value
	 * @throws Exceptions\MissingFlagParamException
	 * @throws Exceptions\InvalidFlagParamException
	 * @throws Exceptions\InvalidFlagTypeException
	 */
	public function parse( array $args = null, $ignoreExceptions = false, $skipFirstArgument = null ) {
		if( $args === null ) {
			$args = $this->args;
		}

		if( $skipFirstArgument === null ) {
			$skipFirstArgument = $this->skipFirstArgument;
		}

		if( $skipFirstArgument ) {
			array_shift($args);
		}

		[ $longParams, $shortParams, $this->arguments ] = $this->splitArguments($args, $this->definedFlags);

		foreach( $longParams as $name => $value ) {
			if( !isset($this->definedFlags[$name]) ) {
				if( !$ignoreExceptions ) {
					throw new InvalidFlagParamException('Unknown option: --' . $name);
				}
			} else {
				$defined_flag =& $this->definedFlags[$name];

				if( $this->validateType($defined_flag[self::DEF_TYPE], $value) ) {
					$defined_flag[self::DEF_VALUE]  = $value;
					$defined_flag[self::DEF_PARSED] = true;
				} else {
					if( !$ignoreExceptions ) {
						throw new InvalidFlagTypeException('Option --' . $name . ' expected type: "' . $defined_flag[self::DEF_TYPE] . '"');
					}
				}
			}
		}

		foreach( $shortParams as $char => $value ) {
			if( !isset($this->definedShortFlags[$char]) ) {
				if( !$ignoreExceptions ) {
					throw new InvalidFlagParamException('Unknown option: -' . $char);
				}
			} else {
				$this->definedShortFlags[$char][self::DEF_VALUE] = $value;
			}
		}

		foreach( $this->definedFlags as $name => $data ) {
			if( $data[self::DEF_VALUE] === null ) {
				if( !$ignoreExceptions ) {
					throw new MissingFlagParamException('Expected option --' . $name . ' missing.');
				}
			}
		}

		$this->parsed = true;
	}

	/**
	 * Returns true if the command-line flags have been parsed.
	 *
	 * @return bool
	 */
	public function parsed() {
		return $this->parsed;
	}

	/**
	 * @param string $type
	 * @param mixed  $value
	 * @return bool
	 */
	private function validateType( $type, &$value ) {
		$validate = [
			self::TYPE_BOOL   => function ( &$val ) {
				$val = strtolower((string)$val);
				if( $val == '0' || $val == 'f' || $val == 'false' ) {
					$val = false;

					return true;
				}
				if( $val == '1' || $val == 't' || $val == 'true' ) {
					$val = true;

					return true;
				}

				return false;
			},
			self::TYPE_UINT   => function ( &$val ) {
				if( abs(floatval($val)) == intval($val) ) {
					$val = intval($val);

					return true;
				}

				return false;
			},
			self::TYPE_INT    => function ( &$val ) {
				if( is_numeric($val) && floatval($val) == intval($val) ) {
					$val = intval($val);

					return true;
				}

				return false;
			},
			self::TYPE_FLOAT  => function ( &$val ) {
				if( is_numeric($val) ) {
					$val = floatval($val);

					return true;
				}

				return false;
			},
			self::TYPE_STRING => function ( &$val ) {
				if( $val !== true ) {
					$val = (string)$val;

					return true;
				}

				return false;
			},
		];

		$test = $validate[$type];

		return $test($value);
	}

	/**
	 * @param array $args
	 * @param array $definedFlags
	 * @return array
	 */
	protected function splitArguments( array $args, array $definedFlags ) {
		$longParams  = [];
		$shortParams = [];
		$arguments   = [];

		$forceValue = false;
		$getValue   = false;
		$startArgs  = false;
		foreach( $args as $arg ) {
			if( isset($arg[0]) && $arg[0] == '-' && !$startArgs && !$forceValue ) {
				$cleanArg = ltrim($arg, '- ');

				if( $getValue ) {
					$longParams[$getValue] = true;
				}

				$getValue = false;

				if( $arg === '--' ) {
					$startArgs = true;
				} elseif( isset($arg[1]) && $arg[1] === '-' ) {
					$split = explode('=', $arg, 2);

					if( count($split) > 1 ) {
						$longParams[ltrim(reset($split), '- ')] = end($split);
					} else {
						$getValue = $cleanArg;

						if( isset($definedFlags[$cleanArg]) && $definedFlags[$cleanArg][self::DEF_TYPE] != self::TYPE_BOOL ) {
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
				$arguments[] = $arg;
			}
		}

		if( $getValue ) {
			$longParams[$getValue] = true;

			return [ $longParams, $shortParams, $arguments ];
		}

		return [ $longParams, $shortParams, $arguments ];
	}

}
