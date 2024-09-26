<?php
// TODO: don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function custom_plugin_styles() {
	if (is_plugin_active('dokan-custom-fields/dokan-custom-fields.php')) {
		wp_enqueue_style('custom-plugin-styles', plugins_url('/css/styles.css', __FILE__));
	}
}
add_action('wp_enqueue_scripts', 'custom_plugin_styles');

add_action( 'dokan_seller_registration_field_after', 'dokan_custom_registration_fields' );
function dokan_custom_registration_fields() {
	$countries = WC()->countries->get_countries();
	$states = WC()->countries->get_states();

	?>
	<p class="form-row form-group form-row-wide">
		<label for="country"><?php esc_html_e( 'Country', 'dokan' ); ?> <span class="required">*</span></label>
		<select name="country" id="country" class="input-text form-control" required="required">
			<option value=""><?php esc_html_e( 'Select a country...', 'dokan' ); ?></option>
			<?php foreach ( $countries as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="state"><?php esc_html_e( 'State', 'dokan' ); ?> <span class="required">*</span></label>
		<select name="state" id="state" class="input-text form-control" required="required">
			<option value=""><?php esc_html_e( 'Select a state...', 'dokan' ); ?></option>
		</select>
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="street_1"><?php esc_html_e( 'Street Address 1', 'dokan' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="street_1" id="street_1" value="" required="required" />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="street_2"><?php esc_html_e( 'Street Address 2', 'dokan' ); ?></label>
		<input type="text" class="input-text form-control" name="street_2" id="street_2" value="" />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="city"><?php esc_html_e( 'City', 'dokan' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="city" id="city" value="" required="required" />
	</p>

	<script type="text/javascript">
        jQuery(document).ready(function($) {
            var states = <?php echo json_encode($states); ?>;

            $('#country').change(function() {
                var country = $(this).val();
                var stateSelect = $('#state');
                stateSelect.empty().append('<option value=""><?php esc_html_e( 'Select a state...', 'dokan' ); ?></option>');

                if (states[country]) {
                    $.each(states[country], function(index, value) {
                        stateSelect.append('<option value="' + index + '">' + value + '</option>');
                    });
                }
            });
        });
	</script>
	<?php
}

add_filter( 'woocommerce_registration_errors', 'dokan_custom_registration_validation', 10, 3 );
function dokan_custom_registration_validation( $errors, $username, $email ) {
	if ( empty( $_POST['country'] ) ) {
		$errors->add( 'country_error', __( 'Please select your Country.', 'dokan' ) );
	}
	if ( empty( $_POST['state'] ) ) {
		$errors->add( 'state_error', __( 'Please select your State.', 'dokan' ) );
	}
	if ( empty( $_POST['street_1'] ) ) {
		$errors->add( 'street_1_error', __( 'Please enter your Street Address 1.', 'dokan' ) );
	}
	if ( empty( $_POST['city'] ) ) {
		$errors->add( 'city_error', __( 'Please enter your City.', 'dokan' ) );
	}
	return $errors;
}

add_action( 'woocommerce_created_customer', 'dokan_save_custom_registration_fields' );
function dokan_save_custom_registration_fields( $customer_id ) {
	if ( isset( $_POST['country'] ) ) {
		update_user_meta( $customer_id, 'country', sanitize_text_field( $_POST['country'] ) );
	}
	if ( isset( $_POST['state'] ) ) {
		update_user_meta( $customer_id, 'state', sanitize_text_field( $_POST['state'] ) );
	}
	if ( isset( $_POST['street_1'] ) ) {
		update_user_meta( $customer_id, 'street_1', sanitize_text_field( $_POST['street_1'] ) );
	}
	if ( isset( $_POST['street_2'] ) ) {
		update_user_meta( $customer_id, 'street_2', sanitize_text_field( $_POST['street_2'] ) );
	}
	if ( isset( $_POST['city'] ) ) {
		update_user_meta( $customer_id, 'city', sanitize_text_field( $_POST['city'] ) );
	}
}

// TODO: Exibir campos personalizados no perfil do vendedor
add_action('dokan_seller_meta_fields', 'dokan_custom_profile_fields');
function dokan_custom_profile_fields( $user ) {
	$country = get_user_meta( $user->ID, 'country', true );
	$state = get_user_meta( $user->ID, 'state', true );
	$street_1 = get_user_meta( $user->ID, 'street_1', true );
	$street_2 = get_user_meta( $user->ID, 'street_2', true );
	$city = get_user_meta( $user->ID, 'city', true );

	$countries = WC()->countries->get_countries();
	$states = WC()->countries->get_states();

	?>
	<tr>
		<th><?php esc_html_e( 'Country', 'dokan' ); ?></th>
		<td>
			<select name="country" class="regular-text">
				<?php foreach ( $countries as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $country, $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'State', 'dokan' ); ?></th>
		<td>
			<select name="state" class="regular-text">
				<option value=""><?php esc_html_e( 'Select a state...', 'dokan' ); ?></option>
				<?php if ( $country && isset( $states[$country] ) ) : ?>
					<?php foreach ( $states[$country] as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $state, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Street Address 1', 'dokan' ); ?></th>
		<td><input type="text" class="regular-text" name="street_1" value="<?php echo esc_attr( $street_1 ); ?>" /></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Street Address 2', 'dokan' ); ?></th>
		<td><input type="text" class="regular-text" name="street_2" value="<?php echo esc_attr( $street_2 ); ?>" /></td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'City', 'dokan' ); ?></th>
		<td><input type="text" class="regular-text" name="city" value="<?php echo esc_attr( $city ); ?>" /></td>
	</tr>
	<?php

	// TODO: Exibir campos personalizados no perfil do vendedor
	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = isset($field['options']) ? $field['options'] : [];
			$field_value = get_user_meta($user->ID, $field_name, true);

			switch ($field_type) {
				case 'text':
					?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<input type="text" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" class="regular-text" />
						</td>
					</tr>
					<?php
					break;

				case 'select':
					?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<select name="<?php echo $field_name; ?>" class="regular-text">
								<option value=""><?php esc_html_e('Selecione uma opção', 'dokan'); ?></option>
								<?php foreach ($field_options as $option): ?>
									<option value="<?php echo esc_attr($option); ?>" <?php selected($field_value, $option); ?>><?php echo esc_html($option); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<?php
					break;

				case 'checkbox':
					?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<?php foreach ($field_options as $option): ?>
								<label>
									<input type="checkbox" name="<?php echo $field_name; ?>[]" value="<?php echo esc_attr($option); ?>" <?php checked(in_array($option, (array)$field_value)); ?> />
									<?php echo esc_html($option); ?>
								</label><br>
							<?php endforeach; ?>
						</td>
					</tr>
					<?php
					break;

				case 'radio':
					?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<?php foreach ($field_options as $option): ?>
								<label>
									<input type="radio" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option); ?>" <?php checked($field_value, $option); ?> />
									<?php echo esc_html($option); ?>
								</label><br>
							<?php endforeach; ?>
						</td>
					</tr>
					<?php
					break;

				default:
					// Outros tipos de campo podem ser tratados aqui
					break;
			}
		}
	}

}

// TODO: Salvar dados dos campos personalizados no perfil do usuário
function dokan_save_custom_profile_fields( $user_id ) {

	// Verifica se o usuário tem permissão para editar
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	if ( current_user_can( 'edit_user', $user_id ) ) {
		update_user_meta( $user_id, 'country', sanitize_text_field( $_POST['country'] ) );
		update_user_meta( $user_id, 'state', sanitize_text_field( $_POST['state'] ) );
		update_user_meta( $user_id, 'street_1', sanitize_text_field( $_POST['street_1'] ) );
		update_user_meta( $user_id, 'street_2', sanitize_text_field( $_POST['street_2'] ) );
		update_user_meta( $user_id, 'city', sanitize_text_field( $_POST['city'] ) );
	}

	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = $field['name'];
			$field_type = $field['type'];
			$field_value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

			switch ($field_type) {
				case 'text':
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;

				case 'select':
				case 'radio':
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;

				case 'checkbox':
					if (is_array($field_value)) {
						$sanitized = array_map('sanitize_text_field', $field_value);
						update_user_meta($user_id, $field_name, $sanitized);
					} else {
						update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					}
					break;

				default:
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;
			}
		}
	}

}
add_action('personal_options_update', 'dokan_save_custom_profile_fields');
add_action('edit_user_profile_update', 'dokan_save_custom_profile_fields');



