<?php
/**
 * Describe a page in Twitter Card markup
 *
 * @since 1.0
 * @version 1.1
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @link https://dev.twitter.com/docs/cards Twitter Card documentation
 * @link https://github.com/niallkennedy/twitter-cards-php Follow on GitHub
 */
class Twitter_Card {
	/**
	 * Library version
	 *
	 * @since 1.0
	 * @var string
	 */
	const VERSION = '1.1';

	/**
	 * Twitter prefix
	 *
	 * @since 1.0
	 * @var string
	 */
	const PREFIX = 'twitter';

	/**
	 * Only allow a publisher to define a valid card type
	 *
	 * @since 1.0
	 * @var array
	 */
	public static $allowed_card_types = array( 'summary' => true, 'photo' => true, 'gallery' => true, 'player' => true, 'product' => true, 'app' => true );

	/**
	 * Only allow HTTP and HTTPs schemes in URLs
	 *
	 * @since 1.0
	 * @var array
	 */
	public static $allowed_schemes = array( 'http' => true, 'https' => true );

	/**
	 * Create a new Twitter Card object, optionally overriding the default card type of "summary"
	 *
	 * @since 1.0
	 * @param string $card_type The card type. one of "summary", "photo", "player"
	 */
	public function __construct( $card_type = '' ) {
		if ( is_string( $card_type ) && isset( self::$allowed_card_types[$card_type] ) )
			$this->card = $card_type;
		else
			$this->card = 'summary';
	}

	/**
	 * Test an inputted Twitter username for validity
	 *
	 * @since 1.0
	 * @param string $username Twitter username
	 * @return bool true if valid else false
	 */
	public static function is_valid_username( $username ) {
		if ( is_string( $username ) && $username )
			return true;
		return false;
	}

	/**
	 * Basic validity test to make sure a Twitter ID input looks like a Twitter numerical ID
	 *
	 * @since 1.0
	 * @param string $id Twitter user ID string
	 * @return bool true if the string contains only digits. else false
	 */
	public static function is_valid_id( $id ) {
		// ints should pass. convert to string later for consistency
		if ( is_int( $id ) )
			return true;
		// string containing only digits
		if ( is_string( $id ) && ctype_digit( $id ) )
			return true;
		return false;
	}

	/**
	 * Test if given URL is valid and matches allowed schemes
	 *
	 * @since 1.0
	 * @param string $url URL to test
	 * @param array $allowed_schemes one or both of http, https
	 * @return bool true if URL can be parsed and scheme allowed, else false
	 */
	public static function is_valid_url( $url, $allowed_schemes = null ) {
		if ( ! ( is_string( $url ) && $url ) )
			return false;

		if ( ! is_array( $allowed_schemes ) || empty( $allowed_schemes ) ) {
			$schemes = self::$allowed_schemes;
		} else {
			$schemes = array();
			foreach ( $allowed_schemes as $scheme ) {
				if ( isset( self::$allowed_schemes[$scheme] ) )
					$schemes[$scheme] = true;
			}

			if ( empty( $schemes ) )
				$schemes = self::$allowed_schemes;
		}

		// parse_url will test scheme + full URL validity vs. just checking if string begins with "https://"
		try {
			$scheme = parse_url( $url, PHP_URL_SCHEME );
			if ( is_string( $scheme ) && isset( $schemes[ strtolower( $scheme ) ] ) )
				return true;
		} catch( Exception $e ) {} // E_WARNING in PHP < 5.3.3

		return false;
	}

	/**
	 * Canonical URL. Basic check for string before setting
	 *
	 * @since 1.0
	 * @param string $url canonical URL
	 * @return Twitter_Card for chaining
	 */
	public function setURL( $url ) {
		if ( self::is_valid_url( $url ) )
			$this->url = $url;
		return $this;
	}

	/**
	 * Page title.
	 * Will be truncated at 70 characters by Twitter but need not necessarily be 70 characters on the page.
	 *
	 * @since 1.0
	 * @param string $title page title
	 * @return Twitter_Card for chaining
	 */
	public function setTitle( $title ) {
		if ( is_string( $title ) ) {
			$title = trim( $title );
			// photo cards may explicitly declare an empty title
			if ( ! $title && $this->card !== 'photo' )
				return;
			$this->title = $title;
		}
		return $this;
	}

	/**
	 * A description of the content.
	 * Descriptions over 200 characters in length will be truncated by Twitter
	 *
	 * @since 1.0
	 * @param string $description description of page content
	 * @return Twitter_Card for chaining
	 */
	public function setDescription( $description ) {
		if ( is_string( $description ) ) {
			$description = trim( $description );
			if ( $description )
				$this->description = $description;
		}
		return $this;
	}

	/**
	 * URL of an image representing the post, with optional dimensions to help preserve aspect ratios on Twitter resizing
	 * Minimum size of 280x150 for photo cards, 60x60 for all other card types
	 * For summary cards images larger than 120x120 will be resized and cropped
	 *
	 * @since 1.0
	 * @param string $url URL of an image representing content
	 * @param int $width width of the specified image in pixels
	 * @param int $height height of the specified image in pixels
	 * @return Twitter_Card for chaining
	 */
	public function setImage( $url, $width = 0, $height = 0 ) {
		if ( ! self::is_valid_url( $url ) )
			return $this;
		$image = new stdClass();
		$image->url = $url;
		if ( is_int( $width ) && is_int( $height ) && $width > 0 && $height > 0 ) {
			// prevent self-inflicted pain

			// minimum dimensions for all card types
			if ( $width < 60 || $height < 60 )
				return $this;

			// minimum dimensions for photo cards
			if ( in_array( $this->card, array( 'photo', 'player' ), true ) && ( $width < 280 || $height < 150 ) )
				return $this;

			$image->width = $width;
			$image->height = $height;
		}
		$this->image = $image;
		return $this;
	}

	/**
	 * HTTPS URL of an HTML suitable for display in an iframe
	 * Expected width and height of the iframe are required
	 * If the iframe width is greater than 435 pixels Twitter will resize to fit a 435 pixel width column
	 *
	 * @since 1.0
	 * @param string $url HTTPS URL to iframe player
	 * @param int $width width in pixels preferred by iframe URL
	 * @param int $height height in pixels preferred by iframe URL
	 * @return Twitter_Card for chaining
	 */
	public function setVideo( $url, $width, $height ) {
		if ( ! ( self::is_valid_url( $url, array( 'https' ) ) && is_int( $width ) && is_int( $height ) && $width > 0 && $height > 0 ) )
			return;

		$video = new stdClass();
		$video->url = $url;
		$video->width = $width;
		$video->height = $height;
		$this->video = $video;
		return $this;
	}

	/**
	 * Link to a direct MP4 file with H.264 Baseline Level 3 video and AAC LC audio tracks
	 * Videos up to 640x480 pixels supported
	 *
	 * @param string $url URL
	 * @return Twitter_Card for chaining
	 */
	public function setVideoStream( $url ) {
		if ( ! ( isset( $this->video ) && self::is_valid_url( $url ) ) )
			return $this;

		$stream = new stdClass();
		$stream->url = $url;
		$stream->type = 'video/mp4; codecs=&quot;avc1.42E01E1, mpa.40.2&quot;';
		$this->video->stream = $stream;
		return $this;
	}

	/**
	 * Build a user object based on username and id inputs
	 *
	 * @since 1.0
	 * @param string $username Twitter username. no need to include the "@" prefix
	 * @param string $id Twitter numerical ID
	 * @return array associative array with username key and optional id key
	 */
	public static function filter_account_info( $username, $id = '' ) {
		if ( ! is_string( $username ) )
			return null;
		$username = ltrim( trim( $username ), '@' );
		if ( ! ( $username && self::is_valid_username( $username ) ) )
			return null;
		$user = new stdClass();
		$user->username = $username;
		if ( $id && self::is_valid_id( $id ) )
			$user->id = (string) $id;
		return $user;
	}

	/**
	 * Twitter account for the site: Twitter username and optional account ID
	 * A user may change his username but his numeric ID will stay the same
	 *
	 * @since 1.0
	 * @param string $username Twitter username. no need to include the "@" prefix
	 * @param string|int $id Twitter numerical ID. passed as a string to better handle large numbers
	 * @return Twitter_Card for chaining
	 */
	public function setSiteAccount( $username, $id = '' ) {
		$user = self::filter_account_info( $username, $id );
		if ( $user && isset( $user->username ) )
			$this->site = $user;
		return $this;
	}

	/**
	 * Content creator / author
	 *
 	 * @since 1.0
	 * @param string $username Twitter username. no need to include the "@" prefix
	 * @param string|int $id Twitter numerical ID. passed as a string to better handle large numbers
	 * @return Twitter_Card for chaining
	 */
	public function setCreatorAccount( $username, $id = '' ) {
		$user = self::filter_account_info( $username, $id );
		if ( $user && isset( $user->username ) )
			$this->creator = $user;
		return $this;
	}

	/**
	 * Check if all required properties have been set
	 * Required properties vary by card type
	 *
	 * @return bool true if all required properties exist for the specified type, else false
	 */
	private function required_properties_exist() {
		if ( ! ( isset( $this->url ) && isset( $this->title ) ) )
			return false;

		// description required for summary & video but not photo
		if ( ! isset( $this->description ) && $this->card !== 'photo' )
			return false;

		// image optional for summary
		if ( in_array( $this->card, array( 'photo', 'player' ), true ) && ! ( isset( $this->image ) && isset( $this->image->url ) ) )
			return false;

		// video player needs a video
		if ( $this->card === 'player' && ! ( isset( $this->video ) && isset( $this->video->url ) && isset( $this->video->width ) && isset( $this->video->height ) ) )
			return false;
		return true;
	}

	/**
	 * Translate object properties into an associative array of Twitter property names as keys mapped to their value
	 *
	 * @return array associative array with Twitter card properties as a key with their respective values
	 */
	public function toArray() {
		if ( ! $this->required_properties_exist() )
			return array();

		// initialize with required properties
		$t = array(
			'card' => $this->card,
			'url' => $this->url,
			'title' => $this->title
		);

		if ( isset( $this->description ) )
			$t['description'] = $this->description;

		// add an image
		if ( isset( $this->image ) && isset( $this->image->url ) ) {
			$t['image'] = $this->image->url;
			if ( isset( $this->image->width ) && isset( $this->image->height ) ) {
				$t['image:width'] = $this->image->width;
				$t['image:height'] = $this->image->height;
			}
		}

		// video on a photo card does not make much sense
		if ( $this->card !== 'photo' && isset( $this->video ) && isset( $this->video->url ) ) {
			$t['player'] = $this->video->url;
			if ( isset( $this->video->width ) && isset( $this->video->height ) ) {
				$t['player:width'] = $this->video->width;
				$t['player:height'] = $this->video->height;
			}

			// no video stream without a main video player. content type required.
			if ( isset( $this->video->stream ) && isset( $this->video->stream->url ) && isset( $this->video->stream->type ) ) {
				$t['player:stream'] = $this->video->stream->url;
				$t['player:stream:content_type'] = $this->video->stream->type;
			}
		}

		// identify the site
		if ( isset( $this->site ) && isset( $this->site->username ) ) {
			$t['site'] = '@' . $this->site->username;
			if ( isset( $this->site->id ) )
				$t['site:id'] = $this->site->id;
		}

		// 
		if ( isset( $this->creator ) && isset( $this->creator->username ) ) {
			$t['creator'] = '@' . $this->creator->username;
			if ( isset( $this->creator->id ) )
				$t['creator:id'] = $this->creator->id;
		}

		return $t;
	}

	/**
	 * Build a single <meta> element from a name and value
	 *
	 * @since 1.0
	 * @param string $name name attribute value
	 * @param string|int $value value attribute value
	 * @param bool $xml include a trailing slash for XML. encode attributes for XHTML in PHP 5.4+
	 * @return meta element or empty string if name or value not valid
	 */
	public static function build_meta_element( $name, $value, $xml = false ) {
		if ( ! ( is_string( $name ) && $name && ( is_string( $value ) || ( is_int( $value ) && $value > 0 ) ) ) )
			return '';
		$flag = ENT_COMPAT;
		// allow PHP 5.4 overrides
		if ( $xml === true && defined( 'ENT_XHTML' ) )
			$flag = ENT_XHTML;
		else if ( defined( 'ENT_HTML5' ) )
			$flag = ENT_HTML5;
		return '<meta name="' . self::PREFIX . ':' . htmlspecialchars( $name, $flag ) . '" content="' . htmlspecialchars( $value, $flag ) . '"' . ( $xml === true ? ' />' : '>' );
	}

	/**
	 * Build a string of <meta> elements representing the object
	 *
	 * @since 1.0
	 * @param string $style markup style. "xml" adds a trailing slash to the meta void element
	 * @return string <meta> elements or empty string if minimum requirements not met
	 */
	private function generate_markup( $style = 'html' ) {
		$xml = false;
		if ( $style === 'xml' )
			$xml = true;
		$t = $this->toArray();
		if ( empty( $t ) )
			return '';
		$s = '';
		foreach ( $t as $name => $value ) {
			$s .= self::build_meta_element( $name, $value, $xml );
		}
		return $s;
	}

	/**
	 * Output object properties as HTML meta elements with name and value attributes
	 *
	 * @return string HTML <meta> elements or empty string if minimum requirements not met for card type
	 */
	public function asHTML() {
		return $this->generate_markup();
	}

	/**
	 * Output object properties as XML meta elements with name and value attributes
	 *
	 * @since 1.0
	 * @return string XML <meta> elements or empty string if minimum requirements not met for card type
	 */
	public function asXML() {
		return $this->generate_markup( 'xml' );
	}
}
?>
