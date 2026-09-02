// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'comprovante_associarController';

$(document).ready(function () {
    
    var url = window.location.href;
    var url_partes = url.split("=");
    var id_comprovante = url_partes[url_partes.length - 1];
    $('#id_comprovante').val(id_comprovante);
    
    listar();
    carregarComponente('carregarAtividade', 'div_atividade');
    carregarComponente('carregarProfessor', 'div_professor');
    carregarComponente('carregarComprovante', 'div_comprovante');
   
    $('#horas').mask('9,9');
    
    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#id_atividade').focus();
    })     

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

function carregarComponente(metodo, id) {
    $('#metodo').val(metodo);
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#' + id).html(json.select);
    });
}

function abrirModal(modal, metodo, id_comprovante_docente) {

    $('#msg').html('');
    $('#modal_formulario_msg').html('');

    $('#id_comprovante_docente').val(id_comprovante_docente);
    if (metodo == 'inserir') {
        // 3 - Aqui deve ser colocado os campos que serão limpos no formulario de 
        // inserção
        $('#id_usuario').val('');
        $('#id_atividade').val('');
        $('#horas').val('');
    }
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
        var json = JSON.parse(resposta);
        $('#tabela').html(json.tabela);
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