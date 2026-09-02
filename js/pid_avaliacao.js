var classe = 'pid_avaliacaoController';

$(document).ready(function () {
    
    var url = window.location.href;
    var url_partes = url.split("?");
    var parametros = url_partes[url_partes.length - 1];
    var campos = parametros.split("&");
    var campo1 = campos[0];
    var campo2 = campos[1];
    var vet_periodo = campo1.split("=");
    var vet_usuario = campo2.split("=");
    var id_periodo = vet_periodo[1];
    var id_usuario = vet_usuario[1];
    //alert(id_periodo+' - '+id_usuario);
    $('#id_periodo').val(id_periodo);
    $('#id_usuario').val(id_usuario);
    
    listar();
    
    $('#horas_planejadas').mask("99.99");    
        
    $('#btn_buscar').click(function () {        
        listar('','disciplinas');
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

function avaliar_pid() {

    $('#metodo').val('avaliar_pid');
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
        $('#id_atividade').attr('readonly', true);
        $('#descricao').attr('readonly', true);
        $('#horas_planejadas').attr('readonly', true);        
        $('#observacao').val('');
        $('#situacao').val('');
        //console.log($('#situacao').val());
    
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
    
    $.when(carregarComponente('carregarAtividade', 'div_atividade')).done(function(){
        //console.log('Método: '+metodo);
        if (metodo == 'avaliar_atividade') {
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

function habilitar_campos(obj){
    if (obj.value == 'APROVADA COM ALTERAÇÃO') {
        $('#id_atividade').attr('readonly', false);
        $('#descricao').attr('readonly', false);
        $('#horas_planejadas').attr('readonly', false);
    } else {
        $('#id_atividade').attr('readonly', true);
        $('#descricao').attr('readonly', true);
        $('#horas_planejadas').attr('readonly', true);
    }
}

function habilita_correcao(obj) {
    if (obj.value == 'REPROVADO') {
        $('#pid_correcao_fim').attr('readonly', true);
    } else {
        $('#pid_correcao_fim').attr('readonly', false);
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
        
        var win = window.open("pid_professor.html", "", "width=1500, height=760");
        var cabecalho = "";
        var titulo = "";
        var fim = "";
        win.document.write(cabecalho+titulo+json.tabela+fim);        
    });
}
