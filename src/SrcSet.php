<?php

declare( strict_types = 1 );
namespace WaughJ\HTMLImage;

use WaughJ\FileLoader\FileLoader;
use WaughJ\FileLoader\MissingFileException;

class SrcSet
{
	//
	//  PUBLIC
	//
	//////////////////////////////////////////////////////////

		public function __construct( $srcset, $loader = null, $show_version = null )
		{
			try
			{
				$this->sources =
					    ( self::testIsThisType( $srcset ) ) ? self::applyLoaderToList( $srcset->getSources(), $loader, $show_version )
					: ( ( is_string( $srcset ) )            ? self::generateSrcSetFromString( $srcset, $loader, $show_version )
					: ( ( is_array( $srcset )  )            ? self::generateSrcSetFromArray( $srcset, $loader, $show_version )
											                : [] ));
			}
			catch ( MissingFileException $e )
			{
				throw new MissingFileException( $e->getFilename(), new SrcSet( $e->getFallbackContent() ) );
			}
		}

		public function getAttributeText() : string
		{
			return ( !empty( $this->sources ) ) ? " srcset=\"{$this->getSourcesText()}\"" : '';
		}

		public function getSourcesText() : string
		{
			return implode( ', ', $this->sources );
		}

		public function getSources() : array
		{
			return $this->sources;
		}



	//
	//  PRIVATE
	//
	//////////////////////////////////////////////////////////

		private static function generateSrcSetFromArray( array $srcset, $loader, $show_version ) : array
		{
			$missing_files = [];
			$list = [];
			foreach ( $srcset as $source )
			{
				try
				{
					if ( is_object( $source ) && get_class( $source ) === SrcSetItem::class )
					{
						if ( $show_version !== null )
						{
							$source = $source->setShowVersion( $show_version );
						}
						$list[] = ( $loader !== null ) ? $source->applyLoader( $loader ) : $source;
					}
					else if ( is_string( $source ) )
					{
						$list[] = self::generateSrcSetItemFromString( $source, $loader, $show_version );
					}
				}
				catch ( MissingFileException $e )
				{
					$missing_files[] = $e->getFilename();
					$list[] = $e->getFallbackContent();
				}
			}

			if ( !empty( $missing_files ) )
			{
				throw new MissingFileException( $missing_files, $list );
			}

			return $list;
		}

		private static function generateSrcSetFromString( string $srcset, $loader, $show_version ) : array
		{
			$list = [];
			$string_list = preg_split( "/,[\s]*/", $srcset );
			return self::generateSrcSetFromArray( $string_list, $loader, $show_version );
		}

		private static function generateSrcSetItemFromString( string $source_string, $loader, $show_version ) : SrcSetItem
		{
			$parts = explode( ' ', $source_string );
			if ( count( $parts ) < 2 )
			{
				throw new MalformedSrcSetStringException( $source_string );
			}
			$width1 = intval( str_replace( 'w', '', array_pop( $parts ) ) ); // Width is last space-divided chunk with "w" remove & forced into an integer.
			$filename = implode( '', $parts ); // Combine all the rest o' the chunks as the filename.

			// Fallback data in case string is malformed.
			$base_filename = $filename;
			$extension = '';
			$width = $width1;
			$height = -1;

			// PHP doesn't allow for breakable block, so this with a break @ the very end is the equivalent.
			// The only "clean" alternative is a separate function with a bunch o' tedious arguments,
			// including either output reference arguments or returning an array, which has to be dealt with.
			//
			// All breaks before the last are due to malformed strings, in which case we throw 'way our work & stick with fallback.
			while ( true )
			{
				$question_mark_divided = explode( '?', $filename );
				$without_question_marks = array_splice( $question_mark_divided, 0, 1 )[ 0 ]; // Get all text left o' 1st ? or everything if there are no ?.

				$period_divided = explode( '.', $without_question_marks );

				$extensionless_filename = $without_question_marks;
				if ( !self::testNoDivision( $period_divided ) )
				{
					// Take out all text left o' 1st period as extensionless filename.
					$extensionless_filename = array_splice( $period_divided, 0, 1 ); // Returns array with all items in 1 string as the only item...
					if ( count( $extensionless_filename ) === 0 ) { break; } // ...'less the string is malformed with just a starting period & text afterward, in which case we give up.
					$extensionless_filename = $extensionless_filename[ 0 ]; // Turn array into string.
					$extension = implode( '.', $period_divided ); // All the rest is reconnected with dots & set as extension.
					$base_filename = $extensionless_filename;
				}

				$x_divided = explode( 'x', $extensionless_filename ); // Now split by "x" to get height.

				if ( self::testNoDivision( $x_divided ) ) { break; } // If no X, then string is malformed. Give up.

				$height_str = intval( array_pop( $x_divided ) ); // Height is text after last x, forced into an integer.
				$heightless_filename = implode( 'x', $x_divided ); // Keep the rest o' the text left o' the last x. ( ¡Obviously our filename may have many mo' x's! )
				$hyphen_divided = explode( '-', $heightless_filename ); // Width string is right o' last hyphen before last x.

				if ( self::testNoDivision( $hyphen_divided ) ) { break; } // If no hyphen, string is malformed. Give up.

				$width2 = intval( array_pop( $hyphen_divided ) ); // Extract width & force into int.

				if ( $width2 !== $width1 ) { break; } // This gives us 2 widths. If the widths are different, the user is following a different scheme & we should leave it @ that & give up.

				// String is valid, & so we set fallback data with newly-found data.
				$base_filename = implode( '-', $hyphen_divided ); // All the remaining text after all the previous extractions.
				$width = $width1;
				$height = $height_str;
				break;
			}

			return new SrcSetItem( $base_filename, $width, $height, $extension, $loader, $show_version ?? true );
		}

		private static function applyLoaderToList( array $srcset, $loader, $show_version ) : array
		{
			$new_list = [];
			foreach ( $srcset as $source )
			{
				if ( $show_version !== null )
				{
					$source = $source->setShowVersion( $show_version );
				}
				$new_list[] = ( $loader !== null ) ? $source->applyLoader( $loader ) : $source;
			}
			return $new_list;
		}

		private static function testNoDivision( array $array ) : bool
		{
			// Explode returns array with just whole string ( 1 element ) if it has no delimiter to divide by.
			return count( $array ) === 1;
		}

		private static function testIsThisType( $subject ) : bool
		{
			return is_object( $subject ) && get_class( $subject ) === self::class;
		}

		private $srcset;
}
