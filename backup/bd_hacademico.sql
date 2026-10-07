-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 07/10/2026 às 15:53
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bd_hacademico`
--
CREATE DATABASE IF NOT EXISTS `bd_hacademico` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bd_hacademico`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividade`
--

DROP TABLE IF EXISTS `atividade`;
CREATE TABLE `atividade` (
  `id_atividade` int(11) NOT NULL,
  `id_tipo_atividade` int(11) NOT NULL,
  `descricao` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atividade_docente`
--

DROP TABLE IF EXISTS `atividade_docente`;
CREATE TABLE `atividade_docente` (
  `id_atividade_docente` int(11) NOT NULL,
  `id_pid` int(11) NOT NULL,
  `id_atividade` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `horas_planejadas` float NOT NULL,
  `horas_executadas` float DEFAULT NULL,
  `id_comprovante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `carga_horaria_docente`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `carga_horaria_docente`;
CREATE TABLE `carga_horaria_docente` (
`id_periodo` int(11)
,`id_usuario` int(11)
,`nome` varchar(150)
,`chs` double
,`chs_ead` double
,`modulo` enum('Semestral','Anual')
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `comprovante`
--

DROP TABLE IF EXISTS `comprovante`;
CREATE TABLE `comprovante` (
  `id_comprovante` int(11) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `inicio_vigencia` date DEFAULT NULL,
  `fim_vigencia` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comprovante_docente`
--

DROP TABLE IF EXISTS `comprovante_docente`;
CREATE TABLE `comprovante_docente` (
  `id_comprovante_docente` int(11) NOT NULL,
  `id_comprovante` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_atividade` int(11) NOT NULL,
  `horas` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `curso`
--

DROP TABLE IF EXISTS `curso`;
CREATE TABLE `curso` (
  `id_curso` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `turno` enum('Integral','Matutino','Vespertino','Noturno','EAD') NOT NULL,
  `nivel` enum('FIC','Técnico','Técnico Integrado','Graduação','Especialização','Mestrado Acadêmico','Mestrado Profissional','Doutorado','Pós-Doutorado') NOT NULL,
  `regime` enum('Anual','Semestral') NOT NULL,
  `modulo` enum('Semestral','Anual') NOT NULL DEFAULT 'Semestral',
  `matriz` varchar(45) NOT NULL,
  `id_coordenador` int(11) NOT NULL,
  `nucleo` enum('Informática','Meio Ambiente','Administração') DEFAULT NULL,
  `perfil_conclusao` text NOT NULL,
  `resolucao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `dia`
--

DROP TABLE IF EXISTS `dia`;
CREATE TABLE `dia` (
  `id_dia` int(11) NOT NULL,
  `descricao` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplina`
--

DROP TABLE IF EXISTS `disciplina`;
CREATE TABLE `disciplina` (
  `id_disciplina` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `chs` float NOT NULL,
  `cht` float NOT NULL,
  `chs_ead` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `feriado`
--

DROP TABLE IF EXISTS `feriado`;
CREATE TABLE `feriado` (
  `id_feriado` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `data_feriado` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `grade`
--

DROP TABLE IF EXISTS `grade`;
CREATE TABLE `grade` (
  `id_grade` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `modulo` int(11) NOT NULL,
  `ementa` text DEFAULT NULL,
  `cod_sigaa` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_atividade`
--

DROP TABLE IF EXISTS `historico_atividade`;
CREATE TABLE `historico_atividade` (
  `id_historico_atividade` int(11) NOT NULL,
  `id_atividade_docente` int(11) NOT NULL,
  `etapa` enum('PID','RID') NOT NULL,
  `situacao` enum('AGUARDANDO AVALIAÇÃO','APROVADA','REPROVADA','CANCELADA','NÃO EXECUTADA') NOT NULL,
  `observacao` text DEFAULT NULL,
  `data_situacao` datetime NOT NULL,
  `id_usuario_avaliador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_pid`
--

DROP TABLE IF EXISTS `historico_pid`;
CREATE TABLE `historico_pid` (
  `id_historico_pid` int(11) NOT NULL,
  `id_pid` int(11) NOT NULL,
  `etapa` enum('PID','RID') NOT NULL DEFAULT 'PID',
  `situacao` enum('AGUARDANDO ENVIO','ENVIADO','APROVADO','REPROVADO','RETORNADO PARA CORREÇÃO') NOT NULL,
  `data_situacao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `hora`
--

DROP TABLE IF EXISTS `hora`;
CREATE TABLE `hora` (
  `id_hora` int(11) NOT NULL,
  `inicio_integrado` time DEFAULT NULL,
  `fim_integrado` time DEFAULT NULL,
  `inicio_concomitante` time DEFAULT NULL,
  `fim_concomitante` time DEFAULT NULL,
  `inicio_superior` time DEFAULT NULL,
  `fim_superior` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `horario`
--

DROP TABLE IF EXISTS `horario`;
CREATE TABLE `horario` (
  `id_horario` int(11) NOT NULL,
  `id_oferta_disciplina` int(11) NOT NULL,
  `id_dia` int(11) NOT NULL,
  `id_hora` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `posicao` tinyint(1) NOT NULL DEFAULT 1,
  `data_hora` datetime NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `log_acao`
--

DROP TABLE IF EXISTS `log_acao`;
CREATE TABLE `log_acao` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `acao` enum('Login','Consulta Horário Turma','Consulta Horário Professor','Consulta Mapa Sala','Administrativo') NOT NULL,
  `data_hora` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Estrutura para tabela `oferta_disciplina`
--

DROP TABLE IF EXISTS `oferta_disciplina`;
CREATE TABLE `oferta_disciplina` (
  `id_oferta_disciplina` int(11) NOT NULL,
  `id_disciplina` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `chs` float DEFAULT NULL,
  `chs_ead` float DEFAULT NULL,
  `cht` float DEFAULT NULL,
  `tipo` enum('Aula','Preparação aula EAD') NOT NULL DEFAULT 'Aula',
  `turma_dividida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `perfil`
--

DROP TABLE IF EXISTS `perfil`;
CREATE TABLE `perfil` (
  `id_perfil` int(11) NOT NULL,
  `descricao` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `periodo`
--

DROP TABLE IF EXISTS `periodo`;
CREATE TABLE `periodo` (
  `id_periodo` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `semestre` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `pid_inicio` date DEFAULT NULL,
  `pid_fim` date DEFAULT NULL,
  `rid_inicio` date DEFAULT NULL,
  `rid_fim` date DEFAULT NULL,
  `publicado` tinyint(4) NOT NULL DEFAULT 1,
  `publicado_coordenador` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pid`
--

DROP TABLE IF EXISTS `pid`;
CREATE TABLE `pid` (
  `id_pid` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `pid_correcao_inicio` date DEFAULT NULL,
  `pid_correcao_fim` date DEFAULT NULL,
  `rid_correcao_inicio` date DEFAULT NULL,
  `rid_correcao_fim` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sabados`
--

DROP TABLE IF EXISTS `sabados`;
CREATE TABLE `sabados` (
  `id_sabados` int(11) NOT NULL,
  `id_dia` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `data` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sala`
--

DROP TABLE IF EXISTS `sala`;
CREATE TABLE `sala` (
  `id_sala` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_atividade`
--

DROP TABLE IF EXISTS `tipo_atividade`;
CREATE TABLE `tipo_atividade` (
  `id_tipo_atividade` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `turma`
--

DROP TABLE IF EXISTS `turma`;
CREATE TABLE `turma` (
  `id_turma` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `vagas` int(11) NOT NULL,
  `turno` enum('Matutino','Vespertino','Noturno','Integral','EAD') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `ultimos_acessos`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `ultimos_acessos`;
CREATE TABLE `ultimos_acessos` (
`hora_acesso` datetime
,`nome` varchar(150)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `ultimo_historico_atividade`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `ultimo_historico_atividade`;
CREATE TABLE `ultimo_historico_atividade` (
`id_atividade_docente` int(11)
,`id_historico_atividade` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `matricula` varchar(25) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `cor` varchar(20) DEFAULT NULL,
  `senha` varchar(100) NOT NULL,
  `area` enum('Informática','Meio Ambiente','Administração','Propedêutica','Setor de Ensino','Setor Administrativo') NOT NULL,
  `cpf` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para view `carga_horaria_docente`
--
DROP TABLE IF EXISTS `carga_horaria_docente`;

DROP VIEW IF EXISTS `carga_horaria_docente`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `carga_horaria_docente`  AS SELECT `turma`.`id_periodo` AS `id_periodo`, `oferta_disciplina`.`id_usuario` AS `id_usuario`, `usuario`.`nome` AS `nome`, sum(`oferta_disciplina`.`chs`) AS `chs`, sum(`oferta_disciplina`.`chs_ead`) AS `chs_ead`, `curso`.`modulo` AS `modulo` FROM ((((`oferta_disciplina` join `usuario` on(`oferta_disciplina`.`id_usuario` = `usuario`.`id_usuario`)) join `disciplina` on(`oferta_disciplina`.`id_disciplina` = `disciplina`.`id_disciplina`)) join `turma` on(`oferta_disciplina`.`id_turma` = `turma`.`id_turma`)) join `curso` on(`turma`.`id_curso` = `curso`.`id_curso`)) GROUP BY `curso`.`modulo`, `turma`.`id_periodo`, `usuario`.`nome`, `usuario`.`id_usuario` ;

-- --------------------------------------------------------

--
-- Estrutura para view `ultimos_acessos`
--
DROP TABLE IF EXISTS `ultimos_acessos`;

DROP VIEW IF EXISTS `ultimos_acessos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ultimos_acessos`  AS SELECT max(`log_acao`.`data_hora`) AS `hora_acesso`, `usuario`.`nome` AS `nome` FROM (`log_acao` join `usuario` on(`log_acao`.`id_usuario` = `usuario`.`id_usuario`)) WHERE `log_acao`.`id_usuario` not in (286,301) GROUP BY `usuario`.`nome` ORDER BY `log_acao`.`data_hora` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `ultimo_historico_atividade`
--
DROP TABLE IF EXISTS `ultimo_historico_atividade`;

DROP VIEW IF EXISTS `ultimo_historico_atividade`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `ultimo_historico_atividade`  AS SELECT `historico_atividade`.`id_atividade_docente` AS `id_atividade_docente`, max(`historico_atividade`.`id_historico_atividade`) AS `id_historico_atividade` FROM `historico_atividade` GROUP BY `historico_atividade`.`id_atividade_docente` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atividade`
--
ALTER TABLE `atividade`
  ADD PRIMARY KEY (`id_atividade`),
  ADD KEY `fk_atividade_docente_tipo_atividade1_idx` (`id_tipo_atividade`);

--
-- Índices de tabela `atividade_docente`
--
ALTER TABLE `atividade_docente`
  ADD PRIMARY KEY (`id_atividade_docente`),
  ADD KEY `fk_pid_atividade_docente1_idx` (`id_atividade`),
  ADD KEY `fk_atividade_docente_comprovante1_idx` (`id_comprovante`),
  ADD KEY `fk_atividade_docente_pid_rid1_idx` (`id_pid`);

--
-- Índices de tabela `comprovante`
--
ALTER TABLE `comprovante`
  ADD PRIMARY KEY (`id_comprovante`);

--
-- Índices de tabela `comprovante_docente`
--
ALTER TABLE `comprovante_docente`
  ADD PRIMARY KEY (`id_comprovante_docente`),
  ADD KEY `fk_comprovante_docente_comprovante_atividade1_idx` (`id_comprovante`),
  ADD KEY `fk_comprovante_docente_usuario1_idx` (`id_usuario`),
  ADD KEY `fk_comprovante_docente_atividade1_idx` (`id_atividade`);

--
-- Índices de tabela `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `fk_curso_usuario1_idx` (`id_coordenador`);

--
-- Índices de tabela `dia`
--
ALTER TABLE `dia`
  ADD PRIMARY KEY (`id_dia`);

--
-- Índices de tabela `disciplina`
--
ALTER TABLE `disciplina`
  ADD PRIMARY KEY (`id_disciplina`);

--
-- Índices de tabela `feriado`
--
ALTER TABLE `feriado`
  ADD PRIMARY KEY (`id_feriado`),
  ADD KEY `fk_feriado_periodo1_idx` (`id_periodo`);

--
-- Índices de tabela `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`id_grade`),
  ADD KEY `id_disciplina` (`id_disciplina`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Índices de tabela `historico_atividade`
--
ALTER TABLE `historico_atividade`
  ADD PRIMARY KEY (`id_historico_atividade`),
  ADD KEY `fk_historico_atividade_atividade_docente1_idx` (`id_atividade_docente`),
  ADD KEY `fk_historico_atividade_usuario1_idx` (`id_usuario_avaliador`);

--
-- Índices de tabela `historico_pid`
--
ALTER TABLE `historico_pid`
  ADD PRIMARY KEY (`id_historico_pid`),
  ADD KEY `fk_historico_pid_pid1_idx` (`id_pid`);

--
-- Índices de tabela `hora`
--
ALTER TABLE `hora`
  ADD PRIMARY KEY (`id_hora`);

--
-- Índices de tabela `horario`
--
ALTER TABLE `horario`
  ADD PRIMARY KEY (`id_horario`),
  ADD KEY `id_oferta_disciplina` (`id_oferta_disciplina`),
  ADD KEY `id_dia` (`id_dia`),
  ADD KEY `id_hora` (`id_hora`),
  ADD KEY `id_sala` (`id_sala`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `log_acao`
--
ALTER TABLE `log_acao`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `log_acao_ibfk_1` (`id_usuario`);

--
-- Índices de tabela `oferta_disciplina`
--
ALTER TABLE `oferta_disciplina`
  ADD PRIMARY KEY (`id_oferta_disciplina`),
  ADD KEY `id_disciplina` (`id_disciplina`),
  ADD KEY `id_turma` (`id_turma`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `perfil`
--
ALTER TABLE `perfil`
  ADD PRIMARY KEY (`id_perfil`);

--
-- Índices de tabela `periodo`
--
ALTER TABLE `periodo`
  ADD PRIMARY KEY (`id_periodo`);

--
-- Índices de tabela `pid`
--
ALTER TABLE `pid`
  ADD PRIMARY KEY (`id_pid`),
  ADD KEY `fk_pid_rid_usuario1_idx` (`id_usuario`),
  ADD KEY `fk_pid_rid_periodo1_idx` (`id_periodo`);

--
-- Índices de tabela `sabados`
--
ALTER TABLE `sabados`
  ADD PRIMARY KEY (`id_sabados`),
  ADD KEY `id_dia` (`id_dia`),
  ADD KEY `id_periodo` (`id_periodo`);

--
-- Índices de tabela `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`id_sala`);

--
-- Índices de tabela `tipo_atividade`
--
ALTER TABLE `tipo_atividade`
  ADD PRIMARY KEY (`id_tipo_atividade`);

--
-- Índices de tabela `turma`
--
ALTER TABLE `turma`
  ADD PRIMARY KEY (`id_turma`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_periodo` (`id_periodo`);

--
-- Índices de tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_perfil` (`id_perfil`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atividade`
--
ALTER TABLE `atividade`
  MODIFY `id_atividade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `atividade_docente`
--
ALTER TABLE `atividade_docente`
  MODIFY `id_atividade_docente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comprovante`
--
ALTER TABLE `comprovante`
  MODIFY `id_comprovante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comprovante_docente`
--
ALTER TABLE `comprovante_docente`
  MODIFY `id_comprovante_docente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `curso`
--
ALTER TABLE `curso`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `dia`
--
ALTER TABLE `dia`
  MODIFY `id_dia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplina`
--
ALTER TABLE `disciplina`
  MODIFY `id_disciplina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `feriado`
--
ALTER TABLE `feriado`
  MODIFY `id_feriado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `grade`
--
ALTER TABLE `grade`
  MODIFY `id_grade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_atividade`
--
ALTER TABLE `historico_atividade`
  MODIFY `id_historico_atividade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_pid`
--
ALTER TABLE `historico_pid`
  MODIFY `id_historico_pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `hora`
--
ALTER TABLE `hora`
  MODIFY `id_hora` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `horario`
--
ALTER TABLE `horario`
  MODIFY `id_horario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `log_acao`
--
ALTER TABLE `log_acao`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `oferta_disciplina`
--
ALTER TABLE `oferta_disciplina`
  MODIFY `id_oferta_disciplina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `perfil`
--
ALTER TABLE `perfil`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `periodo`
--
ALTER TABLE `periodo`
  MODIFY `id_periodo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pid`
--
ALTER TABLE `pid`
  MODIFY `id_pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sabados`
--
ALTER TABLE `sabados`
  MODIFY `id_sabados` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sala`
--
ALTER TABLE `sala`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tipo_atividade`
--
ALTER TABLE `tipo_atividade`
  MODIFY `id_tipo_atividade` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `turma`
--
ALTER TABLE `turma`
  MODIFY `id_turma` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atividade`
--
ALTER TABLE `atividade`
  ADD CONSTRAINT `fk_atividade_docente_tipo_atividade1` FOREIGN KEY (`id_tipo_atividade`) REFERENCES `tipo_atividade` (`id_tipo_atividade`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `atividade_docente`
--
ALTER TABLE `atividade_docente`
  ADD CONSTRAINT `fk_atividade_docente_comprovante1` FOREIGN KEY (`id_comprovante`) REFERENCES `comprovante` (`id_comprovante`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atividade_docente_pid_rid1` FOREIGN KEY (`id_pid`) REFERENCES `pid` (`id_pid`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pid_atividade_docente1` FOREIGN KEY (`id_atividade`) REFERENCES `atividade` (`id_atividade`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `comprovante_docente`
--
ALTER TABLE `comprovante_docente`
  ADD CONSTRAINT `fk_comprovante_docente_atividade1` FOREIGN KEY (`id_atividade`) REFERENCES `atividade` (`id_atividade`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comprovante_docente_comprovante_atividade1` FOREIGN KEY (`id_comprovante`) REFERENCES `comprovante` (`id_comprovante`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comprovante_docente_usuario1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`id_coordenador`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `feriado`
--
ALTER TABLE `feriado`
  ADD CONSTRAINT `fk_feriado_periodo1` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `grade`
--
ALTER TABLE `grade`
  ADD CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `grade_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `historico_atividade`
--
ALTER TABLE `historico_atividade`
  ADD CONSTRAINT `fk_historico_atividade_atividade_docente1` FOREIGN KEY (`id_atividade_docente`) REFERENCES `atividade_docente` (`id_atividade_docente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_historico_atividade_usuario1` FOREIGN KEY (`id_usuario_avaliador`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `historico_pid`
--
ALTER TABLE `historico_pid`
  ADD CONSTRAINT `fk_historico_pid_pid1` FOREIGN KEY (`id_pid`) REFERENCES `pid` (`id_pid`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `horario`
--
ALTER TABLE `horario`
  ADD CONSTRAINT `horario_ibfk_1` FOREIGN KEY (`id_oferta_disciplina`) REFERENCES `oferta_disciplina` (`id_oferta_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `horario_ibfk_2` FOREIGN KEY (`id_dia`) REFERENCES `dia` (`id_dia`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `horario_ibfk_3` FOREIGN KEY (`id_hora`) REFERENCES `hora` (`id_hora`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `horario_ibfk_4` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `horario_ibfk_5` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `log_acao`
--
ALTER TABLE `log_acao`
  ADD CONSTRAINT `log_acao_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `oferta_disciplina`
--
ALTER TABLE `oferta_disciplina`
  ADD CONSTRAINT `oferta_disciplina_ibfk_1` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `oferta_disciplina_ibfk_2` FOREIGN KEY (`id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `oferta_disciplina_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `pid`
--
ALTER TABLE `pid`
  ADD CONSTRAINT `fk_pid_rid_periodo1` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_pid_rid_usuario1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `sabados`
--
ALTER TABLE `sabados`
  ADD CONSTRAINT `sabados_ibfk_1` FOREIGN KEY (`id_dia`) REFERENCES `dia` (`id_dia`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `sabados_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `turma`
--
ALTER TABLE `turma`
  ADD CONSTRAINT `turma_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `turma_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Restrições para tabelas `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
