<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage;

use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;
use WaughJ\HTMLAttributeList\HTMLAttributeList;
use function \WaughJ\TestHashItem\TestHashItemString;

class HTMLImage
{
	//
	//  PUBLIC
	//
	//////////////////////////////////////////////////////////

		public function __construct( string $local_src, FileLoader $loader = null, array $other_attributes = [] )
		{
			$show_version = self::testShowVersionAttribute( $other_attributes );
			$absolute_src_versionless = self::generateSourceVersionless( $local_src, $loader );
			try
			{
				$srcset = new SrcSet( $other_attributes[ 'srcset' ] ?? null );
				$other_attributes[ 'alt' ] = TestHashItemString( $other_attributes, 'alt', '' );

				unset( $other_attributes[ 'show-version' ] ); // We don't want this to accidentally become an HTML attribute.
				unset( $other_attributes[ 'srcset' ] ); // Due to the complexity o' sources & the file loader, we handle this attribute manually.

				$this->show_version = $show_version;
				$this->local_src = $local_src;
				$this->attributes = new HTMLAttributeList( $other_attributes );
				$this->absolute_src = self::generateSource( $local_src, $loader, $show_version );
				$this->absolute_src_versionless = $absolute_src_versionless;
				$this->srcset = $srcset;
				$this->loader = $loader;
				$this->html = "<img src=\"{$this->absolute_src}\"{$this->srcset->getAttributeText( $loader, $show_version )}{$this->attributes->getAttributesText()} />";
			}
			catch ( MissingFileException $e )
			{
				throw new MissingFileException( $e->getFilename(), new HTMLImage( $absolute_src_versionless, null, $other_attributes ) );
			}
		}

		public function __toString()
		{
			return $this->html;
		}

		public function print() : void
		{
			echo $this->html;
		}

		public function getHTML() : string
		{
			return $this->html;
		}

		public function getSource() : string
		{
			return $this->absolute_src;
		}

		public function getSourceVersionless() : string
		{
			return $this->absolute_src_versionless;
		}

		public function setAttribute( string $type, $value ) : HTMLImage
		{
			$new_attributes = $this->getOriginalArguments();
			$new_attributes[ $type ] = $value;
			return new HTMLImage( $this->local_src, $this->loader, $new_attributes );
		}

		public function addToClass( $value ) : HTMLImage
		{
			$old_value = $this->attributes->getAttributeValue( 'class' );
			$new_value = ( $old_value !== null ) ? "{$old_value} {$value}" : $value;
			return $this->setAttribute( 'class', $new_value );
		}

		public function changeLoader( $loader ) : HTMLImage
		{
			$new_attributes = $this->getOriginalArguments();
			return new HTMLImage( $this->local_src, $loader, $new_attributes );
		}

		// Lots o' classes based on this will need to use this.
		public static function testShowVersionAttribute( array $attributes ) : bool
		{
			// Defaults to true if not set.
			return ( bool )( $attributes[ 'show-version' ] ?? true );
		}



	//
	//  PRIVATE
	//
	//////////////////////////////////////////////////////////

		private function getOriginalArguments() : array
		{
			$arguments = $this->attributes->getAttributeValuesMap();
			$arguments[ 'srcset' ] = $this->srcset;
			$arguments[ 'show-version' ] = $this->show_version;
			return $arguments;
		}

		// Automatically apply file loader to srcset URLs.
		private static function adjustSrcSet( string $srcset, $loader, bool $show_version ) : string
		{
			$accepted_sources = [];
			$sources = preg_split( "/,[\s]*/", $srcset );
			foreach ( $sources as $source )
			{
				$parts = explode( ' ', $source );
				$width = $parts[ count( $parts ) - 1 ];
				array_pop( $parts );
				$filename = self::generateSource( implode( '', $parts ), $loader, $show_version );
				array_push( $accepted_sources, "$filename $width" );
			}
			return implode( ', ', $accepted_sources );
		}

		private static function generateSource( string $src, $loader, bool $show_version ) : string
		{
			return ( $loader !== null )
				? (
					( $show_version )
					? $loader->getSourceWithVersion( $src )
					: $loader->getSource( $src )
				)
				: $src;
		}

		private static function generateSourceVersionless( string $src, $loader ) : string
		{
			return ( $loader !== null )
				? $loader->getSource( $src )
				: $src;
		}

		private $show_version;
		private $local_src;
		private $srcset;
		private $attributes;
		private $absolute_src;
		private $absolute_src_versionless;
		private $html;
		private $loader;
}
