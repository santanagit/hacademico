// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'periodoController';

$(document).ready(function () {

    listar();
    $('#ano').mask('0000');
    $('#semestre').mask('9');
    $('#data_inicio').mask('00/00/0000');
    $('#data_fim').mask('00/00/0000');
    $('#pid_inicio').mask('00/00/0000');
    $('#pid_fim').mask('00/00/0000');    
    $('#rid_inicio').mask('00/00/0000');
    $('#rid_fim').mask('00/00/0000');    
    
    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#ano').focus();
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

function abrirModal(modal, metodo, id_periodo) {

    $('#msg').html('');
    $('#modal_formulario_msg').html('');

    $('#id_periodo').val(id_periodo);
    if (metodo == 'atualizar') {
        $('#metodo').val('getPeriodo');
        carregar(id_periodo);
    } else {
        // 3 - Aqui deve ser colocado os campos que serão limpos no formulario de 
        // inserção
        $('#ano').val('');
        $('#semestre').val('');
        $('#data_inicio').val('');
        $('#data_fim').val('');
        $('#pid_inicio').val('');
        $('#pid_fim').val('');
        $('#rid_inicio').val('');
        $('#rid_fim').val('');
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
        $('#ano').val(json.ano);
        $('#semestre').val(json.semestre);
        $('#data_inicio').val(json.data_inicio_formatado);
        $('#data_fim').val(json.data_fim_formatado);
        $('#pid_inicio').val(json.pid_inicio_formatado);
        $('#pid_fim').val(json.pid_fim_formatado);
        $('#rid_inicio').val(json.rid_inicio_formatado);
        $('#rid_fim').val(json.rid_fim_formatado);
        if (json.publicado) { 
            $('#publicado').prop('checked', true);
        } else{   
            $('#publicado').prop('checked', false);           
        }          
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