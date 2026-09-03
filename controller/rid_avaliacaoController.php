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

class rid_avaliacaoController {

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

    public function listar() {

        $tabela = '';
        $id_pid = '';
        $css_horas_executadas = '';
        $vet_situacao_quantidade = array();
        $vet_situacao_quantidade['TOTAL'] = 0;
        $vet_situacao_quantidade['APROVADA'] = 0;
        $vet_situacao_quantidade['AGUARDANDO AVALIAÇÃO'] = 0;
        $vet_situacao_quantidade['REPROVADA'] = 0;
        $vet_situacao_quantidade['NÃO EXECUTADA'] = 0;

        $result_usuario = $this->usuarioM->getUsuarioId($_POST['id_usuario']);
        $linha_usuario = mysqli_fetch_assoc($result_usuario);

        $result_periodo = $this->periodoM->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);

        $tabela .= '<div class="panel panel-info" id="painel_dados">';
        $tabela .= '<div class="panel panel-heading">Situação RID</div>';
        $tabela .= '<div class="panel panel-body">';
        $tabela .= '<div class="container-fluid">';

        $tabela .= '<table class="table table-bordered">';
        $tabela .= '<tr>';
        $tabela .= '<td class="col-sm-6 text-center">Período de preenchimento:<b> ' . $linha_periodo['rid_inicio_formatado'] . ' à ' . $linha_periodo['rid_fim_formatado'] . '</b></td>';
        $tabela .= '<td class="col-sm-6 text-center">Data atual:<b> ' . date("d/m/Y") . '</b></td>';
        $tabela .= '</tr>';

        $result_pid = $this->pidM->getPidPeriodoProfessor($_POST['id_periodo'], $_POST['id_usuario']);
        if (mysqli_num_rows($result_pid) > 0) {

            $linha_pid = mysqli_fetch_assoc($result_pid);
            $id_pid = $linha_pid['id_pid'];
        } else {
            $tabela .= '<tr>';
            $tabela .= '<td class="alert alert-danger text-center">PID não encontrado!</td>';
            $tabela .= '</tr>';
            $tabela .= '</table>';

            $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
            $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar';
            $tabela .= '</button>';

            $tabela .= '</div></div></div>';

            return json_encode(array('tabela' => $tabela));
        }

        $result_historico_rid = $this->historico_pidM->getSituacao($id_pid, 'RID');
        if ($result_historico_rid) {

            $linha_historico_rid = mysqli_fetch_assoc($result_historico_rid);

            $css_horas_executadas = 'readonly style="width:80px;text-align:center;box-shadow: 0 0 0 0;border:0 none;outline: 0;background-color:inherit"';
            $tabela .= '<tr>';
            if ($linha_historico_rid['situacao'] == 'AGUARDANDO ENVIO') {
                $tabela .= '<th colspan="4" class="alert alert-warning text-center">';
                $tabela .= $linha_historico_rid['situacao'];
                $tabela .= '<br><br>Prévia do preenchimento';
            } else if ($linha_historico_rid['situacao'] == 'ENVIADO') {
                $tabela .= '<th colspan="4" class="alert alert-info text-center">';
                $tabela .= $linha_historico_rid['situacao'];
                $tabela .= '<br><br><span style="color:red">Aguardando a avaliação!</span>';
            } else if ($linha_historico_rid['situacao'] == 'APROVADO') {
                $tabela .= '<th colspan="4" class="alert alert-success text-center">';
                $tabela .= $linha_historico_rid['situacao'];
            } else if ($linha_historico_rid['situacao'] == 'REPROVADO') {
                $tabela .= '<th colspan="4" class="alert alert-danger text-center">';
                $tabela .= $linha_historico_rid['situacao'];
            } else if ($linha_historico_rid['situacao'] == 'RETORNADO PARA CORREÇÃO') {
                $tabela .= '<th colspan="4" class="alert alert-warning text-center">';
                $tabela .= $linha_historico_rid['situacao'];
            } else {
                $tabela .= '<th colspan="4" class="alert alert-danger text-center">';
                $tabela .= 'Situação não identificada pelo sistema: ' . $linha_historico_rid['situacao'];
                $tabela .= '<br>Entre em contato com o administrador do sistema!';
                $tabela .= '</th></tr></table>';

                $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
                $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar';
                $tabela .= '</button>';

                $tabela .= '</div></div></div>';

                return json_encode(array('tabela' => $tabela));
            }
        } else {
            $tabela .= '<th colspan="4" class="alert alert-danger text-center">';
            $tabela .= 'RID não cadastrado!';
            $tabela .= '</th></tr></table>';

            $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
            $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar';
            $tabela .= '</button>';

            $tabela .= '</div></div></div>';

            return json_encode(array('tabela' => $tabela));
        }

        $tabela .= '</th></tr></table>';

        $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
        $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar';
        $tabela .= '</button>';

        $tabela .= '</div></div></div>';

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

        $grupos_planejadas = array();
        $grupos_executadas = array();
        $atividades_docente = array();

        // Painéis por grupo (1,3,4,6,7)
        $ordenacao = array('id_tipo_atividade' => 'ASC');
        $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), $ordenacao);
        while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

            $id_tipo_atividade = $linha_tipo_atividade['id_tipo_atividade'];
            $descricao = $linha_tipo_atividade['descricao'];

            if ($id_tipo_atividade != 2) {

                $tabela .= '<div class="panel panel-info" id="painel_atividade_' . $id_tipo_atividade . '">';
                $tabela .= '<div class="panel panel-heading">' . $descricao . '</div>';
                $tabela .= '<div class="panel panel-body">';

                // Orientações (apoio, orientação, extensão, gestão)
                if ($id_tipo_atividade == 3) {
                    $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                            . '<b>Orientações:</b><br><br>'
                            . '<ul>'
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
                            . '<li>Para todas as atividades de <b>extensão</b>, a carga horária semanal deve ser calculada por: <b>CHS = total de horas da atividade no semestre ÷ 20</b>.</li>'
                            . '<li>Considera-se <b>20</b> o número padrão de semanas do semestre letivo.</li>'
                            . '<li>A comprovação deve ser emitida pela <b>PROEX</b> ou pela <b>Diretoria de Extensão</b>.</li>'
                            . '</ul></div>';
                } else if ($id_tipo_atividade == 7) {
                    $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                            . '<b>Orientações:</b><br><br>'
                            . '<ul>'
                            . '<li>A comprovação deve ser feita por <b>declaração</b> emitida pela chefia responsável, coordenação ou presidência da comissão.</li>'
                            . '<li>A declaração deve conter: identificação da atividade; período de atuação; descrição das atividades desempenhadas; carga horária total desenvolvida no semestre; e assinatura da chefia responsável.</li>'
                            . '</ul></div>';
                }

                $tabela .= '<div id="msg_' . $id_tipo_atividade . '"></div>';

                $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_' . $id_tipo_atividade . '">';
                $tabela .= '<thead><tr>';
                $tabela .= '<th style="width:38%">Atividade</th>';
                $tabela .= '<th style="width:38%">Descrição</th>';
                $tabela .= '<th style="width:10%;text-align:center">Horas Planejadas</th>';
                $tabela .= '<th style="width:10%;text-align:center">Horas Executadas</th>';
                $tabela .= '<th style="width:2%;text-align:center"></th>';
                $tabela .= '<th style="width:2%;text-align:center"></th>';
                $tabela .= '</tr></thead><tbody>';

                $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                $parametros = array('atividade.id_tipo_atividade' => $id_tipo_atividade);
                $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                $soma_grupo_planejadas = 0;
                $soma_grupo_executadas = 0;
                while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                    $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                    $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);
                    
                    $result_historico_atividade_pid = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'PID');
                    if ($result_historico_atividade_pid) {
                        $linha_historico_atividade_pid = mysqli_fetch_assoc($result_historico_atividade_pid);
                    } else {
                        $linha_historico_atividade_pid['situacao'] = 'NÃO PLANEJADA';
                    }

                    
                    if (
                            ($linha_historico_atividade['situacao'] != 'CANCELADA') &&
                            ($linha_historico_atividade['situacao'] != 'REPROVADA') &&
                            ($linha_historico_atividade['situacao'] != 'NÃO EXECUTADA')
                    ) {
                        $soma_grupo_executadas += $linha_atividade_docente['horas_executadas'];
                    }
                    
                    if (
                              ($linha_historico_atividade_pid['situacao'] != 'CANCELADA') &&
                              ($linha_historico_atividade_pid['situacao'] != 'NÃO PLANEJADA')
                          ) {                    
                        $soma_grupo_planejadas += $linha_atividade_docente['horas_planejadas'];
                    } else {
                        $linha_atividade_docente['horas_planejadas'] = 0;
                    }
                    
                    
                    $atividades_docente[$id_tipo_atividade][] = $linha_historico_atividade['situacao'];

                    $tabela .= '<tr>';
                    $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                    $tabela .= '<td>' . $linha_atividade_docente['descricao'] . '</td>';
                    $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_executadas . ' type="text" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '"></td>';
                    $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_executadas . ' type="text" name="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_executadas'] . '"></td>';

                    if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                        $tabela .= '<td>';
                        $tabela .= '<a title="Avaliar atividade" href="#void" onclick="abrirModal(\'modal_formulario\',\'avaliar_atividade\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                        $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                        $tabela .= '</a></td><td>';
                        $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                    } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                        $tabela .= '<td>';
                        $tabela .= '<a title="Avaliar atividade" href="#void" onclick="abrirModal(\'modal_formulario\',\'avaliar_atividade\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                        $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                        $tabela .= '</a></td><td>';
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:blue" title="Atividade aprovada"></span>';
                    } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                        $tabela .= '<td>';
                        $tabela .= '<a title="Avaliar atividade" href="#void" onclick="abrirModal(\'modal_formulario\',\'avaliar_atividade\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                        $tabela .= '<span class="glyphicon glyphicon-edit"></span>';
                        $tabela .= '</a></td><td>';
                        $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                    } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                        $tabela .= '<td>';
                        $tabela .= '<a title="Visualizar" href="#void" onclick="abrirModal(\'modal_historico\',\'visualizar_atividade\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                        $tabela .= '<span class="glyphicon glyphicon-eye-open"></span>';
                        $tabela .= '</a></td><td>';
                        $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                    } else if ($linha_historico_atividade['situacao'] == 'NÃO EXECUTADA') {
                        $tabela .= '<td>';
                        $tabela .= '<a title="Visualizar" href="#void" onclick="abrirModal(\'modal_historico\',\'visualizar_atividade\',' . $linha_atividade_docente['id_atividade_docente'] . ',' . $linha_atividade_docente['id_tipo_atividade'] . ')" style="color:green">';
                        $tabela .= '<span class="glyphicon glyphicon-eye-open"></span>';
                        $tabela .= '</a></td><td>';
                        $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade não executada"></span>';
                    } else {
                        $tabela .= 'ERRO:';
                    }
                    $tabela .= '</td></tr>';


                    
                    $soma_grupo_executadas = round($soma_grupo_executadas, 2);
                    $soma_grupo_planejadas = round($soma_grupo_planejadas, 2);
                }
                $grupos_executadas[$id_tipo_atividade] = $soma_grupo_executadas;
                $grupos_planejadas[$id_tipo_atividade] = $soma_grupo_planejadas;

                $tabela .= '<tr>';
                $tabela .= '<td></td>';
                $tabela .= '<td style="font-weight:bold; text-align:right;padding-top:10px">TOTAL:</td>';
                $tabela .= '<td align="center"><input id="soma_planejadas_' . $id_tipo_atividade . '" name="soma_planejadas_' . $id_tipo_atividade . '" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_planejadas . '"></td>';
                $tabela .= '<td align="center"><input id="soma_executadas_' . $id_tipo_atividade . '" name="soma_executadas_' . $id_tipo_atividade . '" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_executadas . '"></td>';
                $tabela .= '<td></td><td></td>';
                $tabela .= '</tr>';

                $tabela .= '</tbody></table></div></div>';

                // Painel especial para grupo 2 quando estamos no grupo 1
                if ($id_tipo_atividade == 1) {

                    $tabela .= '<div class="panel panel-info" id="painel_tipo_atividade_2">';
                    $tabela .= '<div class="panel panel-heading">Atividades de Preparação e Manutenção do Ensino</div>';
                    $tabela .= '<div class="panel panel-body">';

                    $tabela .= '<div class="alert alert-warning" style="text-align:justify">'
                            . '<b>Orientações:</b><br><br>'
                            . '<ul>'
                            . '<li>A soma das horas das atividades deste grupo, somadas às atividades de apoio ao ensino, não deve ultrapassar 1,5 hora por hora de aula executada.</li>'
                            . '<li>A participação nos <b>conselhos de classe</b> deve ser registrada neste grupo (último item) e comprovada por declaração emitida pelo coordenador de curso.</li>'
                            . '</ul></div>';

                    $tabela .= '<div id="msg_2"></div>';

                    $tabela .= '<table class="table table-striped table-hover table-condensed" id="tabela_tipo_atividade_2">';
                    $tabela .= '<thead><tr>';
                    $tabela .= '<th width="76%">Atividade</th>';
                    $tabela .= '<th style="width:10%;text-align:center">Horas Planejadas</th>';
                    $tabela .= '<th style="width:10%;text-align:center">Horas Executadas</th>';
                    $tabela .= '<th style="width:4%;text-align:center"></th>';
                    $tabela .= '</tr></thead><tbody>';

                    // Verifica se todas as disciplinas foram avaliadas (APROVADA ou NÃO EXECUTADA)
                    $acao = '';
                    foreach ($atividades_docente as $value) {
                        $quantidade_atividades = count($value);
                        $qtd_aprovadas = 0;
                        $qtd_nao_exec = 0;
                        foreach ($value as $v) {
                            if (($v == 'APROVADA') || ($v == 'APROVADA COM ALTERAÇÃO')) {
                                $qtd_aprovadas++;
                            } else if ($v == 'NÃO EXECUTADA') {
                                $qtd_nao_exec++;
                            }
                        }
                        if (($qtd_aprovadas + $qtd_nao_exec) == $quantidade_atividades) {
                            $acao = 'APROVAR';
                        } else {
                            $acao = 'VERIFICAR';
                        }
                    }

                    $ordenacao = array('atividade.id_tipo_atividade' => 'ASC', 'atividade.id_atividade' => 'ASC');
                    $parametros = array('atividade.id_tipo_atividade' => 2);
                    $result_atividade_docente = $this->atividade_docenteM->listar($id_pid, $parametros, $ordenacao);

                    $soma_grupo_executadas = 0;
                    $soma_grupo_planejadas = 0;
                    while ($linha_atividade_docente = mysqli_fetch_assoc($result_atividade_docente)) {

                        $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                        $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);

                        $result_historico_atividade_pid = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'PID');
                        if ($result_historico_atividade_pid) {
                            $linha_historico_atividade_pid = mysqli_fetch_assoc($result_historico_atividade_pid);
                        } else {
                            $linha_historico_atividade_pid['situacao'] = 'NÃO PLANEJADA';
                        }                        
                        

                        if (
                                ($linha_historico_atividade_pid['situacao'] != 'CANCELADA') &&
                                ($linha_historico_atividade_pid['situacao'] != 'NÃO PLANEJADA')
                            ) {
                            $soma_grupo_planejadas += $linha_atividade_docente['horas_planejadas'];
                        } else {
                            $linha_atividade_docente['horas_planejadas'] = 0;
                        }
                        $soma_grupo_executadas += $linha_atividade_docente['horas_executadas'];
                        
                        $tabela .= '<tr>';
                        $tabela .= '<td>' . $linha_atividade_docente['atividade'] . '</td>';
                        $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_executadas . ' type="text" name="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_planejadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_planejadas'] . '"></td>';
                        $tabela .= '<td align="center"><input class="form-control" ' . $css_horas_executadas . ' type="text" name="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" id="horas_executadas_' . $linha_atividade_docente['id_atividade_docente'] . '" value="' . $linha_atividade_docente['horas_executadas'] . '"></td>';
                        $tabela .= '<td>';

                        // Automatismo de aprovação/re-torno grupo 2
                        if (($acao == 'APROVAR') && ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO')) {
                            $campos['id_atividade_docente'] = $linha_atividade_docente['id_atividade_docente'];
                            $campos['etapa'] = 'RID';
                            $campos['situacao'] = 'APROVADA';
                            $campos['observacao'] = 'Aprovada pelo sistema após a avaliação de todas as disciplinas.';
                            $campos['id_usuario_avaliador'] = $_SESSION['id_usuario'];
                            $this->historico_atividadeM->inserir($campos);
                            $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                            $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);
                        } else if (($acao == 'VERIFICAR') && ($linha_historico_atividade['situacao'] == 'APROVADA')) {
                            $campos['id_atividade_docente'] = $linha_atividade_docente['id_atividade_docente'];
                            $campos['etapa'] = 'RID';
                            $campos['situacao'] = 'AGUARDANDO AVALIAÇÃO';
                            $campos['observacao'] = 'Retornado para AGUARDANDO AVALIAÇÃO devido a disciplina não aprovada.';
                            $campos['id_usuario_avaliador'] = $_SESSION['id_usuario'];
                            $this->historico_atividadeM->inserir($campos);
                            $result_historico_atividade = $this->historico_atividadeM->getSituacaoAtividade($linha_atividade_docente['id_atividade_docente'], 'RID');
                            $linha_historico_atividade = mysqli_fetch_assoc($result_historico_atividade);
                        }

                        $atividades_docente[2][] = $linha_historico_atividade['situacao'];

                        if ($linha_historico_atividade['situacao'] == 'AGUARDANDO AVALIAÇÃO') {
                            $tabela .= '<span class="glyphicon glyphicon-time" style="color:orange" title="Aguardando envio para avaliação"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'APROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-up" style="color:blue" title="Atividade aprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'REPROVADA') {
                            $tabela .= '<span class="glyphicon glyphicon-thumbs-down" style="color:red" title="Atividade reprovada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'CANCELADA') {
                            $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade cancelada"></span>';
                        } else if ($linha_historico_atividade['situacao'] == 'NÃO EXECUTADA') {
                            $tabela .= '<span class="glyphicon glyphicon-minus" style="color:red" title="Atividade não executada"></span>';
                        } else {
                            $tabela .= 'ERRO';
                        }
                        $tabela .= '</td></tr>';

                        $soma_grupo_executadas = round($soma_grupo_executadas, 2);
                        $soma_grupo_planejadas = round($soma_grupo_planejadas, 2);
                    }
                    $grupos_executadas[2] = $soma_grupo_executadas;
                    $grupos_planejadas[2] = $soma_grupo_planejadas;

                    $tabela .= '<tr>';
                    $tabela .= '<td style="font-weight:bold; text-align:right;padding-top:10px">TOTAL:</td>';
                    $tabela .= '<td align="center"><input id="soma_planejadas_2" name="soma_planejadas_2" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_planejadas . '"></td>';
                    $tabela .= '<td align="center"><input id="soma_executadas_2" name="soma_executadas_2" readonly class="form-control" style="width:80px;text-align:center" type="text" value="' . $soma_grupo_executadas . '"></td>';
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
        $tabela .= '<th style="width:30%">Grupo</th>';
        $tabela .= '<th style="width:10%;text-align:center">Total de atividades</th>';
        $tabela .= '<th style="width:10%;text-align:center">Atividades Aprovadas</th>';
        $tabela .= '<th style="width:10%;text-align:center">Aguardando Avaliação</th>';
        $tabela .= '<th style="width:10%;text-align:center">Atividades Não Executadas</th>';
        $tabela .= '<th style="width:10%;text-align:center">Atividades Reprovadas</th>';
        $tabela .= '<th style="width:10%;text-align:center">Horas Planejadas</th>';
        $tabela .= '<th style="width:10%;text-align:center">Horas Executadas</th>';
        $tabela .= '</tr></thead><tbody>';

        $result_tipo_atividade = $this->tipo_atividadeM->listar(array(), array('id_tipo_atividade' => 'ASC'));
        while ($linha_tipo_atividade = mysqli_fetch_assoc($result_tipo_atividade)) {

            $total_atividades = 0;
            $vet_situacao = array();
            if (isset($atividades_docente[$linha_tipo_atividade['id_tipo_atividade']])) {
                $total_atividades = count($atividades_docente[$linha_tipo_atividade['id_tipo_atividade']]);
                $vet_situacao = array_count_values($atividades_docente[$linha_tipo_atividade['id_tipo_atividade']]);
                if (!isset($vet_situacao['APROVADA']))
                    $vet_situacao['APROVADA'] = 0;
                if (!isset($vet_situacao['AGUARDANDO AVALIAÇÃO']))
                    $vet_situacao['AGUARDANDO AVALIAÇÃO'] = 0;
                if (!isset($vet_situacao['REPROVADA']))
                    $vet_situacao['REPROVADA'] = 0;
                if (!isset($vet_situacao['NÃO EXECUTADA']))
                    $vet_situacao['NÃO EXECUTADA'] = 0;
            } else {
                $total_atividades = 0;
                $vet_situacao['APROVADA'] = 0;
                $vet_situacao['AGUARDANDO AVALIAÇÃO'] = 0;
                $vet_situacao['REPROVADA'] = 0;
                $vet_situacao['NÃO EXECUTADA'] = 0;
            }

            $tabela .= '<tr>';
            if ($linha_tipo_atividade['id_tipo_atividade'] == 1) {
                $tabela .= '<td>Disciplinas</td>';
            } else {
                $tabela .= '<td>' . $linha_tipo_atividade['descricao'] . '</td>';
            }
            if ($total_atividades == $vet_situacao['APROVADA']) {
                $tabela .= '<td style="text-align:center;color:green;font-weight:bold">' . $total_atividades . '</td>';
            } else {
                $tabela .= '<td style="text-align:center;color:red;font-weight:bold">' . $total_atividades . '</td>';
            }
            $tabela .= '<td style="text-align:center;color:blue;font-weight:bold">' . $vet_situacao['APROVADA'] . '</td>';

            if ($vet_situacao['AGUARDANDO AVALIAÇÃO'] == 0) {
                $tabela .= '<td style="text-align:center;color:black">' . $vet_situacao['AGUARDANDO AVALIAÇÃO'] . '</td>';
            } else {
                $tabela .= '<td style="text-align:center;color:orange;font-weight:bold">' . $vet_situacao['AGUARDANDO AVALIAÇÃO'] . '</td>';
            }

            if ($vet_situacao['NÃO EXECUTADA'] == 0) {
                $tabela .= '<td style="text-align:center">' . $vet_situacao['NÃO EXECUTADA'] . '</td>';
            } else {
                $tabela .= '<td style="text-align:center;color:red;font-weight:bold">' . $vet_situacao['NÃO EXECUTADA'] . '</td>';
            }

            if ($vet_situacao['REPROVADA'] == 0) {
                $tabela .= '<td style="text-align:center">' . $vet_situacao['REPROVADA'] . '</td>';
            } else {
                $tabela .= '<td style="text-align:center;color:red;font-weight:bold">' . $vet_situacao['REPROVADA'] . '</td>';
            }

            $chs_planej = isset($grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']]) ? $grupos_planejadas[$linha_tipo_atividade['id_tipo_atividade']] : 0;
            $chs_exec = isset($grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']]) ? $grupos_executadas[$linha_tipo_atividade['id_tipo_atividade']] : 0;

            $tabela .= '<td style="text-align:center">' . $chs_planej . '</td>';
            $tabela .= '<td style="text-align:center">' . $chs_exec . '</td>';
            $tabela .= '</tr>';

            $vet_situacao_quantidade['TOTAL'] += $total_atividades;
            $vet_situacao_quantidade['APROVADA'] += $vet_situacao['APROVADA'];
            $vet_situacao_quantidade['AGUARDANDO AVALIAÇÃO'] += $vet_situacao['AGUARDANDO AVALIAÇÃO'];
            $vet_situacao_quantidade['REPROVADA'] += $vet_situacao['REPROVADA'];
            $vet_situacao_quantidade['NÃO EXECUTADA'] += $vet_situacao['NÃO EXECUTADA'];
        }

        $total_planejadas = round(array_sum($grupos_planejadas), 2);
        $total_executadas = round(array_sum($grupos_executadas), 2);

        $tabela .= '<tr>';
        $tabela .= '<th style="text-align:right">TOTAIS:</th>';
        $tabela .= '<th style="text-align:center">' . $vet_situacao_quantidade['TOTAL'] . '</th>';
        $tabela .= '<th style="text-align:center">' . $vet_situacao_quantidade['APROVADA'] . '</th>';
        $tabela .= '<th style="text-align:center">' . $vet_situacao_quantidade['AGUARDANDO AVALIAÇÃO'] . '</th>';
        $tabela .= '<th style="text-align:center">' . $vet_situacao_quantidade['NÃO EXECUTADA'] . '</th>';
        $tabela .= '<th style="text-align:center">' . $vet_situacao_quantidade['REPROVADA'] . '</th>';
        $tabela .= '<th style="text-align:center"><input name="soma_planejadas" id="soma_planejadas" readonly type="text" style="text-align:center;border:0px;width:80px;background-color:inherit" value="' . $total_planejadas . '"></th>';
        $tabela .= '<th style="text-align:center"><input name="soma_executadas" id="soma_executadas" readonly type="text" style="text-align:center;border:0px;width:80px;background-color:inherit" value="' . $total_executadas . '"></th>';
        $tabela .= '</tr>';
        $tabela .= '</tbody></table>';

        // Totais de atividades para validação no avaliar_rid
        $tabela .= '<input type="hidden" name="total_atividades" id="total_atividades" value="' . $vet_situacao_quantidade['TOTAL'] . '">';
        $tabela .= '<input type="hidden" name="total_aprovadas" id="total_aprovadas" value="' . $vet_situacao_quantidade['APROVADA'] . '">';
        $tabela .= '<input type="hidden" name="total_aguardando" id="total_aguardando" value="' . $vet_situacao_quantidade['AGUARDANDO AVALIAÇÃO'] . '">';
        $tabela .= '<input type="hidden" name="total_reprovadas" id="total_reprovadas" value="' . $vet_situacao_quantidade['REPROVADA'] . '">';
        $tabela .= '<input type="hidden" name="total_nao_executadas" id="total_nao_executadas" value="' . $vet_situacao_quantidade['NÃO EXECUTADA'] . '">';

        // Somatórios por grupo para regra 1,5h
        $soma_executadas_1 = isset($grupos_executadas[1]) ? $grupos_executadas[1] : 0;
        $soma_executadas_2 = isset($grupos_executadas[2]) ? $grupos_executadas[2] : 0;
        $soma_executadas_3 = isset($grupos_executadas[3]) ? $grupos_executadas[3] : 0;

        $tabela .= '<input type="hidden" name="soma_executadas_1" id="soma_executadas_1" value="' . $soma_executadas_1 . '">';
        $tabela .= '<input type="hidden" name="soma_executadas_2" id="soma_executadas_2" value="' . $soma_executadas_2 . '">';
        $tabela .= '<input type="hidden" name="soma_executadas_3" id="soma_executadas_3" value="' . $soma_executadas_3 . '">';

        $tabela .= '<div id="msg_9" class="col-sm-12"></div>';

        if ($linha_historico_rid['situacao'] == 'ENVIADO') {

            $tabela .= '<div class="row">';

            $tabela .= '<div class="form-group col-sm-3">';
            $tabela .= '<label for="situacao_rid">Situacao do RID</label>';
            $tabela .= '<select id="situacao_rid" name="situacao_rid" class="form-control" onchange="habilita_correcao(this)">';
            $tabela .= '<option value=""></option>';
            $tabela .= '<option value="APROVADO">APROVADO</option>';
            if (isset($_POST['situacao_rid']) && $_POST['situacao_rid'] == "RETORNADO PARA CORREÇÃO") {
                $tabela .= '<option selected="selected" value="RETORNADO PARA CORREÇÃO">RETORNADO PARA CORREÇÃO</option>';
            } else {
                $tabela .= '<option value="RETORNADO PARA CORREÇÃO">RETORNADO PARA CORREÇÃO</option>';
            }
            $tabela .= '<option value="REPROVADO">REPROVADO</option>';
            $tabela .= '</select></div>';

            $tabela .= '<div class="form-group col-sm-3">';
            $tabela .= '<label for="rid_correcao_fim">Prazo para correção</label>';
            $tabela .= '<input type="date" class="form-control" name="rid_correcao_fim" id="rid_correcao_fim">';
            $tabela .= '</div>';

            $tabela .= '<div class="form-group col-sm-6" style="padding-top:24px">';
            $tabela .= '<button type="button" class="btn btn-success form-control" id="btn_avaliar_rid" style="margin-right:10px;width:170px" onClick="avaliar_rid()">';
            $tabela .= '<span class="glyphicon glyphicon-send" style="padding-right:10px"></span> Avaliar RID</button>';

            $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
            $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar</button>';
            $tabela .= '</div>';
        } else {

            $tabela .= '<button type="button" style="width:170px" class="btn btn-danger form-inline" onclick="location.href=\'pid_gestao.php?id_periodo=' . $_POST['id_periodo'] . '\'">';
            $tabela .= '<span class="glyphicon glyphicon-arrow-left" style="padding-right:10px"></span>Voltar</button>';
        }

        $tabela .= '</div></div></div>';

        return json_encode(array('tabela' => $tabela));
    }

    public function avaliar_atividade() {

        $res = false;
        $campos = array();
        if ($this->formularioValido()) {

            $campos['id_atividade_docente'] = $_POST['id_atividade_docente'];
            $campos['id_atividade'] = $_POST['id_atividade'];
            $campos['descricao'] = $_POST['descricao'];
            $campos['horas_executadas'] = str_replace(',', '.', $_POST['horas_executadas']);
            $campos['etapa'] = 'RID';
            $campos['observacao'] = $_POST['observacao'];
            if ($_POST['situacao'] == 'APROVADA COM ALTERAÇÃO') {
                $campos['situacao'] = 'APROVADA';
            } else {
                $campos['situacao'] = $_POST['situacao'];
            }

            $res = $this->atividade_docenteM->avaliar_atividade_rid($campos);
            if ($res) {
                $this->msg .= '<div class="alert alert-success">Atividade avaliada com sucesso!</div>';
            } else {
                $this->msg .= '<div class="alert alert-danger">Erro ao inserir - Contactar o administrador do sistema</div>';
            }
        }
        return json_encode(array('resultado' => $res, 'msg' => $this->msg));
    }

    public function avaliar_rid() {

        $resultado = false;

        // 1) Regra 1,5h no RID: (grupo 2 + grupo 3) <= 1,5 * grupo 1 (horas executadas)
        if (
                isset($_POST['soma_executadas_1'], $_POST['soma_executadas_2'], $_POST['soma_executadas_3']) &&
                ($_POST['soma_executadas_1'] > 0)
        ) {

            $g1 = floatval($_POST['soma_executadas_1']); // Disciplinas
            $g2 = floatval($_POST['soma_executadas_2']); // Preparação
            $g3 = floatval($_POST['soma_executadas_3']); // Apoio
            $total23 = $g2 + $g3;
            $limite15 = $g1 * 1.5;

            if (
                    ($total23 > $limite15) &&
                    ($_POST['situacao_rid'] != 'RETORNADO PARA CORREÇÃO') &&
                    ($_POST['situacao_rid'] != 'REPROVADO')
            ) {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'A soma das cargas horárias semanais das '
                        . '"Atividades de Preparação e Manutenção do Ensino" ('
                        . number_format($g2, 2, ',', '.') . ' h) e das '
                        . '"Atividades de Apoio ao Ensino" ('
                        . number_format($g3, 2, ',', '.') . ' h) '
                        . 'totaliza <b>' . number_format($total23, 2, ',', '.') . ' h</b>, '
                        . 'ultrapassando o limite de <b>'
                        . number_format($limite15, 2, ',', '.') . ' h</b> '
                        . 'permitido (1,5 hora por hora de aula executada de '
                        . '<b>' . number_format($g1, 2, ',', '.') . ' h</b>).';
                $this->msg .= '</div>';

                // 2) Proibir APROVAR RID com atividades pendentes
            } else if (
                    ($_POST['situacao_rid'] == 'APROVADO') &&
                    isset($_POST['total_aguardando']) &&
                    ($_POST['total_aguardando'] > 0)
            ) {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Não é possível aprovar o RID enquanto houver atividades pendentes de avaliação.';
                $this->msg .= '</div>';

                // 3) Situação obrigatória
            } else if ($_POST['situacao_rid'] == '') {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Preencha a situação do RID !!!';
                $this->msg .= '</div>';

                // 4) Prazo obrigatório se retornar para correção
            } else if (
                    ($_POST['situacao_rid'] == 'RETORNADO PARA CORREÇÃO') &&
                    ($_POST['rid_correcao_fim'] == '')
            ) {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Preencha o prazo de correção !!!';
                $this->msg .= '</div>';

                // 5) Tudo ok: grava histórico
            } else {

                $campos['id_pid'] = $_POST['id_pid'];
                $campos['etapa'] = 'RID';
                $campos['situacao'] = $_POST['situacao_rid'];

                if ($_POST['situacao_rid'] == 'APROVADO') {
                    $this->msg .= '<div class="alert alert-success text-center">RID aprovado com sucesso!</div>';
                } else {
                    $this->msg .= '<div class="alert alert-success text-center">RID gravado com sucesso!</div>';
                }

                $result_historico_rid = $this->historico_pidM->inserir($campos);
                if ($result_historico_rid) {

                    $resultado = true;

                    if ($_POST['situacao_rid'] == 'RETORNADO PARA CORREÇÃO') {

                        $campos_rid['id_pid'] = $_POST['id_pid'];
                        $campos_rid['rid_correcao_inicio'] = date('Y-m-d H:i:s');
                        $campos_rid['rid_correcao_fim'] = $_POST['rid_correcao_fim'];

                        $this->pidM->atualizar_correcao_rid($campos_rid);
                    }
                } else {
                    $this->msg = '<div class="alert alert-danger text-center">';
                    $this->msg .= 'Erro ao tentar enviar RID. Entre em contato com o Administrador do Sistema !!!';
                    $this->msg .= '</div>';
                }
            }
        } else {
            // Caso faltem dados para 1,5h, mantém só as demais validações
            if (
                    ($_POST['situacao_rid'] == 'APROVADO') &&
                    isset($_POST['total_aguardando']) &&
                    ($_POST['total_aguardando'] > 0)
            ) {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Não é possível aprovar o RID enquanto houver atividades pendentes de avaliação.';
                $this->msg .= '</div>';
            } else if ($_POST['situacao_rid'] == '') {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Preencha a situação do RID !!!';
                $this->msg .= '</div>';
            } else if (
                    ($_POST['situacao_rid'] == 'RETORNADO PARA CORREÇÃO') &&
                    ($_POST['rid_correcao_fim'] == '')
            ) {
                $this->msg .= '<div class="alert alert-danger text-center">';
                $this->msg .= 'Preencha o prazo de correção !!!';
                $this->msg .= '</div>';
            } else {

                $campos['id_pid'] = $_POST['id_pid'];
                $campos['etapa'] = 'RID';
                $campos['situacao'] = $_POST['situacao_rid'];

                if ($_POST['situacao_rid'] == 'APROVADO') {
                    $this->msg .= '<div class="alert alert-success text-center">RID aprovado com sucesso!</div>';
                } else {
                    $this->msg .= '<div class="alert alert-success text-center">RID gravado com sucesso!</div>';
                }

                $result_historico_rid = $this->historico_pidM->inserir($campos);
                if ($result_historico_rid) {

                    $resultado = true;

                    if ($_POST['situacao_rid'] == 'RETORNADO PARA CORREÇÃO') {

                        $campos_rid['id_pid'] = $_POST['id_pid'];
                        $campos_rid['rid_correcao_inicio'] = date('Y-m-d H:i:s');
                        $campos_rid['rid_correcao_fim'] = $_POST['rid_correcao_fim'];

                        $this->pidM->atualizar_correcao_rid($campos_rid);
                    }
                } else {
                    $this->msg = '<div class="alert alert-danger text-center">';
                    $this->msg .= 'Erro ao tentar enviar RID. Entre em contato com o Administrador do Sistema !!!';
                    $this->msg .= '</div>';
                }
            }
        }

        return json_encode(array('resultado' => $resultado, 'msg' => $this->msg));
    }

    public function carregarAtividade() {
        $select = '<label for="id_atividade">Atividade:</label>';
        $select .= '<select readonly id="id_atividade" name="id_atividade" class="form-control" style="width:100%;">';
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
        } else if (trim($_POST['horas_executadas']) == '') {
            $this->msg = 'O preenchimento do campo CHS é obrigatório!';
            $valido = false;
        } else if (trim($_POST['situacao']) == '') {
            $this->msg = 'O preenchimento do campo Situação é obrigatório!';
            $valido = false;
        } else if ((trim($_POST['situacao']) != 'APROVADA') && (trim($_POST['observacao']) == '')) {
            $this->msg = 'O preenchimento do campo observação é obrigatório quando a atividade for reprovada ou aprovada com alteração!';
            $valido = false;
        }

        if (!$valido) {
            $this->msg = '<div class="alert alert-danger">' . $this->msg . '</div>';
        }
        return $valido;
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
                $atividade_docente['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/hacademico/comprovantes/comprovante_' . $linha['id_comprovante'] . '.pdf?nc=' . random_int(1, 10000);
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
    $objeto = new rid_avaliacaoController();
    echo $objeto->$metodo();
}