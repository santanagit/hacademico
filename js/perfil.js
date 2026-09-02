// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'perfilController';

$(document).ready(function () {

    listar();
    
    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#descricao').focus();
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
            ){
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
            alert('Página inexistente: '+valor+' - '+total_paginas);
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
            alert('Página inexistente: '+valor+' - '+total_paginas);
        }
        listar();
    });

    $('#btn_adicionar').click(function () {
        abrirModal('modal_formulario', 'inserir', 0);
    });

    $('#btn_gravar').click(function () {
        enviar('modal_formulario');
    });

    $('#btn_sim').click(function () {
        enviar('modal_confirmacao');
    });
});

function abrirModal(modal, metodo, id_perfil) {

    $('#msg').html('');
    $('#modal_formulario_msg').html('');

    $('#id_perfil').val(id_perfil);
    if (metodo == 'atualizar') {
        $('#metodo').val('getPerfil');
        carregar(id_perfil);
    } else {
        // 3 - Aqui deve ser colocado os campos que serão limpos no formulario de 
        // inserção
        $('#descricao').val('');
    }
    $('#metodo').val(metodo);

    $('#' + modal).modal();
}

function carregar() {
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        // 4 - Aqui deve ser colocado os campos que serão carregados para edição
        $('#descricao').val(json.descricao);
    });
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
        var json = JSON.parse(resposta);
        $('#tabela').html(json.tabela);
        $('#registros').val(json.registros);
        $('#total_paginas').val(json.total_paginas);
        $('#pagina').val(json.pagina);
        $('#filtro').val(json.filtro);
    });
}

function enviar(modal) {
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        if (modal == 'modal_formulario') {
            if (json.resultado) {
                $('#modal_formulario').modal('toggle');
                $('#msg').html(json.msg);
                listar();
            } else {
                $('#modal_formulario_msg').html(json.msg);
            }
        } else {
            if (json.resultado) {
                listar();
            }
            $('#msg').html(json.msg);
        }
    });
}