#====================================================================================================
# Ce script est un exemple de migration de compétences suite à un changement de législation
#====================================================================================================

# ---------------------------------------------------------------------------------------------------
# renommer PAE1 en PAE FPS
# ---------------------------------------------------------------------------------------------------
update poste set TYPE='PAE FPS', DESCRIPTION='Formateur aux Premiers Secours' where PS_ID=11;

# ---------------------------------------------------------------------------------------------------
# renommer PAE2 en PAE FdF
# ---------------------------------------------------------------------------------------------------
update poste set TYPE='PAE FdF', DESCRIPTION='Formateur de Formateurs' where PS_ID=12;

# ---------------------------------------------------------------------------------------------------
# renommer PAE3 en PAE FPSC
# ---------------------------------------------------------------------------------------------------
update poste set TYPE='PAE FPSC', DESCRIPTION='Formateur en Prévention et Secours Civiques' where PS_ID=10;

# ---------------------------------------------------------------------------------------------------
# basculer  PAE 1 ( option PSSP ) = 14 en PAE F PS = 11 puis supprimer le code PAE 1 ( option PSSP )
# ---------------------------------------------------------------------------------------------------

insert into qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, Q_UPDATED_BY, Q_UPDATE_DATE)
select q1.P_ID, 11, q1.Q_VAL, q1.Q_EXPIRATION, 80, NOW()
from qualification q1 where PS_ID=14 
and not exists (select 1 from qualification q2 where q2.P_ID = q1.P_ID and q2.PS_ID = 11);

delete from qualification where PS_ID=14;

delete from equipage where PS_ID=14;

delete from personnel_formation where PS_ID=14;

delete from diplome_param where PS_ID=14;

delete from evenement_competences where PS_ID=14;

update evenement set PS_ID=11 where PS_ID=14;

delete from poste where PS_ID=14;

# ---------------------------------------------------------------------------------------------------
#  CEAF 181 ajouté sur les anciens PAE2
# ---------------------------------------------------------------------------------------------------

insert into qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, Q_UPDATED_BY, Q_UPDATE_DATE)
select q1.P_ID, 181, q1.Q_VAL, q1.Q_EXPIRATION, 80, NOW()
from qualification q1 where PS_ID=12 and ( q1.Q_UPDATE_DATE < '2014-01-01' or q1.Q_UPDATE_DATE is null)
and not exists (select 1 from qualification q2 where q2.P_ID = q1.P_ID and q2.PS_ID = 181);

# ---------------------------------------------------------------------------------------------------
# PICF 182 ajouté sur PAE1 FPS et PAE PdF et PAE FPSC
# ---------------------------------------------------------------------------------------------------

insert into qualification (P_ID, PS_ID, Q_VAL, Q_EXPIRATION, Q_UPDATED_BY, Q_UPDATE_DATE)
select distinct q1.P_ID, 182, q1.Q_VAL, null, 80, NOW()
from qualification q1 where PS_ID in (10,11,12)
and not exists (select 1 from qualification q2 where q2.P_ID = q1.P_ID and q2.PS_ID = 182);

update poste set DESCRIPTION='Pédagogie Initiale et Commune de Formateur' where PS_ID=182;
update qualification set Q_EXPIRATION=null where PS_ID=182;


