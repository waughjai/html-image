HTML Image
=========================

Simple class for encapsulating image info and autogenerating the HTML for it.

The 1st mandatory argument to pass into the constructor is the src.

2nd is an optional hash map o' attributes.

If no alt attribute is passed in, an empty alt attribute will automatically be added to the image's HTML, as per proper HTML protocol.

## Example

	use WaughJ\HTMLImage\HTMLImage;

	$image = new HTMLImage
	(
		'image.png',
		[
			'class' => 'center-img ornate',
			'width' => '600',
			'height' => '400'
		]
	);
	$image->print();

Will output `<img src="image.png" class="center-img ornate" width="600" height="400" />`
