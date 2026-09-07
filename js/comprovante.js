// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'comprovanteController';

$(document).ready(function () {

    var parametros = new URLSearchParams(window.location.search);

    var criterios = parametros.getAll('criterios[]');

    $('input[name="criterios[]"]').each(function () {
        var valor = $(this).val();

        if ($.inArray(valor, criterios) !== -1) {
            $(this).prop('checked', true);
        }
    });    
    
    listar();

    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#nome').focus();
    })

    $('#pagina').blur(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        var valor = parseInt($('#pagina').val());
        var total_paginas = parseInt($('#total_paginas').val());
        if (
                (valor < 1) ||
                (valor > total_paginas) ||
                (isNaN($('#pagina').val()))
                ) {
            alert('Valor de página inválido!');
            $('#pagina').val('');
        } else {
            listar();
        }
    });

    $('#registros').blur(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        var valor = parseInt($('#pagina').val());
        if (
                (valor < 1) ||
                (isNaN($('#registros').val()))
                ) {
            alert('Valor de registro inválido!');
            $('#registros').val('');
        } else {
            $('#pagina').val('1');
            listar();
        }
    });

    $('#btn_buscar').click(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        listar();
    });

    $('#btn_anterior').click(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        var valor = parseInt($('#pagina').val());
        var total_paginas = parseInt($('#total_paginas').val());
        var novo_valor = (valor - 1);
        if ((novo_valor >= 1) && (novo_valor <= total_paginas)) {
            $('#pagina').val(novo_valor);
        } else {
            alert('Página inexistente: ' + valor + ' - ' + total_paginas);
        }
        listar();
    });

    $('#btn_proximo').click(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        var valor = parseInt($('#pagina').val());
        var total_paginas = parseInt($('#total_paginas').val());
        var novo_valor = (valor + 1);
        if ((novo_valor >= 1) && (novo_valor <= total_paginas)) {
            $('#pagina').val(novo_valor);
        } else {
            alert('Página inexistente: ' + valor + ' - ' + total_paginas);
        }
        listar();
    });

    $('#btn_sim').click(function () {
        enviar('modal_confirmacao');
    });
});

function professores_associados(id_comprovante) {
    var criterios = $('input[name="criterios[]"]:checked')
            .map(function () {
                return $(this).val();
            })
            .get();

    var url = 'comprovante_associar.php';

    var parametros = {
        id_comprovante: id_comprovante,
        'criterios[]': criterios
    };

    var queryString = $.param(parametros);

    if (queryString !== '') {
        url += '?' + queryString;
    }

    window.location.href = url;
}

function abrirModal(modal, metodo, id_comprovante) {
    $('#msg').html('');
    $('#modal_formulario_msg').html('');
    $('#id_comprovante').val(id_comprovante);
    $('#metodo').val(metodo);
    $('#' + modal).modal();
}

function listar() {
    $('#metodo').val('listar');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        console.log('Resposta recebida:', resposta);
        var json = JSON.parse(resposta);
        $('#tabela').html(json.tabela);
        $('#registros').val(json.registros);
        $('#total_paginas').val(json.total_paginas);
        $('#pagina').val(json.pagina);
        $('#filtro').val(json.filtro);
        
        // Limpa os checkboxes antes de preencher novamente
        $('input[name="criterios[]"]').prop('checked', false);

        // Marca o checkbox grupo
        if (json.criterios && json.criterios.grupo == 1) {
            $('#grupo').prop('checked', true);
        }

        // Marca o checkbox vigencia
        if (json.criterios && json.criterios.vigencia == 1) {
            $('#vigencia').prop('checked', true);
        }
    });
}
function enviar() {
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        if (json.resultado) {
            listar();
        }
        $('#msg').html(json.msg);
    });
}