-- Banco de dados: misc
create database misc;

use misc; 

-- Estrutura da tabela autos
CREATE TABLE autos (
  autos_id int(11) NOT NULL,
  make varchar(255) DEFAULT NULL,
  model varchar(255) DEFAULT NULL,
  year int(11) DEFAULT NULL,
  mileage int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO autos (autos_id, make, model, year, mileage) VALUES
(1, '1', 'mo1', 2023, 633),
(2, '2', 'ddd', 1111, 1111),
(3, 'chama', 'fdfdf', 2023, 1111);

CREATE TABLE users (
user_id INTEGER NOT NULL AUTO_INCREMENT,
name VARCHAR(128),
email VARCHAR(128),
pass VARCHAR(128),
 PRIMARY KEY(user_id)
) ENGINE=InnoDB CHARSET=utf8;

 INSERT INTO users (name,email,pass) VALUES 
     ('Chuck','csev@umich.edu','1a52e17fa899cf40fb04cfc42e6352f1');
INSERT INTO users (name,email,pass) VALUES
     ('UMSI','umsi@umich.edu','1a52e17fa899cf40fb04cfc42e6352f1');                

