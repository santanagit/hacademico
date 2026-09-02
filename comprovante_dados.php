<?php
require_once('controller/sessao.php');
sessao::validar(array('Coordenador de Ensino', 'Coordenador de Curso'));

require_once $_SESSION['diretorio_base'] . '/model/comprovanteModel.php';

$msg = '';
$script = '';
if (isset($_POST['id_comprovante'])) {
    
    $comprovanteM = new comprovanteModel();
    if ($_POST['id_comprovante'] == '') {
    
        $id_comprovante = 0;
        $res = $comprovanteM->inserir($_POST);
        if ($res) {
            $id_comprovante = $res;
            $msg = '<div class="alert alert-success">';
            $msg .= 'Comprovante cadastrado com sucesso!';
            $msg .= '<a href="download.php?id_comprovante='.$id_comprovante.'" target="_blank" style="color:blue">';
            $msg .= '<span class="glyphicon glyphicon-download-alt"></span>';
            $msg .= '</a>';        
            $msg .= '</div>';

        } else {
            $msg .= '<div class="alert alert-danger">';
            $msg .= 'Erro ao inserir - Contactar o administrador do sistema';
            $msg .= '</div>';
        }
    } else {
        
        $res = $comprovanteM->atualizar($_POST);
        if ($res) {
            header('Location:comprovante.php');

        } else {
            $msg .= '<div class="alert alert-danger">';
            $msg .= 'Erro ao inserir - Contactar o administrador do sistema';
            $msg .= '</div>';
        }        
    }
} else if (isset($_GET['id_comprovante'])) {
    $comprovanteM = new comprovanteModel();
    $result_comprovante = $comprovanteM->getComprovante($_GET['id_comprovante']);
    $linha_comprovante = mysqli_fetch_assoc($result_comprovante);
    $script = "<script>";
    $script .= "document.forms[0].id_comprovante.value = '{$linha_comprovante['id_comprovante']}';";
    $script .= "document.forms[0].descricao.value = '{$linha_comprovante['descricao']}';";
    $script .= "document.forms[0].inicio_vigencia.value = '{$linha_comprovante['inicio_vigencia']}';";
    $script .= "document.forms[0].fim_vigencia.value = '{$linha_comprovante['fim_vigencia']}';";
    $script .= "</script>";
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

        <form id="formulario" method="post" action="comprovante_dados.php" enctype="multipart/form-data">

            <!-- Alterar aqui o ID da tabela -->
            <input type="hidden" name="id_comprovante" id="id_comprovante">

            <div class="container">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">Adicionar comprovante</div>
                        <div class="panel-body">    

                            <div id="msg"><?= $msg ?>
                            <?php
                                if (isset($_FILES)) {
                                    echo '<pre>';
                                    print_r($_FILES);
                                    echo '</pre>';
                                }
                            ?>
                            </div>

                            <div class="form-group">
                                <label for="arquivo">Documento(PDF):</label>
                                <input type="file" accept=".pdf" class="form-control" name="arquivo" id="arquivo" required onChange="preenche_descricao(this)">
                            </div>                                 
                            <div class="form-group">
                                <label for="descricao">Descrição:</label>
                                <input type="text" class="form-control" name="descricao" id="descricao" required>
                            </div>                            
                            <div class="form-group">
                                <label for="inicio_vigencia">Início da vigência:</label>
                                <input type="date" class="form-control" name="inicio_vigencia" id="inicio_vigencia">
                            </div>
                            <div class="form-group">
                                <label for="fim_vigencia">Fim da vigência</label>
                                <input type="date" class="form-control" name="fim_vigencia" id="fim_vigencia">
                            </div>                             
                        </div>
                        <div class="form-group text-center">
                            <button type="button" class="btn btn-danger form-inline" onclick="location.href = 'comprovante.php'">Voltar</button>
                            <input type="submit" class="btn btn-success form-inline" id="button_gravar" value="Gravar">
                        </div>
                    </div>
                </div>       
            </div>
        </div>
    </form>
</body>
<?=$script?>
</html>