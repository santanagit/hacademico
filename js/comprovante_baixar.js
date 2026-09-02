var classe = 'comprovante_baixarController';

$(document).ready(function () {

    carregarPeriodo();
        
    $('#btn_baixar').click(function () {        
        baixar();
    });    

});

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
        $('#div_periodo').html(json.select);
    });
}

function carregarDocente() {
    $('#metodo').val('carregarDocente');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#div_docente').html(json.select);
        if (json.registros == 0) {
            document.getElementById("btn_baixar").style.display = "none";
            $('#tabela').html('');
            $('#id_usuario').val('');
        } else {
            document.getElementById("btn_baixar").style.display = "block";
            document.getElementById("btn_baixar").style.marginTop = "8px";
        }
    });
}

function baixar() {
    $('#metodo').val('baixarArquivos');
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