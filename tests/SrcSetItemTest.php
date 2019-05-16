<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLImage\SrcSetItem;

class SrcSetItemTest extends TestCase
{
	public function testBasic()
	{
		$item = new SrcSetItem( 'demo', 480, 800, 'png' );
		$this->assertEquals( 'demo-480x800.png 480w', $item->getHTML() );
		$this->assertEquals( 'demo-480x800.png 480w', ( string )( $item ) );
	}

	public function testCustom()
	{
		$item = new SrcSetItem( 'demo.png', 1200 );
		$this->assertEquals( 'demo.png 1200w', $item->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item ) );
	}

	public function testNoSizeTag()
	{
		$item = new SrcSetItem( 'demo', 1200, -1, 'png' );
		$this->assertEquals( 'demo.png 1200w', $item->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item ) );
		$item2 = new SrcSetItem( 'demo', 1200, 800, 'png', null, false, false );
		$this->assertEquals( 'demo.png 1200w', $item2->getHTML() );
		$this->assertEquals( 'demo.png 1200w', ( string )( $item2 ) );
	}

	public function testDifferentSizeTagFromWidth()
	{
		$item = new SrcSetItem( 'demo', 1200, 800, 'png', null, false, true, 2400 );
		$this->assertEquals( 'demo-1200x800.png 2400w', $item->getHTML() );
		$this->assertEquals( 'demo-1200x800.png 2400w', ( string )( $item ) );
	}

	public function testWithFileLoaderWithoutVersioning()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$item = new SrcSetItem( 'demo', 1200, 800, '', $loader, false );
		$this->assertEquals( 'https://www.example.com/tests/img/demo-1200x800.png 1200w', $item->getHTML() );
		$this->assertEquals( 'https://www.example.com/tests/img/demo-1200x800.png 1200w', ( string )( $item ) );
		$item = new SrcSetItem( 'demo', 1200, 800, 'png', $loader, false );
		$this->assertEquals( 'https://www.example.com/tests/img/demo-1200x800.png 1200w', $item->getHTML() );
		$this->assertEquals( 'https://www.example.com/tests/img/demo-1200x800.png 1200w', ( string )( $item ) );
	}

	public function testWithFileLoaderWithVersioning()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$item = new SrcSetItem( 'demo', 300, 300, '', $loader );
		$this->assertStringContainsString( 'https://www.example.com/tests/img/demo-300x300.png?m=', $item->getHTML() );
		$this->assertStringContainsString( 'https://www.example.com/tests/img/demo-300x300.png?m=', ( string )( $item ) );
	}

	public function testWithFileLoaderWithMissingFile()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);

		try
		{
			$item = new SrcSetItem( 'missing', 300, 300, '', $loader );
		}
		catch ( MissingFileException $e )
		{
			$item = $e->getFallbackContent();
		}

		$this->assertStringContainsString( 'https://www.example.com/tests/img/missing-300x300.png', $item->getHTML() );
		$this->assertStringNotContainsString( '?m=', $item->getHTML() );
	}
}
