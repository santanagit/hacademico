<header class="navbar navbar-default bs-docs-nav" role="banner">
    <div class="container-fluid">
        <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
                <span class="sr-only">Mudar Navegação</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><img src="img/icone.png" width="40" height="40" style="margin-top: -10px" class="img-responsive" /></a>
        </div>
        <nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
            <ul class="nav navbar-nav">
                <li class="dropdown" id="pg_inicial">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administrativo<span class="caret"></span></a>
                    <ul class="dropdown-menu">                       
                        <li><a href="perfil.php">Perfis do Sistema</a></li>
                        <li><a href="usuario.php">Usuários</a></li>
                        <li><a href="aluno_importar.php">Importar aluno</a></li>
                    </ul>
                </li>
                <li class="dropdown" id="pg_inicial">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">PID/RID<span class="caret"></span></a>
                    <ul class="dropdown-menu">                       
                        <li><a href="pid_gestao.php">Acompanhamento</a></li>
                        <li><a href="comprovante.php">Comprovantes</a></li>
                        <li><a href="comprovante_baixar.php">Baixar Comprovantes</a></li>
                    </ul>
                </li>                
                <li class="dropdown" id="pg_inicial">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Gestão<span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="periodo.php">Periodos Letivos</a></li>
                        <li><a href="curso.php">Cursos</a></li>
                        <li><a href="disciplina.php">Disciplinas</a></li>
                        <li><a href="turma.php">Turmas</a></li>
                        <li><a href="oferta_disciplina.php">Oferta de Disciplinas</a></li>
                        <li><a href="horario.php">Horário</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Relatórios<span class="caret"></span></a>
                    <ul class="dropdown-menu">                       
                        <li><a href="horario_professor.php">Horário dos Professores</a></li>
                        <li><a href="horario_imprimir.php">Horário das Turmas</a></li>
                        <li><a href="mapa_sala.php">Mapa das Salas de Aula</a></li>
                        <li><a href="horario_disciplina.php">Dias letivos das Disciplinas</a></li>
                        <!--<li><a href="lattes.php">Lattes dos Professores</a></li>-->
                    </ul>
                </li>
                

            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="meus_dados.php"><span class="glyphicon glyphicon-user"></span> <?=$_SESSION['email']?></a></li>
                <li><a href="index.php"><span class="glyphicon glyphicon-log-in"></span> Sair</a></li>
            </ul>            
        </nav>        
    </div>
</header>