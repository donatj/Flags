<?php

namespace donatj\Flags;

use donatj\Flags;

class FlagsTest extends \PHPUnit_Framework_TestCase {

	public function testParse() {

		$flags   = new Flags();
		$sponges = & $flags->bool('sponges');
		$what    = & $flags->uint('what');
		$pie     = & $flags->string('pie');
		$cat     = & $flags->string('cat', 'Maine Coon');
		$verbose = & $flags->short('v');
		$all     = & $flags->short('a');

		$flags->parse(explode(' ', 'php test.php --sponges=false -v argument1 --pie 59 argument2 --what=14 -vv -- --argument_that_looks_like_a_param'));


		$longs  = $flags->longs();
		$this->assertSame($sponges, false);
		$this->assertSame($longs['sponges'], false);
		$this->assertSame($what, 14);
		$this->assertSame($longs['what'], 14);
		$this->assertSame($pie, '59');
		$this->assertSame($longs['pie'], '59');
		$this->assertSame($cat, 'Maine Coon');
		$this->assertSame($longs['cat'], 'Maine Coon');

		$shorts = $flags->shorts();
		$this->assertSame($verbose, 3);
		$this->assertSame($shorts['v'], 3);
		$this->assertSame($all, 0);
		$this->assertSame($shorts['a'], 0);

	}

}