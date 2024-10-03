<?php
// TODO: don't call the file directly
if (! defined('ABSPATH')) {
	exit;
}

/**
 * @return void
 */
function custom_plugin_styles(): void
{
	if (is_plugin_active('dokan-custom-fields/dokan-custom-fields.php')) {
		wp_enqueue_style('custom-plugin-styles', plugins_url('/css/styles.css', __FILE__));
	}
}
add_action('wp_enqueue_scripts', 'custom_plugin_styles');

add_action('dokan_seller_registration_field_after', 'dokan_custom_registration_fields');
/**
 * @return void
 */
function dokan_custom_registration_fields(): void
{
	$countries = WC()->countries->get_countries();
	$states = WC()->countries->get_states();

?>
	<p class="form-row form-group form-row-wide">
		<label for="country"><?php esc_html_e('País', 'dokan'); ?> <span class="required">*</span></label>
		<select name="country" id="country" class="input-text form-control" required="required">
			<option value=""><?php esc_html_e('Select a country...', 'dokan'); ?></option>
			<?php foreach ($countries as $key => $value) : ?>
				<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($value); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="state"><?php esc_html_e('Província', 'dokan'); ?> <span class="required">*</span></label>
		<select name="state" id="state" class="input-text form-control" required="required">
			<option value=""><?php esc_html_e('Seleccione Província...', 'dokan'); ?></option>
		</select>
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="street_1"><?php esc_html_e('Endereço 1', 'dokan'); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="street_1" id="street_1" value="" required="required" />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="street_2"><?php esc_html_e('Endereço 2', 'dokan'); ?></label>
		<input type="text" class="input-text form-control" name="street_2" id="street_2" value="" />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="city"><?php esc_html_e('Cidade', 'dokan'); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="city" id="city" value="" required="required" />
	</p>

	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var states = <?php echo json_encode($states); ?>;

			$('#country').change(function() {
				var country = $(this).val();
				var stateSelect = $('#state');
				stateSelect.empty().append('<option value=""><?php esc_html_e('Select a state...', 'dokan'); ?></option>');

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

add_filter('woocommerce_registration_errors', 'dokan_custom_registration_validation', 10, 3);
/**
 * @param $errors
 * @param $username
 * @param $email
 *
 * @return mixed
 */
function dokan_custom_registration_validation($errors, $username, $email)
{
	if (empty($_POST['country'])) {
		$errors->add('country_error', __('Please select your Country.', 'dokan'));
	}
	if (empty($_POST['state'])) {
		$errors->add('state_error', __('Please select your State.', 'dokan'));
	}
	if (empty($_POST['street_1'])) {
		$errors->add('street_1_error', __('Please enter your Street Address 1.', 'dokan'));
	}
	if (empty($_POST['city'])) {
		$errors->add('city_error', __('Please enter your City.', 'dokan'));
	}
	return $errors;
}

add_action('woocommerce_created_customer', 'dokan_save_custom_registration_fields');
/**
 * @param $customer_id
 *
 * @return void
 */
function dokan_save_custom_registration_fields($customer_id): void
{
	if (isset($_POST['country'])) {
		update_user_meta($customer_id, 'country', sanitize_text_field($_POST['country']));
	}
	if (isset($_POST['state'])) {
		update_user_meta($customer_id, 'state', sanitize_text_field($_POST['state']));
	}
	if (isset($_POST['street_1'])) {
		update_user_meta($customer_id, 'street_1', sanitize_text_field($_POST['street_1']));
	}
	if (isset($_POST['street_2'])) {
		update_user_meta($customer_id, 'street_2', sanitize_text_field($_POST['street_2']));
	}
	if (isset($_POST['city'])) {
		update_user_meta($customer_id, 'city', sanitize_text_field($_POST['city']));
	}
}

// TODO: Exibir campos personalizados no perfil do vendedor
add_action('dokan_seller_meta_fields', 'dokan_custom_profile_fields');
/**
 * @param $user
 *
 * @return void
 */
function dokan_custom_profile_fields($user): void
{
	$country = get_user_meta($user->ID, 'country', true);
	$state = get_user_meta($user->ID, 'state', true);
	$street_1 = get_user_meta($user->ID, 'street_1', true);
	$street_2 = get_user_meta($user->ID, 'street_2', true);
	$city = get_user_meta($user->ID, 'city', true);

	$countries = WC()->countries->get_countries();
	$states = WC()->countries->get_states();

?>
	<tr>
		<th><?php esc_html_e('Country', 'dokan'); ?></th>
		<td>
			<select name="country" class="regular-text">
				<?php foreach ($countries as $key => $value) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($country, $key); ?>><?php echo esc_html($value); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e('State', 'dokan'); ?></th>
		<td>
			<select name="state" class="regular-text">
				<option value=""><?php esc_html_e('Select a state...', 'dokan'); ?></option>
				<?php if ($country && isset($states[$country])) : ?>
					<?php foreach ($states[$country] as $key => $value) : ?>
						<option value="<?php echo esc_attr($key); ?>" <?php selected($state, $key); ?>><?php echo esc_html($value); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e('Street Address 1', 'dokan'); ?></th>
		<td><input type="text" class="regular-text" name="street_1" value="<?php echo esc_attr($street_1); ?>" /></td>
	</tr>
	<tr>
		<th><?php esc_html_e('Street Address 2', 'dokan'); ?></th>
		<td><input type="text" class="regular-text" name="street_2" value="<?php echo esc_attr($street_2); ?>" /></td>
	</tr>
	<tr>
		<th><?php esc_html_e('City', 'dokan'); ?></th>
		<td><input type="text" class="regular-text" name="city" value="<?php echo esc_attr($city); ?>" /></td>
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
					// TODO: Outros tipos de campo podem ser tratados aqui
					break;
			}
		}
	}
}

// TODO: Salvar dados dos campos personalizados no perfil do usuário
/**
 * @param $user_id
 *
 * @return false|void
 */
function dokan_save_custom_profile_fields($user_id)
{

	// TODO: Verifica se o usuário tem permissão para editar
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	if (current_user_can('edit_user', $user_id)) {
		update_user_meta($user_id, 'country', sanitize_text_field($_POST['country']));
		update_user_meta($user_id, 'state', sanitize_text_field($_POST['state']));
		update_user_meta($user_id, 'street_1', sanitize_text_field($_POST['street_1']));
		update_user_meta($user_id, 'street_2', sanitize_text_field($_POST['street_2']));
		update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
	}

	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = $field['name'];
			$field_type = $field['type'];
			$field_value = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

			switch ($field_type) {
				case 'text':
				case 'email':
				case 'number':
				case 'date':
					// Para text, email, number, e date, sanitiza e salva o valor diretamente
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;

				case 'select':
				case 'radio':
					// Para select e radio, sanitiza e salva o valor diretamente
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;

				case 'checkbox':
					// Para checkbox, verifica se é um array (vários checkboxes)
					if (is_array($field_value)) {
						$sanitized = array_map('sanitize_text_field', $field_value);
						update_user_meta($user_id, $field_name, $sanitized);
					} else {
						// Para um único checkbox, salva o valor diretamente
						update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					}
					break;

				default:
					// Qualquer outro tipo de campo personalizado, sanitiza e salva o valor
					update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
					break;
			}
		}
	}
}
add_action('personal_options_update', 'dokan_save_custom_profile_fields');
add_action('edit_user_profile_update', 'dokan_save_custom_profile_fields');

// TODO: Exibir campos personalizados na página de Editar Conta
/**
 * @return void
 */
function add_custom_fields_to_edit_account(): void
{
	// TODO: Recuperar os campos personalizados do banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// TODO: Verificar se há campos a serem renderizados
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = $field['options'] ?? [];
			$field_label = isset($field['label']) ? esc_html($field['label']) : ucfirst($field_name);

			// TODO: Recuperar o valor atual do campo para o usuário logado
			$field_value = get_user_meta(get_current_user_id(), $field_name, true);

			// TODO: Renderizar os campos de acordo com o tipo
			switch ($field_type) {
				case 'text':
				?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<input type="text" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" />
					</p>
				<?php
					break;

				case 'email':
				?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<input type="email" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" />
					</p>
				<?php
					break;

				case 'number':
				?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<input type="number" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" />
					</p>
				<?php
					break;

				case 'date':
				?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<input type="date" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" />
					</p>
				<?php
					break;

				case 'select':
				?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="input-text form-control">
							<option value=""><?php esc_html_e('Selecione uma opção', 'dokan'); ?></option>
							<?php foreach ($field_options as $option): ?>
								<option value="<?php echo esc_attr($option); ?>" <?php selected($field_value, $option); ?>><?php echo esc_html($option); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php
					break;

				case 'checkbox':
				?>
					<p class="form-row form-group form-row-wide">
						<label><?php echo $field_label; ?></label>
						<?php foreach ($field_options as $option): ?>
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="checkbox" class="input-checkbox" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked(is_array($field_value) && in_array($option, $field_value)); ?> />
								<?php echo esc_html($option); ?>
							</label>
						<?php endforeach; ?>
					</p>
				<?php
					break;

				case 'radio':
				?>
					<p class="form-row form-group form-row-wide">
						<label><?php echo $field_label; ?></label>
						<?php foreach ($field_options as $option): ?>
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="radio" class="input-radio" name="<?php echo $field_name; ?>" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked($field_value, $option); ?> />
								<?php echo esc_html($option); ?>
							</label>
						<?php endforeach; ?>
					</p>
<?php
					break;

				default:
					// TODO: Outros tipos de campo podem ser tratados aqui
					break;
			}
		}
	}
}
add_action('woocommerce_edit_account_form', 'add_custom_fields_to_edit_account');


// TODO: Salvar os campos personalizados ao editar a conta
/**
 * @param $user_id
 *
 * @return void
 */
function save_custom_fields_on_edit_account($user_id): void
{
	// TODO: Recuperar os campos personalizados
	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			if (isset($_POST[$field_name])) {
				if (is_array($_POST[$field_name])) {
					// TODO: Caso seja um checkbox (array de valores)
					update_user_meta($user_id, $field_name, array_map('sanitize_text_field', $_POST[$field_name]));
				} else {
					// TODO:  Para outros tipos de campo
					update_user_meta($user_id, $field_name, sanitize_text_field($_POST[$field_name]));
				}
			}
		}
	}
}
add_action('woocommerce_save_account_details', 'save_custom_fields_on_edit_account');
