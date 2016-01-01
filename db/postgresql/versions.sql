--
-- Tabella delle versioni del DB
--
CREATE TABLE IF NOT EXISTS versions (
  id SERIAL NOT NULL PRIMARY KEY,
  ver VARCHAR( 40 ),
  cdate DATE DEFAULT CURRENT_DATE
);

ALTER TABLE versions OWNER TO capana;
