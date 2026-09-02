<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';
require_once $_SESSION['diretorio_base'] . '/controller/calendarioController.php';

class horario_disciplinaController {

    private $horarioM;
    private $periodoM;
    private $oferta_disciplinaM;

    public function __construct() {
        $this->horarioM = new horarioModel();
        $this->periodoM = new periodoModel();
        $this->oferta_disciplinaM = new oferta_disciplinaModel();
    }

    public function listar() {
      
        $tabela = '';
        
        // Exceção para tratar do ano 2022/2 onde o semestre começou em 2022 e terminou em 2023
        if ($_POST['id_periodo'] == 14) {
            for ($i = 8; $i <= 13; $i++) {
                if ($i == 13) {
                    $calendario = new calendarioController(1,2023,$_POST['id_oferta_disciplina']);
                    $tabela .= $calendario->exibe_calendario();
                } else {
                    $calendario = new calendarioController($i,2022,$_POST['id_oferta_disciplina']);
                    $tabela .= $calendario->exibe_calendario();
                }
            }
        
        } else if ($_POST['id_periodo'] == 20) {
            for ($i = 9; $i <= 14; $i++) {
                if ($i == 13) {
                    $calendario = new calendarioController(1,2025,$_POST['id_oferta_disciplina']);
                    $tabela .= $calendario->exibe_calendario();
                } else if ($i == 14) {
                    $calendario = new calendarioController(2,2025,$_POST['id_oferta_disciplina']);
                    $tabela .= $calendario->exibe_calendario();                    
                } else {
                    $calendario = new calendarioController($i,2024,$_POST['id_oferta_disciplina']);
                    $tabela .= $calendario->exibe_calendario();
                }
            }
            
        // Situação Padrão    
        } else {
            $result = $this->periodoM->getPeriodo($_POST['id_periodo']);
            $linha = mysqli_fetch_assoc($result);
            //print_r($linha);
            $vet_inicio = explode("-",$linha['data_inicio']);
            $vet_fim = explode("-",$linha['data_fim']);
            for ($i = ($vet_inicio[1]*1); $i <= ($vet_fim[1]*1); $i++) {
                $calendario = new calendarioController($i,$vet_inicio[0],$_POST['id_oferta_disciplina']);
                $tabela .= $calendario->exibe_calendario();                
            }
        }
        
        $result = $this->oferta_disciplinaM->getOfertaDisciplina($_POST['id_oferta_disciplina']);
        $linha = mysqli_fetch_assoc($result);
        
        $professor = $linha['nome'];
        $disciplina = $linha['descricao'];
        $cht = $linha['cht'];
        $chs = $linha['chs'];
        $resposta = array('tabela' => $tabela, 'chs' => $chs, 'cht' => number_format($cht, 2),'professor'=>$professor, 'disciplina'=>$disciplina);
        return json_encode($resposta);
    }

    public function carregarDisciplina() {

        $select = '';
        $disciplinaM = new oferta_disciplinaModel();
        $ordem = array('descricao' => 'ASC');

        $select .= "<option value=''>&nbsp;</option>";
        $result = $disciplinaM->getDisciplinasOfertadasPeriodo($_POST['id_periodo']);
        while ($linha = mysqli_fetch_assoc($result)) {
            $select .= "<option value='{$linha['id_oferta_disciplina']}'>";
            $select .= $linha['disciplina'] . ' ' . $linha['descricao'];
            $select .= '</option>';
        }
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%" onChange="carregarDisciplina()">';
        $periodoM = new periodoModel();
        $resultado_periodos = $periodoM->listar(array(), array('id_periodo' => 'DESC'));

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'] . '/' . $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new horario_disciplinaController();
    echo $objeto->$metodo();
}