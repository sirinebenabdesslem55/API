#====================================================;
#  Upgrade v4.5;
#====================================================;

SET sql_mode = '';

#-------------------------------------;
# Notes de frais v4 - split permission
#-------------------------------------;
update fonctionnalite set F_DESCRIPTION='Modifier et faire la premi�re validation des notes de frais. <br>Recevoir les notifications par mail si une note est envoy�e pour validation.<br><b>Attention:</b> on ne peut cependant pas valider compl�tement ses propres notes de frais.'
where F_ID=73;

delete from fonctionnalite where F_ID in (74,75);
delete from habilitation where F_ID in (74,75);

insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
(74, 'Valider 2 Notes de frais', 0,7,0,'Modifier et faire la deuxi�me validation notes de frais. <br>Recevoir les notifications par mail si une note est valid�e une premi�re fois.<br><b>Attention:</b> on ne peut cependant pas valider compl�tement ses propres notes de frais.');

insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
(75, 'Rembourser Notes de frais', 0,7,0,'Modifier et rembourser les notes de frais. <br>Recevoir les notifications par mail si une note est valid�e.');

insert into habilitation (F_ID, GP_ID)
select 74, GP_ID from habilitation where F_ID=73;

insert into habilitation (F_ID, GP_ID)
select 75, GP_ID from habilitation where F_ID=73;

UPDATE note_de_frais_type_statut SET FS_CLASS = 'darkcyan12' WHERE FS_CODE = 'VAL2';

# ------------------------------------;
# �quipes id radio
# ------------------------------------;

ALTER TABLE evenement_equipe ADD EE_ID_RADIO VARCHAR(12) NULL AFTER IS_ID;

# ------------------------------------;
# notes de frais num�ro comptable
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_CODE1 SMALLINT(4) NULL AFTER NF_ID, ADD NF_CODE2 TINYINT(2) NULL AFTER NF_CODE1;
ALTER TABLE note_de_frais CHANGE NF_CODE NF_CODE3 INT(11) NULL DEFAULT NULL;
update note_de_frais set NF_CODE1=YEAR(NF_CREATE_DATE);
update note_de_frais set NF_CODE2=MONTH(NF_CREATE_DATE);

# ------------------------------------;
# detail interventions
# ------------------------------------;
ALTER TABLE evenement_log ADD EL_DATE_UPDATE DATETIME NULL AFTER EL_AUTHOR, ADD EL_UPDATED_BY INT NULL AFTER EL_DATE_UPDATE;

# ------------------------------------;
# qualifications
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'qualifications.php?page=1&pompier=0&action_comp=default' WHERE MI_CODE = 'COMP';

# ------------------------------------;
# widgets configurables
# ------------------------------------;
drop table if exists widget;
CREATE TABLE widget
(W_ID SMALLINT NOT NULL ,
W_TYPE VARCHAR(40) NOT NULL default 'box',
W_FUNCTION VARCHAR(50) NULL,
W_TITLE VARCHAR(60) NOT NULL,
W_LINK VARCHAR(200) NULL,
W_LINK_COMMENT VARCHAR(200) NULL,
W_ICON VARCHAR(25) NULL,
W_COLUMN TINYINT NOT NULL DEFAULT 1,
W_ORDER TINYINT NOT NULL DEFAULT 1,
PRIMARY KEY (W_ID));

drop table if exists widget_condition;
CREATE TABLE widget_condition
(W_ID SMALLINT NOT NULL,
WC_TYPE VARCHAR(40) NOT NULL default 'permission',
WC_VALUE VARCHAR(50) NOT NULL default '1',
PRIMARY KEY (W_ID,WC_TYPE));

drop table if exists widget_user;
CREATE TABLE widget_user
(P_ID INT NOT NULL,
W_ID SMALLINT NOT NULL,
WU_VISIBLE TINYINT NOT NULL DEFAULT 1,
WU_COLUMN TINYINT NOT NULL DEFAULT 1,
WU_ORDER TINYINT NOT NULL DEFAULT 1,
PRIMARY KEY (P_ID, W_ID));

insert into widget(W_ID, W_TYPE, W_FUNCTION, W_TITLE, W_LINK, W_LINK_COMMENT, W_ICON, W_COLUMN, W_ORDER) values
(1,'button',null,'Recherche','search_personnel.php','Rechercher personne','fa-search',1,1),
(2,'button',null,'Evenements','evenement_choice.php?ec_mode=default','Voir les �v�nements','fa-info-circle',1,2),
(3,'button',null,'Disponibilit�s','dispo.php','Saisir ses disponibilit�s','fa-calendar-check-o',1,3),
(4,'button',null,'Calendrier','calendar.php','Voir mon calendrier','fa-calendar',1,4),
(5,'button',null,'Tableau de garde','tableau_garde.php','Voir le tableau de garde','fa-table',1,5),
(6,'button',null,'Garde du jour','feuille_garde.php?evenement=0&from=gardes','Voir la garde du jour','fa-sun-o',1,6),
(7,'box','welcome','curdate',null,null,null,1,1),
(8,'box','show_duty','La veille Op�rationnelle est assur�e par',null,null,null,1,2),
(9,'box','show_birthdays','Anniversaires � souhaiter',null,null,null,1,3),
(10,'box','show_alerts_horaires','Horaires du personnel salari� � valider (100 jours)',null,null,null,1,4),
(11,'box','show_factures','Evenements termin�s non pay�s (100 jours)','factures','Voir les �v�nements termin�s non pay�s',null,1,5),
(12,'box','show_stats_manquantes','Statistiques manquantes (30 derniers jours)',null,null,null,1,6),
(13,'box','show_participations','Mes participations en cours ou futures','calendar.php','Voir mon calendrier',null,2,1),
(14,'box','show_alerts_cp','Demandes de cong�s � valider',null,null,null,2,2),
(15,'box','show_alerts_vehicules','Gestion des v�hicules','vehicule.php?page=1','Voir les v�hicules',null,2,3),
(16,'box','show_alerts_consommables','Gestion des produits consommables','consommable.php?page=1','Voir les produits consommables',null,2,4),
(17,'box','show_alerts_remplacements','Remplacements pour la garde','remplacements.php','Voir tous les remplacements',null,2,5),
(23,'box','show_alerts_remplacements','Remplacements de personnel','remplacements.php','Voir tous les remplacements',null,2,5),
(24,'box','show_proposed_remplacements','Recherche rempla�ant',null,null,null,2,5),
(25,'box','show_proposed_remplacements','Recherche rempla�ant',null,null,null,2,5),
(18,'box','show_infos','Informations','message.php?catmessage=amicale','Voir la page informations',null,2,6),
(19,'box','show_participations_mc','Mains courantes',null,null,null,3,1),
(20,'box','show_notes','Notes de frais � valider ou rembourser (60 jours)',null,null,null,3,2),
(21,'box','show_events','Calendrier des activit�s','evenement_choice.php?ec_mode=default','Voir tous les �v�nements',null,3,3),
(22,'box','show_about','A propos de cette application','about.php','Voir les informations relatives � cette application',null,3,4);

insert into widget_condition(W_ID,WC_TYPE,WC_VALUE) values
(1,'permission','56'),
(2,'evenements','1'),
(2,'gardes','0'),
(2,'permission','41'),
(3,'disponibilites','1'),
(5,'gardes','1'),
(5,'permission','61'),
(6,'gardes','1'),
(6,'permission','61');


insert into widget_condition(W_ID,WC_TYPE,WC_VALUE) values
(8,'assoc','1'),
(8,'permission','41'),
(10,'permission','13'),
(11,'assoc','1'),
(11,'evenements','1'),
(11,'permission','29'),
(12,'evenements','1'),
(12,'syndicate','0'),
(12,'permission','15'),
(13,'evenements','1'),
(14,'permission','13'),
(15,'vehicules','1'),
(15,'permission','17'),
(16,'consommables','1'),
(16,'permission','71'),
(17,'remplacements','1'),
(17,'gardes','1'),
(17,'permission','61'),
(19,'evenements','1'),
(19,'assoc','1'),
(20,'multi_check_rights_notes','1'),
(21,'evenements','1'),
(21,'permission','41'),
(23,'remplacements','1'),
(23,'gardes','0'),
(23,'permission','41'),
(24,'remplacements','1'),
(24,'gardes','1'),
(24,'permission','61'),
(25,'remplacements','1'),
(25,'gardes','0'),
(25,'permission','41');

# ------------------------------------;
# better log absence evenement
# ------------------------------------;
UPDATE log_type SET LT_DESCRIPTION = 'Modification horaires' WHERE LT_CODE = 'UPDHOR';
delete from log_type where LT_CODE='UPDABS';
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDABS', 'P', 'Modification absence sur �v�nement');

# ------------------------------------;
# mode paiement formation individuelle
# ------------------------------------;
ALTER TABLE evenement_participation ADD MODE_PAIEMENT TINYINT NULL AFTER TSP_ID;

# ------------------------------------;
# fonctionnalit� notification remplacement
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir un email de notification lorsqu\'un �v�nement est cr��, <br>ou lorsqu\'il y a une demande de remplacement sur cet �v�nement.' WHERE F_ID = 21;

# ------------------------------------;
# Notes de frais
# ------------------------------------;
update fonctionnalite set F_LIBELLE='Valider notes - tr�sorier', F_DESCRIPTION='Modifier et faire la validation tr�sorier des notes de frais. <br>Recevoir les notifications par mail si une note est envoy�e pour validation.<br><b>Attention:</b> on ne peut cependant pas valider compl�tement ses propres notes de frais.'
where F_ID=73 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);
update fonctionnalite set F_LIBELLE='Valider notes - pr�sident', F_DESCRIPTION='Modifier et faire la validation pr�sident des notes de frais. <br>Recevoir les notifications par mail si une note est envoy�e pour validation.<br><b>Attention:</b> on ne peut cependant pas valider compl�tement ses propres notes de frais.'
where F_ID=74 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

delete from note_de_frais_type_statut where FS_CODE= 'VAL1';
INSERT INTO note_de_frais_type_statut (FS_CODE,FS_DESCRIPTION,FS_CLASS,FS_ORDER)
VALUES ('VAL1', 'Valid�e', 'green12', '5');
UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Valid�e' WHERE FS_CODE in( 'VAL1');
UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Valid�e' WHERE FS_CODE in( 'VAL');

update note_de_frais_type_statut set FS_ORDER='4' where FS_CODE='VAL';
update note_de_frais_type_statut set FS_ORDER='5' where FS_CODE='VAL1';
update note_de_frais_type_statut set FS_ORDER='6' where FS_CODE='VAL2';
update note_de_frais_type_statut set FS_ORDER='7' where FS_CODE='REMB';


ALTER TABLE note_de_frais ADD S_ID INT NOT NULL DEFAULT '0' AFTER P_ID;
ALTER TABLE note_de_frais ADD INDEX (S_ID);

UPDATE note_de_frais SET note_de_frais.S_ID = (SELECT pompier.P_SECTION FROM pompier WHERE pompier.P_ID = note_de_frais.P_ID );

# ------------------------------------;
# grade
# ------------------------------------;
delete from grade where G_GRADE in ('MCN');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('MCN','M�decin Classe Normale','126','SSSM','SP');

# ------------------------------------;
# victimes PSSP
# ------------------------------------;
ALTER TABLE bilan_victime_category ADD BVC_PAGE VARCHAR(5) NOT NULL DEFAULT 'PSE' AFTER BVC_TITLE, ADD INDEX (BVC_PAGE);

delete from bilan_victime_category where BVC_PAGE='PSSP';
INSERT INTO bilan_victime_category
(BVC_CODE, BVC_TITLE, BVC_PAGE, BVC_ORDER) VALUES
('CONTACT', 'Contacter', 'PSSP', '1'),
('SIGREP', 'Signes Rep�res', 'PSSP', '2'),
('EVOLUT', 'Evolution', 'PSSP', '3'),
('SUITE', 'Suite donn�e', 'PSSP', '4'),
('BILAN2', 'Bilan', 'PSSP', '5');

delete from bilan_victime_param where BVC_CODE in ('CONTACT','SIGREP','RELAT','VERBA','RECIT','EVOLUT','SUITE','BILAN2');
INSERT INTO bilan_victime_param(BVP_ID, BVC_CODE, BVP_TITLE, BVP_COMMENT, BVP_TYPE, DOC_ONLY) VALUES 
('1900', 'CONTACT', 'T�l�phone', NULL, 'text', '0'),
('1910', 'CONTACT', 'Personne � pr�venir', NULL, 'text', '0'),
('2000', 'SIGREP', 'Agitation', NULL, 'checkbox', '0'),
('2001', 'SIGREP', 'Confusion', NULL, 'checkbox', '0'),
('2002', 'SIGREP', 'Euphorie', NULL, 'checkbox', '0'),
('2003', 'SIGREP', 'M�fiance', NULL, 'checkbox', '0'),
('2004', 'SIGREP', 'Prostration', NULL, 'checkbox', '0'),
('2005', 'SIGREP', 'Fuite Panique', NULL, 'checkbox', '1'),
('2006', 'SIGREP', 'Col�re', NULL, 'checkbox', '0'),
('2010', 'SIGREP', 'Culpabilit�', NULL, 'checkbox', '0'),
('2008', 'SIGREP', 'Sid�ration', NULL, 'checkbox', '1'),
('2011', 'SIGREP', 'Gestes Automatiques', NULL, 'checkbox', '1'),
('2012', 'SIGREP', 'Tristesse', NULL, 'checkbox', '0'),
('2013', 'SIGREP', 'D�r�alisation', NULL, 'checkbox', '1'),
('2014', 'SIGREP', 'Agressivit�', NULL, 'checkbox', '0'),
('2015', 'SIGREP', 'Angoisse', NULL, 'checkbox', '0'),
('2016', 'SIGREP', 'Pleurs', NULL, 'checkbox', '0'),
('2030', 'SIGREP', 'Contact Relationnel', 'indiquer ici le niveau du contact relationnel', 'dropdown', '0'),
('2035', 'SIGREP', 'Verbalisation', 'indiquer la verbalisation', 'dropdown', '0'),
('2040', 'SIGREP', 'R�cit de l\'�venement', 'indiquer comment la personne rqconte l\'�venement', 'dropdown', '0'),
('2100', 'EVOLUT', 'Evolution', NULL, 'dropdown', '0'),
('2103', 'EVOLUT', 'Observation', NULL, 'textarea', '0'),
('2120', 'SUITE', 'Avis m�dical', NULL, 'checkbox', '0'),
('2121', 'SUITE', 'Avis CUMP', NULL, 'checkbox', '0'),
('2125', 'SUITE', 'Evacuation', NULL, 'checkbox', '0'),
('2126', 'SUITE', 'Heure', 'heure �vacuation', 'time', '0'),
('2128', 'SUITE', 'H�pital', NULL, 'checkbox', '0'),
('2129', 'SUITE', 'Domicile seul', NULL, 'checkbox', '0'),
('2130', 'SUITE', 'Accompagn�', NULL, 'checkbox', '0'),
('2200', 'BILAN2', 'Pouls++', NULL, 'numeric', '0');

delete from bilan_victime_values where BVP_ID in ('2030','2035','2040','2100');
INSERT INTO bilan_victime_values (BVP_ID,BVP_INDEX,BVP_TEXT) values
('2030',1,'Satisfaisant'),
('2030',2,'Peu Satisfaisant'),
('2030',3,'Insatisfaisant'),
('2035',1,'Spontan�e'),
('2035',2,'Provoqu�ee'),
('2035',3,'Absente'),
('2040',1,'Factuel Exclusif'),
('2040',2,'Emotionnel Exclusif'),
('2040',3,'Factuel et �motionnel'),
('2040',4,'Amn�sie'),
('2100',1,'Sans Changement'),
('2100',2,'Am�lioration'),
('2100',3,'Aggravation');


# ------------------------------------;
# configuration militaire
# ------------------------------------;
delete from configuration where ID=59;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '59', 'army', 0, 'Configuration organisation militaire', 1,1,1,1);

delete from categorie_grade where CG_CODE in ('ARMY');
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES ('ARMY', 'Arm�e de Terre');

delete from grade where G_CATEGORY in ('ARMY');
delete from grade where G_GRADE in ('DRA1','DRA2','MDL','MCH','BG','BGC');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('SDT','Soldat de 2�me classe','1','Hommes du rang','ARMY'),
('SDT1','Soldat de 1�re classe','2','Hommes du rang','ARMY'),
('DRA2','Dragon de 2�me classe','1','Hommes du rang','ARMY'),
('DRA1','Dragon de 1�re classe','2','Hommes du rang','ARMY'),
('BG','Brigadier','5','Hommes du rang','ARMY'),
('BGC','Brigadier Chef','6','Hommes du rang','ARMY'),
('CA','Caporal','5','Hommes du rang','ARMY'),
('CAC','Caporal Chef','6','Hommes du rang','ARMY'),
('SG1','Sergent appel�','10','Sous-Officiers','ARMY'),
('SG','Sergent','11','Sous-Officiers','ARMY'),
('SC','Sergent Chef','12','Sous-Officiers','ARMY'),
('MDL','Mar�chal des Logis','11','Sous-Officiers','ARMY'),
('MCH','Mar�chal des Logis Chef','12','Sous-Officiers','ARMY'),
('AJ','Adjudant','15','Sous-Officiers','ARMY'),
('AC','Adjudant Chef','16','Sous-Officiers','ARMY'),
('MJ','Major','17','Sous-Officiers','ARMY'),
('AS','Aspirant','30','Officiers','ARMY'),
('SL','Sous Lieutenant','31','Officiers','ARMY'),
('LT','Lieutenant','32','Officiers','ARMY'),
('CP','Capitaine','33','Officiers','ARMY'),
('CT','Commandant','34','Officiers','ARMY'),
('LC','Lieutenant Colonel','35','Officiers','ARMY'),
('CL','Colonel','36','Officiers','ARMY'),
('GLBR','G�n�ral de Brigade','100','Officiers G�n�raux','ARMY'),
('GLDIV','G�n�ral de Division','105','Officiers G�n�raux','ARMY'),
('GLCA','G�n�ral de Corps d\'arm�e','110','Officiers G�n�raux','ARMY'),
('GLA','G�n�ral d\'arm�e ','120','Officiers G�n�raux','ARMY');

delete from statut where S_STATUT in ('ACT','RES','CIV');
INSERT INTO statut (S_STATUT,S_DESCRIPTION,S_CONTEXT) VALUES
('ACT', 'Militaire d\'active', '0'),
('RES', 'Militaire de r�serve', '0'),
('CIV', 'Personnel civil', '0');

delete from theme where NAME='army';
INSERT INTO theme (NAME, COLOR, COLOR2, COLOR3) VALUES ('army', 'cccc99','bbbb77', 'aaaa55');

INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE) VALUES ('10', 'army', '0');

# ------------------------------------;
# mode paiement formation individuelle
# ------------------------------------;
ALTER TABLE evenement_participation ADD NUM_CHEQUE varchar(20) NULL AFTER MODE_PAIEMENT;
ALTER TABLE evenement_participation ADD NOM_PAYEUR varchar(40) NULL AFTER NUM_CHEQUE;

# ------------------------------------;
# s�curit�, support bcrypt
# ------------------------------------;
ALTER TABLE pompier CHANGE P_MDP P_MDP VARCHAR(255) NOT NULL;

# ------------------------------------;
# geolocalisation OSM
# ------------------------------------;
delete from configuration where ID=60;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '60', 'api_provider', 'google', 'Service de g�olocaliation', 120,0,1,0);

# ------------------------------------;
# am�liorations options inscription 
# ------------------------------------;
drop table if exists evenement_option_dropdown;
CREATE TABLE evenement_option_dropdown (
EOD_ID INT NOT NULL AUTO_INCREMENT,
EO_ID INT NOT NULL,
EOD_ORDER TINYINT NOT NULL,
EOD_TEXTE VARCHAR(50) NOT NULL,
PRIMARY KEY (EOD_ID),
UNIQUE (EO_ID,EOD_TEXTE));

drop table if exists evenement_option_group;
CREATE TABLE evenement_option_group (
EOG_ID INT NOT NULL AUTO_INCREMENT,
E_CODE INT NOT NULL,
EOG_TITLE varchar(60) NOT NULL,
EOG_ORDER  TINYINT NOT NULL,
PRIMARY KEY (EOG_ID),
INDEX (E_CODE));

alter table evenement_option add EOG_ID INT NOT NULL DEFAULT 0;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.5' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;