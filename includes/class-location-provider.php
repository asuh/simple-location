<?php

abstract class Location_Provider extends Sloc_Provider {

	protected $api;
	protected $user;
	protected $latitude;
	protected $longitude;
	protected $accuracy;
	protected $altitude;
	protected $heading;
	protected $speed;

	/**
	 * Constructor for the Abstract Class
	 *
	 * The default version of this just sets the parameters
	 *
	 * @param string $key API Key if Needed
	 */
	public function __construct( $args = array() ) {
		$defaults   = array(
			'api'  => null,
			'user' => '',
		);
		$defaults   = apply_filters( 'sloc_location_provider_defaults', $defaults );
		$r          = wp_parse_args( $args, $defaults );
		$this->user = $r['user'];
		$this->api  = $r['api'];
	}

	/**
	 * Get Coordinates
	 *
	 * @return array|boolean Array with Latitude and Longitude false if null
	 */
	public function get() {
		$return              = array();
		$return['latitude']  = $this->latitude;
		$return['longitude'] = $this->longitude;
		$return['altitude']  = $this->altitude;
		$return['accuracy']  = $this->accuracy;
		$return['altitude']  = $this->altitude;
		$return['heading']   = $this->heading;
		$return['speed']     = $this->speed;
		$return              = array_filter( $return );
		if ( ! empty( $return ) ) {
			return $return;
		}
		return false;
	}

	public function set_user( $user ) {
		$this->user = $user;
	}

	abstract public function retrieve();
}
