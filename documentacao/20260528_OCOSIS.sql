-- PROJETO OCOSIS - 2026

CREATE TABLE turma (
  id_turma INT(11) NOT NULL AUTO_INCREMENT,
  desc_turma VARCHAR(255) NOT NULL,
  turno VARCHAR(50) NOT NULL,
  ano_letivo INT(11) NOT NULL,
  semestre_letivo INT(11) DEFAULT NULL,
  trimestre_letivo INT(11) DEFAULT NULL,
  PRIMARY KEY (id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE alunos (
  id_aluno int NOT NULL AUTO_INCREMENT,
  id_turma int DEFAULT NULL,
  nome_aluno varchar(150) NOT NULL,
  num_simade varchar(50) NOT NULL,
  dt_nascimento date NOT NULL,
  PRIMARY KEY (id_aluno),
  UNIQUE KEY (num_simade),
  CONSTRAINT fk_alunos_turma FOREIGN KEY (id_turma) REFERENCES turma(id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

create table tipo_func(
  id_tipo_func int NOT NULL AUTO_INCREMENT,
  desc_funcionario varchar(150) NOT NULL, 
  PRIMARY KEY (id_tipo_func)
);

CREATE TABLE funcionarios (
  id_funcionario int NOT NULL AUTO_INCREMENT,
  id_tipo_func int DEFAULT NULL,
  nome_funcionario varchar(150) NOT NULL,
  email_funcionario varchar(150) NOT NULL,
  senha_hash varchar(255) NOT NULL,
  cargo_funcionario varchar(100) NOT NULL,
  PRIMARY KEY (id_funcionario),
  CONSTRAINT fk_tipo_funcionario
  FOREIGN KEY (id_tipo_func)
  REFERENCES tipo_func (id_tipo_func)
);

create table disciplinas(
id_disciplina  int NOT NULL AUTO_INCREMENT,
desc_disciplina varchar(150) NOT NULL,
PRIMARY KEY (id_disciplina)
);

CREATE TABLE professor_disciplina (
  id_funcionario INT NOT NULL,
  id_disciplina INT NOT NULL,
  ano_letivo INT NOT NULL,  
  PRIMARY KEY (id_funcionario, id_disciplina, ano_letivo),
  CONSTRAINT fk_pd_funcionario FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
  CONSTRAINT fk_pd_disciplina  FOREIGN KEY (id_disciplina) REFERENCES disciplinas(id_disciplina)
) ENGINE=InnoDB;

CREATE TABLE tipo_infracao (
  id_tipo_infracao int NOT NULL AUTO_INCREMENT,
  desc_infracao varchar(150) NOT NULL,
  PRIMARY KEY (id_tipo_infracao)
);

CREATE TABLE ocorrencias (
  id_ocorrencia int NOT NULL AUTO_INCREMENT,
  id_aluno int NOT NULL,
  id_funcionario int NOT NULL,
  id_turma int NOT NULL,
  id_tipo_infracao varchar(255) NOT NULL,
  data_ocorrencia date NOT NULL,
  horario time NOT NULL,
  disciplina varchar(100) NOT NULL,
  desc_ocorrencia text NOT NULL,
  data_registro_sistema datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_ocorrencia),
  CONSTRAINT FK_AlunoOcorrencia FOREIGN KEY (id_aluno) REFERENCES alunos(id_aluno),
  CONSTRAINT FK_FuncionarioOcorrencia FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id_funcionario),
  CONSTRAINT FK_TurmaOcorrencia FOREIGN KEY (id_turma) REFERENCES turma(id_turma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

