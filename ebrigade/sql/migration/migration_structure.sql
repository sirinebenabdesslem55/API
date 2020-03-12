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

ALTER TABLE adh�rent CHANGE  `Numero adh�rent`  `Numeroadh�rent` INT( 11 ) NULL;
alter table adh�rent add section int(11) null;

alter table adh�rent add AdresseComplete varchar(300) null;
update adh�rent set AdresseComplete = Adresseadh�rent
where Adresseadh�rent is not null;

update adh�rent set AdresseComplete = concat(AdresseComplete,'
',Adresse2adh�rent)
where Adresse2adh�rent is not null;

update adh�rent set AdresseComplete = concat(AdresseComplete,'
',Adresse3adh�rent)
where Adresse3adh�rent is not null;

ALTER TABLE pompier DROP INDEX P_HOMONYM;
UPDATE configuration SET  VALUE = '0' WHERE ID in(13,14,23,24);

alter table pompier add CENTRE varchar(60) null;

update pompier set P_CODE='admin' where P_ID=1;

update adh�rent set Numeroadh�rent=6383 where Numeroadh�rent=1;

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
Numeroadh�rent,
Numeroadh�rent,
substring(lower(Pr�nomadh�rent)  FROM 1 FOR 20),
lower(Nomadh�rent),
case
  when Titreadh�rent='Monsieur' then 'M'
  else 'F'
end,
case 
  when Titreadh�rent='Monsieur' then '1'
  when Titreadh�rent='Madame' then '2'
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
Dateadh�sion,
Dateradiation,
0,
SUBSTRING_INDEX(Emailadh�rent, '#', 1),
REPLACE(Telportable, ' ', ''),
REPLACE(Teladh�rent, ' ', ''),
AdresseComplete,
Codepostaladh�rent,
substring(Villeadh�rent FROM 1 FOR 30),
Dateadh�sion,
Service,
case 
  when Moder�glement='Pr�l�vement' then '1'
  when Moder�glement='Virement' then '2'
  when Moder�glement='Carte bancaire' then '3'
  when Moder�glement='Ch�que' then '4'
  when Moder�glement='Esp�ces' then '5'
  else '0'
end,
SUBSTRING(concat(Observations1,' ',Observations2,' ', Observations3) FROM 1 FOR 255),
`Motif Radiation`,
NPAI,
DATE(`Date NPAI`),
Suspendu,
DATE(`Datesuspendu`),
Centreadh�rent
from adh�rent
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

# apr�s remplissage

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
("ALSACE", "R�gion Alsace",1),
("AQUITAINE", "R�gion Aquitaine",1),
("AUVERGNE", "R�gion Auvergne",1),
("BOURGOGNE", "R�gion Bourgogne",1),
("BRETAGNE", "R�gion Bretagne",1),
("CENTRE", "R�gion Centre",1),
("CHAMPAGNE", "R�gion Champagne-Ardennes",1),
("CORSE", "R�gion Corse",1),
("FRANCHE C", "R�gion Franche-Comt�",1),
("IDF", "R�gion Ile de France",1),
("LANGUEDOC ROUSS", "R�gion Languedoc-Roussillon",1),
("LIMOUSIN", "R�gion Limousin",1),
("LORRAINE", "R�gion Lorraine",1),
("MIDI PYR", "R�gion Midi-Pyr�n�es",1),
("NORD PDC", "R�gion Nord-Pas-de-Calais",1),
("BASSE NORMANDIE", "R�gion Basse Normandie",1),
("HAUTE NORMANDIE", "R�gion Haute-Normandie",1),
("PAYS DE LOIRE", "R�gion Pays-de-Loire",1),
("PICARDIE", "R�gion Picardie",1),
("POITOU CHARENT", "R�gion Poitou-Charentes",1),
("PACA", "R�gion Provence-Alpes-C�te d'Azur",1),
("RHONE ALPES", "R�gion Rh�ne-Alpes",1),
("OUTRE MER", "R�gion Outre Mer",1),
("01","Ain",23),
("02","Aisne",20),
("03","Allier",4),
("04","Alpes de Haute Provence",22),
("05","Hautes-Alpes",22),
("06","Alpes Maritimes",22),
("07","Ard�che",23),
("08","Ardennes",8),
("09","Ari�ge",15),
("10","Aube",8),
("11","Aude",12),
("12","Aveyron",12),
("13","Bouches du Rh�ne",22),
("14","Calvados",17),
("15","Cantal",4),
("16","Charente",21),
("17","Charente - Maritime",21),
("18","Cher",7),
("19","Corr�ze",13),
("21","C�te d'Or",5),
("22","C�tes d Armor",6),
("23","Creuse",13),
("24","Dordogne",3),
("25","Doubs",10),
("26","Dr�me",23),
("27","Eure",18),
("28","Eure - et - Loir",7),
("29","Finist�re",6),
("30","Gard",12),
("31","Haute-Garonne",15),
("32","Gers",15),
("33","Gironde",3),
("34","H�rault",12),
("35","Ille - et - Vilaine",6),
("36","Indre",7),
("37","Indre-et-Loire",7),
("38","Is�re",23),
("39","Jura",10),
("40","Landes",3),
("41","Loir - et - Cher",7),
("42","Loire",23),
("43","Haute - Loire",4),
("44","Loire Atlantique",19),
("45","Loiret",7),
("46","Lot",15),
("47","Lot et Garonne",3),
("48","Loz�re",12),
("49","Maine et Loire",19),
("50","Manche",17),
("51","Marne",8),
("52","Haute - Marne",8),
("53","Mayenne",19),
("54","Meurthe et Moselle",14),
("55","Meuse",14),
("56","Morbihan",6),
("57","Moselle",14),
("58","Ni�vre",5),
("59","Nord",16),
("60","Oise",20),
("61","Orne",17),
("62","Pas - de - calais",16),
("63","Puy de d�me",4),
("64","Pyr�n�es - Atlantiques",3),
("65","Hautes Pyr�n�es",15),
("66","Pyr�n�es Orientales",12),
("67","Bas - Rhin",2),
("68","Haut - Rhin",2),
("69","Rh�ne",23),
("70","Haute - Sa�ne",11),
("71","Sa�ne et Loire",5),
("72","Sarthe",19),
("73","Savoie",23),
("74","Haute Savoie",23),
("75","Paris",11),
("76","Seine Maritime",18),
("77","Seine et Marne",11),
("78","Yvelines",11),
("79","Deux - S�vres",21),
("80","Somme",20),
("81","Tarn",15),
("82","Tarn et Garonne",15),
("83","Var",22),
("84","Vaucluse",22),
("85","Vend�e",19),
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
("974","R�union",24),
("975","Saint Pierre et Miquelon",24),
("976","Mayotte",24),
("986","Wallis et Futuna",24),
("987","Polyn�sie - Fran�aise",24),
("988","Nouvelle - Cal�donie",24),
("2A","Corse du sud",9),
("2B","Haute Corse",9);

delete from section where S_ID > 0;
insert into section(S_ID, S_CODE, S_DESCRIPTION, S_PARENT)
select id, code, description, parent from section_import;

drop table section_import;

update adh�rent set section=(select S_ID from section where S_CODE=RTRIM(SUBSTRING(Numd�partement FROM 1 FOR 3)));

update pompier 
set P_SECTION=(select section
from adh�rent 
where section is not null 
and Numeroadh�rent=pompier.P_ID);

update pompier set P_SECTION=1 where P_SECTION is null;

update pompier set CENTRE=REPLACE(REPLACE(CENTRE,'�','e'),'�','e');
update pompier set CENTRE=upper(CENTRE);
update pompier set CENTRE=SUBSTRING(CENTRE FROM 5 FOR 50) where CENTRE like 'CIS %';
update pompier set CENTRE='AIRE SUR LA LYS' where CENTRE='AIRE LA LYS';
update pompier set CENTRE='CHALON SUR SAONE' where CENTRE='CHALON SUR SA�NE';

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
update pompier set CENTRE='ST RAPHA�L' where CENTRE='ST RAPHAEL';
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

alter table rejet add D�fautbancaire varchar(200) null;
truncate table rejet;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2013,'JAN',0,Montantrejets,Dater�gul,Montantr�gul,D�fautbancaire
from adh�rent where `janvier` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2013,'FEV',0,Montantrejets02,Dater�gul02,Montantr�gul02,D�fautbancaire02
from adh�rent where `fevrier` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2013,'MAR',0,Montantrejets03,Dater�gul03,Montantr�gul03,D�fautbancaire03
from adh�rent where `mars` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'APR',0,Montantrejets04,Dater�gul04,Montantr�gul04,D�fautbancaire04
from adh�rent where `avril` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'MAI',0,Montantrejets05,Dater�gul05,Montantr�gul05,D�fautbancaire05
from adh�rent where `mai` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'JUN',0,Montantrejets06,Dater�gul06,Montantr�gul06,D�fautbancaire06
from adh�rent where `juin` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'JUL',0,Montantrejets07,Dater�gul07,Montantr�gul07,D�fautbancaire07
from adh�rent where `juillet` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'AUG',0,Montantrejets08,Dater�gul08,Montantr�gul08,D�fautbancaire08
from adh�rent where `aout` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'SEP',0,Montantrejets09,Dater�gul09,Montantr�gul09,D�fautbancaire09
from adh�rent where `septembre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'OCT',0,Montantrejets10,Dater�gul10,Montantr�gul10,D�fautbancaire10
from adh�rent where `octobre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'NOV',0,Montantrejets11,Dater�gul11,Montantr�gul11,D�fautbancaire11
from adh�rent where `novembre` = 1;

insert into rejet (P_ID,ANNEE,PERIODE_CODE,DEFAUT_ID,MONTANT_REJET,DATE_REGUL,MONTANT_REGUL,D�fautbancaire)
select Numeroadh�rent,2012,'DEC',0,Montantrejets12,Dater�gul12,Montantr�gul12,D�fautbancaire12
from adh�rent where `d�cembre` = 1;

update rejet set DEFAUT_ID=1 where lower(D�fautbancaire) like 'compte%'; 
update rejet set DEFAUT_ID=2 where lower(D�fautbancaire) like 'provision%'; 
update rejet set DEFAUT_ID=3 where lower(D�fautbancaire) like 'opposition%'; 
update rejet set DEFAUT_ID=4 where lower(D�fautbancaire) like 'pas%'; 
update rejet set DEFAUT_ID=5 where lower(D�fautbancaire) like 'op�ration%'; 
update rejet set DEFAUT_ID=6 where lower(D�fautbancaire) like 'demande%'; 
update rejet set DEFAUT_ID=7 where lower(D�fautbancaire) like 'tirage%'; 
update rejet set DEFAUT_ID=8 where lower(D�fautbancaire) like 'coor%'; 
update rejet set DEFAUT_ID=8 where lower(D�fautbancaire) like 'r�gul%'; 

update rejet set REGULARISE=1 where DATE_REGUL is not null and MONTANT_REGUL > 0;
OPTIMIZE TABLE rejet;
alter table rejet drop D�fautbancaire;

# -----------------------------------------
# supprimer quelques data inutiles
# -----------------------------------------
delete from type_vehicule where TV_USAGE in ('FEU','SECOURS','LOGISTIQUE') or TV_CODE in ('GER','PCM','QUAD','REM','VCYN','VLC','VPI','VTD');
delete from type_evenement where CEV_CODE in ('C_OPE','C_SEC') or TE_CODE='EXE';

delete from type_document;
INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('REVP', 'Revue de Presse'), ('COMM', 'Communication'), ('CRBE', 'Comptes Rendus BE'), ('CRSS', 'Compte Rendus R�unions Statutaires');

# -----------------------------------------
# modifications ult�rieures
# -----------------------------------------

update pompier set P_OLD_MEMBER=10
where P_OLD_MEMBER> 0;

update pompier set P_OLD_MEMBER=1
where MOTIF_RADIATION = 'A sa demande';

update pompier set P_OLD_MEMBER=2
where MOTIF_RADIATION in ( 'D�part � la retraite','RETRAITE');

update pompier set P_OLD_MEMBER=3
where MOTIF_RADIATION = 'Impay�s';

update pompier set P_OLD_MEMBER=4
where MOTIF_RADIATION = 'D�mission';

update pompier set P_OLD_MEMBER=5
where MOTIF_RADIATION in ( 'D�c�d�','D�c�s','DECEDE');

update pompier set P_OLD_MEMBER=8
where MOTIF_RADIATION = 'Disponibilit�';

update pompier set  GP_ID=-1, GP_ID2=-1 where P_OLD_MEMBER > 0;
update pompier set  GP_ID=-1, GP_ID2=-1 where P_OLD_MEMBER > 0;

ALTER TABLE compte_unknown ORDER BY NOM;
ALTER TABLE compte_unknown ADD ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;

insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('PPH','Pr�parateur Pharmacie',14,'service de sant�','SP');
insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('INF','Infirmier',15,'service de sant�','SP');
insert GRADE (G_GRADE, G_DESCRIPTION,G_LEVEL,G_TYPE,G_CATEGORY)
values ('MED','M�decin',16,'service de sant�','SP');

update pompier set P_GRADE='CPL' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'caporal');
update pompier set P_GRADE='CCH' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) in ('caporal chef','caporal-chef'));
update pompier set P_GRADE='SAP' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'sapeur');
update pompier set P_GRADE='LTN' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'lieutenant');
update pompier set P_GRADE='ADJ' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'adjudant');
update pompier set P_GRADE='ADC' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) in ('adjudant chef','adjudant-chef'));
update pompier set P_GRADE='SGT' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'sergent');
update pompier set P_GRADE='SCH' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) in ('sergent chef','sergent-chef'));
update pompier set P_GRADE='CPT' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'capitaine');
update pompier set P_GRADE='MAJ' where P_ID in (select Numeroadh�rent from adh�rent where trim(lower(Grade)) = 'major');
update pompier set P_GRADE='AA2' where P_ID in (select Numeroadh�rent from adh�rent where Grade in ('Adjoint Administratif 2�me Classe','Adjoint Administratif'));
update pompier set P_GRADE='AA2NT' where P_ID in (select Numeroadh�rent from adh�rent where Grade='Adjoint Administratif 2�me Classe Non Titulaire');
update pompier set P_GRADE='AA1' where P_ID in (select Numeroadh�rent from adh�rent where Grade='Adjoint Administratif 1�re Classe');
update pompier set P_GRADE='AT2' where P_ID in (select Numeroadh�rent from adh�rent where Grade in ('Adjoint Technique 2�me Classe','Adjoint Technique  2�me Classe'));
update pompier set P_GRADE='AT2NT' where P_ID in (select Numeroadh�rent from adh�rent where Grade='Adjoint Technique 2�me Classe Non Titulaire');
update pompier set P_GRADE='AT1' where P_ID in (select Numeroadh�rent from adh�rent where Grade in ('Adjoint Technique 1�re Classe','Adjoint Technique 1�re classe'));
update pompier set P_GRADE='AM' where P_ID in (select Numeroadh�rent from adh�rent wherewhere(lower(Grade)) like 'agent de m%');

update pompier set P_GRADE='INF' where P_ID in (select Numeroadh�rent from adh�rent where(lower(Grade)) like 'infirmier%');
update pompier set P_GRADE='MED' where P_ID in (select Numeroadh�rent from adh�rent where(lower(Grade)) like 'm�decin%');
update pompier set P_GRADE='PPH' where P_ID in (select Numeroadh�rent from adh�rent where(lower(Grade)) like 'pharmacie%');


update adh�rent set Observations1='' where Observations1 is null;
update adh�rent set Observations2='' where Observations2 is null;
update adh�rent set Observations3='' where Observations3 is null;

alter table adh�rent add OBS varchar(255) null;
update adh�rent set OBS = SUBSTRING(concat(Observations1,' ',Observations2,' ', Observations3) FROM 1 FOR 255);

update pompier 
set OBSERVATION = ( select OBS from adh�rent where pompier.P_ID = adh�rent.Numeroadh�rent);

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
and P_ID in (select Numeroadh�rent from adh�rent where Radiation = 1 );

update pompier set SUSPENDU=1 where SUSPENDU=0
and P_ID in (select Numeroadh�rent from adh�rent where Suspendu = 1 );

INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('GADH', 'Guide de l''adh�rent');
INSERT INTO type_document (TD_CODE, TD_LIBELLE) VALUES ('GELU', 'Guide de l''�lu');