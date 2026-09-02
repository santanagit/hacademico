-- -----------------------------------------------------
-- Table `bd_hacademico`.`tipo_atividade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`tipo_atividade` (
  `id_tipo_atividade` INT NOT NULL AUTO_INCREMENT,
  `descricao` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_tipo_atividade`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bd_hacademico`.`atividade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_hacademico`.`atividade` (
  `id_atividade` INT NOT NULL AUTO_INCREMENT,
  `id_tipo_atividade` INT NOT NULL,
  `descricao` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`id_atividade`),
  INDEX `fk_atividade_docente_tipo_atividade1_idx` (`id_tipo_atividade` ASC),
  CONSTRAINT `fk_atividade_docente_tipo_atividade1`
    FOREIGN KEY (`id_tipo_atividade`)
    REFERENCES `bd_hacademico`.`tipo_atividade` (`id_tipo_atividade`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB;

INSERT INTO tipo_atividade (descricao) VALUES ('Aulas');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Preparação e Manutenção do Ensino');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Apoio ao Ensino');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Orientação');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Pesquisa e Inovação');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Extensão');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Gestão Institucional e Representações');
INSERT INTO tipo_atividade (descricao) VALUES ('Atividades de Qualificação e Capacitação');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (1,'Aula');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (1,'Preparação aula EAD');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Estudo, Planejamento e Elaboração de Materiais e Práticas Pedagógicas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Preparação de Aulas Teóricas e Práticas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Organização de Material Pedagógico');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Produção e Correção dos Instrumentos de Avaliação');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Atendimento de Alunos Extraclasse (física ou virtual)');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Registro de Atividades Acadêmicas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (2,'Estudo, Planejamento e Elaboração de Materiais e Práticas Pedagógicas');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (3,'Participação Banca de Trabalho de Conclusão de Curso');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (3,'Atendimento de Alunos em Regime de Exercício Domiciliar');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (3,'Orientação em Olímpiadas do Conhecimento e/ou Competições Diversas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (3,'Organização, Coordenação e/ou Acompanhamento de Visitas Técnicas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (3,'Nivelamento sem Constituição de Turma');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Orientação de Estágio');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Coordenação e Participação como Colaborador em Projeto de Ensino');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Orientação Acadêmica');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Orientação em Monitorias de Ensino e Iniciação à Docência');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Orientação de Trabalhos de Conclusão de Curso');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Orientação ou Coorientação de Mestrado ou Doutorado');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (4,'Participação na Elaboração e Revisão de Projetos Pedagógicos de Cursos');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Coordenação e Participação como colaborador em Projeto de Pesquisa');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Orientação de Aluno de Iniciação à Pesquisa Científica e/ou Tecnológica');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Coordenação e/ou Participação de Grupo de Pesquisa Cadastrado no CNPq');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação de Banca Examinadora de Tese de Doutorado e/ou Dissertação de Mestrado e/ou Monografia de Especialização');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação de Banca Examinadora de Qualificação de Mestrado ou Douturado');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação de Banca Examinadora de Qualificação de Mestrado ou Douturado');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Preparação de Artigo Técnico-Científico a ser publicado em anais de Eventos Acadêmico-Científicos Locais, Regionais, Nacionais ou Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Preparação de Artigo Técnico-Científico a ser publicado em periódico de circulação local ou nacional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Preparação de Artigo Técnico-Científico a ser publicado em periódico indexado nacional ou internacional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Preparação de Livro ou de Capítulo de Livro Didático, Cultural ou Técnico; Produção de Relatório Técnico, Maniual Técnico e/ou Didático com ISBN');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Editoração de Revistas Científicas Locais, Regionais, Nacionais ou Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Editoração, Organização e/ou Tradução de Livros e/ou Periódicos Acadêmicos, Científicos ou Técnicos');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação em Conselho Editoral Local, Regional, Nacional ou Internacional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação, como editor, membro de conselho e/ou parecerista de Publicações Acadêmico Científicas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Tradução de Artigo Didático, Cultural, Artístico ou Técnico');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação em Banco de Avaliadores de Pesquisa, Comitê ou Comissão Científica');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Consultor ad hoc na Análise de Projetos, em Seleção de Editais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Consultor ad hoc, na Condição de Convidado, em Eventos Acadêmicos');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Coordenação ou Participação em Comissão Organizadora de Oficinas, Seminários e outros Eventos Científicos, Locais, Regionais, Nacionais ou Internacional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação como Conferencista Convidado em Eventos Científicos, Locais, Regionais, Nacionais ou Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação em Eventos Acadêmicos-Científicos Locais, Regionais, Nacionais e Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Participação em Visita ou Missão Internacional, Devidamente autorizada pela Instituição para Desenvolver Atividades Acadêmicas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Desenvolvimento e Registro de propriedades Imtelectuais ou Inovação Tecnológica Cadastradas no NITTEC, tais como Elaboração, Submissão e Registro de Patentes, Registro de Software, Desenho Industrial ou Projeto Piloto, Entre Outras');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Desenvolvimento de Aplicativos Computacionais, Registrados ou Publicados em Livros ou Revistas Indexadas');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Organização e/ou Coordenação de Pesquisa de Campo Institucional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (5,'Coordenação de Institutos nacionais de Ciência e Tecnologia e Inovação Externos');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Coordenação e Participação como Colaborador em Programas e Projetos de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Coordenação de Cursos e Eventos de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Publicação de Pôsters, Resumos e/ou artigos Resultantes de Projetos de Extensão, em Periódicos de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Prestação de Serviços: Conjunto de Ações, tais como Consultorias, Laudos Técnicos e Assessorias, vinculadas às Áres de Atuação do IF Sudeste MG');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Atividades Resultantes de Projetos e Programas de Extensão, Tais como Apresentações em Eventos e Publicações de Caráter Extensionista');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Organização e/ou Coordenação de Visitas Técnicas Institucionais de Caráter Extensionista');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Coordenação e/ou Participação de Grupos de Estudos em Ativiades de Extensão Cadastrados na Diretoria de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Relatório, Parcial ou Final, de Atividades Locais, Regionais, Nacionais ou Internacionais de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Orientação de Alunos em Cumprimento de Atividades e/ou de Projetos de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Coordenação de Núcleos de Estudos Interdisciplinares');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Tutoria de Empresas Juniores');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Atividades em Cursos e Eventos de Extensão Aprovados e Cadastrados na PROEX ou Diretorias de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Preparação de Trabalho a ser Apresentado em Eventos Artísticos-Culturais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Editoração de Revistas Culturais, de Extensão Locais, Regionais, Nacionais ou Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Participação como Conferencista Convidado em Eventos Desportivos ou Artístico-Culturais, Locais, Regionais, Nacionais ou Internacionais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Atividades de Assessoria, Minicurso em Congresso, Consultoria, Perícia ou Sindiância cadastradas na PROEX e ou Diretorias de Extensão');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Participação em Concertos, Recitais e Apresentações Diversas como Instrumentista, Orquestradorm Arranjador, Compositor, Regente ou Solista');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Produção Artística em Mídia: Documentarios e/ou Material Didático, Programa de Televisão, Rádio, Video ou Videoconferência, Gravação e Edição de CD, DVD ou Outras Mídias');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Direção e Montagem de Espetáculos Musicais, Teatrais, Dança e Exposições Apresentadas ao Público');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (6,'Outras Atividades de Natureza Similar');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Diretorias Sistêmicas, Chefias e Coordenadorias e Ensino, Pesquisa e Extensão, Planejamento Institucional');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades de Coordenação de Curso');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades de Chefia ou Coordenação de Laboratório de Pesquisa, Ensino, Desenvolvimento Tecnológico e/ou Inovação');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades Referentes aos Processos de Cotação, Compra, Conferência de Materiais de Processos Licitatórios');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades não Remuneradas de Participação em Comissões Permanentes, Comitês, Fóruns e Representações Internas e Externas ao IF Sudeste MG');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Representação Acadêmica e Participação em Órgãos de Formulação e Execução de Políticas Públicas de Ensino, Ciência e Tecnologia e de Políticas Sociais');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades de Participação em Comissões Temporarias');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades de Representação Interna tais como Colegiados, Conselhos, Núcleos e Docentes Estruturantes ');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividade de Representação Externa');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Representação na Entidade Sindical ou de Associação de Docentes que Legalmente Representa a Categoria');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Atividades de Participação em Banca Examinadora de Concurso Público para Professor Efetivo, Processos Simplificados de Docentes, bem Como Bancas de Seleção de Estagiários');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (7,'Participação em Banca Examinadora de Seleção de Doutorado, Mestrado e Especialização');

INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (8,'Curso de Graduação');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (8,'Curso de Pós-Graduação');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (8,'Curso de Mestrado');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (8,'Curso de Doutorado');
INSERT INTO atividade (id_tipo_atividade,descricao) VALUES (8,'Curso de Capacitação');