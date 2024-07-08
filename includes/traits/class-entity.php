<?php
/**
 * Traits Entity.
 *
 * @package AdvancedAds
 * @author  Advanced Ads <info@wpadvancedads.com>
 * @since   1.48.2
 */

namespace AdvancedAds\Traits;

use AdvancedAds\Abstracts\Group;
use AdvancedAds\Abstracts\Placement;

defined( 'ABSPATH' ) || exit;

/**
 * Traits Entity.
 */
trait Entity {

	/**
	 * Entity parent object.
	 *
	 * @var Group|Placement|null
	 */
	private $parent = null;

	/* Getter ------------------- */

	/**
	 * Get the parent entity name.
	 *
	 * @return int
	 */
	public function get_parent_entity_name(): string {
		if ( $this->is_group() ) {
			return _x( 'Ad Group', 'ad group singular name', 'advanced-ads' );
		}

		if ( $this->is_placement() ) {
			return _x( 'Placement', 'ad placement singular name', 'advanced-ads' );
		}

		return __( 'Unknown', 'advanced-ads' );
	}

	/**
	 * Get title.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_title( $context = 'view' ): string {
		return $this->get_prop( 'title', $context );
	}

	/**
	 * Get slug.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_slug( $context = 'view' ): string {
		return $this->get_prop( 'slug', $context );
	}

	/**
	 * Get content.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_content( $context = 'view' ): string {
		return $this->get_prop( 'content', $context );
	}

	/**
	 * Get status.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_status( $context = 'view' ): string {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Get type.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_type( $context = 'view' ): string {
		return $this->get_prop( 'type', $context );
	}

	/**
	 * Get parent object.
	 *
	 * @return Group|Placement|null
	 */
	public function get_parent() {
		return $this->parent;
	}

	/* Setter ------------------- */

	/**
	 * Set title.
	 *
	 * @param string $title Entity title.
	 *
	 * @return void
	 */
	public function set_title( $title ): void {
		$this->set_prop( 'title', $title );
	}

	/**
	 * Set slug.
	 *
	 * @param string $slug Entity slug.
	 *
	 * @return void
	 */
	public function set_slug( $slug ): void {
		$this->set_prop( 'slug', $slug );
	}

	/**
	 * Set content.
	 *
	 * @param string $content Entity content.
	 *
	 * @return void
	 */
	public function set_content( $content ): void {
		$this->set_prop( 'content', $content );
	}

	/**
	 * Set status.
	 *
	 * @param string $status Entity status.
	 *
	 * @return void
	 */
	public function set_status( $status ): void {
		$this->set_prop( 'status', $status );
	}

	/**
	 * Set type.
	 *
	 * @param string $type Entity type.
	 *
	 * @return void
	 */
	public function set_type( $type ): void {
		$this->set_prop( 'type', $type );
	}

	/**
	 * Set parent object.
	 *
	 * @param Group|Placement|null $item Parent object.
	 *
	 * @return void
	 */
	public function set_parent( $item ): void {
		$this->parent = $item;
	}

	/* Conditional ------------------- */

	/**
	 * Check the status.
	 *
	 * @param string|array $status Status to check.
	 *
	 * @return bool
	 */
	public function is_status( $status ): bool {
		return $this->get_status() === $status || ( is_array( $status ) && in_array( $this->get_status(), $status, true ) );
	}

	/**
	 * Check the type.
	 *
	 * @param string|string[] $type Type to check.
	 *
	 * @return bool
	 */
	public function is_type( $type ): bool {
		return $this->get_type() === $type || ( is_array( $type ) && in_array( $this->get_type(), $type, true ) );
	}

	/**
	 * Check if the entity is a group.
	 *
	 * @return bool
	 */
	public function is_group(): bool {
		return $this->parent && $this->parent instanceof Group;
	}

	/**
	 * Check if the entity is a placement.
	 *
	 * @return bool
	 */
	public function is_placement(): bool {
		return $this->parent && $this->parent instanceof Placement;
	}

	/* Additional Methods ----------- */

	/**
	 * Outputs the entity.
	 *
	 * @return string The output of the entity.
	 */
	public function output(): string {
		do_action( "advanced-ads-{$this->object_type}-before-output", $this );

		$output = $this->prepare_output();

		return apply_filters(
			"advanced-ads-{$this->object_type}-output",
			$output,
			$this
		);
	}
}
