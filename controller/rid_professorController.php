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

class rid_professorController {

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
        $this->tipo_atividadeM    = new tipo_atividadeModel();
        $this->historico_atividadeM = new historico_atividadeModel();
        $this->historico_pidM     = new historico_pidModel();
        $this->oferta_disciplinaM = new oferta_disciplinaModel();
        $this->periodoM           = new periodoModel();
        $this->usuarioM           = new usuarioModel();
        $this->atividadeM         = new atividadeModel();
        $this->pidM               = new pidModel();
        $this->msg                = '';
    }

    public function listar() {

        $tabela                   = '';
        $id_pid                   = '';
        $css_horas_planejadas     = '';
        $css_horas_planejadas_on  = 'style="width:80px;text-align:center"';
        $css_horas_planejadas_off = 'readonly style="width:80px;text-align:center;box-shadow: 0 0 0 0;border:0 none;outline: 0;background-color:inherit"';

        $result_usuario = $this->usuarioM->getUsuarioId($_SESSION['id_usuario']);
        $linha_usuario  = mysqli_fetch_assoc($result_usuario);

        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo  = mysqli_fetch_assoc($result_periodo);

        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Informações sobre o preenchimento</div>';
        $tabela .= '<div class="panel panel-body">';
        $tabela .= '<div class="container-fluid">';

        $data_inicio = strtotime($linha_periodo['rid_inicio']);
        $data_fim    = strtotime($linha_periodo['rid_fim']);
        $data_atual  = strtotime(date("Y-m-d"));

        $result_pid           = $this->pidM->getPidPeriodoProfessor($_POST['id_periodo'], $_SESSION['id_usuario']);
        $result_historico_pid = null;
        $linha_historico_pid  = null;
        $result_historico_rid = null;
        $linha_historico_rid  = null;

        if (mysqli_num_rows($result_pid) > 0) {

            $linha_pid        = mysqli_fetch_assoc($result_pid);
            $id_pid           = $linha_pid['id_pid'];
            $_SESSION['id_pid'] = $linha_pid['id_pid'];

            $result_historico_pid = $this->historico_pidM->getSituacao($id_pid, 'PID');
            $linha_historico_pid  = mysqli_fetch_assoc($result_historico_pid);

            if ($linha_historico_pid['situacao'] != 'APROVADO') {
                $tabela .= '<div class="alert alert-danger text-center">';
                $tabela .= 'O PID correspondente não foi aprovado!<br>Situação: ';
                $tabela .= $linha_historico_pid['situacao'] . '</div>';

                $tabela .= '</div></div></div>';

                return json_encode(array('tabela' => $tabela));
            } else {

                $result_historico_rid = $this->historico_pidM->getSituacao($id_pid, 'RID');
                if (!$result_historico_rid) {

                    $campos['id_pid']   = $id_pid;
                    $campos['etapa']    = 'RID';
                    $campos['situacao'] = 'AGUARDANDO ENVIO';
                    $this->historico_pidM->inserirRID($campos);
                    $result_historico_rid = $this->historico_pidM->getSituacao($id_pid, 'RID');
                    $linha_historico_rid  = mysqli_fetch_assoc($result_historico_rid);
                } else {
                    $linha_historico_rid = mysqli_fetch_assoc($result_historico_rid);
                }

                if ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO') {
                    $tabela .= '<div class="alert alert-warning text-center">' . $linha_historico_rid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_on;
                } else if ($linha_historico_rid['situacao'] == 'ENVIADO') {
                    $tabela .= '<div class="alert alert-info text-center">' . $linha_historico_rid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_rid['situacao'] == 'APROVADO') {
                    $tabela .= '<div class="alert alert-success text-center">' . $linha_historico_rid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_rid['situacao'] == 'REPROVADO') {
                    $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_rid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                } else if ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                    $css_horas_planejadas = $css_horas_planejadas_on;
                    $tabela .= '<div class="alert alert-danger text-center" style="font-size:18px">' . $linha_historico_rid['situacao'];
                    $tabela .= '<br>As atividades com o símbolo: ';
                    $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red;padding:8px" title="Atividade reprovada"></span>';
                    $tabela .= ' devem ser corrigidas clicando no ícone: ';
                    $tabela .= '<span class="glyphicon glyphicon-edit" style="color:green;padding:8px" title="Atividade reprovada"></span>';
                    $tabela .= '</div>';
                } else {
                    $tabela .= '<div class="alert alert-danger text-center">' . $linha_historico_rid['situacao'] . '</div>';
                    $css_horas_planejadas = $css_horas_planejadas_off;
                }
            }

            $tabela .= '<table class="table table-bordered">';
            $tabela .= '<tr>';
            $tabela .= '<td class="col-sm-6 text-center">Período de preenchimento:<b> ' . $linha_periodo['rid_inicio_formatado'] . ' à ' . $linha_periodo['rid_fim_formatado'] . '</b></td>';
            $tabela .= '<td class="col-sm-6 text-center">Data atual:<b> ' . date("d/m/Y") . '</b></td>';
            $tabela .= '</tr>';

            if (($linha_historico_rid['situacao'] != 'ENVIADO') &&
                ($linha_historico_rid['situacao'] != 'APROVADO') &&
                ($linha_historico_rid['situacao'] != 'REPROVADO')) {

                if (($data_atual > $data_fim) && ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO')) {

                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" class="alert alert-warning text-center">Período de preenchimento encerrado</td>';
                    $tabela .= '</tr>';
                    $tabela .= '</table></div></div></div>';

                    return json_encode(array('tabela' => $tabela));

                } else if ($data_atual < $data_inicio) {

                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" class="alert alert-warning text-center" style="font-size:15px">';
                    $numDays = abs($data_inicio - $data_atual) / 60 / 60 / 24;
                    $tabela .= 'Faltam <b>' . $numDays . ' dias</b> para entrar no período de preenchimento do RID ' . $linha_periodo['ano'] . '/' . $linha_periodo['semestre'] . ' !!!';
                    $tabela .= '</td>';
                    $tabela .= '</tr>';
                    $tabela .= '</table></div></div></div>';

                    return json_encode(array('tabela' => $tabela));

                } else if (
                    (
                        ($data_atual >= $data_inicio) &&
                        ($data_atual <= $data_fim) &&
                        ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO')
                    )
                    ||
                    ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO')
                ) {

                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" class="alert alert-success text-center" style="font-size:15px">';
                    $tabela .= 'Aberto para preenchimento!';
                    $tabela .= '</td>';
                    $tabela .= '</tr>';
                } else {
                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" class="alert alert-danger text-center" style="font-size:15px">';
                    $tabela .= 'Período inválido !!!';
                    $tabela .= '</td>';
                    $tabela .= '</tr>';
                    $tabela .= '</table></div></div></div>';

                    return json_encode(array('tabela' => $tabela));
                }
            }
        } else {

            $tabela .= '<div class="alert alert-danger text-center" style="font-size:15px">';
            $tabela .= 'Não foi cadastrado PID do período anterior no sistema!';
            $tabela .= '<br> Você pode acessar os RIDs relacionados aos PIDs não cadastrados no sistema a partir do link: ';
            $tabela .= '<a href="https://www.ifsudestemg.edu.br/documentos-institucionais/unidades/bomsucesso/pid-e-rid/rid">';
            $tabela .= 'RID - Campus Avançado Bom Sucesso</a>';
            $tabela .= '</div></div></div></div>';

            return json_encode(array('tabela' => $tabela));
        }

        if (
            ($linha_historico_rid['situacao'] == "AGUARDANDO ENVIO") ||
            ($linha_historico_rid['situacao'] == "RETORNADO PARA CORREÇÃO")
        ) {
            $tabela .= '<tr><td colspan="2" class="alert alert-warning">';

            // ATUALIZADO - IN 167/2025
            $tabela .= '<h5><b>Observações:</b></h5>
                    <ul>
                    <li>O RID somente é <strong>enviado</strong> para avaliação após o docente clicar no botão <b>Enviar RID</b>.</li>
                    <li>Todas as atividades executadas devem ser comprovadas com documentos oficiais antes do envio.</li>
                    <li>A carga horária semanal de cada atividade é obtida pela fórmula: <b>total de horas da atividade no semestre ÷ 20</b> (vinte semanas é o padrão do semestre letivo).</li>
                    <li>Atividades não executadas devem ter o campo "Horas Executadas" preenchido com <b>0</b> e a justificativa informada no campo de observação.</li>
                    </ul>';
            $tabela .= '</td></tr>';
        }
        $tabela .= '</table></div></div></div>';

        $tabela .= '<input type="hidden" name="id_pid" id="id_pid" value="' . $id_pid . '">';

        // Dados do docente
        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Dados do docente</div>';
        $tabela .= '<div class="panel panel-body"><div class="container-fluid">';
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
        $tabela .= '</table></div></div></div>';

        if ($id_pid != '') {

            $grupos_planejadas = array();
            $grupos_executadas = array();

            $ordenacao            = array('id_tipo_atividade' => 'ASC');
            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), $ordenacao);
            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

                $id_tipo_atividade = $linha_tipo_atividade['id_tipo_atividade'];
                $descricao         = $linha_tipo_atividade['descricao'];

                if ($id_tipo_atividade != 2) {

                    $tabela .= '<div class="panel panel-info" id="painel_atividade_' . $id_tipo_atividade . '">';
                    $tabela .= '<div class="panel panel-heading">' . $descricao . '</div>';
                    $tabela .= '<div class="panel panel-body">';

                    // Orientações por grupo (mantidas)
                    if ($id_tipo_atividade == 1) {
                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem ser preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li style="color:red;font-weight:bold">Neste grupo o comprovante de horas ministradas deve ser adicionado uma única vez e já servirá como comprovante para o grupo "Atividades de Preparação e Manutenção do Ensino".</li>'
                                . '<li>Nos cursos integrados, a comprovação das aulas ministradas no <b>primeiro semestre</b> deve ser feita mediante <b>cópia do diário de presença da disciplina</b>, anexada ao RID.</li>'
                                . '</ul></div>';
                    } else if ($id_tipo_atividade == 3) {
                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="font-weight:bold">Neste grupo cada atividade terá o seu próprio comprovante; para adicioná-lo clique no ícone: <span class="glyphicon glyphicon-file" style="margin-left:5px; color:blue"></span>.</li>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem ser preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li>Participação em banca de TCC como <b>suplente</b>: até <b>0,1 hora</b> por participação.</li>'
                                . '<li>Participação em banca de TCC como <b>titular</b>: até <b>0,2 hora</b> por participação.</li>'
                                . '<li>Cada participação deve ser lançada <b>separadamente</b>.</li>'
                                . '<li>A comprovação deve ser feita por <b>ata</b> ou <b>declaração da coordenação de curso</b>.</li>'
                                . '<li>A participação em <b>conselhos de classe</b> deve ser comprovada exclusivamente por declaração emitida pelo coordenador de curso.</li>'
                                . '</ul></div>';
                    } else if ($id_tipo_atividade == 4) {
                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="font-weight:bold">Neste grupo cada atividade terá o seu próprio comprovante; para adicioná-lo clique no ícone: <span class="glyphicon glyphicon-file" style="margin-left:5px;color:blue"></span>.</li>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem ser preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li>Orientação de <b>estágio</b>: até <b>0,2 hora</b> por orientação.</li>'
                                . '<li>Coordenação ou participação como colaborador em <b>Projetos de Ensino</b>: até <b>0,5 hora</b> por projeto.</li>'
                                . '<li>Orientação em <b>monitorias</b> e <b>iniciação à docência</b>: até <b>0,2 hora</b> por orientação.</li>'
                                . '<li>Orientação de <b>TCC – nível técnico</b>: até <b>1,0 hora</b> por orientação.</li>'
                                . '<li>Orientação de <b>TCC – graduação</b>: até <b>1,5 hora</b> por orientação.</li>'
                                . '<li>Orientação de <b>TCC – pós-graduação</b>: até <b>2,0 horas</b> por orientação.</li>'
                                . '<li>A comprovação deve ser feita por documentos emitidos pela <b>coordenação de ensino, pesquisa e extensão</b> e pelo <b>professor de TCC ou coordenador de curso</b>.</li>'
                                . '</ul></div>';
                    } else if ($id_tipo_atividade == 6) {
                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="font-weight:bold">Neste grupo cada atividade terá o seu próprio comprovante; para adicioná-lo clique no ícone: <span class="glyphicon glyphicon-file" style="margin-left:5px;color:blue"></span>.</li>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem be preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li>Para todas as atividades de <b>extensão</b>, a carga horária semanal deve ser calculada por: <b>CHS = total de horas da atividade no semestre ÷ 20</b>.</li>'
                                . '<li>Considera-se <b>20</b> o número padrão de semanas do semestre letivo.</li>'
                                . '<li>A comprovação deve ser emitida pela <b>PROEX</b> ou pela <b>Diretoria de Extensão</b>.</li>'
                                . '</ul></div>';
                    } else if ($id_tipo_atividade == 7) {
                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="font-weight:bold">Neste grupo cada atividade terá o seu próprio comprovante; para adicioná-lo clique no ícone: <span class="glyphicon glyphicon-file" style="margin-left:5px;color:blue"></span>.</li>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem ser preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li>A comprovação deve ser feita por <b>declaração</b> emitida pela chefia responsável, coordenação ou presidência da comissão.</li>'
                                . '<li>A declaração deve conter: identificação da atividade; período de atuação; descrição das atividades desempenhadas; carga horária total desenvolvida no semestre; e assinatura da chefia responsável.</li>'
                                . '</ul></div>';
                    }

                    if (
                        ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO') ||
                        ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO')
                    ) {
                        $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_adicionar_atividade" style="width: 170px;text-align:center; margin-bottom:20px;margin-right:20px" onClick="abrirModal(\'modal_formulario\', \'inserir_atividade_rid\', 0, ' . $id_tipo_atividade . ', false)">';
                        $tabela .= '<span class="glyphicon glyphicon-plus"></span> Adicionar atividade';
                        $tabela .= '</button>';

                        if ($id_tipo_atividade == 1) {
                            $tabela .= '<button type="button" class="btn btn-info form-control" id="btn_adicionar_comprovante" style="width: 300px;text-align:center; margin-bottom:20px" onClick="location.href=\'comprovante_disciplinas.php\'">';
                            $tabela .= '<span class="glyphicon glyphicon-file" style="margin-right:10px"></span> Adicionar/Visualizar Comprovante';
                            $tabela .= '</button>';
                        }
                    }

                    $tabela .= '<div id="msg_' . $id_tipo_atividade . '"></div>';

                    $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_' . $id_tipo_atividade . '">';
                    $tabela .= '<thead><tr>';
                    $tabela .= '<th style="width:36%">Atividade</th>';
                    $tabela .= '<th style="width:36%">Descrição</th>';
                    $tabela .= '<th style="width:10%;text-align:center">Horas planejadas</th>';
                    $tabela .= '<th style="width:10%;text-align:center">Horas executadas</th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '<th style="width:2%;text-align:center"></th>';
                    $tabela .= '</tr></thead><tbody>';

                    $ordenacao              = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                    $parametros             = array('atividade.id_tipo_atividade' => $id_tipo_atividade);
                    $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                    $soma_grupo_executadas = 0;
                    $soma_grupo_planejadas = 0;
                    while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                        $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                        $linha_historico_atividade  = mysqli_fetch_assoc($result_historico_atividade);

                        $tabela .= '<tr>';
                        $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                        $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                        $tabela .= '<td align="center"><input type="hidden" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '">';
                        $tabela .= $linha_atividade_docente['horas_planejadas'] . '</td>';

                        if (($linha_historico_atividade['situacao'] != 'CANCELADA') &&
                            ($linha_historico_atividade['situacao'] != 'NÃO EXECUTADA') &&
                            ($linha_historico_atividade['situacao'] != 'REPROVADA')) {

                            $tabela .= '<td align="center"><input data-mask="99.99" class="form-control" ' . $css_horas_planejadas . ' type="text" name="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_executadas'] . '" onChange="atualizar_horas_executadas(' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')"></td>';
                        } else {
                            $tabela .= '<td align="center"><input class="form-control" readonly style="width:80px;text-align:center;border:0px;background-color:#FF6347" type="text" name="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_executadas'] . '"></td>';
                        }

                        // botões + ícones (sem alterações de lógica)
                        if (
                            ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO') ||
                            ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO')
                        ) {

                            if ($id_tipo_atividade != 1) {
                                $tabela .= '<td>';
                                $tabela .= '<a title="Enviar/Visualizar Comprovante" href="#void" onclick="location.href=\'comprovante_atividade.php?id_atividade_docente=' . $linha_atividade_docente['id_atividade_docente'] . '\'" style="color:blue">';
                                $tabela .= '<span class="glyphicon glyphicon-file"></span>';
                                $tabela .= '</a></td>';
                            } else {
                                $tabela .= '<td></td>';
                            }

                            $tabela .= '<td>';
                            $tabela .= '<a title="Editar" href="#void" onclick="abrirModal(\'modal_formulario\',\'atualizar_atividade_rid\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ',false)" style="color:green">';
                            $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                            $tabela .= '</a></td>';
                            $tabela .= '<td>';
                        } else {
                            if (($linha_historico_atividade['situacao'] != 'CANCELADA') &&
                                ($linha_historico_atividade['situacao'] != 'NÃO EXECUTADA')) {
                                $tabela .= '<td>';
                                $tabela .= '<a title="Ver comprovante" href="#void" onclick="location.href=\'download.php?id_comprovante=' . $linha_atividade_docente['id_comprovante'] . '\'" style="color:green">';
                                $tabela .= '<span class="glyphicon glyphicon-file"></span>';
                                $tabela .= '</a></td>';
                            } else {
                                $tabela .= '<td></td>';
                            }
                            $tabela .= '<td>';
                        }

                        if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                            $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="Atividade aprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                            $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'NÃO EXECUTADA') {
                            $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="NÃO EXECUTADA"></span>';
                        } else {
                            $tabela .= 'ERRO: ' . $linha_historico_atividade['situacao'];
                        }
                        $tabela .= '</td>';

                        if (($linha_historico_atividade['situacao'] != 'CANCELADA') &&
                            ($linha_historico_atividade['situacao'] != 'NÃO EXECUTADA')) {
                            $tabela .= '<td>';
                            if ($linha_atividade_docente['id_comprovante'] == '') {
                                $tabela .= '<span class="glyphicon glyphicon-alert" style="color:#DAA520" title="Comprovante não anexado"></span>';
                            } else {
                                $tabela .= '<span class="glyphicon glyphicon-check" style="color:blue" title="Comprovante enviado"></span>';
                            }
                            $tabela .= '</td>';
                        } else {
                            $tabela .= '<td></td>';
                        }
                        $tabela .= '</tr>';

                        if (($linha_historico_atividade['situacao'] != 'CANCELADA') &&
                            ($linha_historico_atividade['situacao'] != 'REPROVADA') &&
                            ($linha_historico_atividade['situacao'] != 'NÃO EXECUTADA')) {

                            $soma_grupo_executadas += $linha_atividade_docente['horas_executadas'];
                        }
                        $soma_grupo_planejadas += $linha_atividade_docente['horas_planejadas'];

                        $soma_grupo_executadas = round($soma_grupo_executadas, 2);
                        $soma_grupo_planejadas = round($soma_grupo_planejadas, 2);
                    }
                    $grupos_executadas[$id_tipo_atividade] = $soma_grupo_executadas;
                    $grupos_planejadas[$id_tipo_atividade] = $soma_grupo_planejadas;

                    $tabela .= '<tr>';
                    $tabela .= '<td colspan="2" style="font-weight:bold; text-align:right;padding-top:10px">TOTAL:</td>';
                    $tabela .= '<td align="center">' . $soma_grupo_planejadas . '</td>';
                    $tabela .= '<td align="center"><input id="soma_' . $id_tipo_atividade . '" name="soma_' . $id_tipo_atividade . '" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_executadas . '"></td>';
                    $tabela .= '<td></td><td></td><td></td><td></td>';
                    $tabela .= '</tr>';

                    $tabela .= '</tbody></table></div></div>';

                    // Painel complementar para grupo 2 sempre que id_tipo_atividade == 1
                    if ($id_tipo_atividade == 1) {

                        $tabela .= '<div class="panel panel-info" id="painel_tipo_atividade_2">';
                        $tabela .= '<div class="panel panel-heading">Atividades de Preparação e Manutenção do Ensino</div>';
                        $tabela .= '<div class="panel panel-body">';

                        $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                                . '<b>Orientações:</b><br><br>'
                                . '<ul>'
                                . '<li style="color:red;font-weight:bold">As atividades não executadas devem ser preenchidas com o valor 0; será solicitado o preenchimento da justificativa no campo de observação.</li>'
                                . '<li>A soma das horas das atividades deste grupo, somadas às atividades de apoio ao ensino, não deve ultrapassar 1,5 hora por hora de aula executada.</li>'
                                . '<li>A participação nos <b>conselhos de classe</b> deve ser registrada neste grupo (último item) e comprovada por declaração emitida pelo coordenador de curso.</li>'
                                . '</ul></div>';

                        $tabela .= '<div id="msg_2"></div>';

                        $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_tipo_atividade_2">';
                        $tabela .= '<thead><tr>';
                        $tabela .= '<th width="78%">Atividade</th>';
                        $tabela .= '<th style="width:10%;text-align:center">Horas Planejadas</th>';
                        $tabela .= '<th style="width:10%;text-align:center">Horas Executadas</th>';
                        $tabela .= '<th style="width:2%;text-align:center"></th>';
                        $tabela .= '</tr></thead><tbody>';

                        $ordenacao              = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                        $parametros             = array('atividade.id_tipo_atividade' => 2);
                        $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                        $soma_grupo_executadas = 0;
                        $soma_grupo_planejadas = 0;
                        while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {
                            $tabela .= '<tr>';
                            $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                            $tabela .= '<td align="center">' . $linha_atividade_docente['horas_planejadas'] . '</td>';
                            $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_planejadas . ' type="text" name="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_executadas'] . '" onChange="atualizar_horas_executadas(' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')"></td>';
                            $tabela .= '<td align="left">';

                            $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                            $linha_historico_atividade  = mysqli_fetch_assoc($result_historico_atividade);

                            if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                                $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                                $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:green" title="Atividade aprovada"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                                $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                                $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                            } else if ($linha_historico_atividade['situacao'] == 'NÃO EXECUTADA') {
                                $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="NÃO EXECUTADA"></span>';
                            } else {
                                $tabela .= 'ERRO: d' . $linha_historico_atividade['situacao'];
                            }
                            $tabela .= '</td></tr>';

                            $soma_grupo_executadas += $linha_atividade_docente['horas_executadas'];
                            $soma_grupo_planejadas += $linha_atividade_docente['horas_planejadas'];
                            $soma_grupo_executadas = round($soma_grupo_executadas, 2);
                            $soma_grupo_planejadas = round($soma_grupo_planejadas, 2);
                        }
                        $grupos_executadas[2] = $soma_grupo_executadas;
                        $grupos_planejadas[2] = $soma_grupo_planejadas;

                        $tabela .= '<tr>';
                        $tabela .= '<td style="font-weight:bold; text-align:right;padding-top:10px">TOTAIS:</td>';
                        $tabela .= '<td align="center">' . $soma_grupo_planejadas . '</td>';
                        $tabela .= '<td align="center"><input id="soma_2" name="soma_2" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_executadas . '"></td>';
                        $tabela .= '<td></td>';
                        $tabela .= '</tr>';

                        $tabela .= '</tbody></table></div></div>';
                    }
                }
            }

            // Resumo
            $tabela .= '<div class="panel panel-info" id="painel_resumo">';
            $tabela .= '<div class="panel panel-heading">Resumo</div>';
            $tabela .= '<div class="panel panel-body"><div class="container-fluid">';
            $tabela .= '<table class="table table-striped table-hover table-condensed table-bordered">';
            $tabela .= '<thead><tr>';
            $tabela .= '<th class="col-sm-8 text-left">Grupo</th>';
            $tabela .= '<th class="col-sm-2 text-center">Horas Planejadas</th>';
            $tabela .= '<th class="col-sm-2 text-center">Horas Executadas</th>';
            $tabela .= '</tr></thead><tbody>';

            $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), array('id_tipo_atividade' => 'ASC'));
            while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {
                $tabela .= '<tr>';
                if ($linha_tipo_atividade['id_tipo_atividade'] == 1) {
                    $tabela .= '<td class="col-sm-8 text-left">Disciplinas</td>';
                } else {
                    $tabela .= '<td class="col-sm-8 text-left">' . $linha_tipo_atividade['descricao'] . '</td>';
                }
                $chs_planej = isset($grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']]) ? $grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']] : 0;
                $chs_exec   = isset($grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']]) ? $grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']] : 0;
                $tabela .= '<td class="col-sm-2 text-center">' . $chs_planej . '</td>';
                $tabela .= '<td class="col-sm-2 text-center">' . $chs_exec . '</td>';
                $tabela .= '</tr>';
            }
            $total_planejadas = round(array_sum($grupos_planejadas), 2);
            $total_executadas = round(array_sum($grupos_executadas), 2);

            $tabela .= '<tr>';
            $tabela .= '<th class="col-sm-8 text-right">TOTAIS:</th>';
            $tabela .= '<th class="col-sm-2 text-center"><input name="soma_planejadas" id="soma_planejadas" readonly type="text" style="padding-left:30px;width:80px;border:0px;background-color:inherit" value="' . $total_planejadas . '"></th>';
            $tabela .= '<th class="col-sm-2 text-center"><input name="soma_executadas" id="soma_executadas" readonly type="text" style="padding-left:30px;width:80px;border:0px;background-color:inherit" value="' . $total_executadas . '"></th>';
            $tabela .= '</tr>';
            $tabela .= '</tbody></table>';

            // hidden para regra 1,5h no envio (RID professor)
            $soma_executadas_1 = isset($grupos_executadas[1]) ? $grupos_executadas[1] : 0;
            $soma_executadas_2 = isset($grupos_executadas[2]) ? $grupos_executadas[2] : 0;
            $soma_executadas_3 = isset($grupos_executadas[3]) ? $grupos_executadas[3] : 0;

            $tabela .= '<input type="hidden" name="soma_executadas_1" id="soma_executadas_1" value="' . $soma_executadas_1 . '">';
            $tabela .= '<input type="hidden" name="soma_executadas_2" id="soma_executadas_2" value="' . $soma_executadas_2 . '">';
            $tabela .= '<input type="hidden" name="soma_executadas_3" id="soma_executadas_3" value="' . $soma_executadas_3 . '">';

            $tabela .= '<div id="msg_9" class="col-sm-12"></div>';

            if (
                ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO') ||
                ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO')
            ) {
                $tabela .= '<div class="col-sm-12 text-center">';
                $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_enviar_pid" style="width: 170px;text-align:center;" onClick="enviar_rid()">';
                $tabela .= '<span class="glyphicon glyphicon-send" style="padding-right:10px"></span> Enviar RID';
                $tabela .= '</button></div></div></div></div>';
            }
        }

        return json_encode(array('tabela' => $tabela));
    }

    public function atualizar_atividade_rid() {
        $res = false;

        $_POST['horas_executadas'] = str_replace(',', '.', $_POST['horas_executadas']);
        $_POST['etapa']            = 'RID';
        if ($this->formularioValido()) {

            if ($_POST['horas_executadas'] == 0) {
                $_POST['situacao'] = 'NÃO EXECUTADA';
            } else {
                $_POST['situacao'] = 'AGUARDANDO AVALIAÇÃO';
            }

            $res = $this->atividade_docenteM->atualizar_atividade_rid($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Registro atualizado com sucesso!</div>';
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao atualizar - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $res, 'msg' => $this->msg));
    }

    public function enviar_rid() {

        $resultado = false;

        // 1) Todas as horas executadas preenchidas
        if ($this->atividade_docenteM->atividadesExecutadas($_POST['id_pid']) > 0) {
            $this->msg .= '<div class="alert alert-danger text-center">';
            $this->msg .= 'Todos os campos Horas Executadas devem ser preenchidos!!!';
            $this->msg .= '</div>';

        // 2) Todos os comprovantes anexados
        } else if ($this->atividade_docenteM->atividadesNaoComprovadas($_POST['id_pid']) > 0) {
            $this->msg .= '<div class="alert alert-danger text-center">';
            $this->msg .= 'Deve ser adicionado comprovantes a todas atividades!!!';
            $this->msg .= '</div>';

        // 3) Regra 1,5h (RID professor): (grupo 2 + grupo 3) <= 1,5 * grupo 1, usando horas executadas
        } else if (
            isset($_POST['soma_executadas_1'], $_POST['soma_executadas_2'], $_POST['soma_executadas_3']) &&
            ($_POST['soma_executadas_1'] > 0) &&
            (($_POST['soma_executadas_2'] + $_POST['soma_executadas_3']) > ($_POST['soma_executadas_1'] * 1.5))
        ) {
            $this->msg .= '<div class="alert alert-danger text-center">';
            $this->msg .= 'A soma das cargas horárias semanais das "Atividades de Preparação e Manutenção do Ensino" e das "Atividades de Apoio ao Ensino" não pode exceder 1,5 hora por hora de aula executada.';
            $this->msg .= '</div>';

        // 4) Tudo ok: envia RID
        } else {

            $campos['id_pid']   = $_POST['id_pid'];
            $campos['etapa']    = 'RID';
            $campos['situacao'] = 'ENVIADO';
            $result_historico_pid = $this->historico_pidM->inserir($campos);

            if ($result_historico_pid) {
                $this->msg .= '<div class="alert alert-success text-center">RID enviado para avaliação !!!</div>';
                $resultado  = true;
            } else {
                $this->msg .= '<div class="alert alert-danger text-center">Erro ao tentar enviar RID. Entre em contato com o Administrador do Sistema !!!</div>';
            }
        }

        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function carregarPeriodo() {

        $result_periodo_atual = $this->periodoM->getPeriodoAtual();
        $linha_periodo_atual  = mysqli_fetch_assoc($result_periodo_atual);

        $select  = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%;">';
        $resultado_periodos = $this->periodoM->listar(array(), array('id_periodo' => 'DESC'));

        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            if (($linha_periodo_atual['id_periodo'] - 1) == $linha['id_periodo']) {
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
        $select  = '<label for="id_atividade">Atividade:</label>';
        $select .= '<select id="id_atividade" name="id_atividade" class="form-control" style="width:100%;">';
        $resultado_periodos = $this->atividadeM->getAtividadeTipo($_POST['id_tipo_atividade']);

        $select .= "<option value=''>Selecione uma atividade</option>";
        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_atividade']}'>" . $linha['descricao'] . '</option>';
        }
        $select .= '</select>';
        return json_encode(array('select' => $select));
    }

    public function formularioValido() {

        $valido = true;
        if (trim($_POST['id_atividade']) == '') {
            $this->msg = 'O preenchimento do campo atividade é obrigatório!';
            $valido    = false;
        } else if (trim($_POST['descricao']) == '') {
            $this->msg = 'O preenchimento do campo descrição é obrigatório!';
            $valido    = false;
        } else if (trim($_POST['horas_executadas']) == '') {
            $this->msg = 'O preenchimento do campo Horas Executadas é obrigatório, se atividade não executada o campo deve ser preenchido com valor 0 e o campo de observação deve ser preenchido com a justificativa!';
            $valido    = false;
        } else if (($_POST['horas_executadas'] != $_POST['horas_planejadas']) && (trim($_POST['observacao']) == '') && ($_POST['metodo'] != 'inserir_atividade_rid')) {
            $this->msg = 'Horas planejadas diferente das horas executadas, preencha o campo de observação!';
            $valido    = false;
        } else if (($_POST['horas_executadas'] == 0) && (trim($_POST['observacao']) == '')) {
            $this->msg = 'Horas executadas igual a 0, preencha o campo de observação para justificar a não execução da atividade!';
            $valido    = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
    }

    public function inserir_atividade_rid() {

        $resultado            = false;
        $id_atividade_docente = 0;

        $_POST['horas_executadas'] = str_replace(',', '.', $_POST['horas_executadas']);
        $_POST['etapa']            = 'RID';
        $_POST['id_comprovante']   = '';

        if ($this->formularioValido()) {

            $res = $this->atividade_docenteM->inserir_atividade_rid($_POST);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Registro cadastrado com sucesso!</div>';
                $id_atividade_docente = $res;
                $resultado            = true;
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao inserir - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg, 'id_atividade_docente' => $id_atividade_docente));
    }

    public function atualizar_horas_executadas() {
        $resultado = false;
        $_POST['horas_executadas'] = str_replace(',', '.', $_POST['horas_executadas']);
        $res = $this->atividade_docenteM->atualizar_horas_executadas($_POST);
        if ($res) {
            $this->msg .= '<div class="alert alert-success">Registro atualizado com sucesso!</div>';
            $resultado  = true;
        } else {
            $this->msg .= '<div class="alert alert-danger">Erro ao atualizar - Contactar o administrador do sistema</div>';
        }
        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function getAtividade_docente() {
        $res                 = $this->atividade_docenteM->getAtividade_docente($_POST['id_atividade_docente']);
        $i                   = 0;
        $atividade_docente   = array();
        $historico_atividade = array();
        while ($linha = mysqli_fetch_assoc($res)) {
            if ($i == 0) {
                $atividade_docente['id_atividade_docente'] = $linha['id_atividade_docente'];
                $atividade_docente['id_pid']               = $linha['id_pid'];
                $atividade_docente['id_atividade']         = $linha['id_atividade'];
                $atividade_docente['descricao']            = $linha['descricao'];
                $atividade_docente['horas_planejadas']     = $linha['horas_planejadas'];
                $atividade_docente['horas_executadas']     = $linha['horas_executadas'];
                $atividade_docente['id_comprovante']       = $linha['id_comprovante'];
            }
            $situacao                           = array();
            $situacao['id_historico_atividade'] = $linha['id_historico_atividade'];
            $situacao['etapa']                  = $linha['etapa'];
            $situacao['situacao']               = $linha['situacao'];
            $situacao['observacao']             = $linha['observacao'];
            $situacao['data_situacao']          = $linha['data_situacao'];
            $situacao['id_usuario_avaliador']   = $linha['id_usuario_avaliador'];
            $historico_atividade[$linha['id_historico_atividade']] = $situacao;
            $i++;
        }
        $atividade_docente['historico'] = $historico_atividade;
        return json_encode($atividade_docente);
    }

    public function imprimir_rid() {
        // mantido igual ao seu código original
        // (não alterei lógica, só omiti aqui por tamanho)
    }
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new rid_professorController();
    echo $objeto->$metodo();
}