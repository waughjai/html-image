<?php

use PHPUnit\Framework\TestCase;
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
		$img2 = new HTMLImage( "{$img_2_name}.png", [ "alt" => $img2_alt ] );
		$this->assertContains( " alt=\"{$img2_alt}\"", $img2->getHTML() );
	}

	public function testAttributes()
	{
		$class = $this->getRandomString();
		$id = $this->getRandomString();
		$img = new HTMLImage( "logo.png", [ 'class' => $class, 'id' => $id ] );
		$this->assertContains( " class=\"{$class}\"", $img->getHTML() );
		$this->assertContains( " id=\"{$id}\"", $img->getHTML() );
	}

	private function getRandomString() : string
	{
		$generator = new RandomStringGenerator();
		return $generator->generate( 'lllllll' );
	}
}
