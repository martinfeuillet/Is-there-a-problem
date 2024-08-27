<?php // phpcs:ignore

/**
 * The helper function class.
 */
class ItapHelperFunction {


	/**
	 * Count the number of words in a string.
	 *
	 * @param string $string The string to count the words of.
	 * @param int    $mode  The mode to return the words in.
	 * @return array|int
	 */
	public function utf8_word_count( string $string, $mode = 0 ) {
		static $it = null;

		if ( is_null( $it ) ) {
			$it = IntlBreakIterator::createWordInstance( ini_get( 'intl.default_locale' ) );
		}

		$l = 0;
		$it->setText( $string );
		$ret = $mode == 0 ? 0 : array();
		if ( IntlBreakIterator::DONE != ( $u = $it->first() ) ) {
			do {
				if ( IntlBreakIterator::WORD_NONE != $it->getRuleStatus() ) {
					$mode == 0 ? ++$ret : $ret[] = substr( $string, $l, $u - $l );
				}
				$l = $u;
			} while ( IntlBreakIterator::DONE != ( $u = $it->next() ) );
		}

		return $ret;
	}

	/**
	 * Transforms a string into a slug.
	 *
	 * @param string $text The text to transform.
	 * @return string
	 */
	public function slugify( string $text ): string {
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '~[^\pL\d]+~u', '-', $text );
		setlocale( LC_ALL, 'en_US.utf8' );
		$text = iconv( 'utf-8', 'us-ascii//TRANSLIT', $text );
		$text = preg_replace( '~[^-\w]+~', '', $text );
		$text = trim( $text, '-' );
		$text = preg_replace( '~-+~', '-', $text );
		$text = strtolower( $text );
		if ( empty( $text ) ) {
			return 'n-a';
		}
		return $text;
	}

	/**
	 * Determine if a URL is internal (belongs to this WordPress installation) or external.
	 *
	 * @param string $url The URL to check.
	 * @return bool True if the URL is internal, false otherwise.
	 */
	public function is_internal_url( $url ) {
		$url_host = parse_url( $url, PHP_URL_HOST );

		$site_host = parse_url( get_site_url(), PHP_URL_HOST );

		return $url_host == $site_host;
	}

	/**
	 * Helper function to get all parent categories.
	 *
	 * @param array $array The array to push the parent categories to.
	 * @param int   $actual_id_cat the actual category id.
	 * @return array
	 */
	public function push_id_parent_category( array $array, int $actual_id_cat ): array {
		$parent = get_term( $actual_id_cat, 'product_cat' )->parent;
		if ( 0 !== $parent ) {
			$array[] = $parent;
			$array   = $this->push_id_parent_category( $array, $parent );
		}
		return $array;
	}
}
