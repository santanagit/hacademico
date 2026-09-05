var classe = 'rid_professorController';

$(document).ready(function () {

    carregarPeriodo();
    $('#horas_planejadas').mask("99.99");

    $('#btn_buscar').click(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');
        listar();
    });

    $('#btn_imprimir').click(function () {
        imprimir_rid();
    });

    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#observacao').focus();
    });
    
    $("#modal_formulario").on("hidden.bs.modal", function () {
        listar();
    });    

    $('#btn_gravar').click(function () {
        enviar('modal_formulario');
    });

    $('#btn_sim').click(function () {
        enviar('modal_confirmacao');
    });
});

function enviar_rid() {

    $('#metodo').val('enviar_rid');
    $('#etapa').val('RID');
    $('#situacao').val('ENVIADO');

    /*
     * Esse valor é utilizado pela funcao "listar" para saber em qual painel 
     * será colocada as mensagens. Cada painel é identificado com o id do
     * tipo da atividade
     */
    $('#id_tipo_atividade').val(9);

    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        //if (json.resultado) {
        listar(json.msg);
        //}
    });
}

function atualizar_horas_executadas(id_atividade_docente, id_tipo_atividade) {

    var horas_executadas = $('#horas_executadas_' + id_atividade_docente).val().replace(/,/g, ".");
    //console.log(horas_executadas);

    if ((horas_executadas != $('#horas_planejadas_' + id_atividade_docente).val()) && (id_tipo_atividade != 2) ){
        
        abrirModal('modal_formulario', 'atualizar_atividade_rid', id_atividade_docente, id_tipo_atividade, true);
        //console.log($('#horas_executadas_' + id_atividade_docente).val()+" - "+$('#horas_planejadas_' + id_atividade_docente).val());
    } else {

        $('#metodo').val('atualizar_horas_executadas');
        $('#id_atividade_docente').val(id_atividade_docente);
        $('#horas_executadas').val($('#horas_executadas_' + id_atividade_docente).val());

        /*
         * Esse valor é utilizado para a função listar saber em qual painel 
         * será colocada as mensagens. Cada painel é identificado com o id do
         * tipo da atividade
         */
        $('#id_tipo_atividade').val(id_tipo_atividade);

        var dados = $('#formulario').serialize();
        $.ajax({
            url: 'controller/' + classe + '.php',
            type: 'post',
            dataType: 'html',
            data: dados
        }).done(function (resposta) {
            var json = JSON.parse(resposta);
            if (json.resultado) {
                listar(json.msg);
            }
        });
    }
}

//function carregarComponente(metodo, id) {
//    $('#metodo').val(metodo);
//    var dados = $('#formulario').serialize();
//    $.ajax({
//        url: 'controller/' + classe + '.php',
//        type: 'post',
//        dataType: 'html',
//        data: dados
//    }).done(function (resposta) {
//        var json = JSON.parse(resposta);
//        $('#' + id).html(json.select);
//    });
//}

function carregarComponente(metodo, id) {
    var r = $.Deferred();
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
        r.resolve(); // <-- RESOLVE a promise aqui!
    }).fail(function (jqXHR, textStatus, errorThrown) {
        r.reject(errorThrown);
    });
    return r.promise(); // <-- RETORNA a promise!
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
                listar(json.msg);
            } else {
                $('#modal_formulario_msg').html(json.msg);
            }
        } else {
            listar(json.msg);
        }
    });
}

function carregar(id_atividade_docente,horas_executadas,horas) {
    //console.log('Entrou na função carregar, metodo: '+$('#metodo').val());
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
        $('#horas_planejadas').val(json.horas_planejadas);
        $('#horas_executadas').val(horas_executadas);
        $('#id_atividade').val(json.id_atividade);
        $('#observacao').val('');
        
        if (($('#horas_executadas_' + id_atividade_docente).val() != $('#horas_planejadas_' + id_atividade_docente).val()) && (horas)) {
            $('#modal_formulario_msg').html('<div class="alert alert-danger">A quantidade de horas planejadas é difrente da quantidade de horas executadas, preencha a justificativa no campo de observação!</div>');
        }        
        
        var tabela = '<table class="table table-bordered table-striped table-sm">';
        tabela += '<tr>'
        tabela += '<th colspan="5" style="text-align:center">Histórico de tramitação da atividade</th>';
        tabela += '</tr>'
        tabela += '<tr>'
        tabela += '<th>ID</th>';
        tabela += '<th>Etapa</th>';
        tabela += '<th>Situação</th>';
        tabela += '<th>Data</th>';
        tabela += '<th>Observação</th>';
        tabela += '</tr>'

        var historico = json.historico;
        $.each(historico, function (index, value) {
            var obj_historico = value;
            tabela += '<tr>'
            tabela += '<td>' + obj_historico['id_historico_atividade'] + '</td>';
            tabela += '<td>' + obj_historico['etapa'] + '</td>';
            tabela += '<td>' + obj_historico['situacao'] + '</td>';
            tabela += '<td>' + obj_historico['data_situacao'] + '</td>';
            tabela += '<td>' + obj_historico['observacao'] + '</td>';
            tabela += '</tr>'
        });
        tabela += '</table>';
        $('#div_historico_atividade').html(tabela);
    });
}

function abrirModal(modal, metodo, id_atividade_docente, id_tipo_atividade, horas) {

    $('#msg').html('');
    $('#modal_formulario_msg').html('');

    $('#id_atividade_docente').val(id_atividade_docente);
    $('#id_tipo_atividade').val(id_tipo_atividade);
    carregarComponente('carregarAtividade', 'div_atividade');

    if (metodo == 'atualizar_atividade_rid') {
        $('#metodo').val('getAtividade_docente');
        carregar(id_atividade_docente,$('#horas_executadas_' + id_atividade_docente).val(),horas);
        if (horas) {
            $('#horas_executadas_' + id_atividade_docente).val('');
        }
       

    } else {
        // 3 - Aqui deve ser colocado os campos que serão limpos no formulario de 
        // inserção
        $('#descricao').val('');
        $('#horas_planejadas').val('');
        $('#horas_executadas').val('');
        $('#observacao').val('');
        $('#id_atividade').val('');
        $('#div_historico_atividade').html('');
    }

    $('#metodo').val(metodo);
    $('#' + modal).modal();
}

function reativar_atividade(id_atividade_docente, id_tipo_atividade) {
    $('#id_atividade_docente').val(id_atividade_docente);
    $('#id_tipo_atividade').val(id_tipo_atividade);
    $('#metodo').val('reativar_atividade');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        listar(json.msg);
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
        $('#pid_professor').html(json.tabela);
        $('#msg_' + $('#id_tipo_atividade').val()).html(msg);

        $("input[name^='horas_planejadas_']").mask("99.99");
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
