var classe = 'aluno_importarController';

$(document).ready(function () {

    carregarCurso();
    
    $('#btn_importar').click(function () {        
        importar();
    });    
 

});

function carregarCurso() {
    $('#metodo').val('carregarCurso');
    var dados = $('#formulario').serialize();
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: dados
    }).done(function (resposta) {
        var json = JSON.parse(resposta);
        $('#div_curso').html(json.select);
        
    });
}

function importar() {
    $('#metodo').val('importar');
    //var dados = $('#formulario').serialize();
    var formData = new FormData();
    formData.append('arquivo', $('#arquivo')[0].files[0]);
    formData.append('id_curso',$('#id_curso').val());
    formData.append('metodo',$('#metodo').val());
    $.ajax({
        url: 'controller/' + classe + '.php',
        type: 'post',
        dataType: 'html',
        data: formData,
        processData: false,  // tell jQuery not to process the data
        contentType: false,  // tell jQuery not to set contentType        
    }).done(function (resposta) {
        //var json = JSON.parse(resposta);
        $('#tabela').html(resposta);
    });
}  