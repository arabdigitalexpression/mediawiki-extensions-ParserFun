<?php

/**
 * 'Parser Fun' adds a parser function '#parse' for parsing wikitext and introduces the
 * 'THIS:' prefix for page information related magic variables
 *
 * Documentation: https://www.mediawiki.org/wiki/Extension:Parser_Fun
 * Support:       https://www.mediawiki.org/wiki/Extension_talk:Parser_Fun
 * Source code:   https://phabricator.wikimedia.org/diffusion/EPFU/
 *
 * @license: ISC license
 * @author:  Daniel Werner < danweetz@web.de >
 *
 * @file ParserFun.php
 * @ingroup Parse
 */

// Ensure that the script cannot be executed outside of MediaWiki.
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

/**
 * Extension class of the 'Parser Fun' extension.
 * Handling the functionality around the 'THIS' magic word feature.
 */
class ExtParserFun {
	/**
	 * Version of the 'Parser Fun' extension.
	 *
	 * @since 0.1
	 *
	 * @var string
	 */
	const VERSION = '0.5.0';

	static function init( Parser &$parser ) {
		if( self::isEnabledFunction( 'this' ) ) {
			// only register function if not disabled by configuration
			$parser->setFunctionHook( 'this', array( 'ParserFunThis', 'pfObj_this' ), Parser::SFH_NO_HASH | Parser::SFH_OBJECT_ARGS );
		}
		return true;
	}

	/**
	 * returns whether a certain variable/parser function is active by the local wiki configuration.
	 *
	 * @since 0.2
	 *
	 * @param string $word
	 * @return bool
	 */
	static function isEnabledFunction( $word ) {
		global $egParserFunEnabledFunctions;
		return in_array( $word, $egParserFunEnabledFunctions );
	}

	/**
	 * Returns the extensions base installation directory.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	static function getDir() {
		static $dir = null;
		if( $dir === null ) {
			$dir = dirname( __FILE__ );
		}
		return $dir;
	}


	##################
	# Hooks Handling #
	##################

	static function onParserGetVariableValueSwitch( Parser &$parser, &$cache, &$magicWordId, &$ret, $frame = null ) {
		if( $frame === null ) {
			// unsupported MW version
			return true;
		}
		switch( $magicWordId ) {
			/** THIS **/
			case 'this':
				$ret = ParserFunThis::pfObj_this( $parser, $frame, null );
				break;

			/** CALLER **/
			case 'caller':
				$ret = ParserFunCaller::getCallerVar( $frame );
				break;
		}
		return true;
	}

	static function onMagicWordwgVariableIDs( &$variableIds ) {
		// only register variables if not disabled by configuration
		if( self::isEnabledFunction( 'this' ) ) {
			$variableIds[] = 'this';
		}
		if( self::isEnabledFunction( 'caller' ) ) {
			$variableIds[] = 'caller';
		}
		return true;
	}

}
