#====================================================
#  Upgrade v4.2
#====================================================
SET sql_mode = '';
# ------------------------------------
# diplômes
# ------------------------------------

INSERT INTO diplome_param_field (FIELD, FIELD_NAME, CATEGORY, DISPLAY_ORDER)
VALUES ('18', 'Numéro événement', 'Divers', '3');

UPDATE diplome_param_field SET FIELD_NAME = 'Prénom NOM' WHERE FIELD = 0;

UPDATE diplome_param_field SET FIELD_NAME = 'PRENOM NOM' WHERE FIELD = 1;

UPDATE diplome_param_field SET FIELD_NAME = 'Prénom Nom' WHERE FIELD = 2;

UPDATE diplome_param_field SET FIELD_NAME = 'Civilité Prénom NOM' WHERE FIELD = 15;

# ------------------------------------
# géolocalisation
# ------------------------------------
insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('DIVIDERM','divider','INFO', 26, '', '', '');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('GEOLOC','permission',56),
('GEOLOC','geolocalize_enabled',1);

INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES
('G', 'Géolocalisation');

INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('DEMGPS', 'G', 'Demande de géolocalisation'),
('GPS', 'G', 'Géolocalisation réussie');

# ------------------------------------
# remplacements SP
# ------------------------------------
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

delete from menu_item where MI_CODE='REMPLACE';
insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('REMPLACE','Remplacements','GAR', 4, 'Voir les remplacements', 'remplacements.php','user-times');

delete from menu_condition where MC_CODE='REMPLACE';
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE','permission',61),
('REMPLACE','gardes',1);


# ------------------------------------
# Restreindre permission Organigramme
# ------------------------------------
UPDATE fonctionnalite SET F_FLAG = '1' WHERE F_ID = 55;


# ------------------------------------
# Phone numbers
# ------------------------------------
ALTER TABLE evenement CHANGE E_CONTACT_TEL E_CONTACT_TEL VARCHAR(20) NULL;


# ------------------------------------
# Menu Evenements
# ------------------------------------

ALTER TABLE menu_group CHANGE MG_ICON MG_ICON VARCHAR(20) NULL;

ALTER TABLE menu_item CHANGE MI_ICON MI_ICON VARCHAR(20) NULL;


delete from menu_group where MG_CODE='EVE';

delete from menu_item where MI_CODE in ('EVENTADD','EVENT');

delete from menu_condition where MC_CODE in('EVENTADD','EVENT');


INSERT INTO menu_group (MG_CODE, MG_NAME, MG_ORDER) VALUES
('EVE', 'Evénements', '6');

insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('EVENT','Tous les événements','EVE', 1, 'Voir tous les événements', 'evenement_choice.php?ec_mode=default&page=1', null),
('EVENTADD', 'Ajouter', 'EVE', 2, 'Ajouter un évènement', 'evenement_edit.php?action=create', 'plus');


insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('EVENTADD','permission',15),
('EVENTADD','evenements',1);


# ------------------------------------
# Menu éléments facturables
# ------------------------------------
delete from menu_item where MI_CODE ='ELEMFAC';

insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('ELEMFAC','Eléments facturables','ADMIN', 3, 'Configuration des éléments facturables', 'element_facturable.php?page=1&from=top', 'euro');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('ELEMFAC','permission',29),
('ELEMFAC','evenements',1),
('ELEMFAC','assoc',1);


# ------------------------------------
# fonctionnalité
# ------------------------------------
UPDATE fonctionnalite SET F_LIBELLE = 'Permissions globales' WHERE F_ID = 24;

UPDATE fonctionnalite SET F_LIBELLE = 'Recevoir une notification par mail quand une personne est<br>inscrite sur une garde après publication du tableau, ou pour les remplacements.' WHERE F_ID = 60;


# ------------------------------------
# libelle menu
# ------------------------------------
UPDATE menu_item SET MI_NAME = 'Voir les événements' WHERE MI_CODE = 'EVENT';


# ------------------------------------
# index SP
# ------------------------------------
ALTER TABLE remplacement ADD INDEX (SUBSTITUTE);

# ------------------------------------
# intervention_equipe
# ------------------------------------
DROP TABLE IF EXISTS intervention_equipe;
CREATE TABLE intervention_equipe (
EL_ID INT NOT NULL,
E_CODE INT NOT NULL,
EE_ID SMALLINT NOT NULL,
PRIMARY KEY (EL_ID, E_CODE, EE_ID));

ALTER TABLE intervention_equipe ADD INDEX (E_CODE);

# ------------------------------------
# technical fix
# ------------------------------------
SET sql_mode = '';
update evenement_horaire set EH_DATE_FIN=EH_DATE_DEBUT where EH_DATE_FIN='00-00-0000';

delete from audit where A_DEBUT='00-00-0000';
ALTER TABLE audit CHANGE A_DEBUT A_DEBUT DATETIME NOT NULL;
ALTER TABLE message CHANGE M_DATE M_DATE DATETIME NOT NULL;
ALTER TABLE smslog CHANGE S_DATE S_DATE DATETIME NOT NULL;
ALTER TABLE disponibilite CHANGE D_DATE D_DATE DATE NOT NULL;
ALTER TABLE indisponibilite CHANGE I_DEBUT I_DEBUT DATE NOT NULL;
ALTER TABLE indisponibilite CHANGE I_CANCEL I_CANCEL DATETIME NULL;


# ------------------------------------
# param diplome departemental
# ------------------------------------
ALTER TABLE diplome_param ADD S_ID INT NOT NULL DEFAULT '0' AFTER PS_ID;

ALTER TABLE diplome_param DROP PRIMARY KEY;

ALTER TABLE diplome_param ADD PRIMARY KEY (S_ID,PS_ID, FIELD);


# ------------------------------------
# Menu param diplome
# ------------------------------------
delete from menu_item where MI_CODE ='PARAMDIP';

insert into menu_item (MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('PARAMDIP','Impression diplômes','ADMIN', 2, 'Paramétrage de l''impression des diplômes', 'diplome_edit.php?aml=1', 'print');

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('PARAMDIP','permission',54),
('PARAMDIP','assoc',1);


delete from menu_condition where MC_CODE='PARAM' and MC_TYPE='permission' and MC_VALUE=54;

UPDATE fonctionnalite SET F_FLAG = '1' WHERE F_ID = 54;

INSERT INTO diplome_param_field(FIELD, FIELD_NAME, CATEGORY, DISPLAY_ORDER)
VALUES ('19', 'Nom du président National', 'Divers', '1');

update diplome_param_field set FIELD_NAME ='Signature président National' where FIELD=12;


# ------------------------------------
# more logging
# ------------------------------------
delete from log_category where LC_CODE='S';
INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES ('S', 'Section');

delete from log_type where LC_CODE='S';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('INSS', 'S', 'Ajout section'),
('INSSS', 'S', 'Ajout sous-section'),
('DELSS', 'S', 'Suppression de sous-section'),
('MOVES', 'S', 'Déplacement de section'),
('UPDS1', 'S', 'Modification nom de section'),
('UPDS2', 'S', 'Modification description section'),
('UPDS3', 'S', 'Modification adresse section'),
('UPDS4', 'S', 'Modification ville section'),
('UPDS5', 'S', 'Modification code postal section'),
('UPDS6', 'S', 'Modification téléphone section'),
('UPDS7', 'S', 'Modification téléphone 2 section'),
('UPDS8', 'S', 'Modification téléphone 3 section'),
('UPDS9', 'S', 'Modification fax section'),
('UPDS10', 'S', 'Modification email section'),
('UPDS11', 'S', 'Modification email 2 section'),
('UPDS12', 'S', 'Modification email 3 section'),
('UPDS13', 'S', 'Modification URL section'),
('UPDS14', 'S', 'Modification agréments section'),
('UPDS15', 'S', 'Modification cotisation section'),
('UPDS16', 'S', 'Modification paramétrage section'),
('UPDS17', 'S', 'Modification changements dans le passé'),
('UPDS18', 'S', 'Modification masquer événements '),
('UPDS19', 'S', 'Modification paramétrage SMS section'),
('UPDS20', 'S', 'Modification modèle badge section'),
('UPDS21', 'S', 'Modification signature section'),
('UPDS22', 'S', 'Modification DPS type maximum section'),
('UPDS23', 'S', 'Modification section active/inactive'),
('UPDS24', 'S', 'Modification complément adresse section'),
('UPDS25', 'S', 'Modification BIC'),
('UPDS26', 'S', 'Modification IBAN');


# cleanup potential corrupted data
delete from evenement_equipe where E_CODE=0;

# ------------------------------------
# Bug fix data
# ------------------------------------
update victime set VI_AGE=null, VI_BIRTHDATE=null where VI_AGE=127;


# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='4.2' where ID=1;

# ------------------------------------
# end
# ------------------------------------
#====================================================
#  Upgrade v4.3
#====================================================

SET sql_mode = '';

# ------------------------------------
# support SMSGateway.me
# ------------------------------------
UPDATE configuration SET DESCRIPTION = 'utilisateur du compte SMS, email pour smsgateway.me, inutile dans le cas de SMS Gateway Android' WHERE ID = 10;

UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me, inutile dans les autres cas' WHERE ID = 12;



# ------------------------------------
# formation possible sur compétence
# ------------------------------------
ALTER TABLE poste ADD PS_FORMATION TINYINT NOT NULL DEFAULT '1' AFTER DESCRIPTION;

update poste p set p.PS_FORMATION=0
where not exists (select 1 from evenement e where e.PS_ID=p.PS_ID)
and p.PS_RECYCLE=0
and p.PS_PRINTABLE=0
and p.PS_SECOURISME=0;

# ------------------------------------
# tableau de garde
# ------------------------------------
delete from fonctionnalite where F_ID=7;

delete from habilitation where F_ID=7;


# ------------------------------------
# médailles collectives
# ------------------------------------
INSERT INTO categorie_agrement (CA_CODE, CA_DESCRIPTION, CA_FLAG)
VALUES ('_MED', 'Médailles collectives', '1');

INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('CD', '_MED', 'Acte de Courage et de Dévouement', '2'),
('GO', '_MED', 'Médaille Grand Or de la Sécurité Civile', '2');

ALTER TABLE agrement ADD A_COMMENT VARCHAR(100) NULL AFTER TAV_ID;

# ------------------------------------
# véhicules
# ------------------------------------
INSERT INTO vehicule_position (VP_ID, VP_LIBELLE, VP_OPERATIONNEL)
VALUES ('RENDU', 'rendu', '-1');


update vehicule set VP_ID='OP' where VP_ID='DP';
delete from vehicule_position where VP_ID='DP';

# ------------------------------------
# note frais don
# ------------------------------------
ALTER TABLE note_de_frais ADD NF_DON TINYINT NOT NULL DEFAULT '0' AFTER COMMENT, ADD INDEX (NF_DON);

# ------------------------------------
# garde SP
# ------------------------------------
ALTER TABLE equipe CHANGE ASSURE_PAR ASSURE_PAR1 SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE equipe ADD ASSURE_PAR2 SMALLINT NOT NULL DEFAULT '0' AFTER ASSURE_PAR1;

update equipe set ASSURE_PAR2 = ASSURE_PAR1;

alter table equipe ADD EQ_REGIME_TRAVAIL TINYINT NOT NULL DEFAULT '0';

update equipe set EQ_REGIME_TRAVAIL = (select VALUE from configuration where NAME='regime_travail') where EQ_TYPE='GARDE' and EQ_ID=1;

update evenement_horaire set SECTION_GARDE=(select E_S_ID from evenement where evenement.E_CODE = evenement_horaire.E_CODE);

alter table evenement drop E_S_ID;

delete from configuration where id=46;

UPDATE configuration SET DESCRIPTION = 'activer une gestion des gardes (pompiers seulement). Configurer le régime de travail sur le paramétrage des gardes.' 
WHERE ID = 3;


# ------------------------------------
# custom field
# ------------------------------------
ALTER TABLE custom_field ADD CF_ORDER SMALLINT NULL;
update custom_field set CF_ORDER = CF_ID where CF_ORDER is null;

# ------------------------------------
# grades sécurité civile
# ------------------------------------
delete from categorie_grade where CG_CODE='SC';
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES ('SC', 'Sécurité Civile');

delete from grade where G_CATEGORY='SC';
INSERT INTO grade (G_GRADE, G_DESCRIPTION, G_LEVEL, G_TYPE, G_CATEGORY)
VALUES ('EQ', 'Equipier', '1', 'secouriste', 'SC'),
('CE', 'Chef d''Equipe', '2', 'secouriste', 'SC'),
('CS', 'Chef de Secteur', '3', 'secouriste', 'SC'),
('CD', 'Chef de Dispositif', '4', 'secouriste', 'SC');

# ------------------------------------
# improve performance
# ------------------------------------
SET sql_mode = '';
ALTER TABLE evenement_horaire ADD INDEX (E_CODE,EH_DATE_DEBUT);

# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='4.3' where ID=1;

# ------------------------------------
# end
# ------------------------------------
#====================================================
#  Upgrade v4.4
#====================================================

SET sql_mode = '';

ALTER TABLE note_de_frais ADD NF_CODE INT NULL AFTER NF_ID;

UPDATE note_de_frais_type_statut SET FS_ORDER = '6' WHERE FS_CODE = 'REMB';

INSERT INTO note_de_frais_type_statut
(FS_CODE, FS_DESCRIPTION, FS_CLASS, FS_ORDER)
VALUES ('CRE', 'En cours de création', 'blue12', '0');

INSERT INTO note_de_frais_type_statut
(FS_CODE, FS_DESCRIPTION, FS_CLASS, FS_ORDER)
VALUES ('VAL2', 'Validée deux fois', 'purple12', '5');

UPDATE note_de_frais_type_statut SET FS_CODE = 'REJ', FS_DESCRIPTION='Rejetée' WHERE FS_CODE = 'REF';

UPDATE note_de_frais set NF_VALIDATED_BY=NF_STATUT_BY, NF_VALIDATED_DATE=NF_STATUT_DATE
where FS_CODE = 'VAL'
and NF_VALIDATED_DATE is null
and NF_STATUT_DATE is not null;

UPDATE note_de_frais_type_statut SET FS_CLASS = 'green12'
WHERE FS_CODE in ('VAL','VAL2');

UPDATE note_de_frais_type_statut SET FS_CLASS = 'purple12'
WHERE FS_CODE = 'REMB';

# ------------------------------------
# icone menu
# ------------------------------------
UPDATE menu_item SET MI_ICON = 'fa-calendar-times-o' WHERE MI_CODE = 'SAISEABS';

delete from menu_item where MI_CODE='DIVIDERF';

# ------------------------------------
# ID radio
# ------------------------------------
ALTER TABLE section ADD S_ID_RADIO VARCHAR(5) NULL AFTER S_ORDER, ADD UNIQUE (S_ID_RADIO);

INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('UPDS27', 'S', 'Modification ID Radio');


# ------------------------------------
# remplacements désactivables
# ------------------------------------
delete from menu_condition where MC_CODE='REMPLACE' and MC_TYPE <> 'permission';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('REMPLACE', 'remplacements', '1');

delete from configuration where ID=58;
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('58', 'remplacements', '1', 'Possibilité de gérer les demandes de remplacements sur les gardes, avec validation', '105', '0', '1', '1');

UPDATE configuration SET ORDERING=114, DESCRIPTION='Possibilité de gérer les demandes de remplacements sur les événements' WHERE id='58';


delete from menu_item where MI_CODE='REMPLACE2';

insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('REMPLACE2','Remplacements','EVE', 4, 'Voir les remplacements', 'remplacements.php','user-times');

delete from menu_condition where MC_CODE='REMPLACE2';

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE2','permission',15),
('REMPLACE2','assoc',1),
('REMPLACE2','remplacements',1);


delete from menu_condition where MC_CODE='GEOLOC';

insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('GEOLOC','permission',56),
('GEOLOC','geolocalize_enabled',1);


# ------------------------------------
# menu conditions manquantes
# ------------------------------------
delete from menu_condition where MC_CODE='EVENT' and MC_VALUE='41';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('EVENT', 'permission', '41');

delete from menu_condition where MC_CODE='EVENT' and MC_TYPE='evenements';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('EVENT', 'evenements', '1');

delete from menu_condition where MC_CODE='ELEMFAC' and MC_VALUE='29';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('ELEMFAC', 'permission', '29');

delete from menu_condition where MC_CODE='ELEMFAC' and MC_TYPE='assoc';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('ELEMFAC', 'assoc', '1');

delete from menu_condition where MC_CODE='ELEMFAC' and MC_TYPE='evenements';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('ELEMFAC', 'evenements', '1');

delete from menu_condition where MC_CODE='PARAMDIP' and MC_VALUE='54';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('PARAMDIP', 'permission', '54');

delete from menu_condition where MC_CODE='PARAMDIP' and MC_TYPE='assoc';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('PARAMDIP', 'assoc', '1');

UPDATE menu_item SET MI_TITLE = 'voir le personnel disponible pour un jour donné' WHERE MI_CODE = 'PERSODISPO';

# ------------------------------------
# commentaire horaires
# ------------------------------------
ALTER TABLE horaires ADD H_COMMENT VARCHAR(1000) NULL AFTER H_DUREE_MINUTES2;

# ------------------------------------
# evenement options
# ------------------------------------
drop table if exists evenement_option;
CREATE TABLE evenement_option
(EO_ID INT NOT NULL AUTO_INCREMENT,
E_CODE INT NOT NULL,
EO_TITLE VARCHAR(40) NOT NULL,
EO_COMMENT VARCHAR(150) NOT NULL,
EO_TYPE VARCHAR(15) NOT NULL DEFAULT 'checkbox',
EO_ORDER TINYINT NOT NULL,
PRIMARY KEY (EO_ID),
INDEX (E_CODE));

drop table if exists evenement_option_choix;
CREATE TABLE evenement_option_choix
(EO_ID INT NOT NULL,
P_ID INT NOT NULL,
E_CODE INT NOT NULL,
EOC_VALUE VARCHAR(100) NOT NULL,
PRIMARY KEY (EO_ID,P_ID),
INDEX (E_CODE,P_ID));

# ------------------------------------
# evenement tel administratif
# ------------------------------------
ALTER TABLE evenement ADD E_TEL VARCHAR(15) NULL AFTER E_LIEU_RDV;

# ------------------------------------
# conventions sur les événements
# ------------------------------------
update type_evenement set CONVENTION=1 where TE_CODE='FOR';

# ------------------------------------
# changer type evenement
# ------------------------------------
update type_evenement set TE_CODE='ALERT' where TE_CODE='MET';
update evenement set TE_CODE='ALERT' where TE_CODE='MET';

update type_bilan set TE_CODE='ALERT' where TE_CODE='MET';
update type_participation set TE_CODE='ALERT' where TE_CODE='MET';

# ------------------------------------
# paramétrage gardes
# ------------------------------------

drop table if exists type_garde;
CREATE TABLE type_garde
(EQ_ID SMALLINT NOT NULL,
EQ_NOM varchar(30) NOT NULL,
EQ_JOUR TINYINT NOT NULL default 0,
EQ_NUIT TINYINT NOT NULL default 0,
S_ID SMALLINT NOT NULL default 0,
EQ_PERSONNEL SMALLINT NOT null default 0,
EQ_VEHICULES TINYINT NOT NULL default 0,
EQ_SPP TINYINT NOT NULL default 0,
EQ_DEBUT1 time NULL,
EQ_FIN1 time NULL,
EQ_DUREE1 float NULL,
EQ_DEBUT2 time NULL,
EQ_FIN2 time NULL,
EQ_DUREE2 float NULL,
EQ_ICON varchar(150) NULL,
ASSURE_PAR1 SMALLINT NOT NULL default 0,
ASSURE_PAR2 SMALLINT NOT NULL default 0,
ASSURE_PAR_DATE DATETIME NULL,
EQ_REGIME_TRAVAIL TINYINT NOT NULL default 0,
PRIMARY KEY (EQ_ID),
INDEX (S_ID));

insert into type_garde ( EQ_ID,EQ_NOM,EQ_JOUR,EQ_NUIT,S_ID,EQ_PERSONNEL,EQ_VEHICULES,EQ_SPP,EQ_DEBUT1,EQ_FIN1,EQ_DUREE1,EQ_DEBUT2,EQ_FIN2,EQ_DUREE2,EQ_ICON,ASSURE_PAR1,ASSURE_PAR2,ASSURE_PAR_DATE,EQ_REGIME_TRAVAIL)
select EQ_ID,EQ_NOM,EQ_JOUR,EQ_NUIT,S_ID,EQ_PERSONNEL,EQ_VEHICULES,EQ_SPP,EQ_DEBUT1,EQ_FIN1,EQ_DUREE1,EQ_DEBUT2,EQ_FIN2,EQ_DUREE2,EQ_ICON,ASSURE_PAR1,ASSURE_PAR2,ASSURE_PAR_DATE,EQ_REGIME_TRAVAIL
from equipe where EQ_TYPE='GARDE' order by EQ_ID;

ALTER TABLE type_garde CHANGE EQ_PERSONNEL EQ_PERSONNEL1 SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE type_garde ADD EQ_PERSONNEL2 SMALLINT NOT NULL DEFAULT '0' AFTER EQ_PERSONNEL1;

update type_garde set EQ_PERSONNEL2 = EQ_PERSONNEL1;
update type_garde set EQ_PERSONNEL2 = 0 where EQ_NUIT=0;

ALTER TABLE type_garde ADD EQ_ADDRESS varchar(200) NULL;

delete from equipe where EQ_TYPE='GARDE';

ALTER TABLE equipe
DROP EQ_JOUR,
DROP EQ_NUIT,
DROP S_ID,
DROP EQ_TYPE,
DROP EQ_PERSONNEL,
DROP EQ_VEHICULES,
DROP EQ_SPP,
DROP EQ_DEBUT1,
DROP EQ_FIN1,
DROP EQ_DUREE1,
DROP EQ_DEBUT2,
DROP EQ_FIN2,
DROP EQ_DUREE2,
DROP EQ_ICON,
DROP ASSURE_PAR1,
DROP ASSURE_PAR2,
DROP ASSURE_PAR_DATE,
DROP EQ_REGIME_TRAVAIL;

ALTER TABLE equipe
ADD EQ_ORDER TINYINT NOT NULL DEFAULT 0;

update equipe set EQ_ORDER=EQ_ID;

drop table if exists garde_competences;
CREATE TABLE garde_competences
(GC_ID INT NOT NULL AUTO_INCREMENT,
EQ_ID INT NOT NULL,
EH_ID TINYINT NOT NULL,
PS_ID INT NOT NULL,
NB TINYINT NOT NULL,
PRIMARY KEY (GC_ID),
UNIQUE (EQ_ID,EH_ID,PS_ID));

ALTER TABLE evenement_equipe CHANGE EE_ID EE_ID INT NOT NULL;

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES ('PARAM', 'permission', '5');

# ------------------------------------
# permissions specififques materiel et conso
# ------------------------------------
UPDATE fonctionnalite
SET F_LIBELLE='Véhicules', F_DESCRIPTION = 'Ajouter ou modifier des véhicules<br> Permet d\'engager des véhicules sur les événements.'
WHERE fonctionnalite.F_ID = 17;

UPDATE menu_condition SET MC_VALUE = '70' WHERE MC_CODE = 'MAT_E' AND MC_TYPE = 'permission' AND MC_VALUE = 17;


# ------------------------------------
# fix label
# ------------------------------------
UPDATE fonctionnalite SET F_LIBELLE = 'Notification Garde' WHERE F_ID = 60;

# ------------------------------------
# webservice formation
# ------------------------------------
ALTER TABLE section ADD
SHOW_PHONE3 TINYINT NOT NULL DEFAULT '1',
ADD SHOW_EMAIL3 TINYINT NOT NULL DEFAULT '1',
ADD SHOW_URL TINYINT NOT NULL DEFAULT '1';

update section set SHOW_PHONE3=0 where S_PHONE3 is null or S_PHONE3 = '';
update section set SHOW_EMAIL3=0 where S_EMAIL3 is null or S_EMAIL3 = '';
update section set SHOW_URL=0 where S_URL is null or S_URL = '';

delete from log_type where LT_CODE in ('UPDS28','UPDS29','UPDS30');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDS28', 'S', 'Modification affichage téléphone formation'),
('UPDS29', 'S', 'Modification affichage email formation'),
('UPDS30', 'S', 'Modification affichage Site web formation');

update log_type set LT_DESCRIPTION='Modification téléphone formation'
where LT_CODE='UPDS8';
update log_type set LT_DESCRIPTION='Modification email formation'
where LT_CODE='UPDS12';
update log_type set LT_DESCRIPTION='Modification URL site Web'
where LT_CODE='UPDS13';

# ------------------------------------
# grades 
# ------------------------------------

ALTER TABLE grade CHANGE G_GRADE G_GRADE VARCHAR(6) NOT NULL;
ALTER TABLE pompier CHANGE P_GRADE P_GRADE VARCHAR(6) NOT NULL;

delete from grade where G_GRADE in ('AA','REDP1','REDP2','TEC','TECP1','TECP2');
INSERT INTO grade (G_GRADE, G_DESCRIPTION, G_LEVEL, G_TYPE, G_CATEGORY) VALUES
('AA','Adjoint Administratif','1','adjoints administratifs','PATS'),
('REDP1', 'rédacteur principal de 1ère classe', '7', 'rédacteurs', 'PATS'),
('REDP2', 'rédacteur principal de 2ème classe', '7', 'rédacteurs', 'PATS'),
('TEC', 'technicien', '26', 'adjoints technique', 'PATS'),
('TECP1', 'technicien principal de 1ère classe', '26', 'adjoints technique', 'PATS'),
('TECP2', 'technicien principal de 2ème classe', '26', 'adjoints technique', 'PATS');

delete from grade where G_GRADE in ('ISP','ICN','ISPP','ICS','ISPC','IHC','ISPE','MASP','MLTN','MCPT','MCDT','MLCL','MCOL','MHC','PPH','PPHS','PHCPT','PHCDT','PHLCL','PHCOL','VETCPT','VETCDT','VETLCL','VETCOL','CSAN2','CSAN1','CSANSU');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('ISP','Infirmier','120','SSSM','SP'),
('ICN','Infirmier classe normale','120','SSSM','SP'),
('ISPP','Infirmier Principal','121','SSSM','SP'),
('ICS','Infirmier classe supérieure','121','SSSM','SP'),
('ISPC','Infirmier Chef Capitaine','122','SSSM','SP'),
('IHC','Infirmier hors classe','122','SSSM','SP'),
('ISPE','Infirmier d''encadrement ','123','SSSM','SP'),
('MASP','Médecin Aspirant','124','SSSM','SP'),
('MLTN','Médecin Lieutenant','125','SSSM','SP'),
('MCPT','Médecin Capitaine','126','SSSM','SP'),
('MCDT','Médecin Commandant','127','SSSM','SP'),
('MHC','Médecin hors classe','127','SSSM','SP'),
('MLCL','Médecin Lieutenant Colonel','128','SSSM','SP'),
('MCOL','Médecin Colonel','129','SSSM','SP'),
('PPH','Préparateur Pharmacie','130','SSSM','SP'),
('PPHS','Préparateur Pharmacie cl sup','130','SSSM','SP'),
('PHCPT','Pharmacien Capitaine','132','SSSM','SP'),
('PHCDT','Pharmacien Commandant','133','SSSM','SP'),
('PHLCL','Pharmacien Lieutenant Colonel','135','SSSM','SP'),
('PHCOL','Pharmacien Colonel','136','SSSM','SP'),
('VETCPT','Vétérinaire Capitaine','140','SSSM','SP'),
('VETCDT','Vétérinaire Commandant','142','SSSM','SP'),
('VETLCL','Vétérinaire Lieutenant Colonel','145','SSSM','SP'),
('VETCOL','Vétérinaire Colonel','146','SSSM','SP'),
('CSAN2','Cadre de santé 2ème classe','150','SSSM','SP'),
('CSAN1','Cadre de santé 1ère classe','151','SSSM','SP'),
('CSANSU','Cadre supérieur de santé','152','SSSM','SP');

#-------------------------------------
# Improve Display Garde Jour
#-------------------------------------
UPDATE menu_item SET MI_URL = 'feuille_garde.php?evenement=0&filter=0&from=gardes' WHERE MI_CODE = 'GARDEJOUR';

# ------------------------------------
# Add type_indisponibilite
# ------------------------------------
ALTER TABLE type_indisponibilite CHANGE TI_LIBELLE TI_LIBELLE VARCHAR(40) NOT NULL;
delete from type_indisponibilite where TI_CODE in ('CP','CA','DIV','FAM','FOR','MAL','PRO','RTT','RT','REC','RECFO','CET','ASA','NAI','PAT','ENFM','ENFH','AT','MAT','ASYND','ELEC','DISPO','SUSP');

INSERT INTO type_indisponibilite (TI_CODE,TI_LIBELLE,TI_FLAG) VALUES
('CP','Congés payés','1'),
('CA', 'Congé Annuel','1'),
('DIV','Autre Raison','0'),
('FAM','raison familiale','0'),
('FOR','formation','0'),
('MAL','maladie / blessure','0'),
('PRO','raison professionnelle','0'),
('RTT','Réduction du temps de travail','1'),
('RT','Repos régime de travail mixte','1'),
('REC', 'Récupération', '1'),
('RECFO', 'Récupération liée à une formation','1'),
('CET', 'Compte épargne Temps','1'),
('ASA', 'Autorisation spéciale absence','1'),
('NAI', 'Congé Naissance','1'),
('PAT', 'Congé Paternité','1'),
('ENFM', 'Enfant Malade','1'),
('ENFH', 'Enfant Hospitalisé','1'),
('AT', 'Accident en service commandé','1'),
('MAT', 'Congé Maternité','1'),
('ASYND', 'Activité Syndicale','1'),
('ELEC', 'Elections','1'),
('DISPO', 'Disponibilité','1'),
('SUSP', 'Suspension activité','0');

#-------------------------------------
# Nouveaux agréments
#-------------------------------------
ALTER TABLE type_agrement CHANGE TA_CODE TA_CODE VARCHAR(7) NOT NULL;
ALTER TABLE type_agrement CHANGE TA_DESCRIPTION TA_DESCRIPTION VARCHAR(75) NOT NULL;
ALTER TABLE agrement CHANGE TA_CODE TA_CODE VARCHAR(7) NOT NULL;


delete from categorie_agrement where CA_CODE='SPE';
INSERT INTO categorie_agrement (CA_CODE, CA_DESCRIPTION, CA_FLAG)
VALUES ('SPE', 'Formations spécifiques', '0');
delete from type_agrement where CA_CODE='SPE';
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('SC', 'SPE', 'Secourisme canin', '0'),
('PSSP', 'SPE', 'Premiers Secours Socio-psychologiques', '0'),
('CE', 'SPE', 'Chef d''équipe', '0'),
('CP', 'SPE', 'Chef de poste', '0');

delete from type_agrement where TA_CODE in('CPS','APS-ASD');
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('APS-ASD', 'ENT', 'Acteur Prévention Secours / Aide et soins à domicile', '0');
update agrement set TA_CODE='APS-ASD' where TA_CODE='CPS';

update type_agrement set TA_CODE='PS' where TA_CODE='PSE';
update agrement set TA_CODE='PS' where TA_CODE='PSE';

delete from type_agrement where TA_CODE in('PAE','PAE PS','GQS ','PSC1','PAE PSC','PS','PSE');
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('GQS', 'FOR', 'Sensibilisation aux Gestes Qui Sauvent', '0'),
('PSC1', 'FOR', 'Formation Prévention et Secours Civiques de niveau 1', '0'),
('PAE-PSC', 'FOR', 'Formation de formateur en Prévention et Secours Civiques de niveau 1', '0'),
('PAE-PS', 'FOR', 'Formation de formateur aux Premiers Secours', '0'),
('PS', 'FOR', 'Formation de formateur aux Premiers Secours', '0');

update agrement set TA_CODE='PAE-PS' where TA_CODE='PAE';
update agrement set TA_CODE='PAE-PS' where TA_CODE='PAE PS';
update agrement set TA_CODE='PAE-PSC' where TA_CODE='PAE PSC';
update type_agrement set TA_CODE='PAE-PS' where TA_CODE='PAE PS';
update type_agrement set TA_CODE='PAE-PSC' where TA_CODE='PAE PSC';

# ------------------------------------
# compétences
# ------------------------------------
update poste set TYPE='PAE PSC', DESCRIPTION='Formateur en Prévention et Secours Civiques' where TYPE='PAE1';
update poste set TYPE='PAE PS', DESCRIPTION='Formateur aux Premiers Secours' where TYPE='PAE2';
update poste set TYPE='FDF PSE', DESCRIPTION='Formateur de Formateurs aux Premiers Secours' where TYPE='PAE3';

# ------------------------------------
# web URLs
# ------------------------------------
ALTER TABLE section CHANGE S_URL S_URL VARCHAR(60) NULL;

# ------------------------------------
# cascade facultative validation
# ------------------------------------
ALTER TABLE poste_hierarchie ADD PH_UPDATE_MANDATORY TINYINT NOT NULL DEFAULT '0' AFTER PH_UPDATE_LOWER_EXPIRY;

# ------------------------------------
# diplômes
# ------------------------------------
ALTER TABLE poste ADD PS_PRINT_IMAGE TINYINT NOT NULL DEFAULT '0' AFTER PS_PRINTABLE;
ALTER TABLE poste ADD PS_NUMERO TINYINT NOT NULL DEFAULT '0' AFTER PS_DIPLOMA;
update poste set PS_NUMERO=1 where PS_DIPLOMA=1 and PS_PRINT_IMAGE=0;


INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP51', 'P', 'Changement Département de Naissance');

#-------------------------------------
# Notes de frais v3 - split permission
#-------------------------------------
update fonctionnalite set F_LIBELLE='Cotisations',F_DESCRIPTION='Définir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres'
where F_ID=53;

delete from menu_item where MI_CODE in ('ADDNOTE','NOTES');
insert into menu_item(MI_CODE, MI_NAME, MG_CODE, MI_ORDER, MI_TITLE, MI_URL, MI_ICON) VALUES
('ADDNOTE','Ajout note de frais','ME', 6, 'Ajout note de frais pour moi', 'note_frais_edit.php?from=menu','plus'),
('NOTES','Mes note de frais','ME', 6, 'Mes notes de frais', 'upd_personnel.php?tab=9&from=menu&self=1','money');
delete from menu_condition where MC_CODE in ('ADDNOTE','NOTES');
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('ADDNOTE','permission',11),
('ADDNOTE','gardes',0),
('NOTES','permission',11),
('NOTES','gardes',0);
UPDATE menu_item SET MI_ORDER = '7' WHERE MI_CODE = 'DIVIDER02';

ALTER TABLE note_de_frais
ADD NF_FRAIS_DEP TINYINT NOT NULL DEFAULT '0' AFTER NF_DON,
ADD NF_JUSTIF_RECUS TINYINT NOT NULL DEFAULT '0' AFTER NF_FRAIS_DEP;

update notification_block set F_ID=73 where F_ID=53;

#-------------------------------------
#Add type id SSO CAS service
#-------------------------------------
ALTER TABLE demande ADD P_CODE VARCHAR(20) NULL DEFAULT NULL AFTER P_ID;

# --------------------------------------------------------
# Structure de la table evenement_piquets_feu
# --------------------------------------------------------
drop table if exists evenement_piquets_feu;
CREATE TABLE evenement_piquets_feu
(E_CODE int(11) NOT NULL,
EH_ID smallint(6) NOT NULL,
V_ID int(11) NOT NULL,
ROLE_ID tinyint(4) NOT NULL,
P_ID int(11) DEFAULT NULL);

ALTER TABLE evenement_piquets_feu
ADD PRIMARY KEY (E_CODE,EH_ID,V_ID,ROLE_ID),
ADD KEY P_ID (P_ID);

# --------------------------------------------------------
# Définir compétence requise pour un piquet sur véhicule
# --------------------------------------------------------
ALTER TABLE type_vehicule_role ADD PS_ID INT NOT NULL DEFAULT '0' AFTER ROLE_NAME;

# --------------------------------------------------------
# SMSGatewayme new API - API token au lieu de password
# --------------------------------------------------------
ALTER TABLE configuration CHANGE VALUE VALUE VARCHAR(255) NULL;
ALTER TABLE section CHANGE SMS_LOCAL_PASSWORD SMS_LOCAL_PASSWORD VARCHAR(255) NULL;

UPDATE configuration SET DESCRIPTION = 'mot de passe du compte SMS, ou API token pour smsgateway.me' WHERE ID = 11;


# --------------------------------------------------------
# compétences secondaires SP
# --------------------------------------------------------
update qualification set Q_VAL=2 where Q_VAL=1 and PS_ID in (select PS_ID from poste where TYPE='INC1')
and P_ID in ( select P_ID from ( select P_ID from qualification where PS_ID in (select PS_ID from poste where TYPE='INC2') ) as Tmp );

update qualification set Q_VAL=2 where Q_VAL=1 and PS_ID in (select PS_ID from poste where TYPE='INC1')
and P_ID in ( select P_ID from ( select P_ID from qualification where PS_ID in (select PS_ID from poste where TYPE='CE') ) as Tmp );

update qualification set Q_VAL=2 where Q_VAL=1 and PS_ID in (select PS_ID from poste where TYPE='FDF1')
and P_ID in ( select P_ID from ( select P_ID from qualification where PS_ID in (select PS_ID from poste where TYPE='FDF2') ) as Tmp );

update qualification set Q_VAL=2 where Q_VAL=1 and PS_ID in (select PS_ID from poste where TYPE='FDF2')
and P_ID in ( select P_ID from ( select P_ID from qualification where PS_ID in (select PS_ID from poste where TYPE='FDF3') ) as Tmp );

update qualification set Q_VAL=2 where Q_VAL=1 and PS_ID in (select PS_ID from poste where TYPE='CE')
and P_ID in ( select P_ID from ( select P_ID from qualification where PS_ID in (select PS_ID from poste where TYPE='INC2') ) as Tmp );

# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='4.4' where ID=1;

# ------------------------------------
# end
# ------------------------------------
#====================================================
#  Upgrade v4.5
#====================================================

SET sql_mode = '';

UPDATE note_de_frais_type_statut SET FS_CLASS = 'darkcyan12' WHERE FS_CODE = 'VAL2';

# ------------------------------------
# équipes id radio
# ------------------------------------

ALTER TABLE evenement_equipe ADD EE_ID_RADIO VARCHAR(12) NULL AFTER IS_ID;

update note_de_frais set NF_CODE1=YEAR(NF_CREATE_DATE);

update note_de_frais set NF_CODE2=MONTH(NF_CREATE_DATE);


# ------------------------------------
# qualifications
# ------------------------------------
UPDATE menu_item SET MI_URL = 'qualifications.php?page=1&pompier=0&action_comp=default' WHERE MI_CODE = 'COMP';


# ------------------------------------
# widgets configurables
# ------------------------------------
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
(2,'button',null,'Evenements','evenement_choice.php?ec_mode=default','Voir les événements','fa-info-circle',1,2),
(3,'button',null,'Disponibilités','dispo.php','Saisir ses disponibilités','fa-calendar-check-o',1,3),
(4,'button',null,'Calendrier','calendar.php','Voir mon calendrier','fa-calendar',1,4),
(5,'button',null,'Tableau de garde','tableau_garde.php','Voir le tableau de garde','fa-table',1,5),
(6,'button',null,'Garde du jour','feuille_garde.php?evenement=0&from=gardes','Voir la garde du jour','fa-sun-o',1,6),
(7,'box','welcome','curdate',null,null,null,1,1),
(8,'box','show_duty','La veille Opérationnelle est assurée par',null,null,null,1,2),
(9,'box','show_birthdays','Anniversaires à souhaiter',null,null,null,1,3),
(10,'box','show_alerts_horaires','Horaires du personnel salarié à valider (100 jours)',null,null,null,1,4),
(11,'box','show_factures','Evenements terminés non payés (100 jours)','factures','Voir les événements terminés non payés',null,1,5),
(12,'box','show_stats_manquantes','Statistiques manquantes (30 derniers jours)',null,null,null,1,6),
(13,'box','show_participations','Mes participations en cours ou futures','calendar.php','Voir mon calendrier',null,2,1),
(14,'box','show_alerts_cp','Demandes de congés à valider',null,null,null,2,2),
(15,'box','show_alerts_vehicules','Gestion des véhicules','vehicule.php?page=1','Voir les véhicules',null,2,3),
(16,'box','show_alerts_consommables','Gestion des produits consommables','consommable.php?page=1','Voir les produits consommables',null,2,4),
(17,'box','show_alerts_remplacements','Remplacements pour la garde','remplacements.php','Voir tous les remplacements',null,2,5),
(23,'box','show_alerts_remplacements','Remplacements de personnel','remplacements.php','Voir tous les remplacements',null,2,5),
(24,'box','show_proposed_remplacements','Recherche remplaçant',null,null,null,2,5),
(25,'box','show_proposed_remplacements','Recherche remplaçant',null,null,null,2,5),
(18,'box','show_infos','Informations','message.php?catmessage=amicale','Voir la page informations',null,2,6),
(19,'box','show_participations_mc','Mains courantes',null,null,null,3,1),
(20,'box','show_notes','Notes de frais à valider ou rembourser (60 jours)',null,null,null,3,2),
(21,'box','show_events','Calendrier des activités','evenement_choice.php?ec_mode=default','Voir tous les événements',null,3,3),
(22,'box','show_about','A propos de cette application','about.php','Voir les informations relatives à cette application',null,3,4);

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

# ------------------------------------
# better log absence evenement
# ------------------------------------
UPDATE log_type SET LT_DESCRIPTION = 'Modification horaires' WHERE LT_CODE = 'UPDHOR';

delete from log_type where LT_CODE='UPDABS';
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDABS', 'P', 'Modification absence sur événement');

# ------------------------------------
# mode paiement formation individuelle
# ------------------------------------
ALTER TABLE evenement_participation ADD MODE_PAIEMENT TINYINT NULL AFTER TSP_ID;

# ------------------------------------
# fonctionnalité notification remplacement
# ------------------------------------
UPDATE fonctionnalite SET F_DESCRIPTION = 'Recevoir un email de notification lorsqu\'un événement est créé, <br>ou lorsqu\'il y a une demande de remplacement sur cet événement.' WHERE F_ID = 21;

# ------------------------------------
# Notes de frais
# ------------------------------------
update fonctionnalite set F_LIBELLE='Valider notes - trésorier', F_DESCRIPTION='Modifier et faire la validation trésorier des notes de frais. <br>Recevoir les notifications par mail si une note est envoyée pour validation.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.'
where F_ID=73 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);
update fonctionnalite set F_LIBELLE='Valider notes - président', F_DESCRIPTION='Modifier et faire la validation président des notes de frais. <br>Recevoir les notifications par mail si une note est envoyée pour validation.<br><b>Attention:</b> on ne peut cependant pas valider complètement ses propres notes de frais.'
where F_ID=74 and exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

delete from note_de_frais_type_statut where FS_CODE= 'VAL1';
INSERT INTO note_de_frais_type_statut (FS_CODE,FS_DESCRIPTION,FS_CLASS,FS_ORDER)
VALUES ('VAL1', 'Validée', 'green12', '5');

UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Validée' WHERE FS_CODE in( 'VAL1');
UPDATE note_de_frais_type_statut SET FS_DESCRIPTION = 'Validée' WHERE FS_CODE in( 'VAL');

update note_de_frais_type_statut set FS_ORDER='4' where FS_CODE='VAL';
update note_de_frais_type_statut set FS_ORDER='5' where FS_CODE='VAL1';
update note_de_frais_type_statut set FS_ORDER='6' where FS_CODE='VAL2';

update note_de_frais_type_statut set FS_ORDER='7' where FS_CODE='REMB';

UPDATE note_de_frais SET note_de_frais.S_ID = (SELECT pompier.P_SECTION FROM pompier WHERE pompier.P_ID = note_de_frais.P_ID );

# ------------------------------------
# grade
# ------------------------------------
delete from grade where G_GRADE in ('MCN');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('MCN','Médecin Classe Normale','126','SSSM','SP');

delete from bilan_victime_category where BVC_PAGE='PSSP';

INSERT INTO bilan_victime_category
(BVC_CODE, BVC_TITLE, BVC_PAGE, BVC_ORDER) VALUES
('CONTACT', 'Contacter', 'PSSP', '1'),
('SIGREP', 'Signes Repères', 'PSSP', '2'),
('EVOLUT', 'Evolution', 'PSSP', '3'),
('SUITE', 'Suite donnée', 'PSSP', '4'),
('BILAN2', 'Bilan', 'PSSP', '5');

delete from bilan_victime_param where BVC_CODE in ('CONTACT','SIGREP','RELAT','VERBA','RECIT','EVOLUT','SUITE','BILAN2');

INSERT INTO bilan_victime_param(BVP_ID, BVC_CODE, BVP_TITLE, BVP_COMMENT, BVP_TYPE, DOC_ONLY) VALUES 
('1900', 'CONTACT', 'Téléphone', NULL, 'text', '0'),
('1910', 'CONTACT', 'Personne à prévenir', NULL, 'text', '0'),
('2000', 'SIGREP', 'Agitation', NULL, 'checkbox', '0'),
('2001', 'SIGREP', 'Confusion', NULL, 'checkbox', '0'),
('2002', 'SIGREP', 'Euphorie', NULL, 'checkbox', '0'),
('2003', 'SIGREP', 'Méfiance', NULL, 'checkbox', '0'),
('2004', 'SIGREP', 'Prostration', NULL, 'checkbox', '0'),
('2005', 'SIGREP', 'Fuite Panique', NULL, 'checkbox', '1'),
('2006', 'SIGREP', 'Colère', NULL, 'checkbox', '0'),
('2010', 'SIGREP', 'Culpabilité', NULL, 'checkbox', '0'),
('2008', 'SIGREP', 'Sidération', NULL, 'checkbox', '1'),
('2011', 'SIGREP', 'Gestes Automatiques', NULL, 'checkbox', '1'),
('2012', 'SIGREP', 'Tristesse', NULL, 'checkbox', '0'),
('2013', 'SIGREP', 'Déréalisation', NULL, 'checkbox', '1'),
('2014', 'SIGREP', 'Agressivité', NULL, 'checkbox', '0'),
('2015', 'SIGREP', 'Angoisse', NULL, 'checkbox', '0'),
('2016', 'SIGREP', 'Pleurs', NULL, 'checkbox', '0'),
('2030', 'SIGREP', 'Contact Relationnel', 'indiquer ici le niveau du contact relationnel', 'dropdown', '0'),
('2035', 'SIGREP', 'Verbalisation', 'indiquer la verbalisation', 'dropdown', '0'),
('2040', 'SIGREP', 'Récit de l\'évenement', 'indiquer comment la personne rqconte l\'évenement', 'dropdown', '0'),
('2100', 'EVOLUT', 'Evolution', NULL, 'dropdown', '0'),
('2103', 'EVOLUT', 'Observation', NULL, 'textarea', '0'),
('2120', 'SUITE', 'Avis médical', NULL, 'checkbox', '0'),
('2121', 'SUITE', 'Avis CUMP', NULL, 'checkbox', '0'),
('2125', 'SUITE', 'Evacuation', NULL, 'checkbox', '0'),
('2126', 'SUITE', 'Heure', 'heure évacuation', 'time', '0'),
('2128', 'SUITE', 'Hôpital', NULL, 'checkbox', '0'),
('2129', 'SUITE', 'Domicile seul', NULL, 'checkbox', '0'),
('2130', 'SUITE', 'Accompagné', NULL, 'checkbox', '0'),
('2200', 'BILAN2', 'Pouls++', NULL, 'numeric', '0');

delete from bilan_victime_values where BVP_ID in ('2030','2035','2040','2100');
INSERT INTO bilan_victime_values (BVP_ID,BVP_INDEX,BVP_TEXT) values
('2030',1,'Satisfaisant'),
('2030',2,'Peu Satisfaisant'),
('2030',3,'Insatisfaisant'),
('2035',1,'Spontanée'),
('2035',2,'Provoquéee'),
('2035',3,'Absente'),
('2040',1,'Factuel Exclusif'),
('2040',2,'Emotionnel Exclusif'),
('2040',3,'Factuel et émotionnel'),
('2040',4,'Amnésie'),
('2100',1,'Sans Changement'),
('2100',2,'Amélioration'),
('2100',3,'Aggravation');

# ------------------------------------
# configuration militaire
# ------------------------------------
delete from configuration where ID=59;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '59', 'army', 0, 'Configuration organisation militaire', 1,1,1,1);

delete from categorie_grade where CG_CODE in ('ARMY');
INSERT INTO categorie_grade (CG_CODE, CG_DESCRIPTION) VALUES ('ARMY', 'Armée de Terre');

delete from grade where G_CATEGORY in ('ARMY');
delete from grade where G_GRADE in ('DRA1','DRA2','MDL','MCH','BG','BGC');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('SDT','Soldat de 2ème classe','1','Hommes du rang','ARMY'),
('SDT1','Soldat de 1ère classe','2','Hommes du rang','ARMY'),
('DRA2','Dragon de 2ème classe','1','Hommes du rang','ARMY'),
('DRA1','Dragon de 1ère classe','2','Hommes du rang','ARMY'),
('BG','Brigadier','5','Hommes du rang','ARMY'),
('BGC','Brigadier Chef','6','Hommes du rang','ARMY'),
('CA','Caporal','5','Hommes du rang','ARMY'),
('CAC','Caporal Chef','6','Hommes du rang','ARMY'),
('SG1','Sergent appelé','10','Sous-Officiers','ARMY'),
('SG','Sergent','11','Sous-Officiers','ARMY'),
('SC','Sergent Chef','12','Sous-Officiers','ARMY'),
('MDL','Maréchal des Logis','11','Sous-Officiers','ARMY'),
('MCH','Maréchal des Logis Chef','12','Sous-Officiers','ARMY'),
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
('GLBR','Général de Brigade','100','Officiers Généraux','ARMY'),
('GLDIV','Général de Division','105','Officiers Généraux','ARMY'),
('GLCA','Général de Corps d\'armée','110','Officiers Généraux','ARMY'),
('GLA','Général d\'armée ','120','Officiers Généraux','ARMY');

delete from statut where S_STATUT in ('ACT','RES','CIV');
INSERT INTO statut (S_STATUT,S_DESCRIPTION,S_CONTEXT) VALUES
('ACT', 'Militaire d\'active', '0'),
('RES', 'Militaire de réserve', '0'),
('CIV', 'Personnel civil', '0');

delete from theme where NAME='army';
INSERT INTO theme (NAME, COLOR, COLOR2, COLOR3) VALUES ('army', 'cccc99','bbbb77', 'aaaa55');

INSERT INTO widget_condition (W_ID, WC_TYPE, WC_VALUE) VALUES ('10', 'army', '0');

# ------------------------------------
# mode paiement formation individuelle
# ------------------------------------
ALTER TABLE evenement_participation ADD NUM_CHEQUE varchar(20) NULL AFTER MODE_PAIEMENT;
ALTER TABLE evenement_participation ADD NOM_PAYEUR varchar(40) NULL AFTER NUM_CHEQUE;

# ------------------------------------
# sécurité, support bcrypt
# ------------------------------------
ALTER TABLE pompier CHANGE P_MDP P_MDP VARCHAR(255) NOT NULL;

# ------------------------------------
# geolocalisation OSM
# ------------------------------------
delete from configuration where ID=60;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ( '60', 'api_provider', 'google', 'Service de géolocaliation', 120,0,1,0);

# ------------------------------------
# améliorations options inscription 
# ------------------------------------
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

# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='4.5' where ID=1;

# ------------------------------------
# end
# ------------------------------------
#====================================================
#  Upgrade v5.0
#====================================================

SET sql_mode = '';

# ------------------------------------
# documents sur matériel
# ------------------------------------
ALTER TABLE document ADD M_ID INT NOT NULL DEFAULT '0' AFTER V_ID;

INSERT INTO document_security (DS_ID, DS_LIBELLE, F_ID) VALUES ('9', 'accès restreint (70 - Gestion du matériel)', '70');


# ------------------------------------
# graphiques
# ------------------------------------
delete from menu_condition where MC_TYPE='ChartDirector';


# ------------------------------------
# fix permissions menus
# ------------------------------------

delete from menu_condition where MC_CODE='ANCIENS';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('ANCIENS', 'permission', '56');

delete from menu_condition where MC_CODE='GRAPHIC';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('GRAPHIC', 'permission', '27');

delete from menu_condition where MC_CODE='SEARCH';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('SEARCH', 'permission', '56');

delete from menu_condition where MC_CODE='PARTICIP';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('PARTICIP', 'permission', '27'),
('PARTICIP', 'syndicate', '0');

delete from menu_condition where MC_CODE='HISTO';

INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('HISTO', 'permission', '49');


delete from log_category where LC_CODE='A';
INSERT INTO log_category (LC_CODE, LC_DESCRIPTION) VALUES ('A', 'Malveillance');

delete from log_type where LT_CODE='ATTACK';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('ATTACK', 'A', 'Essaye d\'accéder à données du système');


delete from menu_item where MI_CODE= 'ATTACK';
INSERT INTO menu_item (MI_CODE, MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('ATTACK', 'Activités suspectes', 'bomb', 'ADMIN', '8', 'Voir les activités suspectes, tentatives d\'intrusions', 'history.php?ltcode=ALL&lccode=A');

delete from menu_condition where MC_CODE= 'ATTACK';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES ('ATTACK', 'permission', '14');

delete from log_type where LT_CODE='DELP';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('DELP', 'A', 'Suppression de fiche personnel');

delete from log_type where LT_CODE='ERRP';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('ERRP', 'A', 'Erreur de permissions');

# ------------------------------------
# consommables
# ------------------------------------
ALTER TABLE consommable ADD C_LIEU_STOCKAGE VARCHAR(200) NULL AFTER C_DATE_PEREMPTION;

# ------------------------------------
# anciens externes
# ------------------------------------
delete from menu_item where MI_CODE='ANCIENSEXT';
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL)
VALUES ('ANCIENSEXT', 'Anciens externes', 'ban', 'PERSO', '12', 'Personnel externe ancien', 'personnel.php?page=1&category=EXT&position=ancien');


delete from menu_condition where MC_CODE='ANCIENSEXT';
INSERT INTO menu_condition (MC_CODE,MC_TYPE,MC_VALUE) VALUES
('ANCIENSEXT', 'externes', '1'),
('ANCIENSEXT', 'permission', '37'),
('ANCIENSEXT', 'permission', '45');

# ------------------------------------
# grades
# ------------------------------------
update pompier set P_GRADE='-' where P_GRADE='';

# ------------------------------------
# Upgrade to Font-awesome 5
# ------------------------------------
update menu_item set MI_ICON='euro-sign' where  MI_ICON='eur';

update menu_item set MI_ICON='euro-sign' where  MI_ICON='euro';

update menu_item set MI_ICON='building' where  MI_ICON='company';

update menu_item set MI_ICON='building' where  MI_CODE='MYCOMPANY';

update menu_item set MI_ICON='user-edit' where  MI_ICON='pencil-square-o';

update menu_item set MI_ICON='calendar-check' where  MI_ICON='calendar-check-o';

update menu_item set MI_ICON='money-bill-alt' where  MI_ICON='money';

update menu_item set MI_ICON='chart-bar' where  MI_ICON='bar-chart';

update menu_item set MI_ICON='chart-pie' where  MI_ICON='pie-chart';

update menu_item set MI_ICON='chart-area' where  MI_ICON='area-chart';

update menu_item set MI_ICON='chart-line' where  MI_ICON='line-chart';

update menu_item set MI_ICON='cloud-download-alt' where  MI_ICON='cloud-download';

update menu_item set MI_ICON='calendar-plus' where  MI_ICON='calendar-times-o';

update menu_item set MI_ICON='calendar-plus' where  MI_ICON='fa-calendar-times-o';

update menu_item set MI_ICON='file-alt' where  MI_ICON='file-text';

update menu_item set MI_ICON='file-pdf' where  MI_ICON='file-pdf-o';

update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'ORGANI';

update menu_item SET MI_ICON='info-circle' WHERE MI_CODE = 'INFO';
update menu_item set MI_ICON='comments' where  MI_ICON='envelope';

update menu_item set MI_ICON='bell' where MI_ICON='bell';
update menu_item SET MI_ICON='info-circle' WHERE MI_CODE = 'INFODIV';

update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'SECTIONS';

update menu_item SET MI_ICON='sitemap' WHERE MI_CODE = 'DEPART';

update menu_item SET MI_ICON='sun' WHERE MI_CODE = 'GARDEJOUR';

update menu_item SET MI_ICON='calendar-alt' WHERE MI_CODE = 'EVENT';

update menu_item SET MI_ICON='wrench' WHERE MI_CODE = 'CONF';

update menu_item SET MI_ICON='tasks' WHERE MI_CODE = 'PARAM';

update menu_item SET MI_ICON='users-cog' WHERE MI_CODE = 'PERMISSION';

update menu_item SET MI_ICON='calendar-alt' WHERE MI_CODE = 'EVENT';
update menu_item SET MI_ICON='eye' WHERE MI_CODE = 'AUDIT';

update menu_item SET MI_ICON='database' WHERE MI_CODE = 'BACKUP';

update menu_item SET MI_ICON='users' WHERE MI_CODE = 'PERSODISPO';

update menu_item SET MI_ICON='images' WHERE MI_CODE = 'PHOTO';

update menu_item SET MI_ICON='phone-square' WHERE MI_CODE = 'ASTREINTE';

update menu_item SET MI_ICON='calendar-plus' WHERE MI_CODE = 'EVENTADD';

update menu_group set MG_ICON='comments' where  MG_ICON='envelope-o';

update widget SET W_ICON = 'fa-calendar-check' WHERE W_ID = 3;

update categorie_materiel SET PICTURE='keyboard' WHERE TM_USAGE= 'Informatique';

update categorie_materiel SET PICTURE='lightbulb' WHERE TM_USAGE= 'Eclairage';

update categorie_materiel SET PICTURE='utensils' WHERE TM_USAGE= 'Logistique';

update categorie_materiel SET PICTURE='external-link-square-alt' WHERE TM_USAGE= 'Pompage';

update categorie_materiel SET PICTURE='shield-alt' WHERE TM_USAGE= 'Sécurité';

update categorie_materiel SET PICTURE='cut' WHERE TM_USAGE= 'Elagage';

update type_message SET TM_ICON = 'sticky-note' WHERE TM_ID = 0;

# ------------------------------------
# SDIS
# ------------------------------------
UPDATE menu_item SET MI_URL = 'feuille_garde.php?evenement=0&from=gardes' WHERE MI_CODE = 'GARDEJOUR';

UPDATE menu_item SET MI_URL = 'section.php' WHERE MI_CODE in ( 'ORGANI','SECTIONS','DEPART');

delete from menu_item where MI_CODE='LISTE';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('LISTE', 'Liste des sections', 'list-ol', 'INFO', '4', 'Liste des sections de l\'organigramme', 'departement.php');

delete from menu_condition where MC_CODE='LISTE';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('LISTE', 'permission', '52');

# ------------------------------------
# permettre choix de tout le personnel de organigramme pour un rôle
# ------------------------------------
ALTER TABLE groupe ADD TR_ALL_POSSIBLE TINYINT NOT NULL DEFAULT '0' AFTER TR_SUB_POSSIBLE;

# ------------------------------------
# Statistiques evenement
# ------------------------------------
drop table if exists bilan_evenement;
CREATE TABLE bilan_evenement (
E_CODE INT NOT NULL,
TB_NUM SMALLINT NOT NULL,
BE_VALUE SMALLINT NOT NULL,
PRIMARY KEY (E_CODE, TB_NUM));

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 1, E_NB1
from evenement where E_NB1 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 2, E_NB2
from evenement where E_NB2 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 3, E_NB3
from evenement where E_NB3 is not null;

insert into bilan_evenement (E_CODE,TB_NUM, BE_VALUE)
select E_CODE, 4, E_NB4
from evenement where E_NB4 is not null;

optimize table bilan_evenement;

ALTER TABLE evenement
DROP E_NB1,
DROP E_NB2,
DROP E_NB3,
DROP E_NB4;

# ------------------------------------
# Libelle fonctionnalite
# ------------------------------------

UPDATE fonctionnalite
SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques opérationnelles.<br>Utiliser les fonctionnalités de reporting.<br>Voir les cartes de France (si le module france map est installé).'
WHERE F_ID = 27;

# ------------------------------------
# Contacts
# ------------------------------------
drop table if exists personnel_contact;
CREATE TABLE personnel_contact(
P_ID INT NOT NULL,
CT_ID TINYINT NOT NULL,
CONTACT_VALUE VARCHAR(60) NOT NULL,
CONTACT_DATE DATETIME NOT NULL,
PRIMARY KEY (P_ID, CT_ID));

drop table if exists contact_type;
CREATE TABLE contact_type(
CT_ID TINYINT NOT NULL,
CONTACT_TYPE VARCHAR(20) NOT NULL,
CT_ICON VARCHAR(40) NOT NULL,
PRIMARY KEY (CT_ID),
UNIQUE INDEX (CONTACT_TYPE));

insert into contact_type (CT_ID,CONTACT_TYPE,CT_ICON)
values (1,'Skype','fab fa-skype'),
(2,'Zello','fas fa-broadcast-tower'),
(3,'WhatsApp','fab fa-whatsapp');

UPDATE log_type SET LT_DESCRIPTION = 'Modification contact' WHERE LT_CODE = 'UPDP12';


# ------------------------------------
# bugs icons
# ------------------------------------
update menu_item set MI_ICON='comments' where  MI_ICON='envelope-o';
update menu_item set MI_ICON='bell' where MI_ICON='bell-o';
update menu_item set MI_ICON='cloud-download-alt' where MI_ICON='download';

# ------------------------------------
# permission cartes Google
# ------------------------------------
insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION)
values ('76', 'Cartes Google Maps', '0', '9', '0', 'Permission de voir les cartes Google Maps. Cette fonctionnalité étant payante, on peut restreindre l\'accès à certains groupes d\'utilisateurs seulement.');

insert into habilitation(GP_ID, F_ID)
select GP_ID, 76
from habilitation where F_ID=27;

update menu_condition SET MC_VALUE = '76' WHERE MC_CODE = 'GEOLOC' AND MC_TYPE = 'permission' AND MC_VALUE = 56;

# ------------------------------------
# date changements roles
# ------------------------------------
ALTER TABLE section_role ADD UPDATE_DATE DATE NULL AFTER P_ID;

# ------------------------------------
# blocage des types evenements
# ------------------------------------
drop table if exists section_stop_evenement;
CREATE TABLE section_stop_evenement(
SSE_ID INT NOT NULL AUTO_INCREMENT,
S_ID INT NOT NULL,
TE_CODE VARCHAR(6) NOT NULL,
START_DATE DATE NOT NULL,
END_DATE DATE NOT NULL,
SSE_COMMENT VARCHAR(300) NULL,
PRIMARY KEY (SSE_ID));

# ------------------------------------
# icone remorque
# ------------------------------------
UPDATE type_vehicule SET TV_ICON = 'images/vehicules/REM.png' WHERE TV_CODE = 'REM' and TV_ICON is null;

# ------------------------------------
# nouvelles propriétés véhicules
# ------------------------------------

ALTER TABLE vehicule
ADD V_FLAG3 TINYINT NOT NULL DEFAULT '0' AFTER V_FLAG2,
ADD V_FLAG4 TINYINT NOT NULL DEFAULT '0' AFTER V_FLAG3;

# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='5.0' where ID=1;


#====================================================;
#  Upgrade v5.1;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# support SMSEagle
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android, ou Device ID pour smsgateway.me, ou serveur (nom ou IP) pour SMSEagle, inutile dans les autres cas'
WHERE ID = 12;

# ------------------------------------;
# cleanup
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques opérationnelles et sur le personnel<br>Utiliser les fonctionnalités de reporting.<br>Voir les cartes de France (si le module france map est installé).'
WHERE F_ID = 27;

# ------------------------------------;
# gardes possibles pour associations aussi
# ------------------------------------;
UPDATE configuration SET DESCRIPTION = 'activer une gestion des gardes. Configurer le régime de travail sur le paramétrage des gardes.', ORDERING = '108'
WHERE ID = 3;

# ------------------------------------;
# lieu de la garde
# ------------------------------------;
ALTER TABLE type_garde ADD EQ_LIEU VARCHAR(60) NULL AFTER EQ_ADDRESS;

# ------------------------------------;
# accès menu paramétrage gardes
# ------------------------------------;
delete from menu_item where MI_CODE='PARAMGAR';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('PARAMGAR', 'Paramétrage', 'cog', 'GAR', '5', 'Paramétrage des types de gardes', 'type_garde.php');

delete from menu_condition where MC_CODE='PARAMGAR';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('PARAMGAR', 'gardes', '1'),
('PARAMGAR', 'permission', '5');

insert into habilitation(GP_ID,F_ID)
select h1.GP_ID, 61
from habilitation h1
where h1.F_ID=5
and h1.GP_ID not in (select h2.GP_ID from habilitation h2 where h2.F_ID=61);

delete from habilitation where GP_ID=4 and F_ID=61;
insert into habilitation(GP_ID,F_ID)
values (4, 61);

UPDATE configuration SET DESCRIPTION = 'activer la gestion de tableaux de gardes par mois avec événements de gardes quotidiens'
WHERE ID = 3;

# ------------------------------------;
# Notes de frais
# ------------------------------------;
UPDATE widget SET W_TITLE = 'Notes de frais à valider ou rembourser' WHERE W_ID = 20;

INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('77', 'Saisir ses notes de frais', '0', '0', '0', 'Créer des notes de frais pour soi-même. Voir ses cotisations et remboursements');

UPDATE fonctionnalite SET
F_LIBELLE = 'Saisir ses absences',
F_DESCRIPTION = 'Saisir ses absences personnelles, demandes de congés payés (pour le personnel professionnel ou salarié), absences pour raisons personnelles ou autres.Dans le cas d\'une demande de congés, une demande de validation est envoyée au responsable du demandeur.'
WHERE F_ID = 11;

insert into habilitation (GP_ID,F_ID)
select GP_ID, 77
from habilitation
where F_ID=11;

UPDATE menu_condition SET MC_VALUE = '77' WHERE MC_CODE in('ADDNOTE', 'NOTES')
AND MC_TYPE = 'permission';

update fonctionnalite set F_DESCRIPTION = REPLACE(F_DESCRIPTION,'<br>',' ');


# ------------------------------------;
# Rendre notes configurables
# ------------------------------------;
INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('61', 'notes', '1', 'Activer les notes de frais', '121', '0', '1', '1');

update configuration
set VALUE = '0'
where NAME = 'notes'
and exists (select 1 from (select NAME,VALUE from configuration ) as x where x.NAME = 'gardes' and x.VALUE='1');

UPDATE menu_condition SET MC_TYPE = 'notes', MC_VALUE=1 WHERE MC_CODE in('ADDNOTE', 'NOTES') AND MC_TYPE = 'gardes';

UPDATE configuration SET ORDERING = '109' WHERE ID = 58;
UPDATE configuration SET ORDERING = '118' WHERE ID = 24;
UPDATE configuration SET ORDERING = '118' WHERE ID = 61;

UPDATE configuration SET ORDERING = '103' WHERE ID = 6;
UPDATE configuration SET ORDERING = '104' WHERE ID = 7;
UPDATE configuration SET ORDERING = '105' WHERE ID = 27;
UPDATE configuration SET ORDERING = '119' WHERE ID = 24;
UPDATE configuration SET TAB = 2 WHERE ID in (35,57,60);


# ------------------------------------;
# Nouveau type salarié
# ------------------------------------;
INSERT INTO type_salarie (TS_CODE, TS_LIBELLE)
VALUES ('SNU', 'service national universel');

# ------------------------------------;
# 5.1.pre1
# ------------------------------------;

UPDATE menu_condition SET MC_TYPE = 'pompiers'
WHERE MC_CODE = 'SAIABSTBL' AND MC_TYPE = 'gardes';

# ------------------------------------;
# Nouvelle carte de France
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'jvectormap.php' WHERE MI_CODE = 'MAP';
delete from menu_condition where MC_CODE='MAP' and MC_TYPE='iphone';

# ------------------------------------;
# Notes de frais, vérification
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_VERIFIED TINYINT NOT NULL DEFAULT '0' AFTER NF_JUSTIF_RECUS,
ADD NF_VERIFIED_BY INT NULL AFTER NF_VERIFIED, ADD NF_VERIFIED_DATE DATETIME NULL AFTER NF_VERIFIED_BY;

# ------------------------------------;
# Gestion des licenses
# ------------------------------------;
INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('62', 'licences', '0', 'Gestion des licenses pour les adhérents ou bénévoles, avec numéro et date expiration', '116', '0', '1', '1');

update pompier set P_BIRTHDATE=null where P_BIRTHDATE='0000-00-00';

ALTER TABLE pompier 
ADD P_LICENCE VARCHAR(25) NULL AFTER P_PAYS,
ADD P_LICENCE_DATE DATE NULL AFTER P_LICENCE,
ADD P_LICENCE_EXPIRY DATE NULL AFTER P_LICENCE_DATE;

INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP33', 'P', 'Modification numéro de licence'),
('UPDP34', 'P', 'Modification date de licence'),
('UPDP35', 'P', 'Modification date expiration de licence');

update pompier set P_LICENCE=P_SKYPE;

ALTER TABLE pompier drop column P_SKYPE;

# ------------------------------------;
# habilitations
# ------------------------------------;
update groupe set TR_CONFIG=1 where GP_ID < 100;
update groupe set TR_CONFIG=2 where GP_ID >= 100;

update groupe set GP_DESCRIPTION=rtrim(GP_DESCRIPTION);
update groupe set GP_DESCRIPTION=ltrim(GP_DESCRIPTION);
update groupe set TR_CONFIG=3 where GP_ID >=100
and GP_DESCRIPTION in ('Web master','Veille opérationnelle','Adhérent autre ADPC','Gestion des Véhicules','Employé (e)',
'Gestion des Transmissions','Gestion du Matériel','Permanence Téléphonique','Validation des Absences','Secrétaire','Médecin Référent');

update groupe set TR_CONFIG=3 where GP_ID >=100
and GP_DESCRIPTION in ('Responsable opérationnel','Webmaster','Responsable véhicules/matériel','Secrétariat');

# ------------------------------------;
# customisation
# ------------------------------------;

INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('63', 'block_personnel', '0', 'Bloquer les changements sur les principaux champs de la fiche personnel. Une API doit être utilisée pour les mises à jour.', '212', '0', '2', '1');

# ------------------------------------;
# cleanup
# ------------------------------------;
UPDATE menu_item SET MI_URL = 'restore.php' WHERE MI_CODE = 'BACKUP';

UPDATE configuration SET DESCRIPTION = 'api_id SMS pour clickatell, ou address:port pour SMS Gateway Android ou SMSEagle, ou Device ID pour smsgateway.me, inutile dans les autres cas.' WHERE ID = 12;
UPDATE configuration SET DESCRIPTION = 'Service de géolocalisation, google ou osm (open street map)' WHERE ID = 60;

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('64', 'import_api', '0', 'activer une API personalisée pour importer des données depuis une source externe', '214', '0', '2', '1'),
('65', 'import_api_url', NULL, 'URL de base de l\'API utilisée pour les imports de données personnalisés', '215', '0', '2', '0'),
('66', 'import_api_token', NULL, 'token pour se connecter à l\'API d\'import', '216', '0', '2', '0');
UPDATE configuration SET ORDERING = '216' WHERE ID = 41;

# ------------------------------------;
# menu utilisateurs connectes
# ------------------------------------;
delete from menu_item where MI_CODE='CONNUSERS';
INSERT INTO menu_item (MI_CODE,MI_NAME,MI_ICON,MG_CODE,MI_ORDER,MI_TITLE,MI_URL)
VALUES ('CONNUSERS', 'Utilisateurs en ligne', 'users', 'ADMIN', '6', 'Voir les utilisateurs actuellement connectés', 'connected_users.php');

delete from menu_condition where MC_CODE='CONNUSERS';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('CONNUSERS', 'permission', '20');

# ------------------------------------;
# régime travail SPP
# ------------------------------------;
drop table if exists type_regime_travail;
CREATE TABLE type_regime_travail(
TRT_CODE VARCHAR(5) NOT NULL,
TRT_DESC VARCHAR(80) NOT NULL,
TRT_ORDER TINYINT NOT NULL,
PRIMARY KEY (TRT_CODE));

INSERT INTO type_regime_travail
(TRT_CODE,TRT_DESC,TRT_ORDER) VALUES
('24h', 'Service opérationnel en gardes de 24h',1),
('12h', 'Service opérationnel en gardes de 12h, principalement le jour',2),
('SHR', 'Service hors rangs',3);

ALTER TABLE pompier ADD P_REGIME VARCHAR(5) NULL AFTER P_STATUT;

update pompier set P_REGIME='24h';

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION)
VALUES ('UPDP36', 'P', 'Modification du régime de travail');

# ------------------------------------;
# 5.1.pre2
# ------------------------------------;

delete from menu_condition where MC_CODE='REMPLACE';
insert into menu_condition (MC_CODE, MC_TYPE, MC_VALUE) VALUES
('REMPLACE','permission',61),
('REMPLACE','remplacements',1),
('REMPLACE','gardes',1);

# ------------------------------------;
# Log
# ------------------------------------;
delete from log_type where LT_CODE in ('UPDP1','UPDP2','UPDP3','UPDP4','UPDP5','UPDP6','UPDP7','UPDP8','UPDP9','UPDP10','UPDP11','UPDP12','UPDP13','UPDP14','UPDP15','UPDP16','UPDP17','UPDP18','UPDP19','UPDP20');
delete from log_type where LT_CODE in ('UPDP21','UPDP22','UPDP23','UPDP24','UPDP25','UPDP26','UPDP27','UPDP28','UPDP29');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP1', 'P', 'Modification civilité');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP2', 'P', 'Modification prénom');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP3', 'P', 'Modification nom');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP4', 'P', 'Modification nom de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP5', 'P', 'Modification identifiant');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP6', 'P', 'Modification entreprise');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP7', 'P', 'Modification droits d''accès');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP8', 'P', 'Modification date engagement');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP9', 'P', 'Modification date fin');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP10', 'P', 'Modification date de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP11', 'P', 'Modification lieu de naissance');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP12', 'P', 'Modification skype');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP13', 'P', 'Modification date npai');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP14', 'P', 'Modification masquage infos');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP15', 'P', 'Modification notifications');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP16', 'P', 'Modification grade');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP17', 'P', 'Modification profession');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP18', 'P', 'Modification service');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP19', 'P', 'Modification statut');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP20', 'P', 'Modification type salarié');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP21', 'P', 'Modification heures salarié');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP22', 'P', 'Modification date début suspendu');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP23', 'P', 'Modification date fin suspendu');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP24', 'P', 'Modification détail radiation');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP25', 'P', 'Modification sexe');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP26', 'P', 'Modification numéro abbrégé');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP27', 'P', 'Modification contact urgence');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP28', 'P', 'Modification nombre jours CP par an');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDP29', 'P', 'Modification heures annuelles salarié');

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='5.0' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;

update configuration set value='1' where name='licences';

delete from log_type where LT_CODE in ('UPDS31','UPDS32');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION)
VALUES ('UPDS31', 'S', 'Modification Code Siret'), ('UPDS32', 'S', 'Modification Numéro Affiliation');

# ------------------------------------;
# Page import API
# ------------------------------------;
delete from menu_item where MI_CODE in ('IMPORT','DIVIDER11');
INSERT INTO menu_item (MI_CODE, MI_NAME, MI_ICON, MG_CODE, MI_ORDER, MI_TITLE, MI_URL)
VALUES ('IMPORT', 'Import API', 'file-import', 'ADMIN', '10', 'Importer des données en utilisant l\'API', 'import_api.php'),
('DIVIDER11', 'divider', NULL, 'ADMIN', '9', '', '');

delete from menu_condition where MC_CODE = 'IMPORT';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('IMPORT', 'permission', '14'),
('IMPORT', 'import_api', '1');

delete from menu_condition where MC_CODE='ADDP' and MC_TYPE='block_personnel';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('ADDP', 'block_personnel', '0');

# ------------------------------------;
# Blocages types evenements
# ------------------------------------;
ALTER TABLE section_stop_evenement
ADD SSE_ACTIVE TINYINT NOT NULL DEFAULT 1 AFTER SSE_COMMENT,
ADD SSE_BY INT NULL AFTER SSE_ACTIVE,
ADD SSE_WHEN DATETIME NULL AFTER SSE_BY;

# ------------------------------------;
# make fields bigger 20 => 25
# ------------------------------------;
ALTER TABLE pompier CHANGE P_PRENOM P_PRENOM VARCHAR(25) NOT NULL;
ALTER TABLE pompier CHANGE P_PRENOM2 P_PRENOM2 VARCHAR(25) NULL DEFAULT NULL;

# ------------------------------------;
# ID API
# ------------------------------------;
ALTER TABLE pompier ADD ID_API INT NULL DEFAULT NULL AFTER P_LICENCE_EXPIRY, ADD UNIQUE (ID_API);

delete from log_type where LT_CODE='UPDP37';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP37', 'P', 'Modification ID API');

# ------------------------------------;
# FAVORITE SECTION
# ------------------------------------;
ALTER TABLE pompier ADD P_FAVORITE_SECTION INT NULL AFTER ID_API;

# ------------------------------------;
# 5.1.pre3
# ------------------------------------;

ALTER TABLE pompier ADD TS_RELIQUAT_CP FLOAT NULL AFTER TS_HEURES_A_RECUPERER;

delete from log_type where LT_CODE='UPDP38';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP38', 'P', 'Modification Reliquat CP');

ALTER TABLE pompier ADD TS_RELIQUAT_RTT FLOAT NULL AFTER TS_RELIQUAT_CP;

delete from log_type where LT_CODE='UPDP39';
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP39', 'P', 'Modification Reliquat RTT');

ALTER TABLE note_de_frais_type_motif ADD TM_SYNDICATE TINYINT NOT NULL DEFAULT '0' AFTER TM_DESCRIPTION;
ALTER TABLE note_de_frais_type_motif ADD INDEX (TM_SYNDICATE);

ALTER TABLE note_de_frais_type_motif CHANGE TM_DESCRIPTION TM_DESCRIPTION VARCHAR(50) NOT NULL;
delete from note_de_frais_type_motif where TM_SYNDICATE=1;
insert into note_de_frais_type_motif(TM_CODE,TM_DESCRIPTION,TM_SYNDICATE)
values ('ND','Non défini',1),
('MA','Manifestations',1),
('FOS','Formation Syndicale',1),
('GTS','Gestion Trésorerie',1),
('GJS','Gestion Juridique',1),
('MLAS','Mission Logistique et Administrative',1);

update note_de_frais set TM_CODE='ND'
where exists (select 1 from configuration where NAME='syndicate' and VALUE=1);

UPDATE configuration SET ID = '67', HIDDEN = '0', TAB = '2', ORDERING = '216', YESNO=1
WHERE NAME='lock_mailer';

INSERT INTO configuration (ID,NAME,VALUE,DESCRIPTION,ORDERING,HIDDEN,TAB,YESNO)
VALUES ('68', 'photo_obligatoire', '0', 'Le personnel doit obligatoirement  mettre une photo, sinon il ne pourra plus s''inscrire aux événements', '214', '0', '2', '1');

update pompier set P_CREATE_DATE =  P_ACCEPT_DATE where P_CREATE_DATE is null;
update pompier set P_CREATE_DATE =  P_DATE_ENGAGEMENT where P_CREATE_DATE is null;

ALTER TABLE note_de_frais_type_motif ADD MOTIF_LEVEL VARCHAR(1) NULL DEFAULT NULL AFTER TM_SYNDICATE;
delete from note_de_frais_type_motif where TM_SYNDICATE=1;
insert into note_de_frais_type_motif (TM_CODE,TM_DESCRIPTION,MOTIF_LEVEL,TM_SYNDICATE) VALUES
('MLAS','Mission Logistique et Administrative','A',1),
('GTS','Gestion Trésorerie' ,'A',1),
('S04','Congrès Fédéral','A',1),
('FOS','Formation Syndicale','A',1),
('MA','Manifestations','A',1),
('ELE','Elections','A',1),
('ND','Non Défini','A',1),
('S03','Assemblée Générale ','A',1),
('S05','Bureau Départemental','D',1),
('S15','Réunion Instance','D',1),
('S24','Réunion Information','D',1),
('S08','Déplacement Président','D',1),
('S23','Assemblée Générale département' ,'D',1),
('S01','Bureau National','N',1),
('S02','Bureau Exécutif','N',1),
('S06','Réunions Diverses','N',1),
('S07','Déplacement Président Fédéral','N',1),
('S09','Gestion Siège','N',1),
('GJS','Gestion Juridique','N',1),
('S13','Réunion Info pour les Départements','N',1),
('S14','Réunion Ministère de l''intérieur','N',1),
('S20','Réunion Sénat','N',1);

UPDATE configuration SET ORDERING = 212 WHERE ID = 68;
UPDATE configuration SET ORDERING = 213 WHERE ID = 67;
UPDATE configuration SET ORDERING = 216 WHERE ID = 37;

ALTER TABLE section CHANGE TAD_ID TAD_ID INT NOT NULL DEFAULT '0';
insert into section(S_ID,S_PARENT,S_CODE,S_DESCRIPTION,S_AFFILIATION)
select max(S_ID)+1, 0,'IMPORT','Fiches importées section inconnue','0'
from section;
insert into section_flat(NIV,S_ID,S_PARENT,S_CODE,S_DESCRIPTION)
select 1,S_ID,S_PARENT,S_CODE,S_DESCRIPTION
from section where S_CODE='IMPORT';

delete from mailer where MAILDATE < '2020-01-01';

# ------------------------------------;
# type agrément Aqua
# ------------------------------------;
delete from type_agrement where TA_CODE='D-Aqua';
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('D-Aqua', 'SEC', 'Sécurité de la pratique des activités aquatiques', '0');

# ------------------------------------;
# log appels SOAP
# ------------------------------------;

drop table if exists log_soap;
CREATE TABLE log_soap(
LS_ID INT NOT NULL AUTO_INCREMENT,
LS_DATE DATETIME NOT NULL,
LS_SERVICE VARCHAR(25) NOT NULL,
LS_PARAM VARCHAR(30) NULL ,
LS_RET TINYINT NOT NULL,
LS_MESSAGE VARCHAR(255) NULL,
PRIMARY KEY (LS_ID));

ALTER TABLE log_soap ADD INDEX (LS_DATE);
ALTER TABLE log_soap ADD INDEX (LS_SERVICE);

# ------------------------------------;
# horaires syndicat
# ------------------------------------;

ALTER TABLE horaires
ADD FORM TINYINT NOT NULL DEFAULT '0' AFTER ASA,
ADD FORMS TINYINT NOT NULL DEFAULT '0' AFTER FORM;

# ------------------------------------
# change version
# ------------------------------------
update configuration set VALUE='5.1' where ID=1;
