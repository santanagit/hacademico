var classe = 'lattesController';

$(document).ready(function () {
    carregarProfessor();
    //listar();
    $('#btn_buscar').click(function () {        
        listar();
    });
});

function carregarProfessor() {
    $('#metodo').val('carregarProfessor');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#id_usuario').html(json.select);
    });
}

function listar() {
    $('#metodo').val('listar');
    $('#usuario').val($('#id_usuario').find('option:selected').text());

    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#lattes_professor').html(json.tabela);
    });
}