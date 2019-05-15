<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\HTMLImage\SrcSetItem;

class SrcSetItemTest extends TestCase
{
	public function testBasic()
	{
		$item = new SrcSetItem( 'demo', 'png', 480, 800 );
		$this->assertEquals( 'demo-480x800.png 480w', $item->getHTML() );
		$this->assertEquals( 'demo-480x800.png 480w', ( string )( $item ) );
	}

	public function testCustom()
	{
		$item = new SrcSetItem( 'demo.png', '', 1200, -1 );
		$this->assertEquals( 'demo.png 1200w', $item->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item ) );
	}

	public function testNoSizeTag()
	{
		$item = new SrcSetItem( 'demo', 'png', 1200, -1 );
		$this->assertEquals( 'demo.png 1200w', $item->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item ) );
		$item2 = new SrcSetItem( 'demo', 'png', 1200, 800, false );
		$this->assertEquals( 'demo.png 1200w', $item2->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item2 ) );
	}

	public function testDifferentSizeTagFromWidth()
	{
		$item = new SrcSetItem( 'demo', 'png', 1200, 800, true, 2400 );
		$this->assertEquals( 'demo-1200x800.png 2400w', $item->getHTML() );
		$this->assertEquals( 'demo-1200x800.png 2400w', ( string )( $item ) );
	}
}
