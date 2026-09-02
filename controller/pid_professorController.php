<?php

session_start();
require_once $_SESSION['diretorio_base'] . '/model/atividade_docenteModel.php';
require_once $_SESSION['diretorio_base'] . '/model/tipo_atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/historico_atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/historico_pidModel.php';
require_once $_SESSION['diretorio_base'] . '/model/atividadeModel.php';
require_once $_SESSION['diretorio_base'] . '/model/oferta_disciplinaModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/pidModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';

class pid_professorController {

    private $atividade_docenteM;
    private $tipo_atividadeM;
    private $historico_atividadeM;
    private $historico_pidM;
    private $atividadeM;
    private $oferta_disciplinaM;
    private $usuarioM;
    private $periodoM;
    private $pidM;
    private $msg;

    public function __construct() {
        $this->atividade_docenteM = new atividade_docenteModel();
        $this->tipo_atividadeM = new tipo_atividadeModel();
        $this->historico_atividadeM = new historico_atividadeModel();
        $this->historico_pidM = new historico_pidModel();
        $this->oferta_disciplinaM = new oferta_disciplinaModel();
        $this->periodoM = new periodoModel();
        $this->usuarioM = new usuarioModel();
        $this->atividadeM = new atividadeModel();
        $this->pidM = new pidModel();
        $this->msg = '';
    }

    public function cadastrar_atividades_pid($id_pid) {

        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);
        $semestre = $linha_periodo['semestre'];

        $result = $this->atividade_docenteM->listar($id_pid);
        if (mysqli_num_rows($result) == 0) {

            $result_oferta_disciplina = $this->oferta_disciplinaM
                    ->getDisciplinasOfertadasPeriodoProfessor(
                    $_POST['id_periodo'],
                    $_SESSION['id_usuario'],
                    $semestre
            );
            while ($linha_oferta_disciplina = mysqli_fetch_assoc($result_oferta_disciplina)) {
                $campos = array();
                $campos['etapa'] = 'PID';
                $campos['descricao'] = $linha_oferta_disciplina['disciplina'];
                $campos['horas_planejadas'] = $linha_oferta_disciplina['chs'];
                $campos['id_pid'] = $id_pid;
                $campos['id_comprovante'] = '';
                $campos['observacao'] = 'Atividade inserida pelo sistema';
                if ($linha_oferta_disciplina['tipo'] == 'Aula') {
                    $campos['id_atividade'] = 1;
                } else {
                    $campos['id_atividade'] = 2;
                }
                $this->atividade_docenteM->inserir_atividade_pid($campos);
            }

            $result_atividade_associada = $this->atividade_docenteM
                    ->getAtividadesAssociadas($linha_periodo['data_inicio'], $_SESSION['id_usuario']);
            while ($linha_atividade_associada = mysqli_fetch_assoc($result_atividade_associada)) {
                $campos = array();
                $campos['etapa'] = 'PID';
                $campos['descricao'] = $linha_atividade_associada['comprovante'];
                $campos['horas_planejadas'] = $linha_atividade_associada['horas'];
                $campos['id_pid'] = $id_pid;
                $campos['id_atividade'] = $linha_atividade_associada['id_atividade'];
                $campos['id_comprovante'] = $linha_atividade_associada['id_comprovante'];
                $campos['observacao'] = 'Atividade inserida pelo sistema';
                $this->atividade_docenteM->inserir_atividade_pid($campos);
            }
        }
    }

    public function listar() {

        $tabela = '';
        $id_pid = '';
        $css_horas_planejadas = '';
        $css_horas_planejadas_on = 'style="width:80px;text-align:center"';
        $css_horas_planejadas_off = 'readonly style="width:80px;text-align:center;box-shadow:0 0 0 0;border:0 none;outline:0;background-color:inherit"';

        $result_usuario = $this->usuarioM->getUsuarioId($_SESSION['id_usuario']);
        $linha_usuario = mysqli_fetch_assoc($result_usuario);

        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);

        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Informações sobre o preenchimento</div>';
        $tabela .= '<div class="panel panel-body">';
        $tabela .= '<div class="container-fluid">';

        $data_inicio = strtotime($linha_periodo['pid_inicio']);
        $data_fim = strtotime($linha_periodo['pid_fim']);
        $data_atual = strtotime(date("Y-m-d"));

        $result_pid = $this->pidM->getPidPeriodoProfessor($_POST['id_periodo'], $_SESSION['id_usuario']);
        $result_historico_pid = null;
        $linha_historico_pid = null;

        if (mysqli_num_rows($result_pid) > 0) {

            $linha_pid = mysqli_fetch_assoc($result_pid);
            $id_pid = $linha_pid['id_pid'];

            $result_historico_pid = $this->historico_pidM->getSituacao($id_pid, 'PID');
            $linha_historico_pid = mysqli_fetch_assoc($result_historico_pid);

            if ($linha_historico_pid['situacao'] == 'AGUARDANDO ENVIO') {
                $tabela .= '<div class="alert alert-warning text-center">' . $linha_historico_pid['situacao'] . '</div>';
                $css_horas_planejadas = $css_horas_planejadas_on;
            } else if ($linha_historico_pid['situacao'] == 'ENVIADO') {
                $tabela .= '<div class="alert alert-info text-center">' . $linha_historico_pid['situacao'] . '</div>';
                $css_horas_planejadas = $css_horas_planejadas_off;
            } else if ($linha_historico_pid['situacao'] == 'APROVADO') {
                $tabela .= '<div class="alert alert-success text-center">' . $linha_historico_pid['situacao'] . '</div>';
                $css_horas_planejadas = $css_horas_planejadas_off;
            } else if ($linha_historico_pid['situacao'] == 'REPROVADO') {
                $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_pid['situacao'] . '</div>';
                $css_horas_planejadas = $css_horas_planejadas_off;
            } else if ($linha_historico_pid['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                $css_horas_planejadas = $css_horas_planejadas_on;
                $tabela .= '<div class="alert alert-danger text-center" style="font-size:18px">' . $linha_historico_pid['situacao'];
                $tabela .= '<br>As atividades com o símbolo: ';
                $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red;padding:8px" title="Atividade reprovada"></span>';
                $tabela .= ' devem ser corrigidas clicando no ícone: ';
                $tabela .= '<span class="glyphicon glyphicon-edit" style="color:green;padding:8px" title="Atividade reprovada"></span>';
                $tabela .= '</div>';
            } else {
                $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_pid['situacao'] . '</div>';
                $css_horas_planejadas = $css_horas_planejadas_off;
            }

            $tabela .= '<table class="table table-bordered">';
            $tabela .= '<tr>';
            $tabela .= '<td class="col-sm-6 text-center">Período de preenchimento:<b> ' . $linha_periodo['pid_inicio_formatado'] . ' à ' . $linha_periodo['pid_fim_formatado'] . '</b></td>';
            $tabela .= '<td class="col-sm-6 text-center">Data atual:<b> ' . date("d/m/Y") . '</b></td>';
            $tabela .= '</tr>';

            $this->cadastrar_atividades_pid($id_pid);
        } else {

            $tabela .= '<table class="table table-bordered">';
            $tabela .= '<tr>';
            $tabela .= '<td class="col-sm-6 text-center">Período de preenchimento:<b> ' . $linha_periodo['pid_inicio_formatado'] . ' à ' . $linha_periodo['pid_fim_formatado'] . '</b></td>';
            $tabela .= '<td class="col-sm-6 text-center">Data atual:<b> ' . date("d/m/Y") . '</b></td>';
            $tabela .= '</tr>';

            if ($data_atual > $data_fim) {

                $tabela .= '<tr>';
                $tabela .= '<td colspan="2" class="alert alert-warning text-center">O PID desse semestre não foi cadastrado no sistema! '
                        . '<br> Para acessá-lo acesse o link: '
                        . '<a href="https://www.ifsudestemg.edu.br/documentos-institucionais/unidades/bomsucesso/pid-e-rid/pid">'
                        . 'PID - Campus Avançado Bom Sucesso</a></td>';
                $tabela .= '</tr>';
                $tabela .= '</table>';
                $tabela .= '</div>';
                $tabela .= '</div>';
                $tabela .= '</div>';

                return json_encode(array('tabela' => $tabela));
            } else if ($data_atual < $data_inicio) {

                $tabela .= '<tr>';
                $tabela .= '<td colspan="2" class="alert alert-warning text-center" style="font-size:15px">';
                $numDays = abs($data_inicio - $data_atual) / 60 / 60 / 24;
                $tabela .= 'Faltam <b>' . $numDays . ' dias</b> para entrar no período de preenchimento do PID ' . $linha_periodo['ano'] . '/' . $linha_periodo['semestre'] . ' !!!';
                $tabela .= '</td>';
                $tabela .= '</tr>';
                $tabela .= '</table>';
                $tabela .= '</div>';
                $tabela .= '</div>';
                $tabela .= '</div>';

                return json_encode(array('tabela' => $tabela));
            } else if (($data_atual >= $data_inicio) && ($data_atual <= $data_fim)) {

                $tabela .= '<tr>';
                $tabela .= '<td colspan="2" class="alert alert-success text-center" style="font-size:15px">';
                $tabela .= 'Aberto para preenchimento!';
                $tabela .= '</td>';
                $tabela .= '</tr>';

                $campos['id_periodo'] = $_POST['id_periodo'];
                $campos['id_usuario'] = $_SESSION['id_usuario'];

                $id_pid = $this->pidM->inserir($campos);
                $this->cadastrar_atividades_pid($id_pid);

                $result_historico_pid = $this->historico_pidM->getSituacao($id_pid, 'PID');
                $linha_historico_pid = mysqli_fetch_assoc($result_historico_pid);

                if ($linha_historico_pid['situacao'] == 'AGUARDANDO ENVIO') {
                    $tabela .= '<div class="alert alert-warning text-center">' . $linha_historico_pid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_on;
                } else if ($linha_historico_pid['situacao'] == 'ENVIADO') {
                    $tabela .= '<div class="alert alert-info text-center">' . $linha_historico_pid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_pid['situacao'] == 'APROVADO') {
                    $tabela .= '<div class="alert alert-success text-center">' . $linha_historico_pid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_pid['situacao'] == 'REPROVADO') {
                    $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_pid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_pid['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                    $css_horas_planejadas = $css_horas_planejadas_on;
                    $tabela .= '<div class="alert alert-danger text-center" style="font-size:18px">' . $linha_historico_pid['situacao'];
                    $tabela .= '<br>As atividades com o símbolo: ';
                    $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red;padding:8px" title="Atividade reprovada"></span>';
                    $tabela .= ' devem ser corrigidas clicando no ícone: ';
                    $tabela .= '<span class="glyphicon glyphicon-edit" style="color:green;padding:8px" title="Atividade reprovada"></span>';
                    $tabela .= '</div>';
                } else {
                    $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_pid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                }
            } else {

                $tabela .= '<tr>';
                $tabela .= '<td colspan="2" class="alert alert-danger text-center" style="font-size:15px">';
                $tabela .= 'Período inválido !!!';
                $tabela .= '</td>';
                $tabela .= '</tr>';
                $tabela .= '</table>';
                $tabela .= '</div>';
                $tabela .= '</div>';
                $tabela .= '</div>';

                return json_encode(array('tabela' => $tabela));
            }
        }

        if (
                ($linha_historico_pid['situacao'] == "AGUARDANDO ENVIO") ||
                ($linha_historico_pid['situacao'] == "RETORNADO PARA CORREÇÃO")
        ) {
            $tabela .= '<tr>';
            $tabela .= '<td colspan="4" class="alert alert-warning">';

            $tabela .= '<h5><b>Observações:</b></h5>
                    <ul>
                    <li>As disciplinas atribuídas para os professores no semestre anterior são automaticamente cadastradas no PID.</li>
                    <li>Cada atividade inserida ou alterada é automaticamente gravada.</li>
                    <li>O PID somente é <strong>enviado</strong> para avaliação após o docente clicar no botão <b>Enviar PID</b>.</li>
                    <li>A carga horária semanal de cada atividade é obtida pela fórmula: <b>Total de horas da atividade no semestre ÷ 20</b> (20 semanas é o padrão do semestre letivo).</li>
                    <li>Todas as atividades declaradas devem ser comprovadas no RID com documentos oficiais.</li>
                    </ul>
                    ';
            $tabela .= '</td>';
            $tabela .= '</tr>';
        }
        $tabela .= '</table>';
        $tabela .= '</div>';
        $tabela .= '</div>';
        $tabela .= '</div>';

        $tabela .= '<input type="hidden" name="id_pid" id="id_pid" value="' . $id_pid . '">';

        // Dados do docente
        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Dados do docente</div>';
        $tabela .= '<div class="panel panel-body">';
        $tabela .= '<div class="container-fluid">';
        $tabela .= '<table class="table table-bordered">';

        $tabela .= '<tr>';
        $tabela .= '<th class="col-sm-2">Docente:</th>';
        $tabela .= '<td class="col-sm-4">' . $linha_usuario['nome'] . '</td>';
        $tabela .= '<th class="col-sm-2">SIAPE:</th>';
        $tabela .= '<td class="col-sm-4">' . $linha_usuario['matricula'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Departamento:</th>';
        $tabela .= '<td>Ensino</td>';
        $tabela .= '<th>Núcleo:</th>';
        $tabela .= '<td>' . $linha_usuario['area'] . '</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Ano/Semestre:</th>';
        $tabela .= '<td>' . $linha_periodo['ano'] . '/' . $linha_periodo['semestre'] . '</td>';
        $tabela .= '<th>Regime de trabalho:</th>';
        $tabela .= '<td>40h - DE</td>';
        $tabela .= '</tr>';

        $tabela .= '<tr>';
        $tabela .= '<th>Email:</th>';
        $tabela .= '<td colspan="3">' . $linha_usuario['email'] . '</td>';
        $tabela .= '</tr>';
        $tabela .= '</table>';
        $tabela .= '</div>';
        $tabela .= '</div>';
        $tabela .= '</div>';

        if ($id_pid != '') {

            $grupos = array();

            $ordenacao = array('id_tipo_atividade' => 'ASC');
            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), $ordenacao);

            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

                $id_tipo_atividade = $linha_tipo_atividade['id_tipo_atividade'];
                $descricao = $linha_tipo_atividade['descricao'];

                if ($id_tipo_atividade != 2) {

                    $tabela .= '<div class="panel panel-info" id="painel_atividade_' . $id_tipo_atividade . '">';
                    $tabela .= '<div class="panel panel-heading">' . $descricao . '</div>';
                    $tabela .= '<div class="panel panel-body">';

                    // orientações (mantidas) ...

                    if (
                            ($linha_historico_pid['situacao'] == 'AGUARDANDO ENVIO') ||
                            ($linha_historico_pid['situacao'] == 'RETORNADO PARA CORREÇÃO')
                    ) {
                        $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_adicionar_atividade" style="width:170px;text-align:center;margin-bottom:20px" onClick="abrirModal(' . "'modal_formulario', 'inserir_atividade_pid', 0, $id_tipo_atividade)" . '">';
                        $tabela .= '<span class="glyphicon glyphicon-plus"></span> Adicionar atividade';
                        $tabela .= '</button>';
                    }

                    $tabela .= '<div id="msg_' . $id_tipo_atividade . '"></div>';
                    $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_' . $id_tipo_atividade . '">';
                    $tabela .= '<thead>';
                    $tabela .= '<tr>';
                    $tabela .= '<th style="width:44%">Atividade</th>';
                    $tabela .= '<th style="width:40%">Descrição</th>';
                    $tabela .= '<th style="width:10%;text-align:center">CHS</th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '</tr>';
                    $tabela .= '</thead>';
                    $tabela .= '<tbody>';

                    $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                    $parametros = array('atividade.id_tipo_atividade' => $id_tipo_atividade);
                    $result_atividade_docente = $this->atividade_docenteM->listar_atividades_pid($id_pid, $parametros, $ordenacao);

                    $soma_grupo = 0;
                    while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                        $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'PID');
                        $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);

                        $tabela .= '<tr>';
                        $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                        $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';

                        if (($linha_historico_atividade['situacao'] != 'CANCELADA') && ($linha_historico_atividade['situacao'] != 'REPROVADA')) {
                            $tabela .= '<td align="center"><input data-mask="99.99" class="form-control" ' . $css_horas_planejadas . ' type="text" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '" onChange="atualizar_chs(' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')"></td>';
                        } else {
                            $tabela .= '<td align="center"><input class="form-control" readonly style="width:80px;text-align:center;border:0px;background-color:#FF6347" type="text" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '"></td>';
                        }

                        if (
                                ($linha_historico_pid['situacao'] == 'AGUARDANDO ENVIO') ||
                                ($linha_historico_pid['situacao'] == 'RETORNADO PARA CORREÇÃO')
                        ) {
                            $tabela .= '<td>';
                            $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_formulario','atualizar_atividade_pid'," . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                            $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                            $tabela .= '</a>';
                            $tabela .= '</td>';

                            if ($linha_historico_atividade['situacao'] != 'CANCELADA') {
                                $tabela .= '<td>';
                                $tabela .= '<a href="#void" onclick="abrirModal(' . "'modal_confirmacao','deletar'," . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:red">';
                                $tabela .= '<span title="Excluir/Cancelar Atividade" class="glyphicon glyphicon-remove"></span>';
                                $tabela .= '</a>';
                                $tabela .= '</td>';
                            } else {
                                $tabela .= '<td>';
                                $tabela .= '<a href="#void" onclick="reativar_atividade(' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:blue">';
                                $tabela .= '<span title="Reativar atividade" class="glyphicon glyphicon-plus"></span>';
                                $tabela .= '</a>';
                                $tabela .= '</td>';
                            }
                            $tabela .= '<td>';
                        } else {
                            $tabela .= '<td colspan="3">';
                        }

                        if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                            $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="Atividade aprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                            $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                        } else {
                            $tabela .= 'ERRO';
                        }
                        $tabela .= '</td>';
                        $tabela .= '</tr>';

                        if (($linha_historico_atividade['situacao'] != 'CANCELADA') && ($linha_historico_atividade['situacao'] != 'REPROVADA')) {
                            $soma_grupo = round($soma_grupo + $linha_atividade_docente['horas_planejadas'], 2);
                        }
                    }
                    $grupos[$id_tipo_atividade] = $soma_grupo;

                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" style="font-weight:bold;text-align:right;padding-top:10px">TOTAL:</td>';
                    $tabela .= '<td align="center"><input id="soma_' . $id_tipo_atividade . '" name="soma_' . $id_tipo_atividade . '" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo . '"></td>';
                    $tabela .= '<td></td>';
                    $tabela .= '<td></td>';
                    $tabela .= '<td></td>';
                    $tabela .= '</tr>';
                    $tabela .= '</tbody>';
                    $tabela .= '</table>';
                    $tabela .= '</div>';
                    $tabela .= '</div>';

                    if ($id_tipo_atividade == 1) {

                        $tabela .= '<div class="panel panel-info" id="painel_tipo_atividade_2">';
                        $tabela .= '<div class="panel panel-heading">Atividades de Preparação e Manutenção do Ensino</div>';
                        $tabela .= '<div class="panel panel-body">';

                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li>A soma das horas das atividades deste grupo não pode ultrapassar a soma das horas das disciplinas.</li>'
                                . '<li>A participação nos <b>conselhos de classe</b> deve ser registrada dentro das atividades de preparação e manutenção (último item do grupo).</li>'
                                . '<li>Nos cursos integrados, a comprovação das aulas ministradas no <b>primeiro semestre</b> deve ser feita mediante <b>cópia do diário de presença da disciplina</b>, anexada ao RID.</li>'
                                . '</ul>'
                                . '</div>';

                        $tabela .= '<div id="msg_2"></div>';
                        $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_tipo_atividade_2">';
                        $tabela .= '<thead>';
                        $tabela .= '<tr>';
                        $tabela .= '<th width="86%">Atividade</th>';
                        $tabela .= '<th style="width:10%;text-align:center">CHS</th>';
                        $tabela .= '<th style="width:4%;text-align:center"></th>';
                        $tabela .= '</tr>';
                        $tabela .= '</thead>';
                        $tabela .= '<tbody>';

                        $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                        $parametros = array('atividade.id_tipo_atividade' => 2);
                        $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                        $soma_grupo = 0;
                        while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {
                            $tabela .= '<tr>';
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_planejadas . ' type="text" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '" onChange="atualizar_chs(' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')"></td>';
                            $tabela .= '<td>';

                            $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'PID');
                            $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);

                            if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                                $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                                $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="Atividade aprovada"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                                $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                                $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                            } else {
                                $tabela .= 'ERRO';
                            }
                            $tabela .= '</td>';
                            $tabela .= '</tr>';
                            $soma_grupo = round($soma_grupo + $linha_atividade_docente['horas_planejadas'], 2);
                        }
                        $grupos[2] = $soma_grupo;

                        $tabela .= '<tr>';
                        $tabela .= '<td style="font-weight:bold;text-align:right;padding-top:10px">TOTAL:</td>';
                        $tabela .= '<td align="center"><input id="soma_2" name="soma_2" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo . '"></td>';
                        $tabela .= '<td></td>';
                        $tabela .= '</tr>';
                        $tabela .= '</tbody>';
                        $tabela .= '</table>';
                        $tabela .= '</div>';
                        $tabela .= '</div>';
                    }
                }
            }

            // Resumo
            $tabela .= '<div class="panel panel-info" id="painel_resumo">';
            $tabela .= '<div class="panel panel-heading">Resumo</div>';
            $tabela .= '<div class="panel panel-body">';
            $tabela .= '<div class="container-fluid">';
            $tabela .= '<table class="table table-striped table-hover table-condensed table-bordered">';
            $tabela .= '<thead>';
            $tabela .= '<tr>';
            $tabela .= '<th class="col-sm-10 text-left">Grupo</th>';
            $tabela .= '<th class="col-sm-2 text-center">CHS</th>';
            $tabela .= '</tr>';
            $tabela .= '</thead>';
            $tabela .= '<tbody>';

            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), array('id_tipo_atividade' => 'ASC'));
            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {
                $tabela .= '<tr>';
                if ($linha_tipo_atividade['id_tipo_atividade'] == 1) {
                    $tabela .= '<td class="col-sm-10 text-left">Disciplinas</td>';
                } else {
                    $tabela .= '<td class="col-sm-10 text-left">' . $linha_tipo_atividade['descricao'] . '</td>';
                }
                $chs_grupo = isset($grupos[$linha_tipo_atividade['id_tipo_atividade']]) ? $grupos[$linha_tipo_atividade['id_tipo_atividade']] : 0;
                $tabela .= '<td class="col-sm-2 text-center">' . $chs_grupo . '</td>';
                $tabela .= '</tr>';
            }
            $total_geral = round(array_sum($grupos), 2);
            $tabela .= '<tr>';
            $tabela .= '<th class="col-sm-10 text-right">TOTAL:</th>';
            $tabela .= '<th class="col-sm-2 text-center"><input name="soma" id="soma" readonly type="text" style="padding-left:30px;width:80px;border:0px;background-color:inherit" value="' . $total_geral . '"></th>';
            $tabela .= '</tr>';
            $tabela .= '</tbody>';
            $tabela .= '</table>';

            // inputs hidden para regra 1,5h (grupos 1,2,3) e soma
            $soma_1 = isset($grupos[1]) ? $grupos[1] : 0;
            $soma_2 = isset($grupos[2]) ? $grupos[2] : 0;
            $soma_3 = isset($grupos[3]) ? $grupos[3] : 0;
            $tabela .= '<input type="hidden" name="soma_1" id="soma_1" value="' . $soma_1 . '">';
            $tabela .= '<input type="hidden" name="soma_2" id="soma_2" value="' . $soma_2 . '">';
            $tabela .= '<input type="hidden" name="soma_3" id="soma_3" value="' . $soma_3 . '">';

            $tabela .= '<div id="msg_9" class="col-sm-12"></div>';

            if (
                    ($linha_historico_pid['situacao'] == 'AGUARDANDO ENVIO') ||
                    ($linha_historico_pid['situacao'] == 'RETORNADO PARA CORREÇÃO')
            ) {
                $tabela .= '<div class="col-sm-12 text-center">';
                $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_enviar_pid" style="width:170px;text-align:center;" onClick="enviar_pid()">';
                $tabela .= '<span class="glyphicon glyphicon-send" style="padding-right:10px"></span> Enviar PID';
                $tabela .= '</button>';
                $tabela .= '</div>';
                $tabela .= '</div>';
                $tabela .= '</div>';
                $tabela .= '</div>';
            }
        }

        return json_encode(array('tabela' => $tabela));
    }

    public function imprimir() {
        // mantido como no original (não foi incluído aqui no enunciado)
    }

    public function atualizar_atividade_pid() {
        if ($this->formularioValido()) {
            $_POST['horas_planejadas'] = str_replace(',', '.', $_POST['horas_planejadas']);
            $_POST['etapa'] = 'PID';
            $_POST['id_comprovante'] = '';
            $res = $this->atividade_docenteM->atualizar_atividade_pid($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Registro atualizado com sucesso!</div>';
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao inserir - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $res, 'msg' => $this->msg));
    }

    public function enviar_pid() {
        $resultado = false;

        // Regra 1,5h para o professor (grupos 2 + 3 <= 1,5 * grupo 1)
        if (
                isset($_POST['soma_1'], $_POST['soma_2'], $_POST['soma_3']) &&
                ($_POST['soma_1'] > 0) &&
                (($_POST['soma_2'] + $_POST['soma_3']) > ($_POST['soma_1'] * 1.5))
        ) {
            $this->msg .= '<div class="alert alert-danger text-center">';
            $this->msg .= 'A soma das cargas horárias semanais das "Atividades de Preparação e Manutenção do Ensino" e das "Atividades de Apoio ao Ensino" não pode exceder 1,5 hora por hora de aula planejada.';
            $this->msg .= '</div>';

            // Regra das 40 horas totais
        } else if ($_POST['soma'] != 40) {

            $this->msg .= '<div class="alert alert-danger text-center">';
            $this->msg .= 'O Professor deverá planejar um total de 40 horas semanais !!!';
            $this->msg .= '</div>';
        } else {

            // Aqui NÃO há mais validação de pendência de avaliação:
            // o professor apenas envia o PID para o avaliador
            $result_historico_pid = $this->historico_pidM->inserir($_POST);
            if ($result_historico_pid) {
                $this->msg .= '<div class="alert alert-success text-center">PID enviado para avaliação !!!</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger text-center">Erro ao tentar enviar PID. Entre em contato com o Administrador do Sistema !!!</div>';
            }
        }

        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function reativar_atividade() {
        $campos['id_atividade_docente'] = $_POST['id_atividade_docente'];
        $campos['etapa'] = 'PID';
        $campos['situacao'] = 'AGUARDANDO AVALIAÇÃO';
        $campos['observacao'] = '';
        $campos['id_usuario_avaliador'] = '';
        $res = $this->historico_atividadeM->inserir($campos);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">Registro atualizado com sucesso!</div>';
            $resultado = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">Erro ao atualizar - Contactar o administrador do sistema</div>';
        }
        return json_encode(array('resultado' => $res, 'msg' => $this->msg));
    }

    public function carregarPeriodo() {
        $result_periodo_atual = $this->periodoM->getPeriodoAtual();
        $linha_periodo_atual = mysqli_fetch_assoc($result_periodo_atual);
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%;">';
        $resultado_periodos = $this->periodoM->listar(array(), array('id_periodo' => 'DESC'));
        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            if ($linha_periodo_atual['id_periodo'] == $linha['id_periodo']) {
                $select .= "<option selected='selected' value='{$linha['id_periodo']}'>";
            } else {
                $select .= "<option value='{$linha['id_periodo']}'>";
            }
            $select .= $linha['ano'] . '/' . $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        return json_encode(array('select' => $select));
    }

    public function carregarAtividade() {
        $select = '<label for="id_atividade">Atividade:</label>';
        $select .= '<select id="id_atividade" name="id_atividade" class="form-control" style="width:100%;">';
        $resultado_periodos = $this->atividadeM->getAtividadeTipo($_POST['id_tipo_atividade']);
        $select .= "<option value=''>Selecione uma atividade</option>";
        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_atividade']}'>";
            $select .= $linha['descricao'];
            $select .= '</option>';
        }
        $select .= '</select>';
        return json_encode(array('select' => $select));
    }

    public function formularioValido() {
        $valido = true;
        if (trim($_POST['id_atividade']) == '') {
            $this->msg = 'O preenchimento do campo atividade é obrigatório!';
            $valido = false;
        } else if (trim($_POST['descricao']) == '') {
            $this->msg = 'O preenchimento do campo descrição é obrigatório!';
            $valido = false;
        } else if (trim($_POST['horas_planejadas']) == '') {
            $this->msg = 'O preenchimento do campo CHS é obrigatório!';
            $valido = false;
        }
        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function inserir_atividade_pid() {
        $resultado = false;
        $id_atividade_docente = 0;
        if ($this->formularioValido()) {
            $_POST['horas_planejadas'] = str_replace(',', '.', $_POST['horas_planejadas']);
            $_POST['etapa'] = 'PID';
            $_POST['id_comprovante'] = '';
            $res = $this->atividade_docenteM->inserir_atividade_pid($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Registro cadastrado com sucesso!</div>';
                $id_atividade_docente = $res;
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao inserir - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg, 'id_atividade_docente' => $id_atividade_docente));
    }

    public function deletar() {
        $resultado = false;
        if (!$this->atividade_docenteM->atividade_avaliada($_POST['id_atividade_docente'])) {
            $res = $this->atividade_docenteM->deletar_atividade_pid($_POST['id_atividade_docente']);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Registro deletado com sucesso!</div>';
                $resultado = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao deletar - Contactar o administrador do sistema</div>';
            }
        } else {
            $campos['id_atividade_docente'] = $_POST['id_atividade_docente'];
            $campos['etapa'] = 'PID';
            $campos['situacao'] = 'CANCELADA';
            $campos['observacao'] = '';
            $campos['id_usuario_avaliador'] = '';
            $res = $this->historico_atividadeM->inserir($campos);
            if ($res) {
                $this->msg .= '<div class="alert alert-warning">Atividade cancelada! Essa atividade não será mais somada dentro dessa categoria!</div>';
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao tentar cancelar a atividade - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function atualizar_chs() {
        $resultado = false;
        $res = $this->atividade_docenteM->atualizar_chs($_POST);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">Registro atualizado com sucesso!</div>';
            $resultado = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">Erro ao atualizar - Contactar o administrador do sistema</div>';
        }
        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function getAtividade_docente() {
        $res = $this->atividade_docenteM->getAtividade_docente($_POST['id_atividade_docente']);
        $i = 0;
        $atividade_docente = array();
        $historico_atividade = array();
        while ($linha = mysqli_fetch_assoc($res)) {
            if ($i == 0) {
                $atividade_docente['id_atividade_docente'] = $linha['id_atividade_docente'];
                $atividade_docente['id_pid'] = $linha['id_pid'];
                $atividade_docente['id_atividade'] = $linha['id_atividade'];
                $atividade_docente['descricao'] = $linha['descricao'];
                $atividade_docente['horas_planejadas'] = $linha['horas_planejadas'];
                $atividade_docente['horas_executadas'] = $linha['horas_executadas'];
                $atividade_docente['id_comprovante'] = $linha['id_comprovante'];
            }
            $situacao = array();
            $situacao['id_historico_atividade'] = $linha['id_historico_atividade'];
            $situacao['etapa'] = $linha['etapa'];
            $situacao['situacao'] = $linha['situacao'];
            $situacao['observacao'] = $linha['observacao'];
            $situacao['data_situacao'] = $linha['data_situacao'];
            $situacao['id_usuario_avaliador'] = $linha['id_usuario_avaliador'];
            $historico_atividade[$linha['id_historico_atividade']] = $situacao;
            $i++;
        }
        $atividade_docente['historico'] = $historico_atividade;
        return json_encode($atividade_docente);
    }

}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new pid_professorController();
    echo $objeto->$metodo();
}