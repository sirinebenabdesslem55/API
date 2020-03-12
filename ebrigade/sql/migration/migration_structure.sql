#-------------------------------------------------------
# IMPORTER DES DONNEES DANS EBRIGADE
# DEPUIS UNE AUTRE APPLICATION
# MIGRATION ACCESS MYSQL
# ET AJOUT INFOS RIB
#--------------------------------------------------------


# ------------------------------------
# structure for table 'adherent'
# ------------------------------------
# exporter vers MySQL en utilisant la fonction export via ODBC de Access

ALTER TABLE adhérent CHANGE  `Numero adhérent`  `Numeroadhérent` INT( 11 ) NULL;
alter table adhérent add section int(11) null;

alter table adhérent add AdresseComplete varchar(300) null;
update adhérent set AdresseComplete = Adresseadhérent
where Adresseadhérent is not null;

update adhérent set AdresseComplete = concat(AdresseComplete,'
',Adresse2adhérent)
where Adresse2adhérent is not null;

update adhérent set AdresseComplete = concat(AdresseComplete,'
',Adresse3adhérent)
where Adresse3adhérent is not null;

ALTER TABLE pompier DROP INDEX P_HOMONYM;
UPDATE configuration SET  VALUE = '0' WHERE ID in(13,14,23,24);

alter table pompier add CENTRE varchar(60) null;

update pompier set P_CODE='admin' where P_ID=1;

update adhérent set Numeroadhérent=6383 where Numeroadhérent=1;

insert into pompier (
P_ID,
P_CODE,
P_PRENOM,
P_NOM,
P_SEXE,
P_CIVILITE,
P_OLD_MEMBER,
P_GRADE,
P_PROFESSION,
P_STATUT,
P_MDP,
P_DATE_ENGAGEMENT,
P_FIN,
P_SECTION,
P_EMAIL,
P_PHONE,
P_PHONE2,
P_ADDRESS,
P_ZIP_CODE,
P_CITY,
P_CREATE_DATE,
SERVICE,
TP_ID,
OBSERVATION,
MOTIF_RADIATION,
NPAI,
DATE_NPAI,
SUSPENDU,
DATE_SUSPENDU,
CENTRE
)
select 
Numeroadhérent,
Numeroadhérent,
substring(lower(Prénomadhérent)  FROM 1 FOR 20),
lower(Nomadhérent),
case
  when Titreadhérent='Monsieur' then 'M'
  else 'F'
end,
case 
  when Titreadhérent='Monsieur' then '1'
  when Titreadhérent='Madame' then '2'
  else '3'
end,
0,
'-',
case 
  when Typeprof='SPP' then 'SPP'
  when Typeprof='PATS' then 'PATS'
  when Typeprof='SPV' then 'SPP'
  else '-'
end,
'ADH',
MD5('password'),
Dateadhésion,
Dateradiation,
0,
SUBSTRING_INDEX(Emailadhérent, '#', 1),
REPLACE(Telportable, ' ', ''),
REPLACE(Teladhérent, ' ', ''),
AdresseComplete,
Codepostaladhérent,
substring(Villeadhérent FROM 1 FOR 30),
Dateadhésion,
Service,
case 
  when Moderéglement='Prélèvement' then '1'
  when Moderéglement='Virement' then '2'
  when Moderéglement='Carte bancaire' then '3'
  when Moderéglement='Chèque' then '4'
  when Moderéglement='Espèces' then '5'
  else '0'
end,
SUBSTRING(concat(Observations1,' ',Observations2,' ', Observations3) FROM 1 FOR 255),
`Motif Radiation`,
NPAI,
DATE(`Date NPAI`),
Suspendu,
DATE(`Datesuspendu`),
Centreadhérent
from adhérent
;

# remplir table prelevement avec phpmyadmin
# import de CSV
# commencer par coller le texte dans Excel, choisir champs fixes
# puis extraire au format CSV

drop table if exists prelevement;
create table prelevement (
compte varchar(5) null,
emetteur int null,
nom varchar(40) null,
banque varchar(30) null,
monnaie varchar(1) null,
txt_prelev varchar(100) null,
info varchar(100) null,
etablissement varchar(6) null);

# après remplissage

update prelevement set nom=lower(nom);
alter table prelevement add guichet char(5) null;
alter table prelevement add numerocompte char(11) null;

update prelevement set guichet = SUBSTRING(txt_prelev FROM 1 FOR 5);
update prelevement set numerocompte = SUBSTRING(txt_prelev FROM 6 FOR 11);

drop table if exists compte_unknown;
create table compte_unknown (
NOM varchar(50) not null,
NUM tinyint(3) not null,
ETABLISSEMENT varchar(5) not null,
GUICHET varchar(5) not null,
COMPTE varchar(11) not null,
CODE_BANQUE varchar(30) null,
INDEX (NOM));

#select CENTRE,S_ID, count(*)
#FROM pompier, section
#WHERE P_SECTION = S_ID
#AND CENTRE IS NOT NULL 
#GROUP BY CENTRE, S_CODE
#having count(*) > 1;
# 297 lignes avant nettoyage

# -----------------------------------------
# import sections / organigramme
# -----------------------------------------

drop table if exists section_import;
create table section_import (
id int(11) auto_increment not null,
code varchar(15) not null,
description varchar(40) not null,
parent int(11) not null,
PRIMARY KEY (id));

insert into section_import(code,description,parent) 
values 
("FA / SPP PATS","FA / Sapeurs pompiers - PATS",0),
("ALSACE", "Région Alsace",1),
("AQUITAINE", "Région Aquitaine",1),
("AUVERGNE", "Région Auvergne",1),
("BOURGOGNE", "Région Bourgogne",1),
("BRETAGNE", "Région Bretagne",1),
("CENTRE", "Région Centre",1),
("CHAMPAGNE", "Région Champagne-Ardennes",1),
("CORSE", "Région Corse",1),
("FRANCHE C", "Région Franche-Comté",1),
("IDF", "Région Ile de France",1),
("LANGUEDOC ROUSS", "Région Languedoc-Roussillon",1),
("LIMOUSIN", "Région Limousin",1),
("LORRAINE", "Région Lorraine",1),
("MIDI PYR", "Région Midi-Pyrénées",1),
("NORD PDC", "Région Nord-Pas-de-Calais",1),
("BASSE NORMANDIE", "Région Basse Normandie",1),
("HAUTE NORMANDIE", "Région Haute-Normandie",1),
("PAYS DE LOIRE", "Région Pays-de-Loire",1),
("PICARDIE", "Région Picardie",1),
("POITOU CHARENT", "Région Poitou-Charentes",1),
("PACA", "Région Provence-Alpes-Côte d'Azur",1),
("RHONE ALPES", "Région Rhône-Alpes",1),
("OUTRE MER", "Région Outre Mer",1),
("01","Ain",23),
("02","Aisne",20),
("03","Allier",4),
("04","Alpes de Haute Provence",22),
("05","Hautes-Alpes",22),
("06","Alpes Maritimes",22),
("07","Ardèche",23),
("08","Ardennes",8),
("09","Ariège",15),
("10","Aube",8),
("11","Aude",12),
("12","Aveyron",12),
("13","Bouches du Rhône",22),
("14","Calvados",17),
("15","Cantal",4),
("16","Charente",21),
("17","Charente - Maritime",21),
("18","Cher",7),
("19","Corréze",13),
("21","Côte d'Or",5),
("22","Côtes d Armor",6),
("23","Creuse",13),
("24","Dordogne",3),
("25","Doubs",10),
("26","Drôme",23),
("27","Eure",18),
("28","Eure - et - Loir",7),
("29","Finistère",6),
("30","Gard",12),
("31","Haute-Garonne",15),
("32","Gers",15),
("33","Gironde",3),
("34","Hérault",12),
("35","Ille - et - Vilaine",6),
("36","Indre",7),
("37","Indre-et-Loire",7),
("38","Isère",23),
("39","Jura",10),
("40","Landes",3),
("41","Loir - et - Cher",7),
("42","Loire",23),
("43","Haute - Loire",4),
("44","Loire Atlantique",19),
("45","Loiret",7),
("46","Lot",15),
("47","Lot et Garonne",3),
("48","Lozère",12),
("49","Maine et Loire",19),
("50","Manche",17),
("51","Marne",8),
("52","Haute - Marne",8),
("53","Mayenne",19),
("54","Meurthe et Moselle",14),
("55","Meuse",14),
("56","Morbihan",6),
("57","Moselle",14),
("58","Nièvre",5),
("59","Nord",16),
("60","Oise",20),
("61","Orne",17),
("62","Pas - de - calais",16),
("63","Puy de dôme",4),
("64","Pyrénées - Atlantiques",3),
("65","Hautes Pyrénées",15),
("66","Pyrénées Orientales",12),
("67","Bas - Rhin",2),
("68","Haut - Rhin",2),
("69","Rhône",23),
("70","Haute - Saône",11),
("71","Saône et Loire",5),
("72","Sarthe",19),
("73","Savoie",23),
("74","Haute Savoie",23),
("75","Paris",11),
("76","Seine Maritime",18),
("77","Seine et Marne",11),
("78","Yvelines",11),
("79","Deux - Sèvres",21),
("80","Somme",20),
("81","Tarn",15),
("82","Tarn et Garonne",15),
("83","Var",22),
("84","Vaucluse",22),
("85","Vendée",19),
("86","Vienne",21),
("87","Haute - Vienne",13),
("88","Vosges",14),
("89","Yonne",10),
("90","Territoire de Belfort",10),
("91","Essonne",11),
("92","Hauts de Seine",11),
("93","Seine Saint Denis",11),
("94","Val de Marne",11),
("95","Val d'Oise",11),
("971","Guadeloupe",24),
("972","Martinique",24),
("973","Guyane",24),
("974","Réunion",24),
("975","Saint Pierre et Miquelon",24),
("976","Mayotte",24),
("986","Wallis et Futuna",24),
("987","Polynésie - Française",24),
("988","Nouvelle - Calédonie",24),
("2A","Corse du sud",9),
("2B","Haute Corse",9);

delete from section where S_ID > 0;
insert into section(S_ID, S_CODE, S_DESCRIPTION, S_PARENT)
select id, code, description, parent from section_import;

drop table section_import;

update adhérent set section=(select S_ID from section where S_CODE=RTRIM(SUBSTRING(Numdépartement FROM 1 FOR 3)));

update pompier 
set P_SECTION=(select section
from adhérent 
where section is not null 
and Numeroadhérent=pompier.P_ID);

update pompier set P_SECTION=1 where P_SECTION is null;

update pompier set CENTRE=REPLACE(REPLACE(CENTRE,'é','e'),'è','e');
update pompier set CENTRE=upper(CENTRE);
update pompier set CENTRE=SUBSTRING(CENTRE FROM 5 FOR 50) where CENTRE like 'CIS %';
update pompier set CENTRE='AIRE SUR LA LYS' where CENTRE='AIRE LA LYS';
update pompier set CENTRE='CHALON SUR SAONE' where CENTRE='CHALON SUR SAÔNE';

update pompier set CENTRE='DUME D''APLEMONT' where CENTRE in ('DUME','DUME D''APPLEMONT') and P_SECTION=99;
update pompier set CENTRE='FINKWILLER' where CENTRE='FINWILLER';
update pompier set CENTRE='LA ROCHELLE-MIREUIL' where CENTRE like 'LA ROCHEL%' and P_SECTION=41;
update pompier set CENTRE='LOUVIERS - VAL DE REUIL' where CENTRE like 'LOUVIE%' and P_SECTION=50;
update pompier set CENTRE='MONTCEAU-LES-MINES' where CENTRE like 'MONTCEA%' and P_SECTION=94;
update pompier set CENTRE='OLORON STE MARIE' where CENTRE like 'OLORO%' and P_SECTION=87;
update pompier set CENTRE='VILLENEUVE LES AVIGNON' where CENTRE like 'VILLENEUVE%' and P_SECTION=53;
update pompier set CENTRE='DDSIS' where CENTRE like 'DDSIS%' and P_SECTION=106;
update pompier set CENTRE='CTA CODIS' where CENTRE like 'CTA%' and P_SECTION=83;
update pompier set CENTRE='TUNNEL' where CENTRE like 'TUNNEL%' and P_SECTION=85;
update pompier set CENTRE='STRASBOURG' where CENTRE like 'STRASBOURG%' and P_SECTION=90;
update pompier set CENTRE='LA SEYNE' where CENTRE like 'LA SEYNE%' and P_SECTION=106;
update pompier set CENTRE=concat('ST',' ',SUBSTRING(CENTRE FROM 7 FOR 50)) where CENTRE like 'SAINT %';
update pompier set CENTRE=concat('STE',' ',SUBSTRING(CENTRE FROM 8 FOR 50)) where CENTRE like 'SAINTE %';
update pompier set CENTRE='LIMOGES MARTIAL MITOUT' where CENTRE='LIMOGES - MARTIAL MITOUT';
update pompier set CENTRE='LIMOGES MAUVENDIERE' where CENTRE='LIMOGES - MAUVENDIERE';
update pompier set CENTRE='MONTPELLIER MARX DORMOY' where CENTRE='MARX DORMOY';
update pompier set CENTRE='LIMOGES MARTIAL MITOUT' where CENTRE='MARTIAL MITOUT';
update pompier set CENTRE='MONTPELLIER JEAN GUIZONNIER' where CENTRE='JEAN GUIZONNIER';
update pompier set CENTRE='CAEN COUVRECHEF' where CENTRE='CSP COUVRECHEF';
update pompier set CENTRE='AURILLAC' where CENTRE='CSP AURILLAC';
update pompier set CENTRE='RENNES LE BLOSNE' where CENTRE='LE BLOSNE-RENNES';
update pompier set CENTRE='CTA / CODIS' where CENTRE='CTA / CODIS 50';
update pompier set CENTRE='ST RAPHAËL' where CENTRE='ST RAPHAEL';
update pompier set CENTRE='LIMOGES MAUVENDIERE' where CENTRE='MAUVENDIERE';
update pompier set CENTRE='CTA ARRONDISSEMENT DE NICE' where CENTRE='CTA ARRONDISSEMENT NICE';


drop table if exists antenne_import;
create table antenne_import (
id int(11) auto_increment not null,
code varchar(15) not null,
description varchar(50) not null,
parent int(11) not null,
nb int(11) not null,
newid int(11) null,
PRIMARY KEY (id));

insert into antenne_import(code,description,parent,nb) 
select distinct substring(concat(S_CODE,' ',CENTRE) from 1 for 15), CENTRE ,S_ID, count(*)
FROM pompier, section
WHERE P_SECTION = S_ID
AND CENTRE IS NOT NULL and CENTRE <> ""
group by substring(concat(S_CODE,' ',CENTRE) from 1 for 15), CENTRE ,S_ID
having count(*) > 1;

update antenne_import set newid=(select max(S_ID) from section) + id;
update antenne_import set code='34 MONTPEL MD' where description='MONTPELLIER MARX DORMOY';
update antenne_import set code='34 MONTPEL JG' where description='MONTPELLIER JEAN GUIZONNIER';
update antenne_import set code='06 GF6' where description='ATELIER DEPARTEMENTAL - GF6';
update antenne_import set code='06 PATRIMOINE', description='ATELIER - PATRIMOINE IMMOBILIER' where description='ATELIER DEPARTEMENTAL - PATRIMOINE IMMOBILIER';
update antenne_import set code='06 SSSM' where description='ATELIER DEPARTEMENTAL - SSSM';

ALTER TABLE antenne_import ADD UNIQUE (code);

delete from section where S_ID >= 130;
insert into section (S_ID,S_CODE,S_DESCRIPTION,S_PARENT)
select newid,code,description,parent
from antenne_import;

OPTIMIZE TABLE section;
OPTIMIZE TABLE pompier;
OPTIMIZE TABLE section_flat;

alter table pompier add P_ANTENNE int(11) null;
update pompier 
set P_ANTENNE=(select section.S_ID
from section
where rtrim(section.S_DESCRIPTION) = rtrim(pompier.CENTRE)
and section.S_PARENT = pompier.P_SECTION);

update pompier set P_SECTION=P_ANTENNE where P_ANTENNE is not null;
alter table pompier drop P_ANTENNE;
alter table pompier drop CENTRE;
drop table antenne_import;

# select P_SECTION, count(*) from pompier group by P_SECTION 

# -----------------------------------------
# import rejets
# -----------------------------------------

alter table rejet add Défautbancaire varchar(200) null;
truncate table rejet;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2013,'JAN',0,Montantrejets,Daterégul,Montantrégul,Défautbancaire
from adhérent where `janvier` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2013,'FEV',0,Montantrejets02,Daterégul02,Montantrégul02,Défautbancaire02
from adhérent where `fevrier` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2013,'MAR',0,Montantrejets03,Daterégul03,Montantrégul03,Défautbancaire03
from adhérent where `mars` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'APR',0,Montantrejets04,Daterégul04,Montantrégul04,Défautbancaire04
from adhérent where `avril` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'MAI',0,Montantrejets05,Daterégul05,Montantrégul05,Défautbancaire05
from adhérent where `mai` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'JUN',0,Montantrejets06,Daterégul06,Montantrégul06,Défautbancaire06
from adhérent where `juin` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'JUL',0,Montantrejets07,Daterégul07,Montantrégul07,Défautbancaire07
from adhérent where `juillet` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'AUG',0,Montantrejets08,Daterégul08,Montantrégul08,Défautbancaire08
from adhérent where `aout` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'SEP',0,Montantrejets09,Daterégul09,Montantrégul09,Défautbancaire09
from adhérent where `septembre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'OCT',0,Montantrejets10,Daterégul10,Montantrégul10,Défautbancaire10
from adhérent where `octobre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'NOV',0,Montantrejets11,Daterégul11,Montantrégul11,Défautbancaire11
from adhérent where `novembre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,Défautbancaire)
select Numeroadhérent,2012,'DEC',0,Montantrejets12,Daterégul12,Montantrégul12,Défautbancaire12
from adhérent where `décembre` = 1;

update rejet set DEFAUT_ID=1 where lower(Défautbancaire) like 'compte%'; 
update rejet set DEFAUT_ID=2 where lower(Défautbancaire) like 'provision%'; 
update rejet set DEFAUT_ID=3 where lower(Défautbancaire) like 'opposition%'; 
update rejet set DEFAUT_ID=4 where lower(Défautbancaire) like 'pas%'; 
update rejet set DEFAUT_ID=5 where lower(Défautbancaire) like 'opération%'; 
update rejet set DEFAUT_ID=6 where lower(Défautbancaire) like 'demande%'; 
update rejet set DEFAUT_ID=7 where lower(Défautbancaire) like 'tirage%'; 
update rejet set DEFAUT_ID=8 where lower(Défautbancaire) like 'coor%'; 
update rejet set DEFAUT_ID=8 where lower(Défautbancaire) like 'régul%'; 

update rejet set REGULARISE=1 where DATE_REGUL is not null and MONTANT_REGUL > 0;
OPTIMIZE TABLE rejet;
alter table rejet drop Défautbancaire;

# -----------------------------------------
# supprimer quelques data inutiles
# -----------------------------------------
delete from type_vehicule where TV_USAGE in ('FEU','SECOURS','LOGISTIQUE') or TV_CODE in ('GER','PCM','QUAD','REM','VCYN','VLC','VPI','VTD');
delete from type_evenement where CEV_CODE in ('C_OPE','C_SEC') or TE_CODE='EXE';

delete from type_document;
INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('REVP', 'Revue de Presse'), ('COMM', 'Communication'), ('CRBE', 'Comptes Rendus BE'), ('CRSS', 'Compte Rendus Réunions Statutaires');

# -----------------------------------------
# modifications ultérieures
# -----------------------------------------

update pompier set P_OLD_MEMBER=10
where P_OLD_MEMBER> 0;

update pompier set P_OLD_MEMBER=1
where MOTIF_RADIATION = 'A sa demande';

update pompier set P_OLD_MEMBER=2
where MOTIF_RADIATION in ( 'Départ à la retraite','RETRAITE');

update pompier set P_OLD_MEMBER=3
where MOTIF_RADIATION = 'Impayés';

update pompier set P_OLD_MEMBER=4
where MOTIF_RADIATION = 'Démission';

update pompier set P_OLD_MEMBER=5
where MOTIF_RADIATION in ( 'Décédé','Décès','DECEDE');

update pompier set P_OLD_MEMBER=8
where MOTIF_RADIATION = 'Disponibilité';

update pompier set  GP_ID=-1, GP_ID2=-1 where P_OLD_MEMBER > 0;
update pompier set  GP_ID=-1, GP_ID2=-1 where P_OLD_MEMBER > 0;

ALTER TABLE compte_unknown ORDER BY NOM;
ALTER TABLE compte_unknown ADD ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('PPH','Préparateur Pharmacie',14,'service de santé','SP');
insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('INF','Infirmier',15,'service de santé','SP');
insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('MED','Médecin',16,'service de santé','SP');

update pompier set P_GRADE='CPL' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'caporal');
update pompier set P_GRADE='CCH' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) in ('caporal chef','caporal-chef'));
update pompier set P_GRADE='SAP' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'sapeur');
update pompier set P_GRADE='LTN' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'lieutenant');
update pompier set P_GRADE='ADJ' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'adjudant');
update pompier set P_GRADE='ADC' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) in ('adjudant chef','adjudant-chef'));
update pompier set P_GRADE='SGT' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'sergent');
update pompier set P_GRADE='SCH' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) in ('sergent chef','sergent-chef'));
update pompier set P_GRADE='CPT' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'capitaine');
update pompier set P_GRADE='MAJ' where P_ID in (select Numeroadhérent from adhérent where trim(lower(Grade)) = 'major');
update pompier set P_GRADE='AA2' where P_ID in (select Numeroadhérent from adhérent where Grade in ('Adjoint Administratif 2ème Classe','Adjoint Administratif'));
update pompier set P_GRADE='AA2NT' where P_ID in (select Numeroadhérent from adhérent where Grade='Adjoint Administratif 2ème Classe Non Titulaire');
update pompier set P_GRADE='AA1' where P_ID in (select Numeroadhérent from adhérent where Grade='Adjoint Administratif 1ère Classe');
update pompier set P_GRADE='AT2' where P_ID in (select Numeroadhérent from adhérent where Grade in ('Adjoint Technique 2ème Classe','Adjoint Technique  2ème Classe'));
update pompier set P_GRADE='AT2NT' where P_ID in (select Numeroadhérent from adhérent where Grade='Adjoint Technique 2ème Classe Non Titulaire');
update pompier set P_GRADE='AT1' where P_ID in (select Numeroadhérent from adhérent where Grade in ('Adjoint Technique 1ère Classe','Adjoint Technique 1ère classe'));
update pompier set P_GRADE='AM' where P_ID in (select Numeroadhérent from adhérent wherewhere(lower(Grade)) like 'agent de m%');

update pompier set P_GRADE='INF' where P_ID in (select Numeroadhérent from adhérent where(lower(Grade)) like 'infirmier%');
update pompier set P_GRADE='MED' where P_ID in (select Numeroadhérent from adhérent where(lower(Grade)) like 'médecin%');
update pompier set P_GRADE='PPH' where P_ID in (select Numeroadhérent from adhérent where(lower(Grade)) like 'pharmacie%');


update adhérent set Observations1='' where Observations1 is null;
update adhérent set Observations2='' where Observations2 is null;
update adhérent set Observations3='' where Observations3 is null;

alter table adhérent add OBS varchar(255) null;
update adhérent set OBS = SUBSTRING(concat(Observations1,' ',Observations2,' ', Observations3) FROM 1 FOR 255);

update pompier 
set OBSERVATION = ( select OBS from adhérent where pompier.P_ID = adhérent.Numeroadhérent);

update horaires set P_ID=6384 where P_ID=5788;
update horaires set P_ID=6385 where P_ID=5786;
update horaires set P_ID=6386 where P_ID=5787;
update horaires set P_ID=5801 where P_ID=5775;

update horaires_validation set P_ID=6384 where P_ID=5788;
update horaires_validation set P_ID=6385 where P_ID=5786;
update horaires_validation set P_ID=6386 where P_ID=5787;
update horaires_validation set P_ID=5801 where P_ID=5775;

update horaires_validation set CREATED_BY=P_ID;

update pompier set P_MDP=md5(P_ID);
update pompier set P_MDP='07763b42048f6adca263daa71cc5f313' where P_ID=381;
update pompier set P_MDP='588151faf92a44a03dd8ebe0f275aa57' where P_ID=999;
update pompier set P_MDP='4eed39533f8fbfac549c6f57c054a1ec' where P_ID=4178;
update pompier set P_MDP='22a14f8dfd2133ce4dce9da53125a146' where P_ID=5801;
update pompier set P_MDP='4a6eb51b0a941fe9b85a2c06396e9440' where P_ID=6385;
update pompier set P_MDP='feba9698da706f1ae22dcf3c6de8b940' where P_ID=6386;
update pompier set P_MDP='f6b679eeaab59270a47dc2f8c691c8f9' where P_ID=6384;

update indisponibilite set P_ID=6384 where P_ID=5788;
update indisponibilite set P_ID=6385 where P_ID=5786;
update indisponibilite set P_ID=6386 where P_ID=5787;
update indisponibilite set P_ID=5801 where P_ID=5775;

update pompier set P_OLD_MEMBER=10 where P_OLD_MEMBER=0
and P_ID in (select Numeroadhérent from adhérent where Radiation = 1 );

update pompier set SUSPENDU=1 where SUSPENDU=0
and P_ID in (select Numeroadhérent from adhérent where Suspendu = 1 );

INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('GADH', 'Guide de l''adhérent');
INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('GELU', 'Guide de l''élu');