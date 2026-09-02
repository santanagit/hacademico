var classe = 'horarioController';

$(document).ready(function () {
    carregarPeriodo();
    $('#btn_buscar').click(function () {
        getMoldura();
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
            getMoldura();
        });

    });
}

function getMoldura() {
    $('#metodo').val('getMoldura');
    var dados = $('#formulario').serialize();
    dados += "&periodo=" + encodeURIComponent($('#id_periodo option:selected').text());
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#moldura').html(json.moldura);

        $('select').each(function (index, value) {
            var id = $(this).attr('id');
            var valor = $('#' + id).val();
            var vetor = id.split('_');
            if (valor != '') {
                $('#m_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]).html('<span class="glyphicon glyphicon glyphicon-info-sign alert-info btn-sm" style="width:100%; text-align:center">&nbsp;</span>');
            }
        });
    });
}

function setDisciplina(oferta) {
    var id = oferta.id;
    var valor = oferta.value;
    var vetor = id.split('_');
    var valor = oferta.value;
    var vetor2 = valor.split('_');
    $("#disciplina_antiga").val(vetor[1] + '_' + vetor2[2]);
}

function gravarOferta(oferta) {

    var id = oferta.id;
    var valor = oferta.value;
    var vetor = id.split('_');
    var vetor2 = valor.split('_');
    var id_disciplina_antiga = $("#disciplina_antiga").val();
    var id_disciplina_nova = vetor[1] + '_' + vetor2[2];

    var sala = $('#s_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]);
    if (sala.val() == '') {
        alert('Preencha o campo sala de aula!');
        oferta.value = '';
    } else {

        $('#id_turma').val(vetor[1]);
        $('#id_dia').val(vetor[2]);
        $('#id_hora').val(vetor[3]);
        $('#id_oferta_disciplina').val(vetor2[0]);
        $('#id_usuario').val(vetor2[1]);
        $('#id_sala').val(sala.val());

        $('#metodo').val('existeChoque');
        var dados = $('#formulario').serialize();
        dados += "&periodo=" + encodeURIComponent($('#id_periodo option:selected').text());
        $.ajax({
            url: 'controller/' + classe + '.php',
            type: 'post',
            dataType: 'html',
            data: dados
        }).done(function (resposta) {
            var json = JSON.parse(resposta);
            if (json.resultado) {
                $('#m_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]).html(json.msg);
                $('#' + oferta.id).val('');
            } else {
                $('#metodo').val('gravar');
                var dados = $('#formulario').serialize();
                $.ajax({
                    url: 'controller/' + classe + '.php',
                    type: 'post',
                    dataType: 'html',
                    data: dados
                }).done(function (resposta) {
                    var json = JSON.parse(resposta);
                    $('#m_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]).html(json.msg);

                    //alert("ID Disciplina antiga: "+id_disciplina_antiga+"\nID Disciplina nova: "+id_disciplina_nova);
                    if (id_disciplina_nova !== id_disciplina_antiga) {
                        if (id_disciplina_antiga !== '') {
                            $('#' + id_disciplina_antiga).html($('#' + id_disciplina_antiga).html() - 1);

                            var chs_disciplina = parseInt($('#chs_disciplina_'+ id_disciplina_antiga).html());
                            if (($('#' + id_disciplina_antiga).html() * 1) == chs_disciplina) {
                                $('#' + id_disciplina_antiga).css({'color':'blue'});
                            } else {
                                $('#' + id_disciplina_antiga).css({'color':'red'});
                            }

                            if ($('#s_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3] + ' option:selected').text() === 'EAD') {
                                $('#ead_' + id_disciplina_antiga).html($('#ead_' + id_disciplina_antiga).html() - 1);
                            }
                            
                            var chs_ead_disciplina = parseInt($('#chs_ead_disciplina_'+ id_disciplina_antiga).html());
                            //alert(chs_ead_disciplina+' '+($('#ead_' + id_disciplina_antiga).html() * 1));
                            if ( ($('#ead_' + id_disciplina_antiga).html() * 1) == chs_ead_disciplina) {
                                $('#ead_' + id_disciplina_antiga).css({'color':'green'});
                            } else {
                                $('#ead_' + id_disciplina_antiga).css({'color':'red'});
                            }                            
                        }
                        if (id_disciplina_nova !== '') {
                            var chs = parseInt($('#' + id_disciplina_nova).html());
                            var chs_ead = parseInt($('#ead_' + id_disciplina_nova).html());
                            $('#' + id_disciplina_nova).html(chs + 1);
                            
                            var chs_disciplina = parseInt($('#chs_disciplina_'+ id_disciplina_nova).html());
                            if ((chs+1) == chs_disciplina) {
                                $('#' + id_disciplina_nova).css({'color':'blue'});
                            } else {
                                $('#' + id_disciplina_nova).css({'color':'red'});
                            }
                            
                            $("#disciplina_antiga").val(id_disciplina_nova);

                            if ($('#s_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3] + ' option:selected').text() === 'EAD') {
                                $('#ead_' + id_disciplina_nova).html(chs_ead + 1);
                            }
                            
                            var chs_ead_disciplina = parseInt($('#chs_ead_disciplina_'+ id_disciplina_nova).html());                            
                            if (($('#ead_' + id_disciplina_nova).html() * 1) == chs_ead_disciplina) {
                                $('#ead_' + id_disciplina_nova).css({'color':'green'});
                            } else {
                                $('#ead_' + id_disciplina_nova).css({'color':'red'});
                            }
                        } else {
                            $("#disciplina_antiga").val('');
                        }

                    }

                });
            }
        });

    }
}

function gravarSala(sala) {

    //console.log("Value is " + sala.value + "\n" + "Old Value is " + $('#id_sala_antiga').val());
    
    var id_sala_antiga = $('#id_sala_antiga').val();
    var id = sala.id;
    var valor = sala.value;
    var vetor = id.split('_');

    var oferta = $('#d_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]);
    //console.log('#d_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]);
    
    var vetor_oferta = oferta.val().split('_');
    //console.log(oferta.val());
    
    if (oferta.val() != '') {
        $('#metodo').val('gravar');

        $('#id_turma').val(vetor[1]);
        $('#id_dia').val(vetor[2]);
        $('#id_hora').val(vetor[3]);
        $('#id_oferta_disciplina').val(vetor_oferta[0]);
        $('#id_usuario').val(vetor_oferta[1]);
        $('#id_sala').val(valor);
        
        //console.log('Entrou no if: '+oferta.val());

        var dados = $('#formulario').serialize();
        $.ajax({
            url: 'controller/' + classe + '.php',
            type: 'post',
            dataType: 'html',
            data: dados
        }).done(function (resposta) {
            var json = JSON.parse(resposta);
            $('#m_' + vetor[1] + '_' + vetor[2] + '_' + vetor[3]).html(json.msg);

            //console.log('Sala selecionada:'+$('#' + id + ' option:selected').text()+" ID Sala Antiga: "+id_sala_antiga);

            if (($('#' + id + ' option:selected').text() === 'EAD') && (id_sala_antiga != 2)) {
                var chs_ead = parseInt($('#ead_' + vetor[1] + '_' + vetor_oferta[2]).html());
                $('#ead_' + vetor[1] + '_' + vetor_oferta[2]).html(chs_ead + 1);
            } else if (($('#' + id + ' option:selected').text() !== 'EAD') && (id_sala_antiga == 2)) {
                $('#ead_' + vetor[1] + '_' + vetor_oferta[2]).html($('#ead_' + vetor[1] + '_' + vetor_oferta[2]).html() - 1);
            }
            
            //alert(sala.getAttribute('id_sala_antiga'));
            sala.setAttribute('id_sala_antiga',sala.value);  
            //alert(sala.getAttribute('id_sala_antiga'));
            
            if ($('#ead_' + vetor[1] + '_' + vetor_oferta[2]).html() == $('#chs_ead_disciplina_' + vetor[1] + '_' + vetor_oferta[2]).html()) {
                $('#ead_' + vetor[1] + '_' + vetor_oferta[2]).css({'color':'green'});
            } else {
                $('#ead_' + vetor[1] + '_' + vetor_oferta[2]).css({'color':'red'});
            }
            
        });
    }
    
}