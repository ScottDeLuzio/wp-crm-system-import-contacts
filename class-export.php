<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WPCRM_System_Export_Contacts extends WPCRM_System_Export{
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.1
	 */
	public $export_type = 'wpcrm-contact';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 2.1
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'contact_name' 		=> __( 'Contact Name', 'wp-crm-system-import-contacts' ),
			'contact_id' 		=> __( 'Contact ID', 'wp-crm-system-import-contacts' ),
			'prefix' 			=> __( 'Name Prefix', 'wp-crm-system-import-contacts' ),
			'first_name'		=> __( 'First Name', 'wp-crm-system-import-contacts' ),
			'last_name'			=> __( 'Last Name', 'wp-crm-system-import-contacts' ),
			'organization'		=> __( 'Organization Name', 'wp-crm-system-import-contacts' ),
			'organization_id'	=> __( 'Organization ID', 'wp-crm-system-import-contacts' ),
			'role'				=> __( 'Role', 'wp-crm-system-import-contacts' ),
			'street_1'			=> __( 'Address 1', 'wp-crm-system-import-contacts' ),
			'street_2'			=> __( 'Address 2', 'wp-crm-system-import-contacts' ),
			'city'				=> __( 'City', 'wp-crm-system-import-contacts' ),
			'state'				=> __( 'State', 'wp-crm-system-import-contacts' ),
			'postal_code'		=> __( 'Postal Code', 'wp-crm-system-import-contacts' ),
			'country'			=> __( 'Country', 'wp-crm-system-import-contacts' ),
			'phone'				=> __( 'Phone', 'wp-crm-system-import-contacts' ),
			'fax'				=> __( 'Fax', 'wp-crm-system-import-contacts' ),
			'mobile'			=> __( 'Mobile', 'wp-crm-system-import-contacts' ),
			'email'				=> __( 'Email', 'wp-crm-system-import-contacts' ),
			'url'				=> __( 'URL', 'wp-crm-system-import-contacts' ),
			'information'		=> __( 'Information', 'wp-crm-system-import-contacts' )
		);

		if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
			$field_count = get_option( '_wpcrm_system_custom_field_count' );
			if( $field_count ){
				$custom_fields = array();
				for( $field = 1; $field <= $field_count; $field++ ){
					// Make sure we want this field to be imported.
					$field_scope = get_option( '_wpcrm_custom_field_scope_' . $field );
					$can_export = $field_scope == $this->export_type ? true : false;
					if( $can_export ){
						$custom_fields[] = get_option( '_wpcrm_custom_field_name_' . $field );
					}
				}
				$cols = array_merge( $cols, $custom_fields );
			}
		}

		$cols = apply_filters( 'wpcrm_system_export_cols_' . $this->export_type, $cols );

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since 2.1
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		$get_ids = $this->get_cpt_post_ids();
		foreach ( $get_ids as $id ){
			$data[$id] = array(
				'contact_name' 		=> get_the_title( $id ),
				'contact_id' 		=> $id,
				'prefix' 			=> get_post_meta( $id, '_wpcrm_contact-name-prefix', true ),
				'first_name'		=> get_post_meta( $id, '_wpcrm_contact-first-name', true ),
				'last_name'			=> get_post_meta( $id, '_wpcrm_contact-last-name', true ),
				'organization'		=> get_the_title( get_post_meta( $id, '_wpcrm_contact-attach-to-organization', true ) ),
				'organization_id'	=> get_post_meta( $id, '_wpcrm_contact-attach-to-organization', true ),
				'role'				=> get_post_meta( $id, '_wpcrm_contact-role', true ),
				'street_1'			=> get_post_meta( $id, '_wpcrm_contact-address1', true ),
				'street_2'			=> get_post_meta( $id, '_wpcrm_contact-address2', true ),
				'city'				=> get_post_meta( $id, '_wpcrm_contact-city', true ),
				'state'				=> get_post_meta( $id, '_wpcrm_contact-state', true ),
				'postal_code'		=> get_post_meta( $id, '_wpcrm_contact-postal', true ),
				'country'			=> get_post_meta( $id, '_wpcrm_contact-country', true ),
				'phone'				=> get_post_meta( $id, '_wpcrm_contact-phone', true ),
				'fax'				=> get_post_meta( $id, '_wpcrm_contact-fax', true ),
				'mobile'			=> get_post_meta( $id, '_wpcrm_contact-mobile-phone', true ),
				'email'				=> get_post_meta( $id, '_wpcrm_contact-email', true ),
				'url'				=> get_post_meta( $id, '_wpcrm_contact-website', true ),
				'information'		=> esc_html( get_post_meta( $id, '_wpcrm_contact-additional', true ) )
			);
			if( defined( 'WPCRM_CUSTOM_FIELDS' ) ){
				$field_count 	= get_option( '_wpcrm_system_custom_field_count' );
				if( $field_count ){
					for( $field = 1; $field <= $field_count; $field++ ){
						// Make sure we want this field to be imported.
						$field_scope 	= get_option( '_wpcrm_custom_field_scope_' . $field );
						$field_type		= get_option( '_wpcrm_custom_field_type_' . $field );
						$can_export 	= $field_scope == $this->export_type ? true : false;
						if( $can_export ){
							$value 	= get_post_meta( $id, '_wpcrm_custom_field_id_' . $field, true );
							switch ( $field_type ) {
								case 'datepicker':
									$export = date( get_option( 'wpcrm_system_php_date_format' ), $value );
									break;
								case 'repeater-date':
									if ( is_array( $value ) ){
										foreach ( $value as $key => $v ){
											$values[$key] = date( get_option( 'wpcrm_system_php_date_format' ), $v );
										}
										$export = implode( ',', $values );
									} else {
										$export = '';
									}
									break;
								case 'repeater-file':
								case 'repeater-text':
								case 'repeater-textarea':
									if ( is_array( $value ) ){
										$export = implode( ',', $value );
									} else {
										$export = '';
									}
									break;
								default:
									$export = $value;
									break;
							}
							$data[$id][] = $export;
						}
					}
				}
			}
		}

		$data = apply_filters( 'wpcrm_system_export_get_data', $data );
		$data = apply_filters( 'wpcrm_system_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}