<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
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
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 480, 800, 'png' ) ] );
		$this->assertEquals( ' srcset="demo-480x800.png 480w"', $set1->getAttributeText() );
		$set2 = new SrcSet( [ new SrcSetItem( 'demo', 480, 800, 'png' ), new SrcSetItem( 'demo', 1200, 800, 'png' ), new SrcSetItem( 'demo', 2600, -1, 'png' ) ] );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w, demo.png 2600w"', $set2->getAttributeText() );
	}

	public function testGenerationFromOtherSrcSet()
	{
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 480, 800, 'png' ), new SrcSetItem( 'demo', 1200, 800, 'png' ), new SrcSetItem( 'demo', 2600, -1, 'png' ) ] );
		$set2 = new Srcset( $set1 );
		$this->assertEquals( ' srcset="demo-480x800.png 480w, demo-1200x800.png 1200w, demo.png 2600w"', $set2->getAttributeText() );
	}

	public function testWithFileLoader()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img' ]);
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ) ], $loader );
		$this->assertStringContainsString( ' srcset="https://www.example.com/tests/img/demo-300x300.png?m=', $set1->getAttributeText() );
		$this->assertStringContainsString( '300w, https://www.example.com/tests/img/demo-800x500.png?m=', $set1->getAttributeText() );
        $loader = $loader->changeExtension( 'png' );
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 300, 300 ), new SrcSetItem( 'demo', 800, 500 ) ], $loader );
		$this->assertStringContainsString( ' srcset="https://www.example.com/tests/img/demo-300x300.png?m=', $set1->getAttributeText() );
		$this->assertStringContainsString( '300w, https://www.example.com/tests/img/demo-800x500.png?m=', $set1->getAttributeText() );
	}

	public function testWithFileLoaderWithoutVersion()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img' ]);
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ) ], $loader, false );
		$this->assertStringContainsString( ' srcset="https://www.example.com/tests/img/demo-300x300.png', $set1->getAttributeText() );
		$this->assertStringContainsString( '300w, https://www.example.com/tests/img/demo-800x500.png', $set1->getAttributeText() );
		$this->assertStringNotContainsString( '?m=', $set1->getAttributeText() );
        $loader = $loader->changeExtension( 'png' );
		$set1 = new SrcSet( [ new SrcSetItem( 'demo', 300, 300 ), new SrcSetItem( 'demo', 800, 500 ) ], $loader, false );
		$this->assertStringContainsString( ' srcset="https://www.example.com/tests/img/demo-300x300.png', $set1->getAttributeText() );
		$this->assertStringContainsString( '300w, https://www.example.com/tests/img/demo-800x500.png', $set1->getAttributeText() );
		$this->assertStringNotContainsString( '?m=', $set1->getAttributeText() );
		$set2 = new SrcSet( [ new SrcSetItem( 'demo', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ) ], $loader, false );
		$this->assertStringContainsString( $set2->getAttributeText(), $set1->getAttributeText() );
	}

	public function testWithFileLoaderAndMissingFile()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img' ]);
		try
		{
            $set = new SrcSet( [ new SrcSetItem( 'peanut', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ) ], $loader );
		}
		catch ( MissingFileException $e )
		{
			$set = $e->getFallbackContent();
		}

		$this->assertStringContainsString( ' srcset="https://www.example.com/tests/img/peanut-300x300.png 300w, https://www.example.com/tests/img/demo-800x500.png?m=', $set->getAttributeText() );
	}
}
