<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage
{
	use WaughJ\FileLoader\FileLoader;
	use WaughJ\HTMLAttributeList\HTMLAttributeList;
	use function \WaughJ\TestHashItem\TestHashItemString;

	class HTMLImage
	{
		public function __construct( string $src, FileLoader $loader = null, array $other_attributes = [] )
		{
			$this->attributes = $other_attributes;
			$this->src = $src;
			$this->loader = $loader;
			$this->attributes[ 'alt' ] = TestHashItemString( $this->attributes, 'alt', '' );
			$this->show_version = !array_key_exists( 'show-version', $this->attributes ) || $this->attributes[ 'show-version' ];
			unset( $this->attributes[ 'show-version' ] );
			if ( isset( $this->attributes[ 'srcset' ] ) && is_string( $this->attributes[ 'srcset' ] ) )
			{
				$this->attributes[ 'srcset' ] = $this->adjustSrcSet( $this->attributes[ 'srcset' ] );
			}
			$this->attributes = new HTMLAttributeList( $this->attributes );
		}

		public function __toString()
		{
			return $this->getHTML();
		}

		public function print() : void
		{
			echo $this->getHTML();
		}

		public function getHTML() : string
		{
			return "<img src=\"{$this->getSource()}\"{$this->attributes->getAttributesText()} />";
		}

		public function getSource() : string
		{
			return $this->getASource( $this->src );
		}

		public function setAttribute( string $type, $value ) : HTMLImage
		{
			$new_attributes = $this->attributes->getAttributeValuesMap();
			$new_attributes[ $type ] = $value;
			return new HTMLImage( $this->src, $this->loader, $new_attributes );
		}

		public function addToClass( $value ) : HTMLImage
		{
			$old_value = $this->attributes->getAttributeValue( 'class' );
			$new_value = ( $old_value !== null ) ? "{$old_value} {$value}" : $value;
			return $this->setAttribute( 'class', $new_value );
		}

		private function adjustSrcSet( string $srcset ) : string
		{
			$accepted_sources = [];
			$sources = preg_split( "/,[\s]*/", $srcset );
			foreach ( $sources as $source )
			{
				$parts = explode( ' ', $source );
				$width = $parts[ count( $parts ) - 1 ];
				array_pop( $parts );
				$filename = $this->getASource( implode( '', $parts ) );
				array_push( $accepted_sources, "$filename $width" );
			}
			return implode( ', ', $accepted_sources );
		}

		private function getASource( string $src ) : string
		{
			return ( $this->loader !== null )
				? (
					( $this->show_version )
					? $this->loader->getSourceWithVersion( $src )
					: $this->loader->getSource( $src )
				)
				: $src;
		}

		private $src;
		private $loader;
		private $attributes;
		private $show_version;
	}
}
