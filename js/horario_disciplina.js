var classe = 'horario_disciplinaController';

$(document).ready(function () {

    carregarPeriodo();

    $('#btn_buscar').click(function () {
        listar();
    });
    $('#btn_imprimir').click(function () {
        imprimir();
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
        $('#div_periodo').append(json.select).ready(function () {
            carregarDisciplina();
        });
    });
}

function carregarDisciplina() {
    $('#metodo').val('carregarDisciplina');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#id_oferta_disciplina').html(json.select);
    });
}

function listar() {
    if ($('#id_oferta_disciplina').val() == '') {
        $('#disciplina_msg').html('<div class="aler alert-danger">Selecione a disciplina</div>');
    } else {
         $('#disciplina_msg').html('');
        $('#metodo').val('listar');
        var dados = $('#formulario').serialize();
        $.ajax({
            url: 'controller/' + classe + '.php',
            type: 'post',
            dataType: 'html',
            data: dados
        }).done(function (resposta) {
            var json = JSON.parse(resposta);
            $('#horario_disciplina').html(json.tabela);
            $('#professor').html(json.professor);
            $('#disciplina').html(json.disciplina);            
            $('#chs').html(json.chs);
            $('#cht').html(json.cht);

        });
    }
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

        var win = window.open("horario_professor.html", "", "width=1500, height=760");
        var cabecalho = "<!DOCTYPE html><head><title>Horário</title><link href=\"js/jquery-ui-1.12.1/jquery-ui.css\" rel=\"stylesheet\" /> <link href=\"css/bootstrap.min.css\" rel=\"stylesheet\" /> <link href=\"css/bootstrap-theme.min.css\" rel=\"stylesheet\" /> </head><body style='margin:15px; -webkit-print-color-adjust: exact;'>";
        var titulo = "<h4 class='alert alert-success' style='background-color: #DFF0D8 !important'>PROFESSOR(A): " + $('#id_usuario :selected').text() + "&nbsp&nbsp&nbsp&nbsp Aulas: " + json.chs + "&nbsp&nbsp&nbsp&nbsp CHS: " + json.cht + "</h4>";
        var fim = "</body></html>";
        win.document.write(cabecalho + titulo + json.tabela + fim);
    });
}
