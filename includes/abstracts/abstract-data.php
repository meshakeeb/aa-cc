<?php
/**
 * This class is serving as the base for metadata service.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.0
 */

namespace AdvancedAds\Abstracts;

use AdvancedAds\Framework\Utilities\Arr;
use WP_Error;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Data.
 */
abstract class Data {

	/**
	 * ID for this object.
	 *
	 * @var int
	 */
	protected $id = 0;

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Core data changes for this object.
	 *
	 * @var array
	 */
	protected $changes = [];

	/**
	 * Set to _data on construct so we can track and reset data if needed.
	 *
	 * @var array
	 */
	protected $default_data = [];

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @var bool
	 */
	protected $object_read = false;

	/**
	 * This is the object type.
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * Contains a reference to the data store for this class.
	 *
	 * @var object
	 */
	protected $data_store;

	/**
	 * Stores temp data.
	 *
	 * @var array
	 */
	protected $temp_data = [];

	/**
	 * Change data to JSON format.
	 *
	 * @return string Data in JSON format.
	 */
	public function __toString() {
		return wp_json_encode( $this->get_data() );
	}

	/**
	 * Default constructor.
	 *
	 * @param int|object|array $read ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $read = 0 ) { // phpcs:ignore
		$this->default_data = $this->data;
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @return int
	 */
	public function save(): int {
		// Early bail!!
		if ( ! $this->data_store ) {
			return $this->get_id();
		}

		if ( $this->get_id() ) {
			$this->data_store->update( $this );
		} else {
			$this->data_store->create( $this );
		}

		return $this->get_id();
	}

	/**
	 * Delete an object, set the ID to 0, and return result.
	 *
	 * @param bool $force_delete Should the date be deleted permanently.
	 *
	 * @return bool
	 */
	public function delete( $force_delete = false ): bool {
		if ( $this->data_store ) {
			$this->data_store->delete( $this, [ 'force_delete' => $force_delete ] );
			$this->set_id( 0 );
			return true;
		}

		return false;
	}

	/**
	 * Merge changes with data and clear.
	 *
	 * @return void
	 */
	public function apply_changes(): void {
		$this->data    = array_replace_recursive( $this->data, $this->changes );
		$this->changes = [];
	}

	/* Getter ------------------- */

	/**
	 * Returns the unique ID for this object.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get the data store.
	 *
	 * @return object
	 */
	public function get_data_store() {
		return $this->data_store;
	}

	/**
	 * Get object read property.
	 *
	 * @return bool
	 */
	public function get_object_read(): bool {
		return (bool) $this->object_read;
	}

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data(): array {
		return array_merge(
			[ 'id' => $this->get_id() ],
			$this->data
		);
	}

	/**
	 * Returns array of expected data keys for this object.
	 *
	 * @return array
	 */
	public function get_data_keys() {
		$keys = array_keys( $this->data );
		$keys = array_diff( $keys, [ 'title', 'content', 'status' ] );

		return array_merge( $keys );
	}

	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes(): array {
		return $this->changes;
	}

	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @return string
	 */
	protected function get_hook_prefix(): string {
		return 'advanced-ads-' . $this->object_type . '-get-';
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * Gets the value from either current pending changes, or the data itself.
	 * Context controls what happens to the value before it's returned.
	 *
	 * @param string $prop Name of prop to get.
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return mixed
	 */
	public function get_prop( $prop, $context = 'view' ) {
		$value = null;

		if ( Arr::has( $this->temp_data, $prop ) ) {
			$value = Arr::get( $this->temp_data, $prop );
		} elseif ( Arr::has( $this->data, $prop ) ) {
			$value = Arr::has( $this->changes, $prop )
				? Arr::get( $this->changes, $prop )
				: Arr::get( $this->data, $prop );
		}

		if ( 'view' === $context ) {
			$value = apply_filters( $this->get_hook_prefix() . $prop, $value, $this );
		}

		return $value;
	}

	/* Setter ------------------- */

	/**
	 * Set ID.
	 *
	 * @param int $id ID.
	 *
	 * @return void
	 */
	public function set_id( $id ): void {
		$this->id = absint( $id );
	}

	/**
	 * Set object read property.
	 *
	 * @param bool $read Should read?.
	 *
	 * @return void
	 */
	public function set_object_read( $read = true ): void {
		$this->object_read = boolval( $read );
	}

	/**
	 * Set all props to default values.
	 *
	 * @return void
	 */
	public function set_defaults(): void {
		$this->data    = $this->default_data;
		$this->changes = [];
		$this->set_object_read( false );
	}

	/**
	 * Set a collection of props in one go, collect any errors, and return the result.
	 * Only sets using public methods.
	 *
	 * @param array $props Key value pairs to set. Key is the prop and should map to a setter function name.
	 *
	 * @return bool|WP_Error
	 */
	public function set_props( $props ) {
		$errors = false;

		foreach ( $props as $prop => $value ) {
			try {
				/**
				 * Checks if the prop being set is allowed, and the value is not null.
				 */
				if ( is_null( $value ) ) {
					continue;
				}

				$setter = 'set_' . str_replace( '-', '_', $prop );

				if ( is_callable( [ $this, $setter ] ) ) {
					$this->{$setter}( $value );
				} else {
					$this->set_prop( $prop, $value );
				}
			} catch ( Exception $e ) {
				if ( ! $errors ) {
					$errors = new WP_Error();
				}
				$errors->add( $e->getErrorCode(), $e->getMessage(), [ 'property_name' => $prop ] );
			}
		}

		return $errors && count( $errors->get_error_codes() ) ? $errors : true;
	}

	/**
	 * Sets a prop for a setter method.
	 *
	 * @param string $prop  Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 *
	 * @return void
	 */
	public function set_prop( $prop, $value ): void {
		if ( array_key_exists( $prop, $this->data ) && true === $this->object_read ) {
			if ( $value !== $this->data[ $prop ] ) {
				$this->changes[ $prop ] = $value;
			}
		} else {
			$this->data[ $prop ] = $value;
		}
	}

	/**
	 * Sets a prop temporary.
	 *
	 * @param string $prop  Name of prop to set.
	 * @param mixed  $value Value of the prop.
	 *
	 * @return void
	 */
	public function set_prop_temp( $prop, $value ): void {
		$this->temp_data[ $prop ] = $value;
	}

	/**
	 * Unset a prop.
	 *
	 * @param string $prop Name of prop to unset.
	 *
	 * @return void
	 */
	public function unset_prop( $prop ): void {
		if ( array_key_exists( $prop, $this->changes ) ) {
			unset( $this->changes );
			return;
		}

		$this->data[ $prop ] = null;
	}
}
