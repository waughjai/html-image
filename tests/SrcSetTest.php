<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\HTMLImage\SrcSet;
use WaughJ\HTMLImage\SrcSetItem;

class SrcSetTest extends TestCase
{
	public function testGenerationFromString()
	{
		$set1 = new SrcSet( 'demo-480x800.png 480w' );
		$this->assertEquals( ' srcset="demo-480x800.png 480w"', $set1->getAttributeText() );
		$set2 = new SrcSet( 'demo-480x800.png 480w, demo-1200x800.png 1200w' );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w"', $set2->getAttributeText() );
		$set3 = new SrcSet( [ 'demo-480x800.png 480w', 'demo-1200x800.png 1200w' ] );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w"', $set3->getAttributeText() );
	}

	public function testGenerationFromItems()
	{
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 'png', 480, 800 ) ] );
		$this->assertEquals( ' srcset="demo-480x800.png 480w"', $set1->getAttributeText() );
		$set2 = new SrcSet( [ new SrcSetItem( 'demo', 'png', 480, 800 ), new SrcSetItem( 'demo', 'png', 1200, 800 ), new SrcSetItem( 'demo', 'png', 2600, -1 ) ] );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w, demo.png 2600w"', $set2->getAttributeText() );
	}

	public function testGenerationFromOtherSrcSet()
	{
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 'png', 480, 800 ), new SrcSetItem( 'demo', 'png', 1200, 800 ), new SrcSetItem( 'demo', 'png', 2600, -1 ) ] );
		$set2 = new Srcset( $set1 );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w, demo.png 2600w"', $set2->getAttributeText() );
	}
}
