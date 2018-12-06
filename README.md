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
