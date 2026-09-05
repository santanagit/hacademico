var classe = 'pid_professorController';

$(document).ready(function () {
    
    carregarPeriodo();
    $('#horas_planejadas').mask("99.99");    
        
    $('#btn_buscar').click(function () {
        $('#msg').html('');
        $('#modal_formulario_msg').html('');           
        listar();
    });        
        
    $('#btn_imprimir').click(function () {        
        imprimir();
    });
    
    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#descricao').focus();
    });
    
    $('#btn_gravar').click(function () {
        enviar('modal_formulario');
    }); 
    
    $('#btn_sim').click(function () {
        enviar('modal_confirmacao');
    });    
});

function enviar_pid() {

    $('#metodo').val('enviar_pid');
    $('#etapa').val('PID');
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

function atualizar_chs(id_atividade_docente,id_tipo_atividade) {

    $('#metodo').val('atualizar_chs');
    $('#id_atividade_docente').val(id_atividade_docente);
    $('#horas_planejadas').val($('#horas_planejadas_' + id_atividade_docente).val());
    
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
        $('#horas_planejadas').val(json.horas_planejadas);
        $('#id_atividade').val(json.id_atividade);
        $('#observacao').val('');
        
        var tabela = '<table class="table table-bordered table-striped table-sm">';
        tabela += '<tr>'
        tabela += '<th colspan="3" style="text-align:center">Histórico de tramitação da atividade</th>';
        tabela += '</tr>'
        tabela += '<tr>'                
        tabela += '<th>Situação</th>';
        tabela += '<th>Data</th>';
        tabela += '<th>Observação</th>';
        tabela += '</tr>'
        
        var historico = json.historico;
        $.each(historico, function(index, value) {
            var obj_historico = value;
            tabela += '<tr>'                
            tabela += '<td>'+obj_historico['situacao']+'</td>';
            tabela += '<td>'+obj_historico['data_situacao']+'</td>';
            tabela += '<td>'+obj_historico['observacao']+'</td>';
            tabela += '</tr>'
        });
        tabela += '</table>';
        $('#div_historico_atividade').html(tabela);
    });
}

function abrirModal(modal, metodo, id_atividade_docente, id_tipo_atividade) {
    
    $('#msg').html('');
    $('#modal_formulario_msg').html('');
    
    $('#id_atividade_docente').val(id_atividade_docente);
    $('#id_tipo_atividade').val(id_tipo_atividade);
    carregarComponente('carregarAtividade', 'div_atividade');
    
    if (metodo == 'atualizar_atividade_pid') {
        $('#metodo').val('getAtividade_docente');
        carregar(id_atividade_docente);
    } else {
        // 3 - Aqui deve ser colocado os campos que serão limpos no formulario de 
        // inserção
        $('#descricao').val('');
        $('#horas_planejadas').val('');
        $('#observacao').val('');
        $('#id_atividade').val('');
        $('#div_historico_atividade').html('');
    }    
    
    $('#metodo').val(metodo);
    $('#' + modal).modal();
}

function reativar_atividade(id_atividade_docente,id_tipo_atividade) {
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
        $('#msg_'+$('#id_tipo_atividade').val()).html(msg);
        
        $( "input[name^='horas_planejadas_']" ).mask("99.99");
    });
}

function imprimir() {
    $('#metodo').val('imprimir');
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
