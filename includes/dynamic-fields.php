<?php

// Função para verificar se o registro é de um vendedor
function is_seller_registration() {
	if (isset($_POST['role']) && $_POST['role'] === 'seller') {
		return true;
	}
	return false;
}

// Renderizar campos dinâmicos no registro
function render_dynamic_fields() {

	// Verifica se o usuário está se registrando como vendedor
//	if (!is_seller_registration()) {
//		return; // Se não for um vendedor, não exibir os campos
//	}

	// Recupera os campos personalizados armazenados no banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// Verifica se há campos a serem renderizados
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = isset($field['options']) ? $field['options'] : [];

			// Renderiza os campos de acordo com o tipo
			switch ($field_type) {
				case 'text':
					?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo ucfirst($field_name); ?></label>
						<input type="text" class="input-text form-control" name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" value="" />
					</p>
					<?php
					break;

				case 'select':
					?>
					<p class="form-row form-group form-row-wide">
						<label for="<?php echo $field_name; ?>"><?php echo ucfirst($field_name); ?></label>
						<select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="input-text form-control">
							<option value=""><?php esc_html_e('Selecione uma opção', 'dokan'); ?></option>
							<?php foreach ($field_options as $option): ?>
								<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<?php
					break;

				case 'checkbox':
					// Se for um grupo de checkboxes, renderiza cada uma das opções
					foreach ($field_options as $option) {
						?>
						<p class="form-row form-group form-row-wide">
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="checkbox" class="input-checkbox" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" />
								<?php echo esc_html($option); ?>
							</label>
						</p>
						<?php
					}
					break;

				case 'radio':
					// Renderiza as opções de radio
					foreach ($field_options as $option) {
						?>
						<p class="form-row form-group form-row-wide">
							<label for="<?php echo $field_name . '_' . esc_attr($option); ?>">
								<input type="radio" class="input-radio" name="<?php echo $field_name; ?>" id="<?php echo $field_name . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" />
								<?php echo esc_html($option); ?>
							</label>
						</p>
						<?php
					}
					break;

				default:
					// Outros tipos de campo podem ser tratados aqui
					break;
			}
		}
	}
}

// TODO: Hook para adicionar os campos dinâmicos no formulário de registro do vendedor
add_action('dokan_seller_registration_field_after', 'render_dynamic_fields');

// TODO: Salvar dados dos campos personalizados
function save_dynamic_fields($customer_id) {
	$custom_fields = get_option('custom_registration_fields');

	if ($custom_fields && is_array($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = $field['name'];

			if (isset($_POST[$field_name])) {
				$field_value = $_POST[$field_name];

				// Tratar cada tipo de campo de maneira apropriada
				switch ($field['type']) {
					case 'text':
						// Sanitiza como um texto normal
						$sanitized_value = sanitize_text_field($field_value);
						break;

					case 'select':
					case 'radio':
						// Sanitiza como um valor único (já que ambos são escolhas únicas)
						$sanitized_value = sanitize_text_field($field_value);
						break;

					case 'checkbox':
						// Se for checkbox, pode ser um array de valores
						if (is_array($field_value)) {
							// Sanitiza todos os valores dentro do array
							$sanitized_value = array_map('sanitize_text_field', $field_value);
						} else {
							// Se não for array, sanitiza como texto
							$sanitized_value = sanitize_text_field($field_value);
						}
						break;

					default:
						// Se o tipo for inesperado, sanitize como texto por segurança
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