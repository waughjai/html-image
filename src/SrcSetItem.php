<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage;

class SrcSetItem
{
	//
	//  PUBLIC
	//
	//////////////////////////////////////////////////////////

		public function __construct( string $basename, string $extension, int $width, int $height, bool $show_size_tag = true, int $width_size_override = -1 )
		{
			$this->basename = $basename;
			$this->extension = $extension;
			$this->width = $width;
			$this->height = $height;
			$this->show_size_tag = $show_size_tag;
			$this->width_size_override = $width_size_override;
		}

		public function __toString()
		{
			return $this->getHTML();
		}

		public function getHTML() : string
		{
			return "{$this->getFilename()} {$this->getSizeString()}";
		}

		public function getFilename() : string
		{
			return ( !$this->show_size_tag || $this->testIsCustomPattern() )
				? $this->basename . ( ( !empty( $this->extension ) ) ? ".{$this->extension}" : '' )
				: "{$this->basename}-{$this->width}x{$this->height}.{$this->extension}";
		}

		public function getSizeString() : string
		{
			return "{$this->getWidthTag()}w";
		}

		public function getWidthTag() : string
		{
			return ( string )( ( $this->width_size_override !== -1 ) ? $this->width_size_override : $this->width );
		}



	//
	//  PRIVATE
	//
	//////////////////////////////////////////////////////////

		private function testIsCustomPattern() : bool
		{
			return $this->height === -1 || $this->extension === '';
		}

		private $basename;
		private $extension;
		private $width;
		private $height;
		private $show_size_tag;
		private $width_size_override;
}
