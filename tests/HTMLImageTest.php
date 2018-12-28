<?php

use PHPUnit\Framework\TestCase;
use WaughJ\FileLoader\FileLoader;
use WaughJ\HTMLImage\HTMLImage;
use RandomStringGenerator\RandomStringGenerator;

class HTMLImageTest extends TestCase
{
	public function testBasicImage()
	{
		$name = $this->getRandomString();
		$img = new HTMLImage( "$name.png" );
		$this->assertContains( '<img', $img->getHTML() );
		$this->assertContains( " src=\"$name.png\"", $img->getHTML() );
	}

	public function testAlt()
	{
		$img_1_name = $this->getRandomString();
		$img1 = new HTMLImage( "{$img_1_name}.png" );
		$this->assertContains( ' alt=""', $img1->getHTML() );
		$img_2_name = $this->getRandomString();
		$img2_alt = $this->getRandomString();
		$img2 = new HTMLImage( "{$img_2_name}.png", null, [ "alt" => $img2_alt ] );
		$this->assertContains( " alt=\"{$img2_alt}\"", $img2->getHTML() );
	}

	public function testAttributes()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$img = new HTMLImage( "logo.png", null, [ 'class' => $class, 'id' => $id ] );
		$this->assertContains( " class=\"{$class}\"", $img->getHTML() );
		$this->assertContains( " id=\"{$id}\"", $img->getHTML() );
	}

	public function testAttributeChanges()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$img = new HTMLImage( "logo.png", null, [ 'class' => $class, 'id' => $id ] );
		$this->assertContains( " class=\"{$class}\"", $img->getHTML() );
		$this->assertContains( " id=\"{$id}\"", $img->getHTML() );
		$img = $img->addToClass( 'dagadon' );
		$this->assertContains( " class=\"{$class} dagadon\"", $img->getHTML() );
		$img = $img->setAttribute( 'id', 'dagadon' );
		$this->assertContains( " id=\"dagadon\"", $img->getHTML() );
	}

	public function testAddToClassWithoutClass()
	{
		$img = new HTMLImage( "logo.png", null );
		$img = $img->addToClass( 'dagadon' );
		$this->assertContains( " class=\"dagadon\"", $img->getHTML() );
	}

	public function testWithFileLoader()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertContains( '<img', $img->getHTML() );
		$this->assertContains( " src=\"https://www.example.com/tests/demo.png?m=", $img->getHTML() );
	}

	public function testGetSource()
	{
		$loader = new FileLoader([ 'directory-url' => 'https://www.example.com', 'directory-server' => getcwd(), 'shared-directory' => 'tests', 'extension' => 'png' ]);
		$img = new HTMLImage( 'demo', $loader );
		$this->assertContains( '<img', $img->getHTML() );
		$this->assertContains( "https://www.example.com/tests/demo.png?m=", $img->getSource() );
	}

	private function getRandomString() : string
	{
		$generator = new RandomStringGenerator();
		return $generator->generate( 'lllllll' );
	}
}
