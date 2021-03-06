<?php

namespace SMW;

use SMWPropertyValue;
use SMWPrintRequest;

/**
 * This class handles Api related request parameter formatting
 *
 * @licence GNU GPL v2+
 * @since 1.9
 *
 * @author mwjames
 */
final class ApiRequestParameterFormatter {

	/** @var array */
	protected $requestParameters = array();

	/** @var ObjectDictionary */
	protected $results = null;

	/**
	 * @since 1.9
	 *
	 * @param array $requestParameters
	 */
	public function __construct( array $requestParameters ) {
		$this->requestParameters = $requestParameters;
	}

	/**
	 * Return formatted request parameters for the AskApi
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function getAskApiParameters() {

		if ( $this->results === null ) {
			$this->results = isset( $this->requestParameters['query'] ) ? preg_split( "/(?<=[^\|])\|(?=[^\|])/", $this->requestParameters['query'] ) : array();
		}

		return $this->results;
	}

	/**
	 * Return formatted request parameters AskArgsApi
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function getAskArgsApiParameter( $key ) {

		if ( $this->results === null ) {
			$this->results = $this->formatAskArgs();
		}

		return $this->results->get( $key );
	}

	/**
	 * Return formatted request parameters
	 *
	 * @since 1.9
	 *
	 * @return ObjectDictionary
	 */
	protected function formatAskArgs() {

		$result = new SimpleDictionary();

		// Set defaults
		$result->set( 'conditions', array() );
		$result->set( 'printouts' , array() );
		$result->set( 'parameters', array() );

		if ( isset( $this->requestParameters['parameters'] ) && is_array( $this->requestParameters['parameters'] ) ) {
			$result->set( 'parameters', $this->formatParameters() );
		}

		if ( isset( $this->requestParameters['conditions'] ) && is_array( $this->requestParameters['conditions'] ) ) {
			$result->set( 'conditions', implode( ' ', array_map( 'self::formatConditions', $this->requestParameters['conditions'] ) ) );
		}

		if ( isset( $this->requestParameters['printouts'] ) && is_array( $this->requestParameters['printouts'] ) ) {
			$result->set( 'printouts', array_map( 'self::formatPrintouts', $this->requestParameters['printouts'] ) );
		}

		return $result;
	}

	/**
	 * Format parameters
	 *
	 * @since  1.9
	 *
	 * @return string
	 */
	protected function formatParameters() {

		$parameters = array();

		foreach ( $this->requestParameters['parameters'] as $param ) {
			$parts = explode( '=', $param, 2 );

			if ( count( $parts ) == 2 ) {
				$parameters[$parts[0]] = $parts[1];
			}
		}

		return $parameters;
	}

	/**
	 * Format conditions
	 *
	 * @since 1.9
	 *
	 * @param string $condition
	 *
	 * @return string
	 */
	protected function formatConditions( $condition ) {
		return "[[$condition]]";
	}

	/**
	 * Format printout and returns a SMWPrintRequest object
	 *
	 * @since 1.9
	 *
	 * @param string $printout
	 *
	 * @return SMWPrintRequest
	 */
	protected function formatPrintouts( $printout ) {
		return new SMWPrintRequest(
			SMWPrintRequest::PRINT_PROP,
			$printout,
			SMWPropertyValue::makeUserProperty( $printout )
		);
	}

}
