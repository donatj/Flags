<?php

namespace donatj\tests\Flags;

use donatj\Flags;

class FlagsTest extends \PHPUnit_Framework_TestCase {

	public function testSet() {
		$flags = new Flags();
		$nameList = & $flags->set('string', 'name');

		$flags->parse(explode(' ', 'test.php --name hi --name bye'));

		$this->assertSame($nameList, array('hi', 'bye'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testSetException() {
		$flags = new Flags();
		$flags->set('int', 'name');
		$flags->parse(explode(' ', 'test.php --name hi --name bye'));
	}

	public function testBool() {
		$flags = new Flags();
		$bool  = & $flags->bool('bool');

		$flags->parse(explode(' ', 'test.php --bool'));
		$this->assertSame($bool, true);

		$options = array( 0 => array( 'true', 't', '1' ), 1 => array( 'false', 'f', '0' ) );

		foreach( array( '=', ' ' ) as $sep ) {
			foreach( $options as $bool => $values ) {
				foreach( $values as $value ) {
					$flags->parse(explode(' ', 'test.php --bool' . $sep . $value));
					$this->assertSame($bool, (bool)$bool);
				}
			}
		}

		$flags->parse(explode(' ', 'test.php --bool -- argument'));
		$this->assertSame($bool, true);
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testBoolException() {
		$flags = new Flags();
		$flags->bool('bool');
		$flags->parse(explode(' ', 'test.php --bool=10'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testBoolException2() {
		$flags = new Flags();
		$flags->bool('bool');
		$flags->parse(explode(' ', 'test.php --bool string'));
	}

	public function testFloat() {
		$flags = new Flags();
		$uint  = & $flags->float('float');

		$values = array( 4, 4.2, '4.', '-.4', '4.0', .4, -4, 0, 1000, '-012' );

		foreach( array( '=', ' ' ) as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --float' . $sep . $value));
				$this->assertSame($uint, floatval($value));
			}
		}
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testFloatException() {
		$flags = new Flags();
		$flags->float('float');
		$flags->parse(explode(' ', 'test.php --float=spiders'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testFloatException2() {
		$flags = new Flags();
		$flags->float('float');
		$flags->parse(explode(' ', 'test.php --float'));
	}

	public function testInt() {
		$flags = new Flags();
		$int   = & $flags->int('int');

		$values = array( 4, '4.', '4.0', -4, 0, 1000, '-012' );

		foreach( array( '=', ' ' ) as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --int' . $sep . $value));
				$this->assertSame($int, intval($value));
			}
		}
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testIntException() {
		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int=spiders'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testIntException2() {
		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int=1.1'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testIntException3() {
		$flags = new Flags();
		$flags->int('int');
		$flags->parse(explode(' ', 'test.php --int'));
	}

	public function testUint() {
		$flags = new Flags();
		$bool  = & $flags->uint('uint');

		$values = array( 4, '4.', '4.0', 0, 1000, 12 );

		foreach( array( '=', ' ' ) as $sep ) {
			foreach( $values as $value ) {
				$flags->parse(explode(' ', 'test.php --uint' . $sep . $value));
				$this->assertSame($bool, abs(intval($value)));
			}
		}
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testUintException() {
		$flags = new Flags();
		$flags->uint('uint');
		$flags->parse(explode(' ', 'test.php --uint=-2'));
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testUintException2() {
		$flags = new Flags();
		$flags->uint('uint');
		$flags->parse(explode(' ', 'test.php --uint=2.2'));
	}

	public function testString() {
		$flags  = new Flags();
		$string = & $flags->string('string');
		$values = array( 4, '4.', '4.0', 0, 1000, 12, "what", "funky fresh", "hot=dog" );

		foreach( $values as $value ) {
			$flags->parse(array( 'test.php', '--string=' . $value ));
			$this->assertSame($string, strval($value));

			$flags->parse(array( 'test.php', '--string', $value ));
			$this->assertSame($string, strval($value));
		}

	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagTypeException
	 */
	public function testStringException() {
		$flags = new Flags();
		$flags->string('string');
		$flags->parse(explode(' ', 'test.php --string'));
	}

	public function testParse() {

		$testCases = array(
			array( 'test.php --sponges=false --help -v argument1 --pie 59 argument2 --what=14 -vv --int1 7 --int2=-4 --last -- --argument_that_looks_like_a_param', true ),
			array( '--sponges=false --help -v argument1 --pie 59 argument2 --what=14 -vv --int1 7 --int2=-4 --last -- --argument_that_looks_like_a_param', false ),
		);

		foreach($testCases as $testCase) {
			$flags   = new Flags();
			$sponges = & $flags->bool('sponges');
			$what    = & $flags->uint('what');
			$int1    = & $flags->int('int1');
			$int2    = & $flags->int('int2');
			$pie     = & $flags->string('pie');
			$cat     = & $flags->string('cat', 'Maine Coon');
			$help    = & $flags->bool('help', false);
			$last    = & $flags->bool('last', false);
			$verbose = & $flags->short('v');
			$all     = & $flags->short('a');

			$flags->parse(explode(' ', $testCase[0]), false, $testCase[1]);

			$longs = $flags->longs();
			$this->assertSame($sponges, false);
			$this->assertSame($longs['sponges'], false);
			$this->assertSame($what, 14);
			$this->assertSame($longs['what'], 14);
			$this->assertSame($int1, 7);
			$this->assertSame($longs['int1'], 7);
			$this->assertSame($int2, -4);
			$this->assertSame($longs['int2'], -4);
			$this->assertSame($pie, '59');
			$this->assertSame($longs['pie'], '59');
			$this->assertSame($cat, 'Maine Coon');
			$this->assertSame($longs['cat'], 'Maine Coon');
			$this->assertSame($help, true);
			$this->assertSame($longs['help'], true);
			$this->assertSame($last, true);
			$this->assertSame($longs['last'], true);

			$shorts = $flags->shorts();
			$this->assertSame($verbose, 3);
			$this->assertSame($shorts['v'], 3);
			$this->assertSame($all, 0);
			$this->assertSame($shorts['a'], 0);

			$this->assertEquals(array(
				0 => 'argument1',
				1 => 'argument2',
				2 => '--argument_that_looks_like_a_param',
			), $flags->args());

			$this->assertSame($flags->arg(0), 'argument1');
			$this->assertSame($flags->arg(1), 'argument2');
			$this->assertSame($flags->arg(2), '--argument_that_looks_like_a_param');
			$this->assertSame($flags->arg(3), null);
		}


		# ====


		$flags  = new Flags();
		$capx   = & $flags->short('X');
		$lowerx = & $flags->short('x');
		$a      = & $flags->short('a');
		$s      = & $flags->short('s');
		$d      = & $flags->short('d');
		$qm     = & $flags->short('?');


		$flags->parse(explode(' ', 'main.php -Xassd?'));

		$this->assertSame($capx, 1);
		$this->assertSame($lowerx, 0);
		$this->assertSame($a, 1);
		$this->assertSame($s, 2);
		$this->assertSame($d, 1);
		$this->assertSame($qm, 1);

		$this->assertEquals($flags->longs(), array());

		$this->assertEquals($flags->shorts(), array( 'X' => 1, 'x' => 0, 'a' => 1, 's' => 2, 'd' => 1, '?' => 1 ));


		# ====

		$GLOBALS['argv'] = array('test.php','--a', '7');
		$flags  = new Flags();
		$a      = & $flags->int('a');
		$flags->parse();

		$this->assertSame($a, 7);

	}

	public function testParsed() {
		$flags = new Flags();
		$this->assertSame(false, $flags->parsed());

		try{
			$flags->parse(explode(' ', 'test.php --failtoparse=true'));
			$this->fail('An exception should have been thrown.');
		}catch (\Exception $e) {
			//This is expected to fail. We're simply making sure it didn't parse.
		}

		$this->assertSame(false, $flags->parsed());

		$flags->parse(explode(' ', 'test.php a b c'));
		$this->assertSame(true, $flags->parsed());
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagParamException
	 */
	function testParseExceptionInvalidFlagParamException() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake=what'));
	}

	function testNotParseExceptionInvalidFlagParamException() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake=what'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagParamException
	 */
	function testParseExceptionInvalidFlagParamException2() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake what'));
	}

	function testNotParseExceptionInvalidFlagParamException2() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake what'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagParamException
	 */
	function testParseExceptionInvalidFlagParamException3() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake'));
	}

	function testNotParseExceptionInvalidFlagParamException3() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php --fake'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagParamException
	 */
	function testParseExceptionInvalidFlagParamException4() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -fake'));
	}

	function testNotParseExceptionInvalidFlagParamException4() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -fake'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\InvalidFlagParamException
	 */
	function testParseExceptionInvalidFlagParamException5() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -v'));
	}

	function testNotParseExceptionInvalidFlagParamException5() {
		$flags = new Flags();
		$flags->parse(explode(' ', 'test.php -v'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\MissingFlagParamException
	 */
	function testParseExceptionMissingFlagParamException() {
		$flags = new Flags();
		$flags->string('blah');
		$flags->parse(explode(' ', 'test.php'));
	}

	function testNotParseExceptionMissingFlagParamException() {
		$flags = new Flags();
		$flags->string('blah');
		$flags->parse(explode(' ', 'test.php'), true);
	}

	/**
	 * @expectedException \donatj\Exceptions\MissingFlagParamException
	 */
	function testParseExceptionMissingFlagParamException2() {
		$flags = new Flags();
		$flags->bool('foo');
		$flags->bool('bar');
		$flags->parse(explode(' ', 'test.php --foo'));
	}

	function testNotParseExceptionMissingFlagParamException2() {
		$flags = new Flags();
		$flags->bool('foo');
		$flags->bool('bar');
		$flags->parse(explode(' ', 'test.php --foo'), true);
	}


	public function testGetDefaults() {

		$flags = new Flags();

		$longs = array(
			array( 'bool', 'foo', false, 'Enable the foos' ),
			array( 'string', 'bar', 'string', 'Baz text to display' ),
			array( 'int', 'baz', -10, 'How many bazs' ),
			array( 'uint', 'quux', 10, 'How many quuxi' ),
			array( 'float', 'thud', 10.1, 'How many thuds' ),

			array( 'bool', 'xfoo', null, 'Enable the foos' ),
			array( 'string', 'xbar', null, 'Baz text to display' ),
			array( 'int', 'xbaz', null, 'How many bazs' ),
			array( 'uint', 'xquux', null, 'How many quuxi' ),
			array( 'float', 'xthud', null, 'How many thuds' ),
		);

		foreach( $longs as $data ) {
			$flags->{$data[0]}($data[1], $data[2], $data[3]);
		}

		$shorts = array(
			array( 'v', 'verbosity, more v\'s = more verbose' ),
			array( 'a', 'verbosity, more v\'s = more verbose' ),
		);

		foreach( $shorts as $data ) {
			$flags->short($data[0], $data[1]);
		}

		$longCount  = 0;
		$shortCount = 0;

		preg_match_all('/^\s*(?P<long>-)?-(?P<key>[a-z]+)\s+(?:(?P<optional>\[[a-z]+\])|(?P<required><[a-z]+>))?\s+(?P<msg>.*)/m', $flags->getDefaults(), $result, PREG_PATTERN_ORDER);
		for( $i = 0; $i < count($result[0]); $i += 1 ) {
			if( $result['long'][$i] === '-' ) {
				$longCount++;

				foreach( $longs as $long ) {
					if( $long[1] == $result['key'][$i] ) {
						if( $long[2] !== null ) {
							$this->assertTrue(isset($result['optional'][$i][0]) || $long[0] == 'bool');
						} else {
							$this->assertTrue(isset($result['required'][$i][0]) || $long[0] == 'bool');
						}

						$this->assertEquals($long[3], $result['msg'][$i]);
						break;
					}
				}
			} else {
				$shortCount++;

				foreach( $shorts as $short ) {
					if( $short[0] == $result['key'][$i] ) {
						$this->assertEquals($short[1], $result['msg'][$i]);

						break;
					}
				}

			}
		}

		$this->assertEquals(count($longs), $longCount);
		$this->assertEquals(count($shorts), $shortCount);

	}


}
