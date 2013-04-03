# Twitter Cards PHP

[Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards developer documentation") summarize webpage content for inline expansion of linked content on Twitter.com and Twitter native applications (iOS, Android, Mac, Tweetdeck, etc). Twitter users may select an individual Tweet and immediately view a content summary including a linked title, content description, image, author attribution, site attribution, and inline video.

The Twitter_Cards PHP class helps you build Twitter Card markup for your website. Build a summary or photo card, set the appropriate attributes, and build `<meta>` elements suitable for output inside your (x)HTML document `<head>`.

Note: As of April 2013 Twitter Card display on Twitter.com or its applications requires [whitelisting domains](https://dev.twitter.com/form/participate-twitter-cards "request Twitter Card domain whitelist inclusion").

This PHP does not currently support the new properties of [gallery](https://dev.twitter.com/docs/cards/types/gallery-card "Twitter gallery card") (image[0-3]), [app](https://dev.twitter.com/docs/cards/types/app-card "Twitter app card") (Apple App Store and Google Play app IDs), or [product](https://dev.twitter.com/docs/cards/types/product-card "Twitter product card") (data[1|2],label[1|2]).

Test your markup and/or URLs using in the [Twitter Card preview tool](https://dev.twitter.com/docs/cards/preview).

## Summary example

A Twitter Card is a "summary" by default.

Create a summary card like this:

![Twitter summary example from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/web_summary_1.png)
![Twitter summary example for mobile from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/ios_summary.png)

By including Twitter Card markup in your `<head>`:

```php
<?php
// load Twitter_Card
if ( ! class_exists( 'Twitter_Card' ) )
  require_once( dirname( __FILE__ ) . '/twitter-card.php' );

// build a card
$card = new Twitter_Card();
$card->setURL( 'http://www.nytimes.com/2012/02/19/arts/music/amid-police-presence-fans-congregate-for-whitney-houstons-funeral-in-newark.html' );
$card->setTitle( 'Parade of Fans for Houston\'s Funeral' );
$card->setDescription( 'NEWARK - The guest list and parade of limousines with celebrities emerging from them seemed more suited to a red carpet event in Hollywood or New York than than a gritty stretch of Sussex Avenue near the former site of the James M. Baxter Terrace public housing project here.' );
// optional
$card->setImage( 'http://graphics8.nytimes.com/images/2012/02/19/us/19whitney-span/19whitney-span-articleLarge.jpg', 600, 330 );
$card->setSiteAccount( 'nytimes', '807095' );
$card->setCreatorAccount( 'SarahMaslinNir', '24134103' );

// echo a string of <meta> elements
echo $card->asHTML();
?>
```

Image and Twitter account data are optional for Twitter Summary Cards. All other properties in the example above are required: URL, title, description.

Define a Twitter account associated with an author or your site by passing a Twitter screenname and an optional Twitter user id. A user may change his screenname but his ID remains the same. You can look up numeric IDs by screen_name via the [Twitter user/show API](https://dev.twitter.com/docs/api/1/get/users/show). Example: [jack](https://api.twitter.com/1/users/show.json?screen_name=jack "Jack Dorsey Twitter profile expressed as JSON")

Images must be at least 60x60 pixels. Images larger than 120x120 pixels will be resized and cropped. Help preserve aspect ratios by passing optional width and height parameters when defining your image.

## Photo example

A "photo" card takes advantage of the full width of the content area.

Create a photo card like this:

![Twitter Card photo example from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/web_photo.png)
![Twitter Card photo example for mobile from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/ios_photo.png)

By including Twitter Card markup in your `<head>`:

```php
<?php
// load Twitter_Card
if ( ! class_exists( 'Twitter_Card' ) )
	require_once( dirname( __FILE__ ) . '/twitter-card.php' );

// build a photo card
$card = new Twitter_Card( 'photo' );
$card->setURL( 'http://instagr.am/p/H4IZmoOZDk/' );
$card->setTitle( '' );
$card->setDescription( 'Good Morning, San Francisco' );
$card->setImage( 'http://instagr.am/p/H4IZmoOZDk/media/?size=l', 610, 610 );

// optional
$card->setSiteAccount( 'instagram', '180505807' );
$card->setCreatorAccount( 'sippey', '4711' );

// echo a string of <meta> elements
echo $card->asHTML();
?>
```

The title property is intentionally set as blank. In this example Instagram has a photo description but no title.

An image is a required property of a photo card. A specified image must be at least 280 pixels wide and 150 pixels tall. Your image may be resized up to a width of 560 pixels; plan your content accordingly.

The description property is optional.

## Player example

A "player" card creates a video preview from either an HTML iframe or a poster frame. An iframe wider than 435 pixels will be resized to fit a maximum width of 435 pixels.

Create a player card like this:

![Twitter player card example from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/web_player2.png)
![Twitter player card example for mobile from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/ios_player.png)

By including Twitter Card markup in your `<head>`:

```php
<?php
// load Twitter_Card
if ( ! class_exists( 'Twitter_Card' ) )
	require_once( dirname( __FILE__ ) . '/twitter-card.php' );

// build a card
$card = new Twitter_Card( 'player' );
$card->setURL( 'http://www.youtube.com/watch?v=AEngFNb5CRU' );
$card->setTitle( 'Apple - The New iPad' );
$card->setDescription( 'iPad is a magical window where nothing comes between you and what you love. Now that experience is even more incredible with the new iPad.' );
$card->setImage( 'http://i2.ytimg.com/vi/AEngFNb5CRU/hqdefault.jpg', 480, 360 );
$card->setVideo( 'https://www.youtube.com/embed/AEngFNb5CRU', 435, 251 );

// optional
$card->setSiteAccount( 'youtube', '10228272' );

// echo a string of <meta> elements
echo $card->asHTML();
?>
```

An image is a required property of a photo card. An image must have at least 68,600 pixels.

Player URL must be HTTPS to prevent mixed content warnings.