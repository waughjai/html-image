<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage;

use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;

class SrcSetItem
{
	//
	//  PUBLIC
	//
	//////////////////////////////////////////////////////////

		public function __construct
		(
			string $basename,
			int $width,
			int $height = -1,
			string $extension = '',
			$loader = null,
			bool $show_version = true,
			bool $show_size_tag = true,
			int $width_size_override = -1
		)
		{
			$this->basename = $basename;
			$this->extension = $extension;
			$this->width = $width;
			$this->height = $height;
			$this->show_size_tag = $show_size_tag;
			$this->width_size_override = $width_size_override;
			$this->loader = $loader;
			$this->show_version = $show_version;

			$this->width_tag = ( $width_size_override !== -1 ) ? $width_size_override : $width;

			$loader_extension = null;
			if ( $loader !== null )
			{
				$loader_extension = $loader->getExtension();
				if ( !empty( $loader_extension ) )
				{
					$extension = $loader_extension;
				}
			}

			$absolute_filename = ( !$show_size_tag || self::testIsCustomPattern( $height ) )
				? $basename . ( ( !empty( $extension ) && empty( $loader_extension ) ) ? ".{$extension}" : '' )
				: "{$basename}-{$width}x{$height}" . ( ( !empty( $extension ) && empty( $loader_extension ) ) ? ".{$extension}" : '' );

			try
			{

				if ( !empty( $loader_extension ) && strpos( $absolute_filename, '.png' ) !== false )
				{
					echo 'helloooo';
					throw new \Exception( "ASKJFNSAKJNFKJL" );
				}

				$absolute_filename = ( $loader !== null )
					?	( $show_version ) ? $loader->getSourceWithVersion( $absolute_filename ) : $loader->getSource( $absolute_filename )
					: $absolute_filename;

				if ( strpos( $absolute_filename, '.png.png' ) !== false )
				{
					echo 'helloooo';
					throw new \Exception( "ASKJFNSAKJNFKJL" );
				}
			}
			catch ( MissingFileException $e )
			{
				throw new MissingFileException( $e->getFilename(), new SrcSetItem( $basename, $width, $height, $extension, $loader, false, $show_size_tag, $width_size_override ) );
			}

			$this->absolute_filename = $absolute_filename;
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
			return $this->absolute_filename;
		}

		public function getSizeString() : string
		{
			$width_tag = ( string )( $this->width_tag );
			return "{$width_tag}w";
		}

		public function getWidthTag() : int
		{
			return $this->width_tag;
		}

		public function applyLoader( FileLoader $loader ) : SrcSetItem
		{
			return new SrcSetItem( $this->basename, $this->width, $this->height, $this->extension, $loader, $this->show_version, $this->show_size_tag, $this->width_size_override );
		}

		public function setShowVersion( bool $value ) : SrcSetItem
		{
			return new SrcSetItem( $this->basename, $this->width, $this->height, $this->extension, $this->loader, $value, $this->show_size_tag, $this->width_size_override );
		}



	//
	//  PRIVATE
	//
	//////////////////////////////////////////////////////////

		private static function testIsCustomPattern( int $height ) : bool
		{
			return $height === -1;
		}

		private $basename;
		private $extension;
		private $width;
		private $height;
		private $show_size_tag;
		private $width_size_override;
		private $loader;
		private $show_version;
}
