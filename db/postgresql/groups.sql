--
-- Tabella dei gruppi degli utenti
--
CREATE TABLE IF NOT EXISTS groups (
  id SERIAL NOT NULL PRIMARY KEY,
  name VARCHAR( 40 ) NOT NULL UNIQUE
);

ALTER TABLE groups OWNER TO capana;
