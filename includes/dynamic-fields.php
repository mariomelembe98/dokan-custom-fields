<?php

// TODO: Função para verificar se o registro é de um vendedor
/**
 * @return bool
 */
function is_seller_registration(): bool {
	if (current_user_can('seller')) {
		return true;
	} else {
		return false;
	}
}

// TODO: Renderizar campos dinâmicos no registro
/**
 * @return void
 */
function render_dynamic_fields(): void {

	// Verifica se o utilizador está se registrando como vendedor
	// if (!is_seller_registration()) {
	// 	return; // Se não for um vendedor, não exibir os campos
	// }

	// Recupera os campos personalizados armazenados no banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// Verifica se há campos a serem renderizados
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = $field['options'] ?? [];
			$field_label = isset($field['label']) ? esc_html($field['label']) : ucfirst($field_name);

			// Recuperar o valor atual do campo para o usuário logado
			$field_value = get_user_meta(get_current_user_id(), $field_name, true);

			// Renderizar os campos conforme o tipo
			switch ($field_type) {
				case 'text':
            ?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo $field_label; ?></label>
						<input type="text" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" />
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
						<br>
						<?php foreach ($field_options as $option): ?>
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="checkbox" class="input-checkbox" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked(is_array($field_value) && in_array($option, $field_value)); ?> />
								<?php echo esc_html($option); ?>
							</label>
							<br>
						<?php endforeach; ?>
					</p>
				<?php
					break;

				case 'radio':
				?>
					<p class="form-row form-group form-row-wide">
						<label><?php echo $field_label; ?></label>
						<br>
						<?php foreach ($field_options as $option): ?>
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="radio" class="input-radio" name="<?php echo $field_name; ?>" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked($field_value, $option); ?> />
								<?php echo esc_html($option); ?>
							</label>
							<br>
						<?php endforeach; ?>
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

				default:
					// TODO: Outros tipos de campo podem ser tratados aqui
					break;
			}
		}
	}
}


// TODO: Hook para adicionar os campos dinâmicos no formulário de registro do vendedor
add_action('dokan_seller_registration_field_after', 'render_dynamic_fields');

// TODO: Salvar dados dos campos personalizados
/**
 * @param $customer_id
 *
 * @return void
 */
function save_dynamic_fields($customer_id): void {
	$custom_fields = get_option('custom_registration_fields');

	if ($custom_fields && is_array($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = $field['name'];

			if (isset($_POST[$field_name])) {
				$field_value = $_POST[$field_name];

				// Tratar cada tipo de campo de maneira apropriada
				switch ($field['type']) {
					case 'text':
					case 'select':
					case 'radio':
					case 'textarea':
						// Sanitiza como texto
						$sanitized_value = sanitize_text_field($field_value);
						break;

					case 'checkbox':
						// Se for checkbox, pode ser um array de valores
						if (is_array($field_value)) {
							// Sanitiza todos os valores dentro do array
							$sanitized_value = array_map('sanitize_text_field', $field_value);
						} else {
							// Se não for array, sanitiza como texto único
							$sanitized_value = sanitize_text_field($field_value);
						}
						break;

					case 'email':
						// Sanitiza como email
						$sanitized_value = sanitize_email($field_value);
						break;

					case 'number':
						// Sanitiza como número
						$sanitized_value = floatval($field_value);
						break;

					case 'date':
						// Sanitiza como uma data válida
						$sanitized_value = date('Y-m-d', strtotime($field_value));
						break;

					default:
						// Se o tipo for inesperado, sanitiza como texto por segurança
						$sanitized_value = sanitize_text_field($field_value);
						break;
				}

				// Salvar o campo sanitizado no meta do usuário
				update_user_meta($customer_id, $field_name, $sanitized_value);
			}
		}
	}
}
add_action('woocommerce_created_customer', 'save_dynamic_fields');
