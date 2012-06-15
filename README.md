# Twitter Cards PHP

[Twitter Cards](https://dev.twitter.com/docs/cards "Twitter Cards developer documentation") summarize webpage content for inline expansion of linked content on Twitter.com and Twitter native applications (iOS, Android, Mac, Tweetdeck, etc). Twitter users may select an individual Tweet and immediately view a content summary including a linked title, content description, image, author attribution, site attribution, and inline video.

The Twitter_Cards PHP class helps you build Twitter Card markup for your website. Build a summary or photo card, set the appropriate attributes, and build `<meta>` elements suitable for output inside your (x)HTML document `<head>`.

Note: As of June 2012 Twitter Card display on Twitter.com or its applications requires [whitelisting domains](https://dev.twitter.com/form/participate-twitter-cards "request Twitter Card domain whitelist inclusion").

## Summary example

A Twitter Card is a "summary" by default.

Create a summary like this:

![Twitter summary example from dev.twitter.com](https://dev.twitter.com/sites/default/files/images_documentation/card-web-summary_0.png)

By including Twitter Card markup in your `<head>`:

```php
<?php
// load Twitter_Card
if ( ! class_exists( 'Twitter_Card' ) )
  require_once( dirname( __FILE__ ) . '/twitter-card.php' );

// build a card
$card = new Twitter_Card();
$card->setSiteAccount( 'nytimes', '807095' );
$card->setCreatorAccount( 'SarahMaslinNir', '24134103' );
$card->setURL( 'http://www.nytimes.com/2012/02/19/arts/music/amid-police-presence-fans-congregate-for-whitney-houstons-funeral-in-newark.html' );
$card->setTitle( 'Parade of Fans for Houston\'s Funeral' );
$card->setDescription( 'NEWARK - The guest list and parade of limousines with celebrities emerging from them seemed more suited to a red carpet event in Hollywood or New York than than a gritty stretch of Sussex Avenue near the former site of the James M. Baxter Terrace public housing project here.' );
$card->setImage( 'http://graphics8.nytimes.com/images/2012/02/19/us/19whitney-span/19whitney-span-articleLarge.jpg', 600, 330 );

// echo a string of <meta> elements
echo $card->asHTML();
?>
```