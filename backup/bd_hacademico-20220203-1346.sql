CREATE DATABASE  IF NOT EXISTS `bd_hacademico` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `bd_hacademico`;
-- MariaDB dump 10.19  Distrib 10.4.19-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: bd_hacademico
-- ------------------------------------------------------
-- Server version	10.4.19-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `curso`
--

DROP TABLE IF EXISTS `curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `curso` (
  `id_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `turno` enum('Integral','Matutino','Vespertino','Noturno') NOT NULL,
  `nivel` enum('FIC','Técnico','Graduação','Especialização','Mestrado Acadêmico','Mestrado Profissional','Doutorado','Pós-Doutorado') NOT NULL,
  `regime` enum('Anual','Semestral') NOT NULL,
  `matriz` varchar(45) NOT NULL,
  `id_coordenador` int(11) NOT NULL,
  PRIMARY KEY (`id_curso`),
  KEY `fk_curso_usuario1_idx` (`id_coordenador`),
  CONSTRAINT `fk_curso_usuario1` FOREIGN KEY (`id_coordenador`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `curso`
--

LOCK TABLES `curso` WRITE;
/*!40000 ALTER TABLE `curso` DISABLE KEYS */;
/*!40000 ALTER TABLE `curso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dia`
--

DROP TABLE IF EXISTS `dia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dia` (
  `id_dia` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(25) NOT NULL,
  PRIMARY KEY (`id_dia`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dia`
--

LOCK TABLES `dia` WRITE;
/*!40000 ALTER TABLE `dia` DISABLE KEYS */;
INSERT INTO `dia` VALUES (2,'Segunda'),(3,'Terça'),(4,'Quarta'),(5,'Quinta'),(6,'Sexta');
/*!40000 ALTER TABLE `dia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `disciplina`
--

DROP TABLE IF EXISTS `disciplina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `disciplina` (
  `id_disciplina` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  `chs` int(11) NOT NULL,
  `cht` float NOT NULL,
  PRIMARY KEY (`id_disciplina`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `disciplina`
--

LOCK TABLES `disciplina` WRITE;
/*!40000 ALTER TABLE `disciplina` DISABLE KEYS */;
/*!40000 ALTER TABLE `disciplina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grade`
--

DROP TABLE IF EXISTS `grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grade` (
  `id_grade` int(11) NOT NULL AUTO_INCREMENT,
  `id_disciplina` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `modulo` int(11) NOT NULL,
  PRIMARY KEY (`id_grade`),
  KEY `fk_disciplina_matriz_disciplina1_idx` (`id_disciplina`),
  KEY `fk_disciplina_curso_curso1_idx` (`id_curso`),
  CONSTRAINT `fk_disciplina_curso_curso1` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_disciplina_matriz_disciplina1` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grade`
--

LOCK TABLES `grade` WRITE;
/*!40000 ALTER TABLE `grade` DISABLE KEYS */;
/*!40000 ALTER TABLE `grade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hora`
--

DROP TABLE IF EXISTS `hora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hora` (
  `id_hora` int(11) NOT NULL,
  `inicio` time NOT NULL,
  `fim` time NOT NULL,
  PRIMARY KEY (`id_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hora`
--

LOCK TABLES `hora` WRITE;
/*!40000 ALTER TABLE `hora` DISABLE KEYS */;
INSERT INTO `hora` VALUES (13,'13:40:00','14:30:00'),(14,'14:30:00','15:20:00'),(15,'15:40:00','16:30:00'),(16,'16:30:00','17:20:00'),(17,'17:20:00','18:10:00'),(18,'18:20:00','19:20:00'),(19,'19:20:00','20:20:00'),(20,'20:30:00','21:30:00'),(21,'21:30:00','22:30:00');
/*!40000 ALTER TABLE `hora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario`
--

DROP TABLE IF EXISTS `horario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horario` (
  `id_horario` int(11) NOT NULL AUTO_INCREMENT,
  `id_oferta_disciplina` int(11) NOT NULL,
  `id_dia` int(11) NOT NULL,
  `id_hora` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  PRIMARY KEY (`id_horario`),
  KEY `fk_horario_sala1_idx` (`id_sala`),
  KEY `fk_horario_hora1_idx` (`id_hora`),
  KEY `fk_horario_dia1_idx` (`id_dia`),
  KEY `fk_horario_oferta_disciplina1_idx` (`id_oferta_disciplina`),
  CONSTRAINT `fk_horario_dia1` FOREIGN KEY (`id_dia`) REFERENCES `dia` (`id_dia`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_horario_hora1` FOREIGN KEY (`id_hora`) REFERENCES `hora` (`id_hora`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_horario_oferta_disciplina1` FOREIGN KEY (`id_oferta_disciplina`) REFERENCES `oferta_disciplina` (`id_oferta_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_horario_sala1` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario`
--

LOCK TABLES `horario` WRITE;
/*!40000 ALTER TABLE `horario` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `oferta_disciplina`
--

DROP TABLE IF EXISTS `oferta_disciplina`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oferta_disciplina` (
  `id_oferta_disciplina` int(11) NOT NULL AUTO_INCREMENT,
  `id_disciplina` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_oferta_disciplina`),
  KEY `fk_oferta_disciplina_disciplina1_idx` (`id_disciplina`),
  KEY `fk_oferta_disciplina_usuario1_idx` (`id_usuario`),
  KEY `fk_oferta_disciplina_turma1_idx` (`id_turma`),
  CONSTRAINT `fk_oferta_disciplina_disciplina1` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplina` (`id_disciplina`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_oferta_disciplina_turma1` FOREIGN KEY (`id_turma`) REFERENCES `turma` (`id_turma`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_oferta_disciplina_usuario1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=432 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `oferta_disciplina`
--

LOCK TABLES `oferta_disciplina` WRITE;
/*!40000 ALTER TABLE `oferta_disciplina` DISABLE KEYS */;
/*!40000 ALTER TABLE `oferta_disciplina` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil`
--

DROP TABLE IF EXISTS `perfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perfil` (
  `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(45) NOT NULL,
  PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil`
--

LOCK TABLES `perfil` WRITE;
/*!40000 ALTER TABLE `perfil` DISABLE KEYS */;
INSERT INTO `perfil` VALUES (1,'Diretor'),(2,'Coordenador de Ensino'),(5,'Coordenador de Curso'),(8,'NAP'),(9,'Registro Escolar'),(10,'Assistência Estudantil'),(11,'Professor'),(12,'Aluno');
/*!40000 ALTER TABLE `perfil` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodo`
--

DROP TABLE IF EXISTS `periodo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodo` (
  `id_periodo` int(11) NOT NULL AUTO_INCREMENT,
  `ano` int(11) NOT NULL,
  `semestre` int(11) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  PRIMARY KEY (`id_periodo`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodo`
--

LOCK TABLES `periodo` WRITE;
/*!40000 ALTER TABLE `periodo` DISABLE KEYS */;
INSERT INTO `periodo` VALUES (6,2017,1,'2017-02-01','2017-06-30'),(7,2017,2,'2017-07-24','2017-12-05'),(8,2017,1,'2017-02-01','2017-12-05'),(9,2018,1,'2018-01-24','2018-12-17'),(10,2018,1,'2018-01-24','2018-06-30'),(11,2018,2,'2018-07-23','2018-12-05'),(12,2019,1,'2018-12-06','2019-06-30');
/*!40000 ALTER TABLE `periodo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sala`
--

DROP TABLE IF EXISTS `sala`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sala` (
  `id_sala` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(100) NOT NULL,
  PRIMARY KEY (`id_sala`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sala`
--

LOCK TABLES `sala` WRITE;
/*!40000 ALTER TABLE `sala` DISABLE KEYS */;
INSERT INTO `sala` VALUES (1,'Sala 1 ');
/*!40000 ALTER TABLE `sala` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turma`
--

DROP TABLE IF EXISTS `turma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `turma` (
  `id_turma` int(11) NOT NULL AUTO_INCREMENT,
  `id_curso` int(11) NOT NULL,
  `id_periodo` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `vagas` int(11) NOT NULL,
  `turno` enum('Matutino','Vespertino','Noturno','Integral') NOT NULL,
  PRIMARY KEY (`id_turma`),
  KEY `fk_turma_periodo1_idx` (`id_periodo`),
  KEY `fk_turma_curso1_idx` (`id_curso`),
  CONSTRAINT `fk_turma_curso1` FOREIGN KEY (`id_curso`) REFERENCES `curso` (`id_curso`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_turma_periodo1` FOREIGN KEY (`id_periodo`) REFERENCES `periodo` (`id_periodo`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turma`
--

LOCK TABLES `turma` WRITE;
/*!40000 ALTER TABLE `turma` DISABLE KEYS */;
/*!40000 ALTER TABLE `turma` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_perfil` int(11) NOT NULL,
  `matricula` varchar(25) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `id_chefe` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuariocol_UNIQUE` (`matricula`),
  KEY `fk_usuario_perfil1_idx` (`id_perfil`),
  KEY `fk_usuario_usuario1_idx` (`id_chefe`),
  CONSTRAINT `fk_usuario_perfil1` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_usuario1` FOREIGN KEY (`id_chefe`) REFERENCES `usuario` (`id_usuario`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (285,2,'1111111','Diretor de Ensino','santana.cin@gmail.com',1,NULL),(286,11,'1729003','Antonio Rafael Santana','antonio.santana@ifsudestemg.edu.br',1,NULL),(287,11,'1758559','Pedro Henrique de Oliveira e Silva','pedrohenrique.silva@ifsudestemg.edu.br ',1,NULL),(288,11,'1953999','Danielle Pereira Baliza','danielle.baliza@ifsudestemg.edu.br',1,NULL),(289,11,'1857310','Denisson Neves Monteiro','denisson.monteiro@ifsudestemg.edu.br',1,NULL),(290,11,'1966904','Graziany Thiago Fonseca ','graziany.fonseca@ifsudestemg.edu.br',1,NULL),(291,11,'1147455','Heber Fernandes Amaral','heber.amaral@ifsudestemg.edu.br ',1,NULL),(292,11,'3333333','Professor Substituto Meio Ambiente','substitutomeioambiente@ifsudestemg.edu.br',1,NULL),(293,11,'1033178','Larissa Carvalho Soares Amaral','larissa.soares@ifsudestemg.edu.br ',1,NULL),(294,11,'1095841','Oswaldo Guimarães Filho','oswaldo.guimaraes@ifsudestemg.edu.br ',1,NULL),(295,11,'2047063','Robson José da Silva','robson.jose@ifsudestemg.edu.br',1,NULL),(296,11,'1246592','Talita Lara Carvalho Nassur','talita.carvalho@ifsudestemg.edu.br ',1,NULL),(297,11,'3078817','Telma Suely da Silva Morais','telma.morais@ifsudestemg.edu.br ',1,NULL),(298,11,'3082930','Victor Schmidt Comitti','victor.comitti@ifsudestemg.edu.br',1,NULL),(299,11,'1550608','José Alves Junqueira Júnior','jose.junqueira@ifsudestemg.edu.br',1,NULL),(300,11,'4444444','Professor Substituto Administração','professorsubstitutoadm@ifsudestemg.edu.br',1,NULL),(301,5,'5555555','Coordenador do Curso Técnico em Informática','tecinformatica.bomsucesso@ifsudestemg.edu.br',1,NULL),(302,5,'6666666','Coordenador do Curso Tecnólogo em Gestão Ambiental','tga.bomsucesso@ifsudestemg.edu.br ',1,NULL),(303,5,'7777777','Coordenador do Curso Tecnólogo em Análise e Desenvolvimento de Sistemas','ads.bomsucesso@ifsudestemg.edu.br',1,NULL),(304,5,'8888888','Coordenador do curso Técnico em Meio Ambiente','tecmeioambiente.bomsucesso@ifsudestemg.edu.br',0,NULL),(305,5,'9999999','Coordenador do curso Técnico em Administração','tecadministracao.bomsucesso@ifsudestemg.edu.br',1,NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'bd_hacademico'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-02-03 13:46:48
