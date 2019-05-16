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
			$missing_files = [];
			$absolute_src_versionless = self::generateSourceVersionless( $local_src, $loader );
			$show_version = self::testShowVersionAttribute( $other_attributes );

			try
			{
				$absolute_src = self::generateSource( $local_src, $loader, $show_version );
			}
			catch ( MissingFileException $e )
			{
				$missing_files[] = $e->getFilename();
			}

			try
			{
				$srcset = new SrcSet( $other_attributes[ 'srcset' ] ?? null, $loader, $show_version );
			}
			catch ( MissingFileException $e )
			{
				$missing_files[] = $e->getFilename();
			}

			if ( !empty( $missing_files ) )
			{
				$other_attributes[ 'show-version' ] = false;
				throw new MissingFileException( $missing_files, new HTMLImage( $local_src, $loader, $other_attributes ) );
			}

			// Finally set properties.
			$this->original_arguments = $other_attributes;
			$this->local_src = $local_src;
			$this->absolute_src_versionless = $absolute_src_versionless;
			$this->absolute_src = $absolute_src;
			$this->loader = $loader;
			$this->html = self::generateHTML( $absolute_src, $srcset, $other_attributes );
		}

		private static function generateHTML( string $src, SrcSet $srcset, array $other_arguments )
		{
			$html_attributes = self::configureHTMLAttributes( $other_arguments );
			return "<img src=\"{$src}\"{$srcset->getAttributeText()}{$html_attributes->getAttributesText()} />";
		}

		private static function configureHTMLAttributes( array $other_arguments ) : HTMLAttributeList
		{
			$other_arguments[ 'alt' ] = TestHashItemString( $other_arguments, 'alt', '' );
			unset( $other_arguments[ 'show-version' ] ); // We don't want this to accidentally become an HTML attribute.
			unset( $other_arguments[ 'srcset' ] ); // Due to the complexity o' sources & the file loader, we handle this attribute manually.
			return new HTMLAttributeList( $other_arguments );
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
			$new_attributes = $this->original_arguments;
			$new_attributes[ $type ] = $value;
			return new HTMLImage( $this->local_src, $this->loader, $new_attributes );
		}

		public function addToClass( $value ) : HTMLImage
		{
			$old_value = $this->original_arguments[ 'class' ] ?? null;
			$new_value = ( $old_value !== null ) ? "{$old_value} {$value}" : $value;
			return $this->setAttribute( 'class', $new_value );
		}

		public function changeLoader( $loader ) : HTMLImage
		{
			return new HTMLImage( $this->local_src, $loader, $this->original_arguments );
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

		private $local_src;
		private $absolute_src;
		private $absolute_src_versionless;
		private $html;
		private $loader;
		private $original_arguments;
}
