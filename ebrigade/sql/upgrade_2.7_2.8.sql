#====================================================;
#  Upgrade v2.8;
#====================================================;

# ------------------------------------;
# nouveaux type bilans
# ------------------------------------;
INSERT INTO type_bilan (
TB_ID ,
TE_CODE ,
TB_NUM ,
TB_LIBELLE
)
VALUES (
'14', 'NAUT',  '1',  'soins r�alis�s (hors �vac.)'
), (
'15', 'NAUT',  '2',  '�vacuations r�alis�es'
), (
'16', 'NAUT',  '3',  'personnes assist�es'
);

update type_evenement set CEV_CODE='C_SEC' where TE_CODE='NAUT';

# ------------------------------------;
# formatage des messages affich�s
# ------------------------------------;

update type_message set TM_ICON = 'message.png' where TM_ID=0;
update type_message set TM_ICON = 'computer.png' where TM_ID=1;
update type_message set TM_ICON = 'alert.png' where TM_ID=2;

# ------------------------------------;
# possibilit� de d�sactiver mails
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('28', 'mail_allowed', '1', 'Permet l''envoi de tous les mails (parfois utile de d�sactiver pour environnements de test)', '12');

# ------------------------------------;
# nouvelle possibilit� de cacher documents
# ------------------------------------;
INSERT INTO document_security (DS_ID, DS_LIBELLE, F_ID) VALUES ('7', 'visible seulement par le personnel de la section.', '44');

# ------------------------------------;
# civilit�
# ------------------------------------;
drop table if exists type_civilite;
CREATE TABLE type_civilite (
TC_ID tinyint(1) NOT NULL,
TC_LIBELLE varchar(25) NOT NULL,
PRIMARY KEY (TC_ID),
UNIQUE KEY TC_LIBELLE (TC_LIBELLE)
);

INSERT INTO type_civilite (TC_ID,TC_LIBELLE) VALUES
(1, 'Monsieur'),
(2, 'Madame'),
(3, 'Mademoiselle')
;

ALTER TABLE pompier ADD P_CIVILITE TINYINT(1) NOT NULL DEFAULT '1' AFTER P_SEXE;

update pompier set P_CIVILITE=1 where P_SEXE='M';
update pompier set P_CIVILITE=2 where P_SEXE='F';
update pompier set P_CIVILITE=3 where P_SEXE='F' 
and EXTRACT(YEAR FROM (FROM_DAYS(DATEDIFF(NOW(),P_BIRTHDATE))))+0 < 25
and P_BIRTHDATE is not null;

# ------------------------------------;
# bug potentiel data corrompues
# ------------------------------------;

update personnel_formation
set PF_UPDATE_BY=null,
PF_UPDATE_DATE=null
where E_CODE=0;

# ------------------------------------;
# bug potentiel data corrompues
# ------------------------------------;
alter table evenement add E_PARTIES tinyint default 1 not null;

update evenement set E_PARTIES=(select count(1) from evenement_horaire where evenement_horaire.E_CODE = evenement.E_CODE);

# ------------------------------------;
# tarif par personne dans la formation
# ------------------------------------;
ALTER TABLE evenement ADD E_TARIF FLOAT NULL;
ALTER TABLE evenement_participation ADD EP_TARIF FLOAT NULL;
ALTER TABLE evenement_participation ADD EP_PAID TINYINT(1) NULL;

ALTER TABLE evenement ADD INDEX (E_TARIF);
ALTER TABLE evenement_participation ADD INDEX (EP_TARIF);
ALTER TABLE evenement_participation ADD INDEX (EP_PAID);
ALTER TABLE evenement_participation ADD INDEX (EP_FLAG1);

# ------------------------------------;
# corrections descriptions fonctionnalit�s
# ------------------------------------;
UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter du personnel dans l''application.<br>Un mot de passe al�atoire est g�n�r� et un mail est envoy� au nouvel utilisateur <br>pour lui indiquer que son compte a �t� cr��.<br> Seul le personnel interne est concern� ici. L''habilitation 37 est requise pour le personnel externe.' 
WHERE F_ID=1;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Saisir ses absences personnelles, demandes de cong�s pay�s (pour le personnel professionnel <br>ou salari�), absences pour raisons personnelles ou autres.<br>Dans le cas d''une demande de cong�s une demande de validation est envoy�e au responsable <br>du demandeur.' 
WHERE F_ID=11;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Configuration de l''application eBrigade, gestion des sauvegardes <br>de la base de donn�es. Supprimer des sections. Supprimer des messages sur <br>la messagerie instantan�e.<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient �tre habilit�s pour utiliser cette fonctionnalit�.' 
WHERE F_ID=14;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Cr�er de nouveaux �v�nements, modifier les �v�nements existants, inscrire du personnel <br>et du mat�riel sur les �v�nements.' 
WHERE F_ID=15;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter des informations visibles par les autres utilisateurs sur la pages infos diverses.<br>Ces informations sont aussi visibles sur la page d''accueil.' 
WHERE F_ID=16;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Ajouter ou modifier des v�hicules ou du mat�riel. <br>Permet d''engager des v�hicules ou du mat�riel sur les �v�nements.' 
WHERE F_ID=17;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Supprimer des �v�nements, des v�hicules, du mat�riel ou des entreprises clientes.<br> Modifier des �v�nements dans le pass�<br><img src=images/miniwarn.png> Attention seuls les administrateurs devraient �tre habilit�s pour utiliser cette fonctionnalit�.' 
WHERE F_ID=19;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permissions du cadre de permanence. Donne aussi des droits de cr�ation<br> et de modification sur les �v�nements, d''inscription du personnel ou d''engagement <br>des v�hicule et du mat�riel.<br> Permet aussi de changer le cadre de permanence.' 
WHERE F_ID=26;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Voir les graphiques montrant les statistiques op�rationnelles <br>(si le module compl�mentaire ChartDirector est install�).<br>Utiliser les fonctionnalit�s de reporting.<br>Voir les cartes de France (si le module france map est install�).' 
WHERE F_ID=27;

UPDATE fonctionnalite SET F_DESCRIPTION = 'S''inscrire ou inscrire du personnel sur les �v�nements de toutes les sections <br>ou de toutes les zones g�ographiques.' 
WHERE F_ID=28;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Utiliser la fonctionnalit� de comptabilit� permettant de visualiser,<br> de cr�er ou de modifier des devis ou des factures pour les DPS, les formations <br>ou les autres activit�s facturables.<br>Modifier les param�trage des devis et factures sur la page section' 
WHERE F_ID=29;

UPDATE fonctionnalite SET F_DESCRIPTION = 'Permet d''attribuer ou de modifier des comp�tences consid�r�es comme �lev�es.<br> Dans la page de param�trage des comp�tences,<br> on peut d�finir si une comp�tence requiret cette habilitation pour pouvoir �tre attribu�e <br>� une personne.' 
WHERE F_ID=31;
# ------------------------------------;
# bloquer �v�nements termin�s
# ------------------------------------;
ALTER TABLE section ADD  NB_DAYS_BEFORE_BLOCK SMALLINT NOT NULL DEFAULT  '0';

# ------------------------------------;
# Nouveau type �v�nement
# ------------------------------------;
INSERT INTO type_evenement (TE_CODE, TE_LIBELLE, CEV_CODE) VALUES ('COOP','Coop�ration �tat-sdis-samu', 'C_SEC');

INSERT INTO type_bilan (
TB_ID ,
TE_CODE ,
TB_NUM ,
TB_LIBELLE
)
VALUES (
'17', 'COOP',  '1',  'personnes transport�es'
);

# ------------------------------------;
# Bug, �viter doublons sur section_flat
# ------------------------------------;
ALTER TABLE section_flat ADD UNIQUE (S_ID);

# ------------------------------------;
# grades
# ------------------------------------;
update pompier set P_GRADE='SAP' where P_GRADE='1CL';
delete from grade where G_GRADE='1CL';
update grade set G_DESCRIPTION='sapeur' where G_GRADE='SAP';

ALTER TABLE grade ADD G_CATEGORY VARCHAR(5) NOT NULL DEFAULT 'SP';

insert into grade (G_GRADE,G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values 
('AA1','adjoint administratif 1�re classe',1,'adjoints administratifs','PATS'),
('AA2','adjoint administratif 2�me classe',2,'adjoints administratifs','PATS'),
('AA2NT','adjoint administratif 2�me classe non titulaire',3,'adjoints administratifs','PATS'),
('AM','agent de ma�trise',8,'agents de ma�trise','PATS'),
('AMP','agent de ma�trise principal',9,'agents de ma�trise','PATS'),
('ATP1','adjoint technique principal de 1�re classe',15,'adjoints technique','PATS'),
('ATP2','adjoint technique principal de 2�me classe',16,'adjoints technique','PATS'),
('AT1','adjoint technique de 1�re classe',17,'adjoints technique','PATS'),
('AT2','adjoint technique de 2�me classe',18,'adjoints technique','PATS'),
('AT2NT','adjoint technique de 2�me classe non titulaire',19,'adjoints technique','PATS'),
('ATT','attach�',30,'attach�s','PATS'),
('-','non renseign�',0,'tous','ALL');


# ------------------------------------;
# gestion syndicat
# ------------------------------------;

INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('29', 'syndicate', '0', 'association de type syndicat, gestion des adh�rents', '2');
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('30', 'externes', '0', 'Gestion du personnel externe et des entreprises (par exemple les stagiaires des formations)', '12');
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('31', 'cotisations', '0', 'Gestion des cotisations des membres ou des adh�rents', '12');
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('32', 'bank_accounts', '0', 'Enregistrement des comptes bancaires des adh�rents - RIB', '12');

ALTER TABLE configuration ADD HIDDEN TINYINT NOT NULL DEFAULT '0';
update configuration set HIDDEN=1 where ID in (-1 , 29);

update configuration set VALUE=1 where ID=30 and (select count(1) from pompier where P_STATUT = 'EXT' ) > 0;
update configuration set DESCRIPTION='activer la notion de grades' where ID=5;
update configuration set DESCRIPTION='activer la communication par chat - messagerie instantan�e entre les membres' where ID=19;

INSERT INTO statut (S_STATUT, S_DESCRIPTION, S_CONTEXT) 
VALUES ('ADH', 'Adh�rent', '0');

DROP TABLE IF EXISTS categorie_grade;
CREATE TABLE categorie_grade (
CG_CODE varchar(5) NOT NULL,
CG_DESCRIPTION varchar(40) NOT NULL,
PRIMARY KEY (CG_CODE));

insert into categorie_grade (CG_CODE,CG_DESCRIPTION)
values
('ALL', 'Sans cat�gorie'),
('SP', 'Sapeurs pompiers'),
('PATS', 'Agents territoriaux');

update pompier set P_GRADE='-' where P_GRADE='SAP' and (select VALUE from configuration where ID=2 ) = 0;

# ------------------------------------;
# fonctionnalites
# ------------------------------------;

update fonctionnalite set
F_ID=52,
F_LIBELLE='Voir les infos - avanc�',
F_DESCRIPTION="Permet � une personne de voir tous les messages d'information et l'organigramme.<br> Permet aussi d'acc�der � Procion.<br>Tous les membres peuvent avoir cette permission, mais pas les externes."
where F_ID=44;

delete from fonctionnalite where F_ID in (44);
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('44','Voir les infos - basique','0','0','0','Voir les infos au niveau de sa section.<br> Tous les membres peuvent avoir cette permission.');

INSERT INTO habilitation (GP_ID,F_ID)
select GP_ID,'52'
from habilitation
where F_ID=44;

update document_security set F_ID=52 where F_ID=44;

# ------------------------------------;
# profession
# ------------------------------------;

DROP TABLE IF EXISTS type_profession;
CREATE TABLE type_profession (
TP_CODE varchar(6) NOT NULL,
TP_DESCRIPTION  varchar(40) NOT NULL,
PRIMARY KEY (TP_CODE));

insert into type_profession (TP_CODE,TP_DESCRIPTION)
values
('-', 'non renseign�'),
('SPP', 'Sapeur pompier professionnel'),
('SPV', 'Sapeur pompier volontaire'),
('PATS', 'Agent territorial');

alter table pompier add P_PROFESSION varchar(6) not null default 'NR' after P_GRADE;

# ------------------------------------;
# compte bancaire
# ------------------------------------;

drop table if exists compte_bancaire;
create table compte_bancaire (
P_ID int(11) not null,
ETABLISSEMENT varchar(5) not null,
GUICHET varchar(5) not null,
COMPTE varchar(11) not null,
CLE_RIB varchar(2) null,
CODE_BANQUE varchar(30) null,
UPDATE_DATE timestamp not null,
PRIMARY KEY (P_ID));

drop table if exists type_paiement;
create table type_paiement (
TP_ID tinyint(1) not null,
TP_DESCRIPTION varchar(25) not null,
PRIMARY KEY (TP_ID));

insert into type_paiement (TP_ID,TP_DESCRIPTION)
values
(0, 'non renseign�'),
(1, 'pr�l�vement'),
(2, 'virement'),
(3, 'carte bancaire'),
(4, 'ch�que'),
(5, 'esp�ces');

# ------------------------------------;
# ajouter documents aux v�hicules
# ------------------------------------;
ALTER TABLE document ADD V_ID INT NOT NULL DEFAULT '0' AFTER P_ID;
INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('AC', 'Aucune cat�gorie');

# ------------------------------------;
# agrandir champ S_CODE
# ------------------------------------;
ALTER TABLE section CHANGE S_CODE  S_CODE VARCHAR(15)  NOT NULL DEFAULT  'MON CODE';
ALTER TABLE section_flat CHANGE S_CODE  S_CODE VARCHAR(15) NOT NULL;

# ------------------------------------;
# augmenter les permissions modif de section
# ------------------------------------;
UPDATE  fonctionnalite SET F_FLAG = '0' WHERE F_ID=22;

# ------------------------------------;
# gestion des rejets
# ------------------------------------;
drop table if exists defaut_bancaire;
create table defaut_bancaire (
D_ID tinyint(4) not null,
D_DESCRIPTION varchar(50) not null,
PRIMARY KEY (D_ID));

insert into defaut_bancaire (D_ID,D_DESCRIPTION)
values
(0, 'non renseign�'),
(1, 'compte sold�'),
(2, 'provision insuffisante'),
(3, 'opposition sur compte'),
(4, 'pas d''ordre � payer'),
(5, 'op�ration pr�sum�e erron�e'),
(6, 'demande de prorogation'),
(7, 'tirage contest�'),
(8, 'coordonn�es bancaires inexploitables'),
(9, 'r�gularisation d''autorisation de pr�l�vement');

drop table if exists periode;
create table periode (
P_CODE varchar(4) not null,
P_DESCRIPTION  varchar(30) not null,
P_ORDER tinyint not null default 1,
PRIMARY KEY (P_CODE));

insert into periode (P_CODE,P_DESCRIPTION,P_ORDER)
values
('A', 'ann�e compl�te', 1),
('S1', 'premier semestre', 2),
('S2', 'deuxi�me semestre', 3),
('T1', 'premier trimestre', 4),
('T2', 'deuxi�me trimestre', 5),
('T3', 'troisi�me trimestre', 6),
('T4', 'quatri�me trimestre', 7),
('JAN', 'janvier', 8),
('FEV', 'f�vrier', 9),
('MAR', 'mars', 10),
('APR', 'avril', 11),
('MAI', 'mai', 12),
('JUN', 'juin', 13),
('JUL', 'juillet', 14),
('AUG', 'ao�t', 15),
('SEP', 'septembre', 16),
('OCT', 'octobre', 17),
('NOV', 'novembre', 18),
('DEC', 'd�cembre', 19);


drop table if exists rejet;
create table rejet (
R_ID int(11) auto_increment not null,
P_ID int(11) not null,
ANNEE varchar(4) not null,
PERIODE_CODE varchar(30) not null,
DEFAUT_ID tinyint(4) not null,
MONTANT_REJET float null,
REGULARISE tinyint not null default 0,
DATE_REGUL date null,
MONTANT_REGUL float null,
OBSERVATION varchar(150) null,
PRIMARY KEY (R_ID),
INDEX (P_ID),
INDEX (DEFAUT_ID),
INDEX (ANNEE,PERIODE_CODE));


# ------------------------------------;
# suppression sections
# ------------------------------------;

update fonctionnalite set
F_DESCRIPTION="Ajouter ou modifier des sections dans l'organigramme.<br> Cette fonctionnalit� ne permet pas de supprimer une section � un niveau autre que<br> antenne locale (il faut avoir 14 pour cela)."
where F_ID=22;


update fonctionnalite set
F_DESCRIPTION="Enregistrer des absences pour les autres personnes."
where F_ID=12;

# ------------------------------------;
# modification fiche personnel
# ------------------------------------;
ALTER TABLE pompier ADD P_DATE_ENGAGEMENT DATE NULL AFTER P_DEBUT;
update pompier set P_DATE_ENGAGEMENT = concat(P_DEBUT,'-01-01') where P_DEBUT is not null and P_DEBUT <> '';
ALTER TABLE pompier ADD INDEX (P_DATE_ENGAGEMENT);
ALTER TABLE pompier ADD INDEX (P_FIN);

ALTER TABLE pompier ADD P_NOM_NAISSANCE VARCHAR(30) NULL AFTER P_NOM;
ALTER TABLE pompier ADD INDEX (P_NOM_NAISSANCE);

insert into log_history(P_ID,LH_STAMP,LT_CODE,LH_WHAT,LH_COMPLEMENT,COMPLEMENT_CODE)
select p.P_UPDATED_BY,p.P_FIN, 'UPDSTP', p.P_ID, 'Radiation => ancien membre' ,null
from pompier p
where p.P_UPDATED_BY is not null
and p.P_OLD_MEMBER > 0 
and not exists (select 1 from log_history l where p.P_ID=l.LH_WHAT and l.LT_CODE='UPDSTP' and l.LH_COMPLEMENT='ancien membre');

insert into log_history(P_ID,LH_STAMP,LT_CODE,LH_WHAT,LH_COMPLEMENT,COMPLEMENT_CODE)
select p.P_CREATED_BY, p.P_CREATE_DATE, 'INSP', p.P_ID, 'Cr�ation fiche' ,null
from pompier p
where p.P_CREATED_BY is not null
and not exists (select 1 from log_history l where p.P_ID=l.LH_WHAT and l.LT_CODE='INSP');

ALTER TABLE pompier DROP P_DEBUT;
ALTER TABLE pompier DROP P_UPDATED_BY;
ALTER TABLE pompier DROP P_CREATED_BY;

ALTER TABLE type_membre ADD TM_SYNDICAT TINYINT NOT NULL DEFAULT  '0' AFTER  TM_ID;
ALTER TABLE type_membre DROP PRIMARY KEY;
ALTER TABLE type_membre ADD PRIMARY KEY (TM_ID, TM_SYNDICAT);

INSERT INTO  type_membre (TM_ID ,TM_SYNDICAT ,TM_CODE)
VALUES (
'0',  '1',  'actif'
), (
'1',  '1',  'radi� - � sa demande'
), (
'2',  '1',  'radi� - d�part retraite'
), (
'3',  '1',  'radi� - impay�s'
), (
'4',  '1',  'radi� - d�mission'
), (
'5',  '1',  'radi� - d�c�d�'
), (
'6',  '1',  'radi� - mutation'
), (
'7',  '1',  'radi� - pr�sident'
), (
'8',  '1',  'radi� - disponibilit�'
), (
'9',  '1',  'radi� - exclusion'
), (
'10',  '1',  'radi� - autre motif'
);

update type_membre set TM_CODE = concat('ancien - ',TM_CODE) 
where TM_ID > 0 and TM_SYNDICAT=0;

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDADR', 'P', 'Changement d''adresse');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDMAIL', 'P', 'Changement email');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDPHONE', 'P', 'Changement t�l�phone');

INSERT INTO  type_evenement (TE_CODE,TE_LIBELLE,CEV_CODE)
VALUES ('WEB', 'Visio conf�rence', 'C_DIV');

INSERT INTO  type_evenement (TE_CODE,TE_LIBELLE,CEV_CODE)
VALUES ('MED', 'M�dicalisation, �quipe m�dicale', 'C_SEC');

# ------------------------------------;
# modification adh�rents
# ------------------------------------;
ALTER TABLE periode ADD P_DATE VARCHAR( 7 ) NULL;

update periode set P_DATE='01' where P_CODE='JAN';
update periode set P_DATE='02' where P_CODE='FEV';
update periode set P_DATE='03' where P_CODE='MAR';
update periode set P_DATE='04' where P_CODE='APR';
update periode set P_DATE='05' where P_CODE='MAI';
update periode set P_DATE='06' where P_CODE='JUN';
update periode set P_DATE='07' where P_CODE='JUL';
update periode set P_DATE='08' where P_CODE='AUG';
update periode set P_DATE='09' where P_CODE='SEP';
update periode set P_DATE='10' where P_CODE='OCT';
update periode set P_DATE='11' where P_CODE='NOV';
update periode set P_DATE='12' where P_CODE='DEC';

# ------------------------------------;
# param�trage cotisations par section
# ------------------------------------;

ALTER TABLE pompier ADD SERVICE varchar(60) null;
ALTER TABLE pompier ADD TP_ID tinyint(1) not null default 0;
ALTER TABLE pompier ADD COTISATION_ANNUELLE float null;
ALTER TABLE pompier ADD MOTIF_RADIATION varchar(100) null;
ALTER TABLE pompier ADD NPAI tinyint(1) not null default 0;
ALTER TABLE pompier ADD DATE_NPAI DATE null;
ALTER TABLE pompier ADD OBSERVATION varchar(255) null;
ALTER TABLE pompier ADD SUSPENDU tinyint(1) not null default 0;
ALTER TABLE pompier ADD DATE_SUSPENDU DATE null;

update type_profession set TP_CODE='NR' where TP_CODE='-';
update pompier set P_PROFESSION='NR' where P_PROFESSION='-';

drop table if exists section_cotisation;
CREATE TABLE section_cotisation (
S_ID int(11) NOT NULL,
TP_CODE varchar(6) NOT NULL,
MONTANT float NOT NULL,
IDEM tinyint NOT NULL DEFAULT 0,
COMMENTAIRE varchar(150) DEFAULT NULL,
PRIMARY KEY (S_ID,TP_CODE),
INDEX (TP_CODE,IDEM)
);

drop table if exists personnel_cotisation;
CREATE TABLE personnel_cotisation (
PC_ID int(11) auto_increment not null,
P_ID int(11) NOT NULL,
ANNEE year NOT NULL,
TP_CODE varchar(4) NOT NULL,
PC_DATE date NOT NULL,
MONTANT float NOT NULL,
TP_ID tinyint(1) NOT NULL DEFAULT 0,
COMMENTAIRE varchar(100) DEFAULT NULL,
NUM_CHEQUE varchar(50) DEFAULT NULL,
CHEQUE_REJETE tinyint(1) NOT NULL DEFAULT 0,
PRIMARY KEY (PC_ID),
UNIQUE (P_ID,ANNEE,TP_CODE),
INDEX (NUM_CHEQUE),
INDEX (ANNEE,TP_CODE),
INDEX (CHEQUE_REJETE),
INDEX (TP_ID)
);

INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDRIB', 'P', 'Modification de compte bancaire');

ALTER TABLE  pompier ADD INDEX (TP_ID);
ALTER TABLE  pompier ADD INDEX (NPAI);
ALTER TABLE  pompier ADD INDEX (SUSPENDU);

delete from fonctionnalite where F_ID in (53);
INSERT INTO fonctionnalite (F_ID,F_LIBELLE,F_TYPE,TF_ID,F_FLAG,F_DESCRIPTION)
VALUES ('53','Gestion des cotisations','0','7','0','D�finir les montants des cotisations au niveau de sa section.<br>Enregistrer les cotisations des membres.');

INSERT INTO habilitation (GP_ID,F_ID)
values (4,'53');

ALTER TABLE  rejet CHANGE  PERIODE_CODE  PERIODE_CODE VARCHAR(4) NOT NULL;
ALTER TABLE  personnel_cotisation CHANGE  TP_CODE  PERIODE_CODE VARCHAR(4);

INSERT INTO  defaut_bancaire (D_ID ,D_DESCRIPTION)
VALUES ('10', 'ch�que rejet�');

ALTER TABLE rejet ADD DATE_REJET DATE NULL AFTER PERIODE_CODE;

ALTER TABLE periode ADD P_FRACTION SMALLINT NOT NULL;
update periode set P_FRACTION=1 where P_CODE='A';
update periode set P_FRACTION=2 where P_CODE in ('S1','S2');
update periode set P_FRACTION=4 where P_CODE in ('T1','T2','T3','T4');
update periode set P_FRACTION=12 where P_DATE is not null;

ALTER TABLE pompier add MONTANT_REGUL float null DEFAULT '0';
ALTER TABLE rejet ADD REPRESENTE TINYINT NOT NULL DEFAULT '0' AFTER REGULARISE;

insert into personnel_cotisation (P_ID,ANNEE,PERIODE_CODE,PC_DATE,MONTANT,TP_ID)
select P_ID, '2012','A', Q_UPDATE_DATE, 15, 4
from qualification
where PS_ID = (select PS_ID from poste where TYPE='Cot.')
and (YEAR(Q_EXPIRATION) = '2012' or Q_EXPIRATION is null);

update personnel_cotisation set PC_DATE='2012-01-01' where ANNEE=2012 and PC_DATE='0000-00-00';
update personnel_cotisation set PC_DATE='2011-01-01' where ANNEE=2011 and PC_DATE='0000-00-00';
update personnel_cotisation set PC_DATE='2010-01-01' where ANNEE=2010 and PC_DATE='0000-00-00';

update pompier set TP_ID=4 where TP_ID=0;


INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('INSCOT', 'P', 'Ajout cotisation');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDCOT', 'P', 'Mise � jour cotisation');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('DELCOT', 'P', 'Suppression cotisation');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('INSREJ', 'P', 'Ajout rejet');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('UPDREJ', 'P', 'Mise � jour rejet');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('DELREJ', 'P', 'Suppression rejet');

# ------------------------------------;
# Calendriers multiples
# ------------------------------------;
ALTER TABLE pompier ADD P_CALENDAR VARCHAR( 100 ) NULL;

# ------------------------------------;
# log roles
# ------------------------------------;
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('ADDROLE', 'P', 'Ajout r�le dans organigramme');
INSERT INTO log_type (LT_CODE,LC_CODE,LT_DESCRIPTION) VALUES ('DELROLE', 'P', 'Suppression r�le dans organigramme');

# ------------------------------------;
# bug -- start from here
# ------------------------------------;

update pompier set P_DATE_ENGAGEMENT=null where P_DATE_ENGAGEMENT='0000-00-00';
update pompier set P_BIRTHDATE=null where P_BIRTHDATE='0000-00-00';
update pompier set P_FIN=null where P_FIN='0000-00-00';
update pompier set DATE_NPAI=null where DATE_NPAI='0000-00-00';
update pompier set DATE_SUSPENDU=null where DATE_SUSPENDU='0000-00-00';

update pompier set P_OLD_MEMBER=0, P_FIN=null where P_STATUT='EXT';

# ------------------------------------;
# gestion des interventions et victimes
# ------------------------------------;

drop table if exists evenement_log;
CREATE TABLE evenement_log (
EL_ID int(11) auto_increment not null,
E_CODE int(11) not null,
TEL_CODE varchar(2) not null,
EL_DEBUT datetime NOT NULL,
EL_SLL TIME NULL,
EL_FIN datetime NULL,
EL_ADDRESS varchar(150) NULL,
EL_ORIGINE varchar(30) NULL,
EL_DESTINATAIRE varchar(30) NULL,
EL_TITLE varchar(30) NULL,
EL_COMMENTAIRE varchar(1000) NULL,
EL_RESPONSABLE int(11) NULL,
EL_DATE_ADD DATETIME not null,
PRIMARY KEY (EL_ID),
INDEX (E_CODE),
INDEX (EL_RESPONSABLE),
INDEX (EL_DEBUT),
INDEX (TEL_CODE)
);

drop table if exists victime;
CREATE TABLE victime (
VI_ID int(11) auto_increment not null,
EL_ID int(11) NOT NULL,
VI_NUMEROTATION smallint NULL,
VI_NOM varchar(30) NULL,
VI_PRENOM varchar(20) NULL,
VI_ADDRESS varchar(150) NULL,
VI_BIRTHDATE date NULL,
VI_SEXE char(1) NOT NULL DEFAULT 'M',
VI_PAYS smallint NOT NULL DEFAULT 65,
VI_DETRESSE_VITALE tinyint NOT NULL DEFAULT 0,
VI_DECEDE tinyint NOT NULL DEFAULT 0,
VI_MALAISE tinyint NOT NULL DEFAULT 0,
VI_INFORMATION tinyint NOT NULL DEFAULT 0,
VI_SOINS tinyint NOT NULL DEFAULT 0,
VI_REFUS tinyint NOT NULL DEFAULT 0,
VI_TRANSPORT tinyint NOT NULL DEFAULT 0,
VI_VETEMENT tinyint NOT NULL DEFAULT 0,
VI_ALIMENTATION tinyint NOT NULL DEFAULT 0,
D_CODE varchar(6) NOT NULL DEFAULT 'NR',
T_CODE varchar(6) NOT NULL DEFAULT 'ASS',
VI_COMMENTAIRE varchar(1000) NULL,
PRIMARY KEY (VI_ID),
INDEX (EL_ID),
INDEX (VI_DETRESSE_VITALE),
INDEX (VI_DECEDE),
INDEX (VI_INFORMATION),
INDEX (VI_MALAISE),
INDEX (VI_TRANSPORT),
INDEX (VI_SOINS),
INDEX (VI_VETEMENT),
INDEX (VI_ALIMENTATION),
INDEX (VI_REFUS),
INDEX (D_CODE),
INDEX (T_CODE),
INDEX (VI_PAYS)
);

drop table if exists destination;
CREATE TABLE destination (
D_CODE varchar(6) not null,
D_NAME varchar(30) not null,
PRIMARY KEY (D_CODE)
);

INSERT INTO destination (D_CODE,D_NAME)
values ('NR','Non renseign�');

INSERT INTO destination (D_CODE,D_NAME)
values ('HOSP','Centre hospitalier');

INSERT INTO destination (D_CODE,D_NAME)
values ('ACC','Accueil de jour/nuit');

INSERT INTO destination (D_CODE,D_NAME)
values ('DOUCH','Douche publique');

INSERT INTO destination (D_CODE,D_NAME)
values ('MISS','Mission locale');

INSERT INTO destination (D_CODE,D_NAME)
values ('PMA','Poste M�dical Avanc�');

INSERT INTO destination (D_CODE,D_NAME)
values ('CM','Cabinet M�dical');

INSERT INTO destination (D_CODE,D_NAME)
values ('CL','Clinique');


drop table if exists type_evenement_log;
CREATE TABLE type_evenement_log (
TEL_CODE varchar(2) not null,
TEL_DESCRIPTION varchar(30) not null,
PRIMARY KEY (TEL_CODE)
);

INSERT INTO type_evenement_log (TEL_CODE,TEL_DESCRIPTION)
values ('I','Intervention');

INSERT INTO type_evenement_log (TEL_CODE,TEL_DESCRIPTION)
values ('M','Message');



drop table if exists transporteur;
CREATE TABLE transporteur (
T_CODE varchar(6) not null,
T_NAME varchar(30) not null,
PRIMARY KEY (T_CODE)
);

INSERT INTO transporteur (T_CODE,T_NAME)
values ('ASS','Notre Association');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('AUTASS','Autre Association');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('SP','Sapeurs pompiers');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('SAMU','SAMU ou SMUR');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('AP','Ambulance priv�e');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('AUTR','Autre type de transport');

INSERT INTO transporteur (T_CODE,T_NAME)
values ('HELI','H�licopt�re');

drop table if exists pays;
CREATE TABLE pays (
ID smallint not null,
NAME varchar(50) not null,
PRIMARY KEY (ID)
);

INSERT INTO pays (ID, NAME) VALUES
(1, 'Afghanistan'),
(2, 'Afrique du Sud'),
(3, 'Albanie'),
(4, 'Alg�rie'),
(5, 'Allemagne'),
(6, 'Andorre'),
(7, 'Angola'),
(8, 'Antigua-et-Barbuda'),
(9, 'Arabie saoudite'),
(10, 'Argentine'),
(11, 'Arm�nie'),
(12, 'Australie'),
(13, 'Autriche'),
(14, 'Azerba�djan'),
(15, 'Bahamas'),
(16, 'Bahre�n'),
(17, 'Bangladesh'),
(18, 'Barbade'),
(19, 'Belau'),
(20, 'Belgique'),
(21, 'Belize'),
(22, 'B�nin'),
(23, 'Bhoutan'),
(24, 'Bi�lorussie'),
(25, 'Birmanie'),
(26, 'Bolivie'),
(27, 'Bosnie-Herz�govine'),
(28, 'Botswana'),
(29, 'Br�sil'),
(30, 'Brunei'),
(31, 'Bulgarie'),
(32, 'Burkina'),
(33, 'Burundi'),
(34, 'Cambodge'),
(35, 'Cameroun'),
(36, 'Canada'),
(37, 'Cap-Vert'),
(38, 'Chili'),
(39, 'Chine'),
(40, 'Chypre'),
(41, 'Colombie'),
(42, 'Comores'),
(43, 'Congo'),
(44, 'Congo'),
(45, 'Cook'),
(46, 'Cor�e du Nord'),
(47, 'Cor�e du Sud'),
(48, 'Costa Rica'),
(49, 'C�te d''Ivoire'),
(50, 'Croatie'),
(51, 'Cuba'),
(52, 'Danemark'),
(53, 'Djibouti'),
(54, 'Dominique'),
(55, '�gypte'),
(56, '�mirats arabes unis'),
(57, '�quateur'),
(58, '�rythr�e'),
(59, 'Espagne'),
(60, 'Estonie'),
(61, '�tats-Unis'),
(62, '�thiopie'),
(63, 'Fidji'),
(64, 'Finlande'),
(65, 'France'),
(66, 'Gabon'),
(67, 'Gambie'),
(68, 'G�orgie'),
(69, 'Ghana'),
(70, 'Gr�ce'),
(71, 'Grenade'),
(72, 'Guatemala'),
(73, 'Guin�e'),
(74, 'Guin�e-Bissao'),
(75, 'Guin�e �quatoriale'),
(76, 'Guyana'),
(77, 'Ha�ti'),
(78, 'Honduras'),
(79, 'Hongrie'),
(80, 'Inde'),
(81, 'Indon�sie'),
(82, 'Iran'),
(83, 'Iraq'),
(84, 'Irlande'),
(85, 'Islande'),
(86, 'Isra�l'),
(87, 'Italie'),
(88, 'Jama�que'),
(89, 'Japon'),
(90, 'Jordanie'),
(91, 'Kazakhstan'),
(92, 'Kenya'),
(93, 'Kirghizistan'),
(94, 'Kiribati'),
(95, 'Kowe�t'),
(96, 'Laos'),
(97, 'Lesotho'),
(98, 'Lettonie'),
(99, 'Liban'),
(100, 'Liberia'),
(101, 'Libye'),
(102, 'Liechtenstein'),
(103, 'Lituanie'),
(104, 'Luxembourg'),
(105, 'Mac�doine'),
(106, 'Madagascar'),
(107, 'Malaisie'),
(108, 'Malawi'),
(109, 'Maldives'),
(110, 'Mali'),
(111, 'Malte'),
(112, 'Maroc'),
(113, 'Marshall'),
(114, 'Maurice'),
(115, 'Mauritanie'),
(116, 'Mexique'),
(117, 'Micron�sie'),
(118, 'Moldavie'),
(119, 'Monaco'),
(120, 'Mongolie'),
(121, 'Mozambique'),
(122, 'Namibie'),
(123, 'Nauru'),
(124, 'N�pal'),
(125, 'Nicaragua'),
(126, 'Niger'),
(127, 'Nigeria'),
(128, 'Niue'),
(129, 'Norv�ge'),
(130, 'Nouvelle-Z�lande'),
(131, 'Oman'),
(132, 'Ouganda'),
(133, 'Ouzb�kistan'),
(134, 'Pakistan'),
(135, 'Panama'),
(136, 'Papouasie - Nouvelle Guin�e'),
(137, 'Paraguay'),
(138, 'Pays-Bas'),
(139, 'P�rou'),
(140, 'Philippines'),
(141, 'Pologne'),
(142, 'Portugal'),
(143, 'Qatar'),
(144, 'R�publique centrafricaine'),
(145, 'R�publique dominicaine'),
(146, 'R�publique tch�que'),
(147, 'Roumanie'),
(148, 'Royaume-Uni'),
(149, 'Russie'),
(150, 'Rwanda'),
(151, 'Saint-Christophe-et-Ni�v�s'),
(152, 'Sainte-Lucie'),
(153, 'Saint-Marin'),
(154, 'Saint-Si�ge'),
(155, 'Saint-Vincent-et-les Grenadine'),
(156, 'Salomon'),
(157, 'Salvador'),
(158, 'Samoa occidentales'),
(159, 'Sao Tom�-et-Principe'),
(160, 'S�n�gal'),
(161, 'Seychelles'),
(162, 'Sierra Leone'),
(163, 'Singapour'),
(164, 'Slovaquie'),
(165, 'Slov�nie'),
(166, 'Somalie'),
(167, 'Soudan'),
(168, 'Sri Lanka'),
(169, 'Su�de'),
(170, 'Suisse'),
(171, 'Suriname'),
(172, 'Swaziland'),
(173, 'Syrie'),
(174, 'Tadjikistan'),
(175, 'Tanzanie'),
(176, 'Tchad'),
(177, 'Tha�lande'),
(178, 'Togo'),
(179, 'Tonga'),
(180, 'Trinit�-et-Tobago'),
(181, 'Tunisie'),
(182, 'Turkm�nistan'),
(183, 'Turquie'),
(184, 'Tuvalu'),
(185, 'Ukraine'),
(186, 'Uruguay'),
(187, 'Vanuatu'),
(188, 'Venezuela'),
(189, 'Vi�t Nam'),
(190, 'Y�men'),
(191, 'Yougoslavie'),
(192, 'Za�re'),
(193, 'Zambie'),
(194, 'Zimbabwe');


# ------------------------------------;
# afficher donn�es confidentielles, m�dicales
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('33', 'store_confidential_data', '0', 'Permet l''enregistrement de donn�es confidentielles (dossier m�dical), suppose que toutes les pr�cautions de s�curit� et contraintes CNIL ont �t� prises en compte. Sinon seules les initiales des victimes pourront �tre sauv�es', '12');

# ------------------------------------;
# historiser autres actions
# ------------------------------------;
INSERT INTO log_type (
LT_CODE ,
LC_CODE ,
LT_DESCRIPTION
)
VALUES (
'INSEVT',  'E',  'cr�ation �v�nement'
), (
'UPDEVT',  'E',  'modification �v�nement'
), (
'CLOTEVT',  'E',  'cloture �v�nement'
), (
'OUVEVT',  'E',  'ouverture �v�nement'
), (
'INSMAIN',  'E',  'ajout main courante'
), (
'UPDMAIN',  'E',  'modification main courante'
);

# ------------------------------------;
# mettre days_audit dans la table de configuration
# ------------------------------------;
INSERT INTO configuration(ID,NAME,VALUE,DESCRIPTION,ORDERING) VALUES ('34', 'days_audit', '2', 'Nombre de jours pendant lesquels on conserve les enregistrements de connexion des utilisateurs', '15');

# ------------------------------------;
# ASA et DAS
# ------------------------------------;
ALTER TABLE  evenement_participation ADD EP_ASA TINYINT NOT NULL DEFAULT '0',
ADD EP_DAS TINYINT NOT NULL DEFAULT '0';
# ASA: autorisation sp�ciale d absence
# DAS: d�charge activit� de service

# ------------------------------------;
# Stats evenements
# ------------------------------------;
ALTER TABLE type_bilan ADD VICTIME_DETAIL VARCHAR(50) NULL;
update type_bilan set VICTIME_DETAIL='VI_TRANSPORT' where TB_LIBELLE in ("�vacuations r�alis�es","personnes transport�es","transports en centre d'h�bergement");
update type_bilan set VICTIME_DETAIL='VI_INFORMATION' where TB_LIBELLE in ("personnes assist�es");
update type_bilan set VICTIME_DETAIL='VI_SOINS' where TB_LIBELLE in ("soins r�alis�s (hors �vac.)");
update type_bilan set VICTIME_DETAIL='VICTIMES' where TB_LIBELLE in ("personnes rencontr�es");
update type_bilan set VICTIME_DETAIL='INTERVENTIONS' where TB_LIBELLE in ("interventions");

# ------------------------------------;
# change version
# ------------------------------------;
update configuration set VALUE='2.8' where ID=1;

# ------------------------------------;
# end
# ------------------------------------;
