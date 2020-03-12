#====================================================;
#  Upgrade v4.4;
#====================================================;

SET sql_mode = '';

# ------------------------------------;
# mail contact urgence
# ------------------------------------;
ALTER TABLE pompier ADD P_RELATION_MAIL VARCHAR(60) NULL AFTER P_RELATION_PHONE;

# ------------------------------------;
# messages a imprimer
# ------------------------------------;
ALTER TABLE evenement_log ADD EL_IMPRIMER TINYINT NOT NULL DEFAULT 1 AFTER EL_IMPORTANT;

# ------------------------------------;
# notes de frais
# ------------------------------------;
ALTER TABLE note_de_frais ADD NF_VALIDATED_DATE DATETIME NULL AFTER NF_STATUT_BY,
ADD NF_VALIDATED_BY INT NULL AFTER NF_VALIDATED_DATE;

ALTER TABLE note_de_frais ADD NF_VALIDATED2_DATE DATETIME NULL AFTER NF_VALIDATED_BY,
ADD NF_VALIDATED2_BY INT NULL AFTER NF_VALIDATED2_DATE;

ALTER TABLE note_de_frais ADD NF_CODE INT NULL AFTER NF_ID;

UPDATE note_de_frais_type_statut SET FS_ORDER = '6' WHERE FS_CODE = 'REMB';

INSERT INTO note_de_frais_type_statut
(FS_CODE, FS_DESCRIPTION, FS_CLASS, FS_ORDER)
VALUES ('CRE', 'En cours de cr�ation', 'blue12', '0');

INSERT INTO note_de_frais_type_statut
(FS_CODE, FS_DESCRIPTION, FS_CLASS, FS_ORDER)
VALUES ('VAL2', 'Valid�e deux fois', 'purple12', '5');

UPDATE note_de_frais_type_statut SET FS_CODE = 'REJ', FS_DESCRIPTION='Rejet�e' WHERE FS_CODE = 'REF';

UPDATE note_de_frais set NF_VALIDATED_BY=NF_STATUT_BY, NF_VALIDATED_DATE=NF_STATUT_DATE
where FS_CODE = 'VAL'
and NF_VALIDATED_DATE is null
and NF_STATUT_DATE is not null;

UPDATE note_de_frais_type_statut SET FS_CLASS = 'green12'
WHERE FS_CODE in ('VAL','VAL2');
UPDATE note_de_frais_type_statut SET FS_CLASS = 'purple12'
WHERE FS_CODE = 'REMB';

# ------------------------------------;
# icone menu
# ------------------------------------;
UPDATE menu_item SET MI_ICON = 'fa-calendar-times-o' WHERE MI_CODE = 'SAISEABS';
delete from menu_item where MI_CODE='DIVIDERF';

# ------------------------------------;
# ID radio
# ------------------------------------;
ALTER TABLE section ADD S_ID_RADIO VARCHAR(5) NULL AFTER S_ORDER, ADD UNIQUE (S_ID_RADIO);
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES ('UPDS27', 'S', 'Modification ID Radio');

# ------------------------------------;
# remplacements d�sactivables
# ------------------------------------;
delete from menu_condition where MC_CODE='REMPLACE' and MC_TYPE <> 'permission';
INSERT INTO menu_condition (MC_CODE, MC_TYPE, MC_VALUE)
VALUES ('REMPLACE', 'remplacements', '1');
delete from configuration where ID=58;
INSERT INTO configuration (ID, NAME, VALUE, DESCRIPTION, ORDERING, HIDDEN, TAB, YESNO)
VALUES ('58', 'remplacements', '1', 'Possibilit� de g�rer les demandes de remplacements sur les gardes, avec validation', '105', '0', '1', '1');
UPDATE configuration SET ORDERING=114, DESCRIPTION='Possibilit� de g�rer les demandes de remplacements sur les �v�nements' WHERE id='58';

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


# ------------------------------------;
# menu conditions manquantes
# ------------------------------------;
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

ALTER TABLE menu_condition ADD UNIQUE (MC_CODE, MC_TYPE, MC_VALUE);

UPDATE menu_item SET MI_TITLE = 'voir le personnel disponible pour un jour donn�' WHERE MI_CODE = 'PERSODISPO';

# ------------------------------------;
# commentaire horaires
# ------------------------------------;
ALTER TABLE horaires ADD H_COMMENT VARCHAR(1000) NULL AFTER H_DUREE_MINUTES2;

# ------------------------------------;
# evenement options
# ------------------------------------;
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

# ------------------------------------;
# evenement tel administratif
# ------------------------------------;
ALTER TABLE evenement ADD E_TEL VARCHAR(15) NULL AFTER E_LIEU_RDV;

# ------------------------------------;
# conventions sur les �v�nements
# ------------------------------------;
update type_evenement set CONVENTION=1 where TE_CODE='FOR';

# ------------------------------------;
# changer type evenement
# ------------------------------------;
update type_evenement set TE_CODE='ALERT' where TE_CODE='MET';
update evenement set TE_CODE='ALERT' where TE_CODE='MET';
update type_bilan set TE_CODE='ALERT' where TE_CODE='MET';
update type_participation set TE_CODE='ALERT' where TE_CODE='MET';

# ------------------------------------;
# param�trage gardes
# ------------------------------------;

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

# ------------------------------------;
# permissions specififques materiel et conso
# ------------------------------------;
UPDATE fonctionnalite
SET F_LIBELLE='V�hicules', F_DESCRIPTION = 'Ajouter ou modifier des v�hicules<br> Permet d\'engager des v�hicules sur les �v�nements.'
WHERE fonctionnalite.F_ID = 17;

INSERT INTO fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
('70', 'Mat�riel et tenues', '0', '7', '0', 'Ajouter ou modifier le mat�riel ou les tenues<br> Permet d\'engager du mat�riel sur les �v�nements.'),
('71', 'Consommables', '0', '7', '0', 'Ajouter ou modifier des produits consommables<br> Permet d\'enregistrer les consommations sur les �v�nements.');

INSERT INTO habilitation (GP_ID, F_ID)
select GP_ID,70
from habilitation where F_ID=17;

INSERT INTO habilitation (GP_ID, F_ID)
select GP_ID,71
from habilitation where F_ID=17;

UPDATE menu_condition SET MC_VALUE = '70' WHERE MC_CODE = 'MAT_E' AND MC_TYPE = 'permission' AND MC_VALUE = 17;

# ------------------------------------;
# fix label
# ------------------------------------;
UPDATE fonctionnalite SET F_LIBELLE = 'Notification Garde' WHERE F_ID = 60;

# ------------------------------------;
# webservice formation
# ------------------------------------;
ALTER TABLE section ADD
SHOW_PHONE3 TINYINT NOT NULL DEFAULT '1',
ADD SHOW_EMAIL3 TINYINT NOT NULL DEFAULT '1',
ADD SHOW_URL TINYINT NOT NULL DEFAULT '1';

update section set SHOW_PHONE3=0 where S_PHONE3 is null or S_PHONE3 = '';
update section set SHOW_EMAIL3=0 where S_EMAIL3 is null or S_EMAIL3 = '';
update section set SHOW_URL=0 where S_URL is null or S_URL = '';

delete from log_type where LT_CODE in ('UPDS28','UPDS29','UPDS30');
INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDS28', 'S', 'Modification affichage t�l�phone formation'),
('UPDS29', 'S', 'Modification affichage email formation'),
('UPDS30', 'S', 'Modification affichage Site web formation');

update log_type set LT_DESCRIPTION='Modification t�l�phone formation'
where LT_CODE='UPDS8';
update log_type set LT_DESCRIPTION='Modification email formation'
where LT_CODE='UPDS12';
update log_type set LT_DESCRIPTION='Modification URL site Web'
where LT_CODE='UPDS13';

# ------------------------------------;
# grades 
# ------------------------------------;

ALTER TABLE grade CHANGE G_GRADE G_GRADE VARCHAR(6) NOT NULL;
ALTER TABLE pompier CHANGE P_GRADE P_GRADE VARCHAR(6) NOT NULL;

delete from grade where G_GRADE in ('AA','REDP1','REDP2','TEC','TECP1','TECP2');
INSERT INTO grade (G_GRADE, G_DESCRIPTION, G_LEVEL, G_TYPE, G_CATEGORY) VALUES
('AA','Adjoint Administratif','1','adjoints administratifs','PATS'),
('REDP1', 'r�dacteur principal de 1�re classe', '7', 'r�dacteurs', 'PATS'),
('REDP2', 'r�dacteur principal de 2�me classe', '7', 'r�dacteurs', 'PATS'),
('TEC', 'technicien', '26', 'adjoints technique', 'PATS'),
('TECP1', 'technicien principal de 1�re classe', '26', 'adjoints technique', 'PATS'),
('TECP2', 'technicien principal de 2�me classe', '26', 'adjoints technique', 'PATS');

delete from grade where G_GRADE in ('ISP','ICN','ISPP','ICS','ISPC','IHC','ISPE','MASP','MLTN','MCPT','MCDT','MLCL','MCOL','MHC','PPH','PPHS','PHCPT','PHCDT','PHLCL','PHCOL','VETCPT','VETCDT','VETLCL','VETCOL','CSAN2','CSAN1','CSANSU');
INSERT INTO grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY) values
('ISP','Infirmier','120','SSSM','SP'),
('ICN','Infirmier classe normale','120','SSSM','SP'),
('ISPP','Infirmier Principal','121','SSSM','SP'),
('ICS','Infirmier classe sup�rieure','121','SSSM','SP'),
('ISPC','Infirmier Chef Capitaine','122','SSSM','SP'),
('IHC','Infirmier hors classe','122','SSSM','SP'),
('ISPE','Infirmier d''encadrement ','123','SSSM','SP'),
('MASP','M�decin Aspirant','124','SSSM','SP'),
('MLTN','M�decin Lieutenant','125','SSSM','SP'),
('MCPT','M�decin Capitaine','126','SSSM','SP'),
('MCDT','M�decin Commandant','127','SSSM','SP'),
('MHC','M�decin hors classe','127','SSSM','SP'),
('MLCL','M�decin Lieutenant Colonel','128','SSSM','SP'),
('MCOL','M�decin Colonel','129','SSSM','SP'),
('PPH','Pr�parateur Pharmacie','130','SSSM','SP'),
('PPHS','Pr�parateur Pharmacie cl sup','130','SSSM','SP'),
('PHCPT','Pharmacien Capitaine','132','SSSM','SP'),
('PHCDT','Pharmacien Commandant','133','SSSM','SP'),
('PHLCL','Pharmacien Lieutenant Colonel','135','SSSM','SP'),
('PHCOL','Pharmacien Colonel','136','SSSM','SP'),
('VETCPT','V�t�rinaire Capitaine','140','SSSM','SP'),
('VETCDT','V�t�rinaire Commandant','142','SSSM','SP'),
('VETLCL','V�t�rinaire Lieutenant Colonel','145','SSSM','SP'),
('VETCOL','V�t�rinaire Colonel','146','SSSM','SP'),
('CSAN2','Cadre de sant� 2�me classe','150','SSSM','SP'),
('CSAN1','Cadre de sant� 1�re classe','151','SSSM','SP'),
('CSANSU','Cadre sup�rieur de sant�','152','SSSM','SP');

#-------------------------------------;
# Improve Display Garde Jour
#-------------------------------------;
UPDATE menu_item SET MI_URL = 'feuille_garde.php?evenement=0&filter=0&from=gardes' WHERE MI_CODE = 'GARDEJOUR';

# ------------------------------------
# Add type_indisponibilite
# ------------------------------------
ALTER TABLE type_indisponibilite CHANGE TI_LIBELLE TI_LIBELLE VARCHAR(40) NOT NULL;
delete from type_indisponibilite where TI_CODE in ('CP','CA','DIV','FAM','FOR','MAL','PRO','RTT','RT','REC','RECFO','CET','ASA','NAI','PAT','ENFM','ENFH','AT','MAT','ASYND','ELEC','DISPO','SUSP');
INSERT INTO type_indisponibilite (TI_CODE,TI_LIBELLE,TI_FLAG) VALUES
('CP','Cong�s pay�s','1'),
('CA', 'Cong� Annuel','1'),
('DIV','Autre Raison','0'),
('FAM','raison familiale','0'),
('FOR','formation','0'),
('MAL','maladie / blessure','0'),
('PRO','raison professionnelle','0'),
('RTT','R�duction du temps de travail','1'),
('RT','Repos r�gime de travail mixte','1'),
('REC', 'R�cup�ration', '1'),
('RECFO', 'R�cup�ration li�e � une formation','1'),
('CET', 'Compte �pargne Temps','1'),
('ASA', 'Autorisation sp�ciale absence','1'),
('NAI', 'Cong� Naissance','1'),
('PAT', 'Cong� Paternit�','1'),
('ENFM', 'Enfant Malade','1'),
('ENFH', 'Enfant Hospitalis�','1'),
('AT', 'Accident en service command�','1'),
('MAT', 'Cong� Maternit�','1'),
('ASYND', 'Activit� Syndicale','1'),
('ELEC', 'Elections','1'),
('DISPO', 'Disponibilit�','1'),
('SUSP', 'Suspension activit�','0');

#-------------------------------------;
# Nouveaux agr�ments
#-------------------------------------;
ALTER TABLE type_agrement CHANGE TA_CODE TA_CODE VARCHAR(7) NOT NULL;
ALTER TABLE type_agrement CHANGE TA_DESCRIPTION TA_DESCRIPTION VARCHAR(75) NOT NULL;
ALTER TABLE agrement CHANGE TA_CODE TA_CODE VARCHAR(7) NOT NULL;

delete from categorie_agrement where CA_CODE='SPE';
INSERT INTO categorie_agrement (CA_CODE, CA_DESCRIPTION, CA_FLAG)
VALUES ('SPE', 'Formations sp�cifiques', '0');
delete from type_agrement where CA_CODE='SPE';
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('SC', 'SPE', 'Secourisme canin', '0'),
('PSSP', 'SPE', 'Premiers Secours Socio-psychologiques', '0'),
('CE', 'SPE', 'Chef d''�quipe', '0'),
('CP', 'SPE', 'Chef de poste', '0');

delete from type_agrement where TA_CODE in('CPS','APS-ASD');
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('APS-ASD', 'ENT', 'Acteur Pr�vention Secours / Aide et soins � domicile', '0');
update agrement set TA_CODE='APS-ASD' where TA_CODE='CPS';

update type_agrement set TA_CODE='PS' where TA_CODE='PSE';
update agrement set TA_CODE='PS' where TA_CODE='PSE';

delete from type_agrement where TA_CODE in('PAE','PAE PS','GQS ','PSC1','PAE PSC','PS','PSE');
INSERT INTO type_agrement (TA_CODE, CA_CODE, TA_DESCRIPTION, TA_FLAG)
VALUES ('GQS', 'FOR', 'Sensibilisation aux Gestes Qui Sauvent', '0'),
('PSC1', 'FOR', 'Formation Pr�vention et Secours Civiques de niveau 1', '0'),
('PAE-PSC', 'FOR', 'Formation de formateur en Pr�vention et Secours Civiques de niveau 1', '0'),
('PAE-PS', 'FOR', 'Formation de formateur aux Premiers Secours', '0'),
('PS', 'FOR', 'Formation de formateur aux Premiers Secours', '0');

update agrement set TA_CODE='PAE-PS' where TA_CODE='PAE';
update agrement set TA_CODE='PAE-PS' where TA_CODE='PAE PS';
update agrement set TA_CODE='PAE-PSC' where TA_CODE='PAE PSC';
update type_agrement set TA_CODE='PAE-PS' where TA_CODE='PAE PS';
update type_agrement set TA_CODE='PAE-PSC' where TA_CODE='PAE PSC';

# ------------------------------------;
# comp�tences
# ------------------------------------;
update poste set TYPE='PAE PSC', DESCRIPTION='Formateur en Pr�vention et Secours Civiques' where TYPE='PAE1';
update poste set TYPE='PAE PS', DESCRIPTION='Formateur aux Premiers Secours' where TYPE='PAE2';
update poste set TYPE='FDF PSE', DESCRIPTION='Formateur de Formateurs aux Premiers Secours' where TYPE='PAE3';

# ------------------------------------;
# web URLs
# ------------------------------------;
ALTER TABLE section CHANGE S_URL S_URL VARCHAR(60) NULL;

# ------------------------------------;
# impliqu�s
# ------------------------------------;
ALTER TABLE victime ADD VI_IMPLIQUE TINYINT NOT NULL DEFAULT '0' AFTER VI_REFUS, ADD INDEX (VI_IMPLIQUE);

# ------------------------------------;
# cascade facultative validation
# ------------------------------------;
ALTER TABLE poste_hierarchie ADD PH_UPDATE_MANDATORY TINYINT NOT NULL DEFAULT '0' AFTER PH_UPDATE_LOWER_EXPIRY;

# ------------------------------------;
# dipl�mes
# ------------------------------------;
ALTER TABLE poste ADD PS_PRINT_IMAGE TINYINT NOT NULL DEFAULT '0' AFTER PS_PRINTABLE;
ALTER TABLE poste ADD PS_NUMERO TINYINT NOT NULL DEFAULT '0' AFTER PS_DIPLOMA;
update poste set PS_NUMERO=1 where PS_DIPLOMA=1 and PS_PRINT_IMAGE=0;

# ------------------------------------;
# departement de naissance
# ------------------------------------;
ALTER TABLE pompier ADD P_BIRTH_DEP VARCHAR(3) NULL AFTER P_BIRTHPLACE;

INSERT INTO log_type (LT_CODE, LC_CODE, LT_DESCRIPTION) VALUES
('UPDP51', 'P', 'Changement D�partement de Naissance');

#-------------------------------------;
# Add type notification facultative
#-------------------------------------;
INSERT INTO fonctionnalite(F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES 
(72,'Notification participation',0,10,0,'Permet de recevoir un rappel la veille de la participation � un �v�nement');

#-------------------------------------;
# Notes de frais v3 - split permission
#-------------------------------------;
update fonctionnalite set F_LIBELLE='Cotisations',F_DESCRIPTION='D�finir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres'
where F_ID=53;

insert into fonctionnalite (F_ID, F_LIBELLE, F_TYPE, TF_ID, F_FLAG, F_DESCRIPTION) VALUES
(73, 'Valider Notes de frais', 0,7,0,'<br>Modifier et valider les notes de frais. <br>Recevoir les notifications par mail si une note est cr��e.<br><b>Attention:</b> on ne peut cependant pas valider compl�tement ses propres notes de frais.');

insert into habilitation(GP_ID,F_ID)
select GP_ID, 73
from habilitation where F_ID=53;

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

ALTER TABLE note_de_frais_type_frais
ADD TF_COMMENT VARCHAR(300) NULL AFTER TF_UNITE;

update notification_block set F_ID=73 where F_ID=53;

#-------------------------------------;
#Add type id SSO CAS service
#-------------------------------------;
ALTER TABLE demande ADD P_CODE VARCHAR(20) NULL DEFAULT NULL AFTER P_ID;

# --------------------------------------------------------;
# Structure de la table evenement_piquets_feu
# --------------------------------------------------------;
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

# --------------------------------------------------------;
# D�finir comp�tence requise pour un piquet sur v�hicule
# --------------------------------------------------------;
ALTER TABLE type_vehicule_role ADD PS_ID INT NOT NULL DEFAULT '0' AFTER ROLE_NAME;

# --------------------------------------------------------;
# SMSGatewayme new API - API token au lieu de password
# --------------------------------------------------------;
ALTER TABLE configuration CHANGE VALUE VALUE VARCHAR(255) NULL;
ALTER TABLE section CHANGE SMS_LOCAL_PASSWORD SMS_LOCAL_PASSWORD VARCHAR(255) NULL;
UPDATE configuration SET DESCRIPTION = 'mot de passe du compte SMS, ou API token pour smsgateway.me' WHERE ID = 11;

# --------------------------------------------------------;
# comp�tences secondaires SP
# --------------------------------------------------------;
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

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='4.4' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;