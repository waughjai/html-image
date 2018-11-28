<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage
{
	use WaughJ\HTMLAttributeList\HTMLAttributeList;
	use function \WaughJ\TestHashItem\TestHashItemString;

	class HTMLImage
	{
		public function __construct( string $src, array $other_attributes = [] )
		{
			$this->attributes = $other_attributes;
			$this->attributes[ 'src' ] = TestHashItemString( $this->attributes, 'src', $src );
			$this->attributes[ 'alt' ] = TestHashItemString( $this->attributes, 'alt', '' );
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
			return "<img{$this->attributes->getAttributesText()} />";
		}

		private $attributes;
	}
}
