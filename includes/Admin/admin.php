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

	// Salvar campos se o formulário for enviado
	if (isset($_POST['custom_fields_submit'])) {
		// Verifica o nonce para segurança
		check_admin_referer('custom_fields_save', 'custom_fields_nonce');

		$fields = isset($_POST['custom_fields']) ? $_POST['custom_fields'] : [];

		// Filtrar e sanitizar campos
		$fields = array_filter($fields, function ($field) {
			return !empty($field['label']) && !empty($field['type']);
		});

		// Verificar duplicados e sanitizar os campos
		$sanitized_fields = [];
		foreach ($fields as $field) {
			$sanitized_label = sanitize_text_field($field['label']);
			$sanitized_name = sanitize_text_field($field['name']);
			$sanitized_name = strtolower($sanitized_name); // Minúsculas
			$sanitized_name = str_replace(' ', '_', $sanitized_name); // Substituir espaços por underscores
			$sanitized_name = preg_replace('/[^a-zA-Z0-9-_]/', '', $sanitized_name); // Remover caracteres especiais
			$sanitized_name = substr($sanitized_name, 0, 30); // Limitar comprimento

			$sanitized_field = [
				'label' => $sanitized_label,
				'name' => $sanitized_name,
				'type' => sanitize_text_field($field['type']),
			];

			// Sanitizar opções, se existirem
			if (in_array($field['type'], ['select', 'checkbox', 'radio'])) {
				$sanitized_field['options'] = isset($field['options']) && is_array($field['options'])
					? array_map('sanitize_text_field', $field['options'])
					: [];
			}

			// Remover duplicados com base no 'name'
			$sanitized_fields[$sanitized_name] = $sanitized_field;
		}

		update_option('custom_registration_fields', array_values($sanitized_fields)); // Reindexar o array
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
                <tbody id="sortable" id="custom-fields-table">
                    <tr>
                        <th>Rótulo do Campo</th>
                        <th>Tipo de Campo</th>
                        <th>Opções (para Select, Checkbox, Radio)</th>
                        <th>Ações</th>
                    </tr>
                    <?php foreach ($custom_fields as $index => $field): ?>
                        <tr>
                            <td>
                                <label class="label">Texto a apresentar</label><br>
                                <input type="text" name="custom_fields[<?php echo $index; ?>][label]" value="<?php echo esc_attr($field['label'] ?? ''); ?>" required />
                                <br><br>
                                <label class="label">Nome do campo na base de dados (max: 30 caracteres)</label><br>
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
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="button" class="button" id="add-field">Adicionar Campo</button>
            <input type="submit" name="custom_fields_submit" class="button button-primary" value="Salvar Campos" />
        </form>
    </div>

    <script>
        document.getElementById('add-field').addEventListener('click', function() {
            const table = document.querySelector('.form-table');
            const rowCount = table.rows.length - 1;
            const row = document.createElement('tr');
            row.setAttribute('draggable', 'true');
            row.innerHTML = `
            <td>
                <label class="label">Texto a apresentar</label>
                <br>
                <input type="text" name="custom_fields[${rowCount}][label]" required />
                <br><br>
                <label class="label">Nome do campo na base de dados (max: 30 caracteres)</label>
                <br>
                <input type="text" name="custom_fields[${rowCount}][name]" maxlength="30" required />
            </td>
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

