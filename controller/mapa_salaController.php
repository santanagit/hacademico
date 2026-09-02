<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/salaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class mapa_salaController {

    private $msg;
    private $logM;

    public function __construct() {
        $this->msg = '';
    }

    public function getSalas() {

        $options = '';
        $salaM = new salaModel();
        $result = $salaM->listar();
        if ($result) {
            while ($linha = $result->fetch_assoc()) {
                $options .= '<option value="' . $linha['id_sala'] . '">';
                $options .= $linha['descricao'];
                $options .= '</option>';
            }
        }

        $resposta = array('options' => $options);
        return json_encode($resposta);
    }

    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%">';
        $periodoM = new periodoModel();

        if ($_SESSION['perfil'] == 'Professor') {
            $resultado_periodos = $periodoM->listar(array("publicado"=>1), array('id_periodo'=>'DESC'));
        } else {
            $resultado_periodos = $periodoM->listar(array(), array('id_periodo'=>'DESC'));
        }

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'].'/'.$linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }    

    public function listar() {

        $periodo = explode("/",$_POST['periodo']);
        $semestre = $periodo[1];        

        $horarioM = new horarioModel();
        $result = $horarioM->mapa_sala($_POST['id_sala'],$_POST['id_periodo'],$semestre);
        $salaAnterior = '';
        $diaAnterior = '';
        $horaAnterior = 0;
        $i = 2;
        $tabela = '';
        $clinha = 0;

        // Array para armazenar todas as disciplinas de cada célula (dia + hora)
        $celulas = array();

        if (mysqli_num_rows($result) > 0) {

            // Primeiro loop: agrupa todas as disciplinas por sala, dia e hora
            while ($linha = mysqli_fetch_assoc($result)) {
                $chave = $linha['sala'] . '|' . $linha['id_dia'] . '|' . $linha['id_hora'];

                if (!isset($celulas[$chave])) {
                    $celulas[$chave] = array(
                        'sala' => $linha['sala'],
                        'id_dia' => $linha['id_dia'],
                        'id_hora' => $linha['id_hora'],
                        'horario' => $linha['horario'],
                        'cor' => $linha['cor'],
                        'disciplinas' => array()
                    );
                }

                $celulas[$chave]['disciplinas'][] = array(
                    'disciplina' => $linha['disciplina'],
                    'professor' => $linha['professor'],
                    'turma' => $linha['turma']
                );
            }

            // Segundo loop: monta a tabela com as células agrupadas
            $salaAnterior = '';
            $horaAnterior = 0;
            $i = 2;
            $clinha = 0;

            foreach ($celulas as $chave => $celula) {

                if ($celula['sala'] != $salaAnterior) {

                    if ($salaAnterior != '') {
                        // Completa a última linha se necessário
                        while (($i <= 6) && ($i > 2)) {
                            $tabela .= '<td></td>' . "\n";
                            $i++;
                        }
                        if ($i > 2) {
                            $tabela .= '</tr>' . "\n";
                        }
                        $tabela .= '</tbody>' . "\n";
                        $tabela .= '</table>' . "\n";
                    }

                    $salaAnterior = $celula['sala'];
                    $horaAnterior = 0;
                    $i = 2;
                    $clinha = 0;

                    $tabela .= '<table class="table table-bordered">' . "\n";
                    $tabela .= '<thead>' . "\n";
                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<th colspan="6" style="background-color:#D9EDF7 !important"><center>' . $celula['sala'] . '</center></th>' . "\n";
                    $tabela .= '</tr>' . "\n";
                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<th width="10%" style="background-color:#DFF0D8 !important">Horário</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Segunda</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Terça</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Quarta</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Quinta</th>' . "\n";
                    $tabela .= '<th width="18%" style="background-color:#DFF0D8 !important">Sexta</th>' . "\n";
                    $tabela .= '</tr>' . "\n";
                    $tabela .= '</thead>' . "\n";
                    $tabela .= '<tbody>' . "\n";
                }

                // Verifica se mudou de hora
                if ($horaAnterior != $celula['id_hora']) {

                    // Completa células vazias da linha anterior
                    if ($horaAnterior != 0) {
                        while ($i <= 6) {
                            $tabela .= '<td></td>' . "\n";
                            $i++;
                        }
                        $tabela .= '</tr>' . "\n";
                    }

                    $clinha++;

                    // Verifica se precisa inserir intervalo
                    if (
                        (($celula['id_hora'] == 9) || 
                        ($celula['id_hora'] == 13) || 
                        ($celula['id_hora'] == 15) || 
                        ($celula['id_hora'] == 18) || 
                        ($celula['id_hora'] == 20)) && ($clinha > 1)
                    ){
                        $tabela .= '<tr align="center">' . "\n";
                        $tabela .= '<td colspan="6"><b>Intervalo</b></td>' . "\n";
                        $tabela .= '</tr>' . "\n";
                    }

                    $tabela .= '<tr>' . "\n";
                    $tabela .= '<td><b>' . $celula['horario'] . '</b></td>' . "\n";
                    $horaAnterior = $celula['id_hora'];
                    $i = 2;
                }

                // Preenche células vazias até o dia atual
                while ($i < $celula['id_dia']) {
                    $tabela .= '<td></td>' . "\n";
                    $i++;
                }

                // Monta o conteúdo da célula
                $tabela .= "<td title='{$celula['id_hora']}' style='background-color:{$celula['cor']} !important'>";

                // Se houver mais de uma disciplina, há choque
                if (count($celula['disciplinas']) > 1) {
                    $tabela .= '<div style="color:red; text-align:center; font-weight:bold !important; margin-bottom:10px;">Choque de sala de aula</div>';
                }

                // Adiciona todas as disciplinas na mesma célula
                foreach ($celula['disciplinas'] as $disc) {
                    $tabela .= '<div style="color:blue; font-weight:bold !important">' . $disc['disciplina'] . '</div>';
                    $tabela .= '<div style="color:green !important">' . $disc['professor'] . '</div>';
                    $tabela .= '<div style="color:brown !important">' . $disc['turma'] . '</div>';

                    // Se houver mais de uma disciplina, adiciona separador
                    if (count($celula['disciplinas']) > 1) {
                        $tabela .= '<hr style="margin:5px 0; border-top:1px dashed #ccc;">';
                    }
                }

                $tabela .= '</td>' . "\n";
                $i++;
            }

            // Finaliza a última tabela
            while (($i <= 6) && ($i > 2)) {
                $tabela .= '<td></td>' . "\n";
                $i++;
            }
            if ($i > 2) {
                $tabela .= '</tr>' . "\n";
            }
            $tabela .= '</tbody>' . "\n";
            $tabela .= '</table>' . "\n";
        }

        $this->logM = new log_acaoModel();
        $this->logM->inserir(array('id_usuario'=>$_SESSION['id_usuario'],'acao'=>'Consulta Mapa Sala','data_hora'=>date('Y-m-d H:i:s')));

        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new mapa_salaController();
    echo $objeto->$metodo();
}
