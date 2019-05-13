HTML Image
=========================

Simple class for encapsulating image info and autogenerating the HTML for it.

The 1st mandatory argument to pass into the constructor is the src.

2nd optional argument is a FileLoader instance that will make loading images from certain URLs & adding a cache-corruption-fixing version parameter to it. For no FileLoader, pass in null. For mo' info on FileLoader, visit https://github.com/waughjai/file-loader.

3rd is an optional hash map o' attributes.

If no alt attribute is passed in, an empty alt attribute will automatically be added to the image's HTML, as per proper HTML protocol.

## Example

	use WaughJ\HTMLImage\HTMLImage;
	use WaughJ\FileLoader\FileLoader;

	$image = new HTMLImage
	(
		'image.png',
		new FileLoader([ 'directory-url' => 'https://www.example.com' ]),
		[
			'class' => 'center-img ornate',
			'width' => '600',
			'height' => '400'
		]
	);
	$image->print();

Will output `<img src="https://www.example.com/image.png" class="center-img ornate" width="600" height="400" />`

	echo $image->getSource();

Will output `https://www.example.com/image.png`

### Changing Attributes o' Already-Existing Instances

You can also set attributes or add classes to an already-created image using the "setAttribute" & "addToClass" methods:

	$image = new HTMLImage
	(
		'image.png',
		new FileLoader([ 'directory-url' => 'https://www.example.com' ]),
		[
			'class' => 'center-img ornate',
			'width' => '600',
			'height' => '400'
		]
	);
	$image = $image->addToClass( 'new-image' );
	$image = $image->setAttribute( 'id', 'first-image' )
	$image->print();

Will output `<img src="https://www.example.com/image.png" class="center-img ornate new-image" id="first-image" width="600" height="400" />`

Note that "setAttribute" & "addToClass" do not directly change object, which is immutable, but return a clone o' the image with the changes. Thus, if you want to change an object, you must set the object equal to the output o' the method call: `$image = $image->setAttribute( 'id', 'first-image' )`. Just `$image->setAttribute( 'id', 'first-image' )` won't do anything.

### Error Handling

The getHTML method may throw a WaughJ\FileLoader\MissingFileException exception if it is set to show a version tag ( the default ) & it can't access the file to get its modified date ( usually caused by the file not being where it's expected to be ). This exception includes in its getFallbackContent method fallback HTML with the versionless source for easy recovery like so ( while the getFilename method can be used to find where it's trying to find the file on the server ):

$html = null;
try
{
	$html = $image->getHTML();
}
catch ( MissingFileException $e )
{
	// Maybe log somewhere that it couldn't find the file located @ $e->getFilename().
	$html = $e->getFallbackContent(); // This will be the equivalent o' the image HTML with its 'show_version' property false.
}

Since the toString method can't throw exceptions, converting an image to a string through ( string )( $image ) or such will just automatically return the fallback without throwing any exceptions or showing any errors.
