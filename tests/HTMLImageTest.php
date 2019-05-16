<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLImage\HTMLImage;
use WaughJ\HTMLImage\SrcSetItem;
use RandomStringGenerator\RandomStringGenerator;

class HTMLImageTest extends TestCase
{
	public function testBasicImage()
	{
		$name = $this->getRandomString();
		$img = new HTMLImage( "$name.png" );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( " src=\"$name.png\"", $img->getHTML() );
	}

	public function testAlt()
	{
		$img_1_name = $this->getRandomString();
		$img1 = new HTMLImage( "{$img_1_name}.png" );
		$this->assertStringContainsString( ' alt=""', $img1->getHTML() );
		$img_2_name = $this->getRandomString();
		$img2_alt = $this->getRandomString();
		$img2 = new HTMLImage( "{$img_2_name}.png", null, [ "alt" => $img2_alt ] );
		$this->assertStringContainsString( " alt=\"{$img2_alt}\"", $img2->getHTML() );
	}

	public function testAttributes()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$img = new HTMLImage( "logo.png", null, [ 'class' => $class, 'id' => $id ] );
		$this->assertStringContainsString( " class=\"{$class}\"", $img->getHTML() );
		$this->assertStringContainsString( " id=\"{$id}\"", $img->getHTML() );
	}

	public function testWithoutVersioning()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader, [ 'show-version' => false ] );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/img/demo.png\"", $img->getHTML() );
		$this->assertStringNotContainsString( " show-version=\"", $img->getHTML() );
		$this->assertStringNotContainsString( "m?=", $img->getHTML() );
	}

	public function testSrcSet()
	{
		$image = new HTMLImage( "demo.png", null, [ 'srcset' => 'demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w' ] );
		$this->assertStringContainsString( " srcset=\"demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w", $image->getHTML() );

		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img' ]);
		$image = $image->changeLoader( $loader );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png?m=", $image->getHTML() );
		$this->assertStringContainsString( "300w, https://www.example.com/tests/img/demo-800x500.png?m=", $image->getHTML() );

		$image = $image->setAttribute( 'srcset', 'demo-300x300.png 300w, demo-800x500.png 800w' );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png?m=", $image->getHTML() );
		$this->assertStringContainsString( "300w, https://www.example.com/tests/img/demo-800x500.png?m=", $image->getHTML() );
		$image = $image->setAttribute( 'srcset', [ new SrcSetItem( 'demo', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ) ] );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png?m=", $image->getHTML() );
		$this->assertStringContainsString( "300w, https://www.example.com/tests/img/demo-800x500.png?m=", $image->getHTML() );
	}

	public function testAttributeChanges()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$img = new HTMLImage( "demo", $loader, [ 'class' => $class, 'id' => $id, 'srcset' => 'demo-300x300 300w, demo-800x500 800w, demo 1280w' ] );
		$this->assertStringContainsString( " class=\"{$class}\"", $img->getHTML() );
		$this->assertStringContainsString( " id=\"{$id}\"", $img->getHTML() );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png?m=", $img->getHTML() );
		$img = $img->addToClass( 'dagadon' )->setAttribute( 'id', 'dagadon' );
		$this->assertStringContainsString( " class=\"{$class} dagadon\"", $img->getHTML() );
		$this->assertStringContainsString( " id=\"dagadon\"", $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/img/demo.png?m=", $img->getHTML() );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png?m=", $img->getHTML() );
	}

	public function testAddToClassWithoutClass()
	{
		$img = new HTMLImage( "logo.png", null );
		$img = $img->addToClass( 'dagadon' );
		$this->assertStringContainsString( " class=\"dagadon\"", $img->getHTML() );
	}

	public function testWithFileLoader()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/img/demo.png?m=", $img->getHTML() );
	}

	public function testNonExistentFile()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$image = null;
		try
		{
			$image = new HTMLImage( 'jibber', $loader, [ 'class' => 'seasonal', 'srcset' => 'demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w' ] );
		}
		catch ( MissingFileException $e )
		{
			$image = $e->getFallbackContent();
		}
		$this->assertStringContainsString( '<img', $image->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/img/jibber.png", $image->getHTML() );
		$this->assertStringContainsString( ' class="seasonal"', $image->getHTML() );
		$this->assertStringContainsString( ' alt=""', $image->getHTML() );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/img/demo-300x300.png", $image->getHTML() );
		$this->assertStringNotContainsString( "m?=", $image->getHTML() );
	}

	public function testGetSource()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests/img', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( "https://www.example.com/tests/img/demo.png?m=", $img->getSource() );
	}

	private function getRandomString() : string
	{
		$generator = new RandomStringGenerator();
		return $generator->generate( 'lllllll' );
	}
}
