#====================================================================================================
# Ce script est un exemple de migration de rôles
#  EN 3.4 on peut avoir plusieurs personnes sur le même rôle
#  DONC on peut supprimer les rôles dupliqués
#====================================================================================================

# on supprime le 119 (77), on met tout le monde sur le 103 (183)
insert into section_role (S_ID, GP_ID, P_ID )
select S_ID, 103, P_ID
from section_role O where O.GP_ID = 119
and not exists (select 1 from section_role X where X.S_ID = O.S_ID and X.GP_ID = 103 and X.P_ID = O.P_ID );
delete from section_role where GP_ID = 119;
delete from groupe where GP_ID = 119;

# on supprime le 112 (7), on met tout le monde sur le 111 (18)
insert into section_role (S_ID, GP_ID, P_ID )
select S_ID, 111, P_ID
from section_role O where O.GP_ID = 112
and not exists (select 1 from section_role X where X.S_ID = O.S_ID and X.GP_ID = 111 and X.P_ID = O.P_ID );
delete from section_role where GP_ID = 112;
delete from groupe where GP_ID = 112;

# on supprime le 118 (21), on met tout le monde sur le 101 (37)
insert into section_role (S_ID, GP_ID, P_ID )
select S_ID, 101, P_ID
from section_role O where O.GP_ID = 118
and not exists (select 1 from section_role X where X.S_ID = O.S_ID and X.GP_ID = 101 and X.P_ID = O.P_ID );
delete from section_role where GP_ID = 118;
delete from groupe where GP_ID = 118;