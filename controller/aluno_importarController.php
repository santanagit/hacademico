<?php

session_start();

require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/cursoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class aluno_importarController {

    private $msg;

    public function __construct() {
        $this->msg = '';
    }

    public function carregarCurso() {
        $select = '<label for="id_curso">Curso:</label>';
        $select .= '<select id="id_curso" name="id_curso" class="form-control" style="width:100%">';
        $cursoM = new cursoModel();
        $resultado_periodos = $cursoM->listar(array(), array('nome' => 'ASC'));

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_curso']}'>";
            $select .= $linha['nome'] . " (" . $linha['matriz'] . ")";
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function tirarAcentos($string) {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
    }

    public function importar() {

        $tabela = '';
        $usuarioM = new usuarioModel();
        $linha = 0;
        $posicao = array();

        $handle = fopen($_FILES['arquivo']['tmp_name'], 'r');
        while (($data = fgetcsv($handle, 0, ";") ) !== FALSE) {

            // Cria um vetor associativo cujo os índices são os campos da
            // primeira linha do arquivo csv
            if ($linha == 0) {
                foreach ($data as $key => $value) {
                    $posicao[$key] = str_replace('-', '', strtolower($this->tirarAcentos(utf8_encode($value))));
                }
            } else if (trim($data[0]) != '') {
                $campos = array();
                for ($i = 0; $i < count($posicao); $i++) {
                    $campos[$posicao[$i]] = $data[$i];
                }
                $campos['id_curso'] = $_POST['id_curso'];
                if ($usuarioM->importar_aluno($campos)) {
                    $tabela .= '<div class="alert alert-success">';
                    $tabela .= 'O aluno '.utf8_encode($campos['nome']).' foi importado com sucesso!';
                    $tabela .= '</div>';
                } else {
                    $tabela .= '<div class="alert alert-danger">';
                    $tabela .= 'O aluno '.utf8_encode($campos['nome']).' já está cadastrado no sistema!';
                    $tabela .= '</div>';
                }
            }
            $linha++;
        }


        //$resposta = array('tabela' => $tabela);
        return $tabela;
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new aluno_importarController();
    echo $objeto->$metodo();
}