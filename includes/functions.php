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

// TODO: Exibir campos personalizados no perfil do vendedor
add_action('dokan_seller_meta_fields', 'dokan_custom_profile_fields');

/**
 * @param $user
 *
 * @return void
 */
function dokan_custom_profile_fields($user): void
{

	// TODO: Exibir campos personalizados no perfil do vendedor
	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = $field['options'] ?? [];
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

	$custom_fields = get_option('custom_registration_fields', []);

	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = $field['name'];
			$field_type = $field['type'];
			$field_value = $_POST[ $field_name ] ?? '';

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
