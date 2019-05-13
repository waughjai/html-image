<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLImage\HTMLImage;
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

	public function testAttributeChanges()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( "demo", $loader, [ 'class' => $class, 'id' => $id, 'srcset' => 'demo-300x300 300w, demo-800x500 800w, demo 1280w' ] );
		$this->assertStringContainsString( " class=\"{$class}\"", $img->getHTML() );
		$this->assertStringContainsString( " id=\"{$id}\"", $img->getHTML() );
		$img = $img->addToClass( 'dagadon' )->setAttribute( 'id', 'dagadon' );
		$this->assertStringContainsString( " class=\"{$class} dagadon\"", $img->getHTML() );
		$this->assertStringContainsString( " id=\"dagadon\"", $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/demo.png?m=", $img->getHTML() );
		$this->assertStringContainsString( " srcset=\"https://www.example.com/tests/demo-300x300.png?m=", $img->getHTML() );
	}

	public function testAddToClassWithoutClass()
	{
		$img = new HTMLImage( "logo.png", null );
		$img = $img->addToClass( 'dagadon' );
		$this->assertStringContainsString( " class=\"dagadon\"", $img->getHTML() );
	}

	public function testWithFileLoader()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/demo.png?m=", $img->getHTML() );
	}

	public function testNonExistentFile()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$html = null;
		try
		{
			$image = new HTMLImage( 'jibber', $loader );
			$html = $image->getHTML();
		}
		catch ( MissingFileException $e )
		{
			$html = $e->getFallbackContent();
		}
		$this->assertStringContainsString( '<img', $html );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/jibber.png", $html );
		$this->assertStringContainsString( ' alt=""', $html );

		// Test auto string conversion.
		// Can't throw, so it just automatically returns fallback content.
		$image2 = new HTMLImage( 'alsonothere.png', $loader );
		$this->assertStringContainsString( '<img', ( string )( $image2 ) );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/alsonothere.png", ( string )( $image2 ) );
	}

	public function testGetSource()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( "https://www.example.com/tests/demo.png?m=", $img->getSource() );
	}

	public function testWithoutVersioning()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader, [ 'show-version' => false ] );
		$this->assertStringContainsString( '<img', $img->getHTML() );
		$this->assertStringContainsString( " src=\"https://www.example.com/tests/demo.png\"", $img->getHTML() );
		$this->assertStringNotContainsString( " show-version=\"", $img->getHTML() );
	}

	private function getRandomString() : string
	{
		$generator = new RandomStringGenerator();
		return $generator->generate( 'lllllll' );
	}
}
