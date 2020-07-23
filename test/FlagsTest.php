<?php

namespace donatj\tests\Flags;


use donatj\Exceptions\InvalidFlagParamException;
use donatj\Exceptions\InvalidFlagTypeException;
use donatj\Exceptions\MissingFlagParamException;
use donatj\Flags;
use PHPUnit\Framework\TestCase;

class FlagsTest extends TestCase {

	public function testBool() : void {
		$flags = new Flags();
		$bool  = &$flags->bool('bool');

		$flags->parse(explode(' ', 'test.php --bool'));
		self::assertTrue($bool);

		$options = [ 0 => [ 'true', 't', '1' ], 1 => [ 'false', 'f', '0' ] ];

		foreach( [ '=', ' ' ] as $sep ) {
			foreach( $options as $bool => $values ) {
				foreach( $values as $value ) {
					$flags->parse(explode(' ', 'test.php --bool' . $sep . $value));
					self::assertSame($bool, (bool)$bool);
				}
			}
		}

		$flags->parse(explode(' ', 'test.php --bool -- argument'));
		self::assertTrue($bool);
	}

	public function testBoolException() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->bool('bool');
		$flags->parse(explode(' ', 'test.php --bool=10'));
	}

	public function testBoolException2() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->bool('bool');
		$flags->parse(explode(' ', 'test.php --bool string'));
	}

	public function testFloat() : void {
		$flags = new Flags();
		$uint  = &$flags->float('float');

		$values = [ 4, 4.2, '4.', '-.4', '4.0', .4, -4, 0, 1000, '-012' ];

		foreach( [ '=', ' ' ] as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --float' . $sep . $value));
				self::assertSame($uint, floatval($value));
			}
		}
	}

	public function testFloatException() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->float('float');
		$flags->parse(explode(' ', 'test.php --float=spiders'));
	}

	public function testFloatException2() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->float('float');
		$flags->parse(explode(' ', 'test.php --float'));
	}

	public function testInt() : void {
		$flags = new Flags();
		$int   = &$flags->int('int');

		$values = [ 4, '4.', '4.0', -4, 0, 1000, '-012' ];

		foreach( [ '=', ' ' ] as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --int' . $sep . $value));
				self::assertSame($int, intval($value));
			}
		}
	}

	public function testIntException() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int=spiders'));
	}

	public function testIntException2() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int=1.1'));
	}

	public function testIntException3() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int'));
	}

	public function testUint() : void {
		$flags = new Flags();
		$bool  = &$flags->uint('uint');

		$values = [ 4, '4.', '4.0', 0, 1000, 12 ];

		foreach( [ '=', ' ' ] as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --uint' . $sep . $value));
				self::assertSame($bool, abs(intval($value)));
			}
		}
	}

	public function testUintException() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->uint('uint');
		$flags->parse(explode(' ', 'test.php --uint=-2'));
	}

	public function testUintException2() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->uint('uint');
		$flags->parse(explode(' ', 'test.php --uint=2.2'));
	}

	public function testString() : void {
		$flags  = new Flags();
		$string = &$flags->string('string');
		$values = [ 4, '4.', '4.0', 0, 1000, 12, "what", "funky fresh", "hot=dog" ];

		foreach( $values as $value ) {
			$flags->parse([ 'test.php', '--string=' . $value ]);
			self::assertSame($string, strval($value));

			$flags->parse([ 'test.php', '--string', $value ]);
			self::assertSame($string, strval($value));
		}
	}

	public function testStringException() : void {
		$this->expectException(InvalidFlagTypeException::class);

		$flags = new Flags();
		$flags->string('string');
		$flags->parse(explode(' ', 'test.php --string'));
	}

	public function parseProvider() : array {
		return [
			[ 'test.php --sponges=false --help -v argument1 --pie 59 argument2 --what=14 -vv --int1 7 --int2=-4 --last -- --argument_that_looks_like_a_param', true ],
			[ '--sponges=false --help -v argument1 --pie 59 argument2 --what=14 -vv --int1 7 --int2=-4 --last -- --argument_that_looks_like_a_param', false ],
		];
	}

	/**
	 * @dataProvider parseProvider
	 */
	public function testParse( string $arguments, bool $skipFirst ) : void {
		$argParts = explode(' ', $arguments);

		foreach( [ 0, 1, 2 ] as $useConstructor ) {
			if( $useConstructor === 0 ) {
				$flags = new Flags($argParts, $skipFirst);
			} elseif( $useConstructor === 1 ) {
				$flags = new Flags();
			} elseif( $useConstructor === 2 ) {
				$flags = new Flags([ '--this=is', 'trash', 'data' ], !$skipFirst);
			} else {
				throw new \OutOfBoundsException('unexpected loop');
			}

			$sponges = &$flags->bool('sponges');
			$what    = &$flags->uint('what');
			$int1    = &$flags->int('int1');
			$int2    = &$flags->int('int2');
			$pie     = &$flags->string('pie');
			$cat     = &$flags->string('cat', 'Maine Coon');
			$help    = &$flags->bool('help', false);
			$last    = &$flags->bool('last', false);
			$verbose = &$flags->short('v');
			$all     = &$flags->short('a');

			if( $useConstructor !== 0 ) {
				$flags->parse($argParts, false, $skipFirst);
			} else {
				$flags->parse();
			}

			$longs = $flags->longs();
			self::assertFalse($sponges);
			self::assertFalse($longs['sponges']);
			self::assertSame($what, 14);
			self::assertSame($longs['what'], 14);
			self::assertSame($int1, 7);
			self::assertSame($longs['int1'], 7);
			self::assertSame($int2, -4);
			self::assertSame($longs['int2'], -4);
			self::assertSame($pie, '59');
			self::assertSame($longs['pie'], '59');
			self::assertSame($cat, 'Maine Coon');
			self::assertSame($longs['cat'], 'Maine Coon');
			self::assertTrue($help);
			self::assertTrue($longs['help']);
			self::assertTrue($last);
			self::assertTrue($longs['last']);

			$shorts = $flags->shorts();
			self::assertSame($verbose, 3);
			self::assertSame($shorts['v'], 3);
			self::assertSame($all, 0);
			self::assertSame($shorts['a'], 0);

			self::assertEquals([
				0 => 'argument1',
				1 => 'argument2',
				2 => '--argument_that_looks_like_a_param',
			], $flags->args());

			self::assertSame($flags->arg(0), 'argument1');
			self::assertSame($flags->arg(1), 'argument2');
			self::assertSame($flags->arg(2), '--argument_that_looks_like_a_param');
			self::assertNull($flags->arg(3));
		}
	}

	public function testParse2() : void {
		$flags  = new Flags();
		$capx   = &$flags->short('X');
		$lowerx = &$flags->short('x');
		$a      = &$flags->short('a');
		$s      = &$flags->short('s');
		$d      = &$flags->short('d');
		$qm     = &$flags->short('?');

		$flags->parse(explode(' ', 'main.php -Xassd?'));

		self::assertSame($capx, 1);
		self::assertSame($lowerx, 0);
		self::assertSame($a, 1);
		self::assertSame($s, 2);
		self::assertSame($d, 1);
		self::assertSame($qm, 1);

		self::assertEquals([], $flags->longs());

		self::assertEquals([ 'X' => 1, 'x' => 0, 'a' => 1, 's' => 2, 'd' => 1, '?' => 1 ], $flags->shorts());

		# ====

		$_SERVER['argv'] = [ 'test.php', '--a', '7' ];
		$flags           = new Flags();
		$a               = &$flags->int('a');
		$flags->parse();

		self::assertSame($a, 7);
	}

	public function testParsed() : void {
		$flags = new Flags();
		self::assertFalse($flags->parsed());

		try {
			$flags->parse(explode(' ', 'test.php --failtoparse=true'));
			self::fail('An exception should have been thrown.');
		} catch( \Exception $e ) {
			//This is expected to fail. We're simply making sure it didn't parse.
		}

		self::assertFalse($flags->parsed());

		$flags->parse(explode(' ', 'test.php a b c'));
		self::assertTrue($flags->parsed());
	}

	public function testParseExceptionInvalidFlagParamException() : void {
		$this->expectException(InvalidFlagParamException::class);

		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake=what'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionInvalidFlagParamException() : void {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake=what'), true);
	}

	public function testParseExceptionInvalidFlagParamException2() : void {
		$this->expectException(InvalidFlagParamException::class);

		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake what'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionInvalidFlagParamException2() : void {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake what'), true);
	}

	public function testParseExceptionInvalidFlagParamException3() : void {
		$this->expectException(InvalidFlagParamException::class);

		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionInvalidFlagParamException3() : void {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake'), true);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testParseExceptionInvalidFlagParamException4() : void {
		$this->expectException(InvalidFlagParamException::class);
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -fake'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionInvalidFlagParamException4() : void {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -fake'), true);
	}

	public function testParseExceptionInvalidFlagParamException5() : void {
		$this->expectException(InvalidFlagParamException::class);

		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -v'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionInvalidFlagParamException5() : void {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -v'), true);
	}

	public function testParseExceptionMissingFlagParamException() : void {
		$this->expectException(MissingFlagParamException::class);

		$flags = new Flags();
		$flags->string('blah');
		$flags->parse(explode(' ', 'test.php'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionMissingFlagParamException() : void {
		$flags = new Flags();
		$flags->string('blah');
		$flags->parse(explode(' ', 'test.php'), true);
	}

	public function testParseExceptionMissingFlagParamException2() : void {
		$this->expectException(MissingFlagParamException::class);

		$flags = new Flags();
		$flags->bool('foo');
		$flags->bool('bar');
		$flags->parse(explode(' ', 'test.php --foo'));
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testNotParseExceptionMissingFlagParamException2() : void {
		$flags = new Flags();
		$flags->bool('foo');
		$flags->bool('bar');
		$flags->parse(explode(' ', 'test.php --foo'), true);
	}

	public function testGetDefaults() : void {

		$flags = new Flags();

		$longs = [
			[ 'bool', 'foo', false, 'Enable the foos' ],
			[ 'string', 'bar', 'string', 'Baz text to display' ],
			[ 'int', 'baz', -10, 'How many bazs' ],
			[ 'uint', 'quux', 10, 'How many quuxi' ],
			[ 'float', 'thud', 10.1, 'How many thuds' ],

			[ 'bool', 'xfoo', null, 'Enable the foos' ],
			[ 'string', 'xbar', null, 'Baz text to display' ],
			[ 'int', 'xbaz', null, 'How many bazs' ],
			[ 'uint', 'xquux', null, 'How many quuxi' ],
			[ 'float', 'xthud', null, 'How many thuds' ],
		];

		foreach( $longs as $data ) {
			$flags->{$data[0]}($data[1], $data[2], $data[3]);
		}

		$shorts = [
			[ 'v', 'verbosity, more v\'s = more verbose' ],
			[ 'a', 'verbosity, more v\'s = more verbose' ],
		];

		foreach( $shorts as $data ) {
			$flags->short($data[0], $data[1]);
		}

		$longCount  = 0;
		$shortCount = 0;

		preg_match_all('/^\s*(?P<long>-)?-(?P<key>[a-z]+)\s+(?:(?P<optional>\[[a-z]+\])|(?P<required><[a-z]+>))?\s+(?P<msg>.*)/m', $flags->getDefaults(), $result, PREG_PATTERN_ORDER);
		for( $i = 0; $i < count($result[0]); $i++ ) {
			if( $result['long'][$i] === '-' ) {
				$longCount++;

				foreach( $longs as $long ) {
					if( $long[1] === $result['key'][$i] ) {
						if( $long[2] !== null ) {
							self::assertTrue(isset($result['optional'][$i][0]) || $long[0] === 'bool');
						} else {
							self::assertTrue(isset($result['required'][$i][0]) || $long[0] === 'bool');
						}

						self::assertEquals($long[3], $result['msg'][$i]);
						break;
					}
				}
			} else {
				$shortCount++;

				foreach( $shorts as $short ) {
					if( $short[0] === $result['key'][$i] ) {
						self::assertEquals($short[1], $result['msg'][$i]);

						break;
					}
				}
			}
		}

		self::assertEquals(count($longs), $longCount);
		self::assertEquals(count($shorts), $shortCount);
	}

	/**
	 * Test that an empty string e.g. `foo.php --foo ""` does not explode
	 */
	public function testEmptyStringValueRegression() : void {
		$flags = new Flags();

		$foo =& $flags->string('foo', 'test');
		$flags->parse([ 'souplex', '--foo', '' ]);

		self::assertSame($foo, '');
	}

	/**
	 * Test that an empty string e.g. `foo.php --foo ""` does not explode but rather returns an exception.
	 *
	 * In a future refractor we'll ideally be able to use a value of "-" but that's not in the cards right now.
	 */
	public function testEmptyStringValueRegression2() : void {
		$this->expectException(InvalidFlagParamException::class);

		$flags = new Flags();

		$flags->string('foo', 'test');
		$flags->parse([ 'fooplex', '-', 'sass' ]);
	}

}
