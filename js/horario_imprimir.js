var classe = 'horario_imprimirController';

$(document).ready(function () {

    carregarPeriodo();
    
    $('#btn_buscar').click(function () {        
        listar();
    });    
    $('#btn_imprimir').click(function () {        
        imprimir();
    });   

});

function getTurmasAtivas() {
    $('#metodo').val('getTurmasAtivas');
    var dados = $('#formulario').serialize();
    dados += "&periodo=" + encodeURIComponent($('#id_periodo option:selected').text()); 
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#id_turma').html(json.options);
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
        $('#div_periodo').append(json.select).ready(function() {
            getTurmasAtivas();
        });
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
    });
}  

function imprimir() {
    $('#metodo').val('listar');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        //$('#tabela').html(json.tabela);
        var win = window.open("horario.html", "", "width=1080, height=800");
        var cabecalho = "<!DOCTYPE html><head><title>Horário</title><link href=\"js/jquery-ui-1.12.1/jquery-ui.css\" rel=\"stylesheet\" /> <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\" /> <link href=\"css/bootstrap-theme.min.css\" rel=\"stylesheet\" /> </head><body style='padding:10px; -webkit-print-color-adjust: exact;'>";
        var fim = "</body></html>";
        win.document.write(cabecalho+json.tabela+fim);
    });    
}