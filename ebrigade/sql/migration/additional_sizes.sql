#====================================================;
#  Optional setup
#====================================================;

SET sql_mode = '';
# ------------------------------------;
# taille_vetement
# ------------------------------------;
delete from taille_vetement where TT_CODE='PT' and TV_ORDER > 160;
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('PT','0 (F)','170'),
('PT','1 (F)','180'),
('PT','2 (F)','190'),
('PT','3 (F)','200'),
('PT','4 (F)','210'),
('PT','5 (F)','220'),
('PT','6 (F)','230'),
('PT','0 (H)','240'),
('PT','1 (H)','250'),
('PT','2 (H)','260'),
('PT','3 (H)','270'),
('PT','4 (H)','280'),
('PT','5 (H)','290'),
('PT','6 (H)','300');

delete from taille_vetement where TT_CODE='US' and TV_ORDER > 70;
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('US','0 (F)','170'),
('US','1 (F)','180'),
('US','2 (F)','190'),
('US','3 (F)','200'),
('US','4 (F)','210'),
('US','5 (F)','220'),
('US','6 (F)','230'),
('US','0 (H)','240'),
('US','1 (H)','250'),
('US','2 (H)','260'),
('US','3 (H)','270'),
('US','4 (H)','280'),
('US','5 (H)','290'),
('US','6 (H)','300'),
('US','XS (F)','370'),
('US','S (F)','380'),
('US','M (F)','390'),
('US','L (F)','300'),
('US','XL (F)','410'),
('US','XXL (F)','420'),
('US','XXXL (F)','430'),
('US','XS (H)','440'),
('US','S (H)','450'),
('US','M (H)','460'),
('US','L (H)','470'),
('US','XL (H)','480'),
('US','XXL (H)','490'),
('US','XXXL (H)','500');

delete from taille_vetement where TT_CODE='VESTE' and TV_ORDER > 290;
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('VESTE','XS','300'),
('VESTE','S','310'),
('VESTE','M','320'),
('VESTE','L','330'),
('VESTE','XL','340'),
('VESTE','XXL','350'),
('VESTE','XXXL','360');

delete from type_taille where TT_CODE='GHV';
INSERT INTO type_taille (TT_CODE,TT_NAME,TT_DESCRIPTION,TT_ORDER)
VALUES ('GHV', 'Gilets HV', 'S, M, L, XL, XXL ...', '100');

delete from taille_vetement where TT_CODE='GHV';
INSERT INTO taille_vetement (TT_CODE, TV_NAME, TV_ORDER) VALUES
('GHV','XS','300'),
('GHV','S','310'),
('GHV','M','320'),
('GHV','L','330'),
('GHV','XL','340'),
('GHV','XXL','350'),
('GHV','XXXL','350');
