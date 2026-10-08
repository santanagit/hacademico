var classe = 'horarioController';
var horarioCarregando = false;
var horarioGravacoes = 0;
var horarioCelulasOcupadas = {};

$(document).ready(function () {
    carregarPeriodo();

    $('#btn_buscar').on('click', function (event) {
        event.preventDefault();
        getMoldura();
    });
});

function horarioEscapar(texto) {
    return $('<div>').text(String(texto)).html();
}

function horarioMensagem(texto, tipo) {
    var icone = 'info-sign';

    if (tipo === 'success') {
        icone = 'ok';
    } else if (tipo === 'warning') {
        icone = 'warning-sign';
    }

    return '<span class="glyphicon glyphicon glyphicon-' + icone +
        ' alert-' + tipo +
        ' btn-sm" style="width:100%; text-align:center">&nbsp;' +
        horarioEscapar(texto) + '</span>';
}

function horarioFiltros() {
    return {
        id_periodo: $('#id_periodo').val() || '',
        periodo: $('#id_periodo option:selected').text(),
        turno: $('#turno').val() || 'Integral'
    };
}

function horarioRequisicao(dados) {
    return $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'POST',
        dataType: 'json',
        data: dados
    });
}

function horarioInicializarSeletores(container) {
    $(container).find('select').each(function () {
        this.oldvalue = this.value;
    });
}

function horarioErroAjax(xhr) {
    if (xhr.responseJSON && xhr.responseJSON.msg) {
        return xhr.responseJSON.msg;
    }

    console.error(
        'Resposta inválida ou falha HTTP no horário:',
        xhr.status,
        xhr.responseText
    );

    return horarioMensagem(
        'Falha na comunicação ou resposta inválida do servidor. HTTP ' +
        xhr.status +
        '. Consulte o console do navegador e o log do PHP.',
        'warning'
    );
}

function carregarPeriodo() {
    horarioRequisicao({
        metodo: 'carregarPeriodo'
    }).done(function (json) {
        if (json.resultado === 'ERRO') {
            $('#msg').html(json.msg);
            return;
        }

        $('#div_periodo').html(json.select);
        getMoldura();
    }).fail(function (xhr) {
        $('#msg').html(horarioErroAjax(xhr));
    });
}

function getMoldura() {
    if (horarioCarregando || horarioGravacoes > 0) {
        return;
    }

    horarioCarregando = true;
    $('#btn_buscar').prop('disabled', true);

    var dados = horarioFiltros();
    dados.metodo = 'getMoldura';

    horarioRequisicao(dados).done(function (json) {
        if (json.resultado === 'ERRO') {
            $('#msg').html(json.msg);
            return;
        }

        $('#moldura').html(json.moldura);
        horarioInicializarSeletores('#moldura');
        $('#msg').empty();
    }).fail(function (xhr) {
        $('#msg').html(horarioErroAjax(xhr));
    }).always(function () {
        horarioCarregando = false;
        $('#btn_buscar').prop('disabled', false);
    });
}

function setDisciplina(oferta) {
    oferta.oldvalue = oferta.value;

    var partes = oferta.id.split('_');
    var valores = oferta.value.split('_');

    $('#disciplina_antiga').val(
        oferta.value !== '' ? partes[1] + '_' + valores[2] : ''
    );
}

function horarioNumero(elemento) {
    var numero = parseFloat($(elemento).text().replace(',', '.'));
    return isNaN(numero) ? 0 : numero;
}

function horarioAtualizarContadores(deltas) {
    $.each(deltas || {}, function (chave, delta) {
        var chs = document.getElementById(chave);
        var ead = document.getElementById('ead_' + chave);
        var prevista = document.getElementById('chs_disciplina_' + chave);
        var previstaEad = document.getElementById('chs_ead_disciplina_' + chave);

        if (chs && prevista) {
            var total = horarioNumero(chs) + Number(delta.chs);
            $(chs).text(total).css(
                'color',
                total === horarioNumero(prevista) ? 'blue' : 'red'
            );
        }

        if (ead && previstaEad) {
            var totalEad = horarioNumero(ead) + Number(delta.ead);
            $(ead).text(totalEad).css(
                'color',
                totalEad === horarioNumero(previstaEad) ? 'green' : 'red'
            );
        }
    });
}

function horarioGravar(elemento, alteracaoSala) {
    var partes = elemento.id.split('_');
    var chave = partes[1] + '_' + partes[2] + '_' + partes[3];
    var posicao = partes[4] ? partes[4] : '1';

    var container = $('#c_' + chave);
    var mensagem = $('#m_' + chave);
    var oferta = $('#d_' + chave + '_' + posicao);
    var sala = $('#s_' + chave + '_' + posicao);

    var valorAntigo = typeof elemento.oldvalue !== 'undefined'
        ? elemento.oldvalue
        : '';

    if (horarioCelulasOcupadas[chave] || horarioCarregando) {
        elemento.value = valorAntigo;
        return;
    }

    if (alteracaoSala && oferta.val() === '') {
        elemento.oldvalue = elemento.value;
        return;
    }

    if (oferta.val() !== '' && !sala.val()) {
        elemento.value = valorAntigo;

        mensagem.html(horarioMensagem(
            'Preencha o campo sala de aula!',
            'warning'
        ));

        return;
    }

    var valores = (oferta.val() || '').split('_');
    var dados = horarioFiltros();

    dados.metodo = 'gravar';
    dados.id_turma = partes[1];
    dados.id_dia = partes[2];
    dados.id_hora = partes[3];
    dados.posicao = posicao;
    dados.id_oferta_disciplina = valores[0] || '';
    dados.id_sala = sala.val() || '';

    horarioCelulasOcupadas[chave] = true;
    horarioGravacoes++;

    container.find('select').prop('disabled', true);
    $('#btn_buscar').prop('disabled', true);
    $('#id_periodo, #turno').prop('disabled', true);

    horarioRequisicao(dados).done(function (json) {
        if (!json || typeof json.resultado === 'undefined') {
            elemento.value = valorAntigo;

            mensagem.html(horarioMensagem(
                'O servidor retornou uma resposta inesperada.',
                'warning'
            ));

            console.error('Resposta inesperada:', json);
            alert('Resposta inesperada do servidor. Consulte o console.');

            return;
        }

        mensagem.html(json.msg || horarioMensagem('', 'info'));

        if (json.resultado !== 'OK') {
            elemento.value = valorAntigo;

            if (json.erro_tecnico) {
                console.error(json.erro_tecnico);
                alert(json.erro_tecnico);
            }

            return;
        }

        horarioAtualizarContadores(json.deltas || {});
        container.html(json.celula);
        horarioInicializarSeletores(container);
    }).fail(function (xhr, textStatus, errorThrown) {
        elemento.value = valorAntigo;

        var json = xhr.responseJSON;

        if (!json && xhr.responseText) {
            try {
                json = JSON.parse(xhr.responseText);
            } catch (erroParse) {
                json = null;
            }
        }

        if (json && json.msg) {
            mensagem.html(json.msg);
        } else {
            mensagem.html(horarioMensagem(
                'Falha ao processar a gravação. HTTP ' +
                xhr.status + '. O detalhe técnico foi exibido no alerta.',
                'warning'
            ));
        }

        var detalhes = json && json.erro_tecnico
            ? json.erro_tecnico
            : (xhr.responseText || errorThrown || textStatus);

        console.error(
            'Erro na gravação do horário:',
            {
                http: xhr.status,
                status: textStatus,
                erro: errorThrown,
                resposta: xhr.responseText,
                dados: dados
            }
        );

        alert(
            'Erro ao gravar horário\n\n' +
            'HTTP: ' + xhr.status + '\n' +
            'Status: ' + textStatus + '\n\n' +
            detalhes
        );
    }).always(function () {
        delete horarioCelulasOcupadas[chave];
        horarioGravacoes--;

        container.find('select').prop('disabled', false);

        if (horarioGravacoes === 0) {
            $('#btn_buscar').prop('disabled', false);
            $('#id_periodo, #turno').prop('disabled', false);
        }
    });
}

function gravarOferta(oferta) {
    horarioGravar(oferta, false);
}

function gravarSala(sala) {
    horarioGravar(sala, true);
}