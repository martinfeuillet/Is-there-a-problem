<?php

class Itap_Helper_Function
{

    /**
     * @param string $string
     * @param int $mode
     * @return array|int
     */
    public static function utf8_word_count( string $string , $mode = 0 ) {
        static $it = NULL;

        if ( is_null( $it ) ) {
            $it = IntlBreakIterator::createWordInstance( ini_get( 'intl.default_locale' ) );
        }

        $l = 0;
        $it->setText( $string );
        $ret = $mode == 0 ? 0 : array();
        if ( IntlBreakIterator::DONE != ( $u = $it->first() ) ) {
            do {
                if ( IntlBreakIterator::WORD_NONE != $it->getRuleStatus() ) {
                    $mode == 0 ? ++$ret : $ret[] = substr( $string , $l , $u - $l );
                }
                $l = $u;
            } while ( IntlBreakIterator::DONE != ( $u = $it->next() ) );
        }

        return $ret;
    }
}