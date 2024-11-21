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
	// Verifica se o utilizador tem permissão
	if (!current_user_can('manage_options')) {
		return;
	}

	// Salvar campos se o formulário for enviado
	if (isset($_POST['custom_fields_submit'])) {
		// Verifica o nonce para segurança
		check_admin_referer('custom_fields_save', 'custom_fields_nonce');

		$fields = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];

		// Filtrar e sanitizar campos
		$fields = array_filter($fields, function ($field) {
			return !empty($field['label']) && !empty($field['type']);
		});

		$fields = array_map(function ($field) {
			// Verifica se 'label' existe
			$sanitized_label = isset($field['label']) ? sanitize_text_field($field['label']) : '';
			$sanitized_name = isset($field['name']) ? sanitize_text_field($field['name']) : '';

			// Verifica se 'label' existe
			$sanitized_name = strtolower($sanitized_name); // transforma as letras em minúsculas

			// Substitui espaços por hífens e remove caracteres especiais
			$sanitized_name = str_replace(' ', '_', $sanitized_name);
			$sanitized_name = preg_replace('/[^a-zA-Z0-9-_]/', '', $sanitized_name); // Remove caracteres especiais
			$sanitized_name = substr($sanitized_name, 0, 30); // Limita o comprimento do nome, se necessário

			$sanitized_field = [
				'label' => $sanitized_label,
				'name' => $sanitized_name,
				'type' => isset($field['type']) ? sanitize_text_field($field['type']) : '',
			];

			// Se o tipo exigir opções, sanitiza-as
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

	// Recuperar campos existentes
	$custom_fields = get_option('custom_registration_fields', []);

	?>
    <div class="wrap">
        <h1>Gerenciar Campos Personalizados</h1>
        <form method="post" action="">
			<?php wp_nonce_field('custom_fields_save', 'custom_fields_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th>Rótulo do Campo</th>
                    <th>Tipo de Campo</th>
                    <th>Opções (para Select, Checkbox, Radio)</th>
                    <th>Ações</th>
                </tr>
				<?php foreach ($custom_fields as $index => $field): ?>
                    <tr>
                        <td>
                            <label class="label"> Texto a apresentar </label>
                            <br>
                            <input type="text" name="custom_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label'] ?? ''); ?>" required />
                            <br>
                            <br>
                            <label class="label"> Nome do campo na base de dados (max: 30 caracteres)</label>
                            <br>
                            <input type="text" name="custom_fields[<?php echo $index; ?>][name]" value="<?php echo esc_attr($field['name'] ?? ''); ?>" required />

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
                            <!-- Campos para definir as opções -->
                            <div class="options-container" <?php echo in_array($field['type'], ['select', 'checkbox', 'radio']) ? '' : 'style="display:none;"'; ?>>
								<?php if (in_array($field['type'], ['select', 'checkbox', 'radio']) && !empty($field['options'])): ?>
									<?php foreach ($field['options'] as $option): ?>
                                        <div class="option-row">
                                            <input type="text" name="custom_fields[<?php echo $index; ?>][options][]" value="<?php echo esc_attr($option); ?>" />
                                            <button type="button" class="button remove-option">Remover</button>
                                        </div>
                                        <br>
									<?php endforeach; ?>
								<?php endif; ?>
                                <br>
                                <button type="button" class="button button-primary add-option">Adicionar Opção</button>
                                <br>
                                <br>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="button remove-field">Remover</button>
                        </td>
                    </tr>
				<?php endforeach; ?>
            </table>

            <button type="button" class="button" id="add-field">Adicionar Campo</button>
            <input type="submit" name="custom_fields_submit" class="button button-primary" value="Salvar Campos" />
        </form>
    </div>

    <script>
        document.getElementById('add-field').addEventListener('click', function() {
            const table = document.querySelector('.form-table');
            const rowCount = table.rows.length;
            const row = document.createElement('tr');
            row.setAttribute('draggable', 'true');
            row.innerHTML = `
                <td><input type="text" name="custom_fields[${rowCount}][name]" required /></td>
                <td>
                    <select name="custom_fields[${rowCount}][type]" class="field-type">
                        <option value="text">Texto</option>
                        <option value="email">Email</option>
                        <option value="number">Número</option>
                        <option value="date">Data</option>
                        <option value="select">Seleção</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                    </select>
                </td>
                <td>
                    <div class="options-container" style="display:none;"></div>
                    <button type="button" class="button add-option">Adicionar Opção</button>
                </td>
                <td><button type="button" class="button remove-field">Remover</button></td>
            `;
            table.appendChild(row);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-field')) {
                if (confirm('Tem certeza que deseja remover este campo?')) {
                    e.target.closest('tr').remove();
                }
            }

            if (e.target.classList.contains('add-option')) {
                const container = e.target.closest('td').querySelector('.options-container');
                const optionCount = container.querySelectorAll('.option-row').length;
                const div = document.createElement('div');
                div.className = 'option-row';
                div.innerHTML = `
                    <input type="text" name="${e.target.closest('tr').querySelector('select').name.replace('[type]', '[options][]')}" />
                    <button type="button" class="button remove-option">Remover</button>
                `;
                container.appendChild(div);
                container.style.display = 'block';
            }

            if (e.target.classList.contains('remove-option')) {
                e.target.closest('.option-row').remove();
            }
        });

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
    </script>
	<?php
}

