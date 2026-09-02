var classe = 'meus_dadosController';

$(document).ready(function () {
    carregarDados();
    $('#btn_gravar').click(function () {
        atualizar();
    }); 
});

function carregarDados() {
    $('#metodo').val('carregarDados');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#id_usuario').val(json.id_usuario);
        $('#nome').val(json.nome);
        $('#matricula').val(json.matricula);
        $('#email').val(json.email);
        $("#perfil").html(json.perfil);
    });
}

function atualizar() {
    $('#metodo').val('atualizar');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#msg').html(json.msg);
    });
}