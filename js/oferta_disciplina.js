// 1 - Aqui deve ser colocado o nome da classe controler que será acessada para
// fazer as requisições dessa página
var classe = 'oferta_disciplinaController';

$(document).ready(function () {

    carregarPeriodo();

    $('#btn_buscar').click(function () {
        listar('');
    });

    $('#modal_formulario').on('shown.bs.modal', function () {
        // 2 - Aqui deve ser colocado o campos que terá o foco ao abrir o formulario
        $('#id_disciplina').focus();
    });

    $('#btn_buscar').click(function () {
        $('#modal_formulario_msg').html('');
        listar('');
    });

    $('#btn_gravar').click(function () {
        enviar('modal_formulario');
    });

    $('#btn_sim').click(function () {
        enviar('modal_confirmacao');
    });

    $("#modal_choques").on("hidden.bs.modal", function () {
        if ($('#metodo').val() == 'choques_horario') {
            $('#professor_' + $('#id_oferta_disciplina').val()).val($('#id_usuario_antigo').val());
        }
    });

    $(window).scroll(function () {
        posicaoPainelCh();
    });

    $(window).resize(function () {
        posicaoPainelCh();
    });


    $('#chs').focusin(function () {
        //console.log("Saving value " + ($('#cht').val()/$(this).val()));
        $(this).data('val', ($('#cht').val() / $(this).val()));
    }).change(function () {
        var prev = $(this).data('val');
        var current = $(this).val();
        $('#cht').val(prev * current);
        //console.log("Prev value " + prev);
        //console.log("New value " + current);
    });

});

function replaceClass(id, oldClass, newClass) {
    //alert(id+" "+oldClass+" "+newClass);

    $('#metodo').val('atualizar_tipo');
    $('#id_oferta_disciplina').val(id);

    if ($('#tipo_' + id).attr('class') == 'glyphicon glyphicon-education') {

        $('#tipo').val('Preparação aula EAD');

        var elem = $('#tipo_' + id);
        if (elem.hasClass(oldClass)) {
            elem.removeClass(oldClass);
        }
        elem.addClass(newClass);
        $('#tipo_cor_' + id).css('color', 'orange');

    } else {

        $('#tipo').val('Aula');

        var elem = $('#tipo_' + id);
        if (elem.hasClass(newClass)) {
            elem.removeClass(newClass);
        }
        elem.addClass(oldClass);
        $('#tipo_cor_' + id).css('color', 'blue');
    }

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


function abrirModal(modal, metodo, id_oferta_disciplina, id_turma) {

    $('#modal_formulario_msg').html('');
    if (metodo == 'deletar') {
        $('#id_oferta_disciplina').val(id_oferta_disciplina);
        $('#id_turma').val(id_turma);
        $('#turma_label').remove();
        $('#id_turma').show();
    } else {
        $('#id_turma').val(id_turma);
        $('#id_turma').hide();
        $('#turma_label').remove();
        $('#div_turma').append('<span id="turma_label" class="form-control text-center" style="background-color:#FFFAF0"><span style="font-weight:bold;margin-right:10px">Turma: </span><span>' + $('#id_turma option:selected').text() + '</span></span>');
    }
    $('#id_disciplina').val('');
    $('#chs').val('');
    $('#chs_ead').val('');
    $('#cht').val('');
    $('#id_usuario').val('');
    $('#metodo').val(metodo);
    $('#' + modal).modal();
}

function carregarComponente(metodo, id) {
    $('#metodo').val(metodo);
    var dados = $('#formulario').serialize();
    dados += "&periodo=" + encodeURIComponent($('#id_periodo option:selected').text()); 
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


function choques_horario(id_oferta_disciplina, professor) {

    $('#modal_choques').modal();

    var id_usuario_antigo = professor.oldvalue;
    $('#metodo').val('choques_horario');
    $('#id_usuario').val(professor.value);
    $('#id_usuario_antigo').val(id_usuario_antigo);
    $('#professor_novo').val($('#professor_' + id_oferta_disciplina + ' option:selected').text());
    $('#id_oferta_disciplina').val(id_oferta_disciplina);

    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#div_choques').html(json.tabela);
    });
}

//function add_oferta(id_turma,id_disciplina,disciplina,chs,chs_ead) {
//     $("#turma_"+id_turma).append('<tr><td></td><td>'+disciplina+'</td><td align="center">'+chs+'</td><td align="center">'+chs_ead+'</td><td><a onclick="$(this).parent().parent().remove();" href="javascript:void(0);">Remover</a></td></tr>'); 
//}

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
        if (json.resultado) {
            listar(json.msg);
        }
        $('#modal_choques').modal('hide');
    });
}

function atualizar2() {

    $('#metodo').val('atualizar2');
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
        $('#modal_choques').modal('hide');
    });
}

function atualizar_chs(id_oferta_disciplina, chs_real) {

    $('#metodo').val('atualizar_chs');
    $('#id_oferta_disciplina').val(id_oferta_disciplina);
    $('#chs').val($('#chs_' + id_oferta_disciplina).val());
    $('#cht').val(chs_real * $('#chs_' + id_oferta_disciplina).val());

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

function atualizar_chs_ead(id_oferta_disciplina) {

    $('#metodo').val('atualizar_chs_ead');
    $('#id_oferta_disciplina').val(id_oferta_disciplina);
    $('#chs_ead').val($('#chs_ead_' + id_oferta_disciplina).val());

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

function listar(msg) {
    $('#metodo').val('listar');
    var dados = $('#formulario').serialize();
    //console.log($('#id_periodo option:selected').text());
    dados += "&periodo=" + encodeURIComponent($('#id_periodo option:selected').text()); 
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#tabela').html(json.tabela);
        $('#msg_' + $('#id_turma').val()).html(msg);

        posicaoPainelCh();
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
                listar(json.msg);
            } else {
                $('#modal_formulario_msg').html(json.msg);
            }
        } else {
            listar(json.msg);
        }
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
            carregarNucleo();
        });
    });
}

function carregarNucleo() {
    $('#metodo').val('carregarNucleo');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#id_nucleo_busca').html(json.options).ready(function () {
            getTurmasAtivas();
        });
    });
}

function setCH() {
    $('#metodo').val('setCH');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#chs').val(json.chs);
        $('#chs_ead').val(json.chs_ead);
        $('#cht').val(json.cht);
        $('#metodo').val('inserir');
    });
}

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
        $('#id_turma_busca').html(json.options).ready(function () {
            listar('');
            carregarComponente('carregarDisciplina', 'div_disciplina');
            carregarComponente('carregarTurma', 'div_turma');
            carregarComponente('carregarProfessor', 'div_professor');
            carregarComponente('carregarTipo', 'div_tipo');
        });
    });
}

function posicaoPainelCh() {

    //alert("Tamanho do Painel: "+$("#painel_carga_horaria").height()+"\nTamanho da janela: "+$(window).width()+"\nPosição do scrollTop: "+$(window).scrollTop());
    //console.log("\nAltura painel busca: "+$("#painel_busca").height()+"\nAltura do Painel Carga Horaria: "+$("#painel_carga_horaria").height()+"\nAltura da janela: "+$(window).height()+"\nPosição do scrollTop: "+$(window).scrollTop());
    //console.log($(window).width());

    $('#corpo_painel_ch').css({'max-height': $(window).height() - 100});
    if (
            ($(window).scrollTop() > ($("#painel_busca").height() + 160)) &&
            ($(window).width() > 750)
            ) {
        $('#painel_carga_horaria').css({'top': 20});
        $('#painel_carga_horaria').css({'position': 'fixed'});
        $('#painel_carga_horaria').css({'width': '39%'});
    } else {
        $('#painel_carga_horaria').css({'top': ''});
        $('#painel_carga_horaria').css({'position': ''});
        $('#painel_carga_horaria').css({'width': ''});
    }
}