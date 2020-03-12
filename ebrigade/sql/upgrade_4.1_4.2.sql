#====================================================;
#  Upgrade v4.2;
#====================================================;

# ------------------------------------;
# dipl�mes
# ------------------------------------;
INSERT INTO diplome_param_field (FIELD, FIELD_NAME, CATEGORY, DISPLAY_ORDER)
VALUES ('18', 'Num�ro �v�nement', 'Divers', '3');

UPDATE diplome_param_field SET FIELD_NAME = 'Pr�nom NOM' WHERE FIELD = 0;
UPDATE diplome_param_field SET FIELD_NAME = 'PRENOM NOM' WHERE FIELD = 1;
UPDATE diplome_param_field SET FIELD_NAME = 'Pr�nom Nom' WHERE FIELD = 2;
UPDATE diplome_param_field SET FIELD_NAME = 'Civilit� Pr�nom NOM' WHERE FIELD = 15;

# ------------------------------------;
# g�olocalisation
# ------------------------------------;
insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('DIVIDERM','divider','INFO', 26, '', '', ''),
('GEOLOC','Geolocalisation','INFO',27, 'Geolocaliser le personnel', 'gps.php','map-marker');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('GEOLOC','permission',56),
('GEOLOC','geolocalize_enabled',1);

ALTER TABLE demande ADD D_BY INT NULL AFTER D_DATE;
ALTER TABLE gps ADD ADDRESS VARCHAR(500) NOT NULL AFTER LNG;

INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES
('G', 'G�olocalisation');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('DEMGPS', 'G', 'Demande de g�olocalisation'),
('GPS', 'G', 'G�olocalisation r�ussie');

# ------------------------------------;
# remplacements SP
# ------------------------------------;
DROP TABLE IF EXISTS remplacement;
CREATE TABLE remplacement (
R_ID int(11) NOT NULL  AUTO_INCREMENT,
E_CODE int(11) NOT NULL,
EH_ID tinyint(4) NOT NULL DEFAULT '1',
REPLACED int(11) NOT NULL,
SUBSTITUTE int(11) DEFAULT '0',
REQUEST_DATE datetime NOT NULL,
REQUEST_BY int(11) NOT NULL,
ACCEPTED tinyint(4) NOT NULL DEFAULT '0',
ACCEPT_DATE datetime DEFAULT NULL,
ACCEPT_BY int(11) DEFAULT NULL,
APPROVED tinyint(4) NOT NULL DEFAULT '0',
APPROVED_DATE datetime DEFAULT NULL,
APPROVED_BY int(11) DEFAULT NULL,
REJECTED tinyint(4) NOT NULL DEFAULT '0',
REJECT_DATE datetime DEFAULT NULL,
REJECT_BY int(11) DEFAULT NULL,
PRIMARY KEY (R_ID),
INDEX(REPLACED),
INDEX(APPROVED),
INDEX(E_CODE)
);

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('REMPLACE','Remplacements','GAR', 4, 'Voir les remplacements', 'remplacements.php','user-times');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE','permission',61),
('REMPLACE','gardes',1);

# ------------------------------------;
# Traumatisme
# ------------------------------------;
ALTER TABLE victime ADD VI_TRAUMATISME TINYINT NOT NULL DEFAULT '0' AFTER VI_REPARTI;

# ------------------------------------;
# Restreindre permission Organigramme
# ------------------------------------;
UPDATE fonctionnalite SET F_FLAG = '1' WHERE F_ID = 55;

# ------------------------------------;
# Phone numbers
# ------------------------------------;
ALTER TABLE evenement CHANGE E_CONTACT_TEL E_CONTACT_TEL VARCHAR(20) NULL;

# ------------------------------------;
# Menu Evenements
# ------------------------------------;

ALTER TABLE menu_group CHANGE MG_ICON MG_ICON VARCHAR(20) NULL;
ALTER TABLE menu_item CHANGE MI_ICON MI_ICON VARCHAR(20) NULL;

delete from menu_group where MG_CODE='EVE';
delete from menu_item where MI_CODE in ('EVENTADD','EVENT');
delete from menu_condition where MC_CODE in('EVENTADD','EVENT');

INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ORDER) VALUES
('EVE', 'Ev�nements', '6');
insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('EVENT','Tous les �v�nements','EVE', 1, 'Voir tous les �v�nements', 'evenement_choice.php?ec_mode=default&page=1', null),
('EVENTADD', 'Ajouter', 'EVE', 2, 'Ajouter un �v�nement', 'evenement_edit.php?action=create', 'plus');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('EVENTADD','permission',15),
('EVENTADD','evenements',1);

# ------------------------------------;
# Menu �l�ments facturables
# ------------------------------------;
delete from menu_item where MI_CODE ='ELEMFAC';
insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('ELEMFAC','El�ments facturables','ADMIN', 3, 'Configuration des �l�ments facturables', 'element_facturable.php?page=1&from=top', 'euro');
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('ELEMFAC','permission',29),
('ELEMFAC','evenements',1),
('ELEMFAC','assoc',1);


# ------------------------------------;
# 2eme prenom
# ------------------------------------;
ALTER TABLE pompier ADD P_PRENOM2 VARCHAR(20) NULL AFTER P_PRENOM;
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDP32', 'P', 'Modification du 2�me pr�nom');

# ------------------------------------;
# fonctionnalit�
# ------------------------------------;
UPDATE fonctionnalite SET F_LIBELLE = 'Permissions globales' WHERE F_ID = 24;
UPDATE fonctionnalite SET F_LIBELLE = 'Recevoir une notification par mail quand une personne est<br>inscrite sur une garde apr�s publication du tableau, ou pour les remplacements.' WHERE F_ID = 60;

# ------------------------------------;
# libelle menu
# ------------------------------------;
UPDATE menu_item SET MI_NAME = 'Voir les �v�nements' WHERE MI_CODE = 'EVENT';

# ------------------------------------;
# index SP
# ------------------------------------;
ALTER TABLE remplacement ADD INDEX (SUBSTITUTE);

# ------------------------------------;
# intervention_equipe
# ------------------------------------;
DROP TABLE IF EXISTS intervention_equipe;
CREATE TABLE intervention_equipe (
EL_ID INT NOT NULL,
E_CODE INT NOT NULL,
EE_ID SMALLINT NOT NULL,
PRIMARY KEY (EL_ID, E_CODE, EE_ID));

ALTER TABLE intervention_equipe ADD INDEX (E_CODE);

# ------------------------------------;
# technical fix
# ------------------------------------;
SET sql_mode = '';
update evenement_horaire set EH_DATE_FIN=EH_DATE_DEBUT where EH_DATE_FIN='00-00-0000';
delete from audit where A_DEBUT='00-00-0000';
ALTER TABLE audit CHANGE A_DEBUT A_DEBUT DATETIME NOT NULL;
ALTER TABLE message CHANGE M_DATE M_DATE DATETIME NOT NULL;
ALTER TABLE smslog CHANGE S_DATE S_DATE DATETIME NOT NULL;
ALTER TABLE disponibilite CHANGE D_DATE D_DATE DATE NOT NULL;
ALTER TABLE indisponibilite CHANGE I_DEBUT I_DEBUT DATE NOT NULL;
ALTER TABLE indisponibilite CHANGE I_CANCEL I_CANCEL DATETIME NULL;

# ------------------------------------;
# param diplome departemental
# ------------------------------------;
ALTER TABLE diplome_param ADD S_ID INT NOT NULL DEFAULT '0' AFTER PS_ID;
ALTER TABLE diplome_param DROP PRIMARY KEY;
ALTER TABLE diplome_param ADD PRIMARY KEY (S_ID,PS_ID, FIELD);

# ------------------------------------;
# Menu param diplome
# ------------------------------------;
delete from menu_item where MI_CODE ='PARAMDIP';
insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('PARAMDIP','Impression dipl�mes','ADMIN', 2, 'Param�trage de l''impression des dipl�mes', 'diplome_edit.php?aml=1', 'print');
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('PARAMDIP','permission',54),
('PARAMDIP','assoc',1);

delete from menu_condition where MC_CODE='PARAM' and MC_TYPE='permission' and MC_VALUE=54;
UPDATE fonctionnalite SET F_FLAG = '1' WHERE F_ID = 54;

INSERT INTO diplome_param_field(FIELD, FIELD_NAME, CATEGORY, DISPLAY_ORDER)
VALUES ('19', 'Nom du pr�sident National', 'Divers', '1');

update diplome_param_field set FIELD_NAME ='Signature pr�sident National' where FIELD=12;

# ------------------------------------;
# more logging
# ------------------------------------;
delete from log_category where LC_CODE='S';
INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES ('S', 'Section');
delete from log_type where LC_CODE='S';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('INSS', 'S', 'Ajout section'),
('INSSS', 'S', 'Ajout sous-section'),
('DELSS', 'S', 'Suppression de sous-section'),
('MOVES', 'S', 'D�placement de section'),
('UPDS1', 'S', 'Modification nom de section'),
('UPDS2', 'S', 'Modification description section'),
('UPDS3', 'S', 'Modification adresse section'),
('UPDS4', 'S', 'Modification ville section'),
('UPDS5', 'S', 'Modification code postal section'),
('UPDS6', 'S', 'Modification t�l�phone section'),
('UPDS7', 'S', 'Modification t�l�phone 2 section'),
('UPDS8', 'S', 'Modification t�l�phone 3 section'),
('UPDS9', 'S', 'Modification fax section'),
('UPDS10', 'S', 'Modification email section'),
('UPDS11', 'S', 'Modification email 2 section'),
('UPDS12', 'S', 'Modification email 3 section'),
('UPDS13', 'S', 'Modification URL section'),
('UPDS14', 'S', 'Modification agr�ments section'),
('UPDS15', 'S', 'Modification cotisation section'),
('UPDS16', 'S', 'Modification param�trage section'),
('UPDS17', 'S', 'Modification changements dans le pass�'),
('UPDS18', 'S', 'Modification masquer �v�nements '),
('UPDS19', 'S', 'Modification param�trage SMS section'),
('UPDS20', 'S', 'Modification mod�le badge section'),
('UPDS21', 'S', 'Modification signature section'),
('UPDS22', 'S', 'Modification DPS type maximum section'),
('UPDS23', 'S', 'Modification section active/inactive'),
('UPDS24', 'S', 'Modification compl�ment adresse section'),
('UPDS25', 'S', 'Modification BIC'),
('UPDS26', 'S', 'Modification IBAN');

# ------------------------------------;
# ------------------------------------;
# Nationalit�
# ------------------------------------;
alter table pompier add P_PAYS smallint(6) null;
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP50', 'P', 'Changement Nationalit�');

# cleanup potential corrupted data
delete from evenement_equipe where E_CODE=0;

# ------------------------------------;
# Bug fix data
# ------------------------------------;
update victime set VI_AGE=null, VI_BIRTHDATE=null where VI_AGE=127;

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.2' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;