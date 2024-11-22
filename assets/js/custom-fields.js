jQuery(document).ready(function ($) {
    // Ativar sortable na tabela
    $('.sortable tbody').sortable({
        handle: 'td', // Permitir arrastar em toda a linha
        placeholder: 'ui-state-highlight',
    }).disableSelection();
});
