<?php

require_once('controller/sessao.php');
require_once $_SESSION['diretorio_base'] . '/model/comprovanteModel.php';
sessao::validar(array('Professor'));

$msg = '';
$acao = '';
$id_pid = $_SESSION['id_pid'];
$id_comprovante = '';
$descricao = '';

$comprovanteM = new comprovanteModel();
$result = $comprovanteM->getComprovanteDisciplinas($id_pid);
if (mysqli_num_rows($result) > 0) {
    $linha = mysqli_fetch_assoc($result);
    $id_comprovante = $linha['id_comprovante'];
    $descricao = $linha['descricao'];
    $acao = 'atualizar';
} else {
    $acao = 'inserir';
}

if (isset($_POST['descricao'])) {
    if ($acao == 'inserir') {
        $res = $comprovanteM->inserirComprovanteDisciplinas($_POST);
        if ($res) {
            $id_comprovante = $res;
            $descricao = $_POST['descricao'];
            $msg = '<div class="alert alert-success">';
            $msg .= 'Comprovante cadastrado com sucesso!';
            $msg .= '</div>';
            $acao = 'atualizar';
        } else {
            $msg .= '<div class="alert alert-danger">';
            $msg .= 'Erro ao inserir - Contactar o administrador do sistema';
            $msg .= '</div>';
        }
    } else {
        $res = $comprovanteM->atualizarComprovanteDisciplinas($_POST);
        if ($res) {
            $descricao = $_POST['descricao'];
            $msg = '<div class="alert alert-success">';
            $msg .= 'Comprovante atualizado com sucesso!';
            $msg .= '</div>';
        } else {
            $msg .= '<div class="alert alert-danger">';
            $msg .= 'Erro ao inserir - Contactar o administrador do sistema';
            $msg .= '</div>';
        }        
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Horário Acadêmico">
        <meta name="author" content="Antonio Rafael Santana">

        <title>hAcademico</title>

        <!-- Bootstrap CSS file -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <!-- Jquery and Bootstrap Script files -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

        <!-- Others JS Files -->
        <script src="js/jquery.mask.min.js"></script>
        <script>
            
            function replaceAll(string, search, replace) {
                return string.split(search).join(replace);
            }
            function preenche_descricao(obj) {
                var path = String(obj.value)
                var path = replaceAll(path, "\\", '/')
                var partes = path.split("/");
                //alert(partes[partes.length - 1]);
                path = String(partes[partes.length - 1])
                partes = path.split(".");
                //alert(partes[0]);
                document.forms[0].descricao.value = partes[0];
            }
        </script>        
    </head>
    <body>

        <?php include $_SESSION['topo']; ?>

        <form id="formulario" method="post" action="comprovante_disciplinas.php" enctype="multipart/form-data">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_comprovante" id="id_comprovante" value="<?=$id_comprovante?>">
            <input type="hidden" name="id_pid" id="id_pid" value="<?=$id_pid?>">
            <input type="hidden" name="acao" id="acao" value="<?=$acao?>">

            <div class="container">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">Adicionar comprovante das disciplinas ministradas</div>
                        <div class="panel-body">    
                            
                            <?php
                            $no_cache = random_int(1, 10000);
                            if ($acao == 'atualizar') {
                                echo '<iframe id="myframe" src="http://'.$_SERVER['HTTP_HOST'].'/hacademico/comprovantes/comprovante_'.$id_comprovante.'.pdf?nc='.$no_cache.'" width="100%" height="500" frameborder="0" style="border:0"></iframe>';
                            }
                            ?>                            
                            <div id="msg"><?= $msg ?></div>

                            <div class="alert alert-warning" style="text-align:justify">
                                <b>Orientações:</b><br><br>
                                <ul>
                                    <li>Fazer o upload do arquivo de comprovação de disciplinas ministradas do SIGAA.</li>
                                    <li>Este comprovante já servirá como comprovação de todas disciplinas e das atividades de Atividades de Preparação e Manutenção do Ensino.</li>
                                    <li>Os comprovantes devem estar em formato PDF.</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <label for="arquivo">Documento(PDF):</label>
                                <input type="file" accept=".pdf" class="form-control" name="arquivo" id="arquivo" required onChange="preenche_descricao(this)">
                            </div>                                 
                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <input type="text" class="form-control" name="descricao" id="descricao" required value="<?=$descricao?>">
                            </div>                            
                        </div>
                        <div class="form-group text-center">
                            <button type="button" class="btn btn-danger form-inline" onclick="location.href='rid_professor.php'">Voltar</button>
                            <input type="submit" class="btn btn-success form-inline" id="button_gravar" value="Gravar">
                        </div>
                    </div>
                </div>       
            </div>
        </div>
    </form>
</body>
</html>