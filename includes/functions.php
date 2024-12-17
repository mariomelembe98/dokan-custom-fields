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

				case 'textarea':
				?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<textarea name="<?php echo $field_name; ?>" rows="5" class="regular-text"><?php echo esc_textarea($field_value); ?></textarea>
						</td>
					</tr>
				<?php
					break;

				case 'email':
				?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<input type="email" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" class="regular-text" />
						</td>
					</tr>
				<?php
					break;

				case 'number':
				?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<input type="number" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" class="regular-text" />
						</td>
					</tr>
				<?php
					break;

				case 'date':
				?>
					<tr>
						<th><?php echo ucfirst($field_name); ?></th>
						<td>
							<input type="date" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($field_value); ?>" class="regular-text" />
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
	// TODO: Verifica se o utilizador está se registrando como vendedor
	if (!is_seller_registration()) {
		return; // Se não for um vendedor, não exibir os campos
	}

	// TODO: Recuperar os campos personalizados do banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// TODO: Verificar se há campos a serem renderizados
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {

			// Chama a função para obter os atributos do campo
			$field_attributes = get_field_attributes($field);

			// Extrai os valores retornados pela função
			$field_name = $field_attributes['name'];       // Nome do campo (ex: 'email', 'nome', etc.)
			$field_type = $field_attributes['type'];       // Tipo do campo (ex: 'text', 'select', etc.)
			$field_options = $field_attributes['options']; // Opções para selects ou checkboxes
			$field_label = $field_attributes['label'];     // Rótulo do campo (o que será exibido no formulário)
			$field_value = $field_attributes['value'];     // Valor actual do campo para o utilizador logado

			// TODO: Renderizar os campos de acordo com o tipo
			// Renderiza o campo usando a função genérica
			render_custom_field($field_name, $field_label, $field_type, $field_value, $field_options);

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
	// Retrieve custom fields from the database
	$custom_fields = get_option('custom_registration_fields', []);

	// Loop through each custom field and save its value
	foreach ($custom_fields as $field) {
		$field_name = $field['name'];

		// Check if the field value is set in the POST request
		if (isset($_POST[$field_name])) {
			// Handle array fields (e.g., checkboxes)
			if (is_array($_POST[$field_name])) {
				update_user_meta($user_id, $field_name, array_map('sanitize_text_field', $_POST[$field_name]));
			} else {
				// Handle single value fields (e.g., text, email, number, date, radio)
				update_user_meta($user_id, $field_name, sanitize_text_field($_POST[$field_name]));
			}
		} else {
			// If the field is not set in the POST request, delete its value from the user meta
			delete_user_meta($user_id, $field_name);
		}
	}
}

// Hook the function to the appropriate action
add_action('woocommerce_save_account_details', 'save_custom_fields_on_edit_account');

/**
 * Função que adiciona campos personalizados à página Become a Vendor.
 */
function add_custom_fields_to_become_vendor_form(): void {
	// Recuperar os campos personalizados do banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// Verificar se há campos a serem renderizados
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);
			$field_type = esc_attr($field['type']);
			$field_options = $field['options'] ?? [];
			$field_label = isset($field['label']) ? esc_html($field['label']) : ucfirst($field_name);

			// Recuperar o valor actual do campo para o usuário logado
			$user_id = get_current_user_id();
			$field_value = get_user_meta($user_id, $field_name, true);

			// Renderizar os campos conforme o tipo
			// Renderiza o campo usando a função genérica
			render_custom_field($field_name, $field_label, $field_type, $field_value, $field_options);

		}
	}
}

// Hook para adicionar os campos personalizados após os campos padrão na página "Become a Vendor"
add_action( 'dokan_after_seller_migration_fields', 'add_custom_fields_to_become_vendor_form' );

/**
 * Função para renderizar campos personalizados de diferentes tipos.
 *
 * @param string $field_name O nome do campo.
 * @param string $field_label O rótulo (label) do campo.
 * @param string $field_type O tipo do campo (text, email, number, etc.).
 * @param string $field_value O valor atual do campo.
 * @param array $field_options As opções do campo (para select, checkbox, radio).
 */
function render_custom_field( string $field_name, string $field_label, string $field_type, $field_value = '', $field_options = []): void {
	switch ($field_type) {
		case 'text':
		case 'email':
		case 'number':
		case 'date':
			?>
            <p class="form-row form-group form-row-wide">
                <label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($field_label); ?></label>
                <input type="<?php echo esc_attr($field_type); ?>" class="input-text form-control" name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($field_value); ?>" />
            </p>
			<?php
			break;

		case 'select':
			?>
			<p class="form-row form-group form-row-wide">
				<label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($field_label); ?></label>
				<select name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" class="input-text form-control">
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
				<label><?php echo esc_html($field_label); ?></label>
				<br>
				<?php foreach ($field_options as $option): ?>
					<label for="<?php echo esc_attr($field_name) . '_' . esc_attr($option); ?>">
						<input type="checkbox" class="input-checkbox" name="<?php echo esc_attr($field_name); ?>[]" id="<?php echo esc_attr($field_name) . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked(is_array($field_value) && in_array($option, $field_value)); ?> />
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
				<label><?php echo esc_html($field_label); ?></label>
				<br>
				<?php foreach ($field_options as $option): ?>
					<label for="<?php echo esc_attr($field_name) . '_' . esc_attr($option); ?>">
						<input type="radio" class="input-radio" name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name) . '_' . esc_attr($option); ?>" value="<?php echo esc_attr($option); ?>" <?php checked($field_value, $option); ?> />
						<?php echo esc_html($option); ?>
					</label>
				<br>
				<?php endforeach; ?>
			</p>
			<?php
			break;

		case 'date':
			?>
			<p class="form-row form-group form-row-wide">
				<label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($field_label); ?></label>
				<input type="date" class="input-text form-control" name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($field_value); ?>" />
			</p>
			<?php
			break;

		default:
			// Outros tipos de campo podem ser tratados aqui
			break;
	}
}
function save_custom_fields($user_id): void
{
    $custom_fields = get_option('custom_registration_fields', []);
    foreach ($custom_fields as $field) {
        $field_name = $field['name'];
        if (isset($_POST[$field_name])) {
            if (is_array($_POST[$field_name])) {
                update_user_meta($user_id, $field_name, array_map('sanitize_text_field', $_POST[$field_name]));
            } else {
                update_user_meta($user_id, $field_name, sanitize_text_field($_POST[$field_name]));
            }
        } else {
            delete_user_meta($user_id, $field_name);
        }
    }
}
// Form submission handler
add_action('woocommerce_save_account_details', 'save_custom_fields');

/**
 * Função que recupera os atributos de um campo personalizado.
 *
 * @param array $field Array que contém os dados de um campo.
 *
 * @return array Retorna um array com os atributos processados do campo.
 */
function get_field_attributes( array $field): array {

	$field_name = esc_attr($field['name']);
    $field_type = esc_attr($field['type']);
    $field_options = $field['options'] ?? [];
    $field_label = isset($field['label']) ? esc_html($field['label']) : ucfirst($field_name);
    $field_value = get_user_meta(get_current_user_id(), $field_name, true);

	// Retorna todos os atributos do campo num array associativo
	return [
		'name' => $field_name,
		'type' => $field_type,
		'options' => $field_options,
		'label' => $field_label,
		'value' => $field_value,
	];
}

/**
 * Função para salvar os campos personalizados após a migração de cliente para vendedor.
 *
 * @param int $user_id O ID do usuário que está se tornando vendedor.
 */
function save_custom_fields_after_migration( int $user_id): void {
	// Verifica se os campos personalizados existem no banco de dados
	$custom_fields = get_option('custom_registration_fields', []);

	// Se houver campos personalizados, itere sobre eles e salve os valores
	if (!empty($custom_fields)) {
		foreach ($custom_fields as $field) {
			$field_name = esc_attr($field['name']);

			// Verifica se o campo foi enviado no formulário
			if (isset($_POST[$field_name])) {
				$field_value = $_POST[$field_name];

				// Se for um checkbox, o valor é um array, então salvamos como uma string serializada
				if (is_array($field_value)) {
					$field_value = maybe_serialize($field_value);
				}

				// Salva o valor no 'meta' do utilizador
				update_user_meta($user_id, $field_name, sanitize_text_field($field_value));
			}
		}
	}
}
// Hook para salvar os campos personalizados após o utilizador se tornar vendedor
add_action('dokan_customer_migration', 'save_custom_fields_after_migration');

function import_all_vendors_from_excel($file_path): void {
    require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

    // Carregar planilha
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Verificar se há dados válidos
    if (empty($data) || count($data) <= 1) {
        echo '<div class="error"><p>Erro: O arquivo Excel está vazio ou sem dados válidos.</p></div>';
        return;
    }

    $errors = [];
    $success_count = 0;

    // Adicionar barra de progresso
    echo '<div id="progress-bar" style="width: 0%; background: green; height: 10px;"></div>';
    echo '<script>
    function updateProgress(percent) {
        document.getElementById("progress-bar").style.width = percent + "%";
    }
    </script>';

    $total_rows = count($data) - 1;

    // Processar cada linha do Excel (ignorando cabeçalhos)
    foreach ($data as $index => $row) {
        if ($index === 0) continue; // Ignorar cabeçalhos

        // Recuperar os dados da linha
        $nr_lic = sanitize_text_field($row[0] ?? null);
        $nr_proc = sanitize_text_field($row[1] ?? null);
        $tipo_licenca = sanitize_text_field($row[2] ?? null);
        $data_emissao = sanitize_text_field($row[3] ?? null);
        $nr_trabalhadores = intval($row[4] ?? 0);
        $investimento_inicial = floatval($row[5] ?? 0);
        $full_name = sanitize_text_field($row[6] ?? null);
        $tipo_doc = sanitize_text_field($row[7] ?? null);
        $nr_doc = sanitize_text_field($row[8] ?? null);
        $nacionalidade = sanitize_text_field($row[9] ?? 'MZ');
        $nuit = sanitize_text_field($row[10] ?? null);
        $denominacao_social = sanitize_text_field($row[11] ?? null);
        $nuel = sanitize_text_field($row[12] ?? null);
        $empresario = sanitize_text_field($row[13] ?? null);
        $cae_principal = sanitize_text_field($row[14] ?? null);
        $caes = sanitize_text_field($row[15] ?? null);
        $address = sanitize_text_field($row[16] ?? null);
        $nr_porta = sanitize_text_field($row[17] ?? null);
        $andar = sanitize_text_field($row[18] ?? null);
        $bairro = sanitize_text_field($row[19] ?? null);
        $distrito = sanitize_text_field($row[20] ?? null);
        $provincia = sanitize_text_field($row[21] ?? null);
        $phone = sanitize_text_field($row[22] ?? null);
        $store_url = sanitize_user(strtolower(str_replace(' ', '', $denominacao_social)));

		// Extrair o primeiro nome
		$name = explode(' ', trim($full_name))[0];

        // Ajustar nacionalidade
        if ($nacionalidade === "REPÚBLICA DE MOÇAMBIQUE") {
            $nacionalidade = "MZ";
        } elseif ($nacionalidade === "REPÚBLICA PORTUGUESA") {
            $nacionalidade = "PT";
        }

        // Validar campos obrigatórios
        if (empty($name) || empty($denominacao_social)) {
            $errors[] = "Linha $index ignorada: Nome ou Denominação Social ausente.";
            continue;
        }

        // Gerar username único
        $username_base = sanitize_user(strtolower(str_replace(' ', '', $name)));
        $username = $username_base;
        $counter = 1;
        while (username_exists($username)) {
            $username = $username_base . $counter;
            $counter++;
        }

        // Gerar endereço eletrónico único
        $email = $username . '@tempo.co.mz';
        if (email_exists($email)) {
            $errors[] = "Linha $index ignorada: Email $email já cadastrado.";
            continue;
        }

        // Criar utilizador no WordPress
        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_email' => $email,
            'user_pass'  => wp_generate_password(),
        ]);

        if (is_wp_error($user_id)) {
            $errors[] = "Erro ao criar usuário na linha $index: " . $user_id->get_error_message();
            continue;
        }

        // Atribuir papel de vendedor
        $user = new WP_User($user_id);
        $user->set_role('seller');

	    // Salvar metadados adicionais
	    update_user_meta($user_id, 'first_name', $name);
	    update_user_meta($user_id, 'last_name', '');
	    update_user_meta($user_id, 'dokan_enable_selling', 'yes');
	    update_user_meta($user_id, 'dokan_publishing', 'no');

        // Configurar dokan_profile_settings diretamente como array
	    $profile_settings = [
		    'store_name' => $denominacao_social,
		    'store_url'  => $store_url,
		    'social' => [
			    'fb' => '',
			    'twitter' => '',
			    'instagram' => '',
		    ],
		    'phone' => $phone,
		    'address' => [
			    'street_1' => $address,
			    'street_2' => '',
			    'city'     => $bairro,
			    'state'    => $provincia,
			    'zip'      => '',
			    'country'  => $nacionalidade
		    ],
		    'banner' => 0,
		    'icon'   => 0,
		    'gravatar' => 0
	    ];

        // Salvar sem serialização
	    update_user_meta($user_id, 'dokan_profile_settings', $profile_settings);

        // Outros campos personalizados
	    update_user_meta($user_id, 'investimento_inicial', $investimento_inicial);
	    update_user_meta($user_id, 'tipo_doc', $tipo_doc);
	    update_user_meta($user_id, 'nr_doc', $nr_doc);
	    update_user_meta($user_id, 'country', $nacionalidade);
	    update_user_meta($user_id, 'nuit', $nuit);
	    update_user_meta($user_id, 'nuel', $nuel);
	    update_user_meta($user_id, 'empresario', $empresario);
	    update_user_meta($user_id, 'cae_principal', $cae_principal);
	    update_user_meta($user_id, 'caes', $caes);
	    update_user_meta($user_id, 'vendor_address', $address);
	    update_user_meta($user_id, 'nr_lic', $nr_lic);
	    update_user_meta($user_id, 'nr_proc', $nr_proc);
	    update_user_meta($user_id, 'tipo_licenca', $tipo_licenca);
	    update_user_meta($user_id, 'data_emissao', $data_emissao);
	    update_user_meta($user_id, 'nr_trab', $nr_trabalhadores);
	    update_user_meta($user_id, 'vendor_phone', $phone);
	    update_user_meta($user_id, 'nr_porta', $nr_porta);
	    update_user_meta($user_id, 'andar', $andar);
	    update_user_meta($user_id, 'distrito', $distrito);
	    update_user_meta($user_id, 'provincia', $provincia);

	    $success_count++;

        // Atualizar barra de progresso
        $progress = round(($index / $total_rows) * 100);
        echo '<script>updateProgress(' . $progress . ');</script>';
        flush();
    }

    // Exibir resultado
    echo '<div class="updated"><p>Importação concluída: ' . $success_count . ' utilizadores importados com sucesso.</p></div>';

    if (!empty($errors)) {
        echo '<div class="error"><p>Erros encontrados:</p><ul>';
        foreach ($errors as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul></div>';
    }
}


