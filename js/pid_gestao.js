// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'pid_gestaoController';

$(document).ready(function () {

    carregarPeriodo();
    carregarComponente('carregarProfessor', 'div_professor');

    $('#btn_buscar').click(function () {
        listar('');
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

function listar(msg) {
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
        $('#msg').html(msg);
    });
}

function carregarPeriodo() {
    $('#metodo').val('carregarPeriodo');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#div_periodo').append(json.select).ready(function () {
            listar('');
        });
    });
}

function imprimir(id_pid,id_usuario) {
    $('#metodo').val('imprimir');
    $('#id_pid').val(id_pid);
    $('#id_usuario').val(id_usuario);
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        
        var win = window.open("pid_professor.html", "", "width=1024, height=768");
        win.document.write(json.tabela);        
    });
}

function imprimir_rid(id_pid,id_usuario) {
    $('#metodo').val('imprimir_rid');
    $('#id_pid').val(id_pid);
    $('#id_usuario').val(id_usuario);
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        
        var win = window.open("pid_professor.html", "", "width=1024, height=768");
        win.document.write(json.tabela);        
    });
}