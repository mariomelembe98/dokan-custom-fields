<?php
// Adicionar um menu ao admin para gerir campos personalizados
add_action('admin_menu', 'custom_fields_menu');

/**
 * Adiciona uma página de menu para gerenciar campos personalizados
 */
function custom_fields_menu(): void {
	add_menu_page(
		'Custom Fields',        // Título da página
		'Custom Fields',        // Título do menu
		'manage_options',       // Capacidade
		'custom-fields',        // Slug do menu
		'custom_fields_page',   // Função que renderiza a página
		'dashicons-list-view',  // Ícone do menu
		60                      // Posição no menu
	);
}

// Página de gerenciamento de campos personalizados
function custom_fields_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	if (isset($_POST['custom_fields_submit'])) {
		check_admin_referer('custom_fields_save', 'custom_fields_nonce');

		$fields = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];

		$fields = array_filter($fields, function ($field) {
			return !empty($field['label']) && !empty($field['type']);
		});

		$fields = array_map(function ($field) {
			$sanitized_label = isset($field['label']) ? sanitize_text_field($field['label']) : '';
			$sanitized_name = $sanitized_label;
			$sanitized_name = str_replace(' ', '-', $sanitized_name);
			$sanitized_name = preg_replace('/[^a-zA-Z0-9-_]/', '', $sanitized_name);
			$sanitized_name = substr($sanitized_name, 0, 50);

			$sanitized_field = [
				'label' => $sanitized_label,
				'name' => $sanitized_name,
				'type' => isset($field['type']) ? sanitize_text_field($field['type']) : '',
			];

			if (in_array($field['type'], ['select', 'checkbox', 'radio'])) {
				if (isset($field['options']) && is_array($field['options'])) {
					$sanitized_field['options'] = array_map('sanitize_text_field', $field['options']);
				} else {
					$sanitized_field['options'] = [];
				}
			}

			return $sanitized_field;
		}, $fields);

		update_option('custom_registration_fields', $fields);
		echo '<div class="updated"><p>Campos salvos com sucesso!</p></div>';
	}

	$custom_fields = get_option('custom_registration_fields', []);
	?>
	<div class="wrap">
		<h1>Gerenciar Campos Personalizados</h1>
		<form method="post" action="">
			<?php wp_nonce_field('custom_fields_save', 'custom_fields_nonce'); ?>
			<table class="form-table" id="custom-fields-table">
				<tbody id="sortable">
				<tr>
					<th>Rótulo do Campo</th>
					<th>Tipo de Campo</th>
					<th>Opções (para Select, Checkbox, Radio)</th>
					<th>Ações</th>
				</tr>
				<?php foreach ($custom_fields as $index => $field): ?>
					<tr class="ui-state-default">
						<td>
							<input type="text" name="custom_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label'] ?? ''); ?>" required />
						</td>
						<td>
							<select name="custom_fields[<?php echo $index; ?>][type]" class="field-type">
								<option value="text" <?php selected($field['type'], 'text'); ?>>Texto</option>
								<option value="email" <?php selected($field['type'], 'email'); ?>>Email</option>
								<option value="number" <?php selected($field['type'], 'number'); ?>>Número</option>
								<option value="date" <?php selected($field['type'], 'date'); ?>>Data</option>
								<option value="select" <?php selected($field['type'], 'select'); ?>>Seleção</option>
								<option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
								<option value="radio" <?php selected($field['type'], 'radio'); ?>>Radio</option>
							</select>
						</td>
						<td>
							<div class="options-container" <?php echo in_array($field['type'], ['select', 'checkbox', 'radio']) ? '' : 'style="display:none;"'; ?>>
								<?php if (in_array($field['type'], ['select', 'checkbox', 'radio']) && !empty($field['options'])): ?>
									<?php foreach ($field['options'] as $option): ?>
										<div class="option-row">
											<input type="text" name="custom_fields[<?php echo $index; ?>][options][]" value="<?php echo esc_attr($option); ?>" />
											<button type="button" class="button remove-option">Remover</button>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
								<button type="button" class="button button-primary add-option">Adicionar Opção</button>
							</div>
						</td>
						<td>
							<button type="button" class="button remove-field">Remover</button>
						</td>
					</tr>
                    <tr>
                        <td colspan="4">
                            <hr class="horizontal-line"> <!-- Adiciona a linha horizontal -->
                        </td>
                    </tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<button type="button" class="button" id="add-field">Adicionar Campo</button>
			<input type="submit" name="custom_fields_submit" class="button button-primary" value="Salvar Campos" />
		</form>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
	<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
	<script>
        jQuery(document).ready(function($) {
            $("#sortable").sortable();
            $("#sortable").disableSelection();

            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('field-type')) {
                    const optionsContainer = e.target.closest('tr').querySelector('.options-container');
                    if (['select', 'checkbox', 'radio'].includes(e.target.value)) {
                        optionsContainer.style.display = 'block';
                    } else {
                        optionsContainer.style.display = 'none';
                    }
                }
            });
        });
	</script>
	<?php
}
