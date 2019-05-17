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
		new FileLoader([ 'directory-url' => 'https://www.example.com' ]),
		'image.png',
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

### Responsive Images

HTMLImage makes setting srcset & sizes for responsive images easier & mo’ convenient.

For instance, if srcset is set, but not sizes, the constructor will automatically generate a sizes attribute based on how srcset is set, which will regenerate if srcset is changed later, but will ne’er o’erride manually-set sizes.

Example:

	use WaughJ\HTMLImage\HTMLImage;

	$image = new HTMLImage
	(
		"demo.png",
		null,
		[ 'srcset' => 'demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w' ]
	);

	// Will output “<img src="demo.png" srcset="demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w" sizes="(max-width: 300px) 300px, (max-width: 800px) 800px, 1280px" alt="" />”
	$image->print();

HTMLImage can also recognize a shorthand version o’ srcset using : as delimiter following the following pattern:

	[$path].[$extension]:[$width]x[$height],[$width]x[$height][…]

For each comma-delimited size, height is optional. Not providing a height will simply use the base filename as the full filename & will just use the width as the width tag that goes after the filename.

For example, to print out the same content as the previous example, you can type out ’stead:

	use WaughJ\HTMLImage\HTMLImage;

	$image = new HTMLImage
	(
		"demo.png",
		null,
		[ 'srcset' => 'demo.png:300x300,800x500,1280' ]
	);

	// Will output “<img src="demo.png" srcset="demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w" sizes="(max-width: 300px) 300px, (max-width: 800px) 800px, 1280px" alt="" />”
	$image->print();

Malformed srcset values will throw a MalformedSrcSetStringException. These are srcset values that don’t follow the standard HTML format or the shorthand format.

If for some reason you want to, you can pass in an array o’ SrcSetItem instances ’stead o’ a string:

	use WaughJ\HTMLImage\HTMLImage;
	use WaughJ\HTMLImage\SrcSetItem;

	$image = new HTMLImage
	(
		"demo.png",
		null,
		[ 'srcset' => [ new SrcSetItem( 'demo', 300, 300, 'png' ), new SrcSetItem( 'demo', 800, 500, 'png' ), new SrcSetItem( 'demo', 1280, -1, 'png' ) ] ]
	);

	// Will output “<img src="demo.png" srcset="demo-300x300.png 300w, demo-800x500.png 800w, demo.png 1280w" sizes="(max-width: 300px) 300px, (max-width: 800px) 800px, 1280px" alt="" />”
	$image->print();

As shown, to make a srcset item keep the base filename & only use the width as a width tag, pass -1 for height.

You can also pass in a SrcSet instance instead o’ an array, which can take in an array o’ SrcSetItem instances, an array o’ strings, or a string. ( All o’ the options shown here are simply passed into a SrcSet constructor, including a SrcSet instance, in the backend, anyway ).

### Error Handling

The HTMLImage constructor may throw a WaughJ\FileLoader\MissingFileException exception if it is set to show a version tag ( the default ) & it can't access the file to get its modified date ( usually caused by the file not being where it's expected to be ). This exception includes in its getFallbackContent method with an HTMLImage object with the versionless source for easy recovery like so ( while the getFilename method can be used to find where it's trying to find the file on the server ):

	use WaughJ\HTMLImage\HTMLImage;
	use WaughJ\FileLoader\FileLoader;
	use WaughJ\FileLoader\MissingFileException;

	try
	{
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
	}
	catch ( MissingFileException $e )
	{
		// Maybe log somewhere that it couldn't find the file located @ $e->getFilename().
		$image = $e->getFallbackContent(); // This will be the equivalent o' the image with its 'show_version' property false.
	}

	$image->print(); // Will still work, e'en if an exception is thrown.

As mentioned in the Responsive Images section, malformed srcset values will throw a MalformedSrcSetStringException.
