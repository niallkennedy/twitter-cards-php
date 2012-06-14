<?php
/**
 * Describe a page in Twitter Card markup
 *
 * @since 1.0
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @todo add player
 */
class Twitter_Card {
	/**
	 * Library version
	 *
	 * @since 1.0
	 * @var string
	 */
	const VERSION = '1.0';

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
	public static $allowed_card_types = array( 'summary', 'photo' /*, 'player' */ );

	/**
	 * Create a new Twitter Card object, optionally overriding the default card type of "summary"
	 *
	 * @since 1.0
	 * @param string $card_type The card type. one of "summary", "photo"
	 */
	public function __construct( $card_type = '' ) {
		if ( is_string( $card_type ) && in_array( $card_type, self::$allowed_card_types, true ) )
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
		if ( ! is_string( $username ) || ! $username )
			return false;
		return true;
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
	 * Canonical URL. Basic check for string before setting
	 *
	 * @since 1.0
	 * @param string $url canonical URL
	 * @return Twitter_Card for chaining
	 */
	public function setURL( $url ) {
		if ( is_string( $url ) && $url )
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
			if ( $title )
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
	 * @param string $image_url URL of an image representing content
	 * @param int $width width of the specified image in pixels
	 * @param int $height height of the specified image in pixels
	 * @return Twitter_Card for chaining
	 */
	public function setImage( $image_url, $width = 0, $height = 0 ) {
		if ( ! ( is_string( $image_url ) && $image_url ) )
			return $this;
		$image = array( 'url' => $image_url );
		if ( is_int( $width ) && is_int( $height ) && $width !== 0 && $height !== 0 ) {
			// prevent self-inflicted pain

			// minimum dimensions for all card types
			if ( $width < 60 || $height < 60 )
				return $this;

			// minimum dimensions for photo cards
			if ( isset( $this->card ) && $this->card === 'photo' && ( $width < 280 || $height < 150 ) )
				return $this;

			$image['width'] = $width;
			$image['height'] = $height;
		}
		$this->image = $image;
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
			return array();
		$username = ltrim( trim( $username ), '@' );
		if ( ! ( $username && self::is_valid_username( $username ) ) )
			return array();
		$user = array( 'username' => $username );
		if ( $id && self::is_valid_id( $id ) )
			$user['id'] = (string) $id;
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
		if ( is_array( $user ) && array_key_exists( 'username', $user ) )
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
		if ( is_array( $user ) && array_key_exists( 'username', $user ) )
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
		if ( ! ( isset( $this->url ) && isset( $this->title ) && isset( $this->description ) ) )
			return false;
		if ( $this->card === 'photo' && ! isset( $this->image ) )
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
			'title' => $this->title,
			'description' => $this->description
		);

		// add an image
		if ( isset( $this->image ) && is_array( $this->image ) && array_key_exists( 'url', $this->image ) ) {
			$t['image'] = $this->image['url'];
			if ( array_key_exists( 'width', $this->image ) && array_key_exists( 'height', $image ) ) {
				$t['image:width'] = $this->image['width'];
				$t['image:height'] = $this->image['height'];
			}
		}

		// identify the site
		if ( isset( $this->site ) && is_array( $this->site ) && array_key_exists( 'username', $this->site ) ) {
			$t['site'] = '@' . $this->site['username'];
			if ( array_key_exists( 'id', $this->site ) )
				$t['site:id'] = $this->site['id'];
		}

		// 
		if ( isset( $this->creator ) && is_array( $this->creator ) && array_key_exists( 'username', $this->creator ) ) {
			$t['creator'] = '@' . $this->creator['username'];
			if ( array_key_exists( 'id', $this->creator ) )
				$t['site:id'] = $this->creator['id'];
		}

		return $t;
	}

	/**
	 * Build a single <meta> element from a name and value
	 *
	 * @param string $name name attribute value
	 * @param string|int $value value attribute value
	 * @param bool $xml include a trailing slash for XML. encode attributes for XHTML in PHP 5.4+
	 * @return meta element or empty string if name or value not valid
	 */
	public static function build_meta_element( $name, $value, $xml = false ) {
		if ( ! ( is_string( $name ) && $name && ( ( is_string( $value ) && $value ) || ( is_int( $value ) && $value > 0 ) ) ) )
			return '';
		$flag = ENT_COMPAT;
		// allow PHP 5.4 overrides
		if ( $xml === true && defined( 'ENT_XHTML' ) )
			$flag = ENT_XHTML;
		else if ( defined( 'ENT_HTML5' ) )
			$flag = ENT_HTML5;
		return '<meta name="' . self::PREFIX . ':' . htmlspecialchars( $name, $flag ) . '" value="' . htmlspecialchars( $value, $flag ) . '"' . ( $xml === true ? ' />' : '>' );
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