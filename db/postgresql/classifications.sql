--
-- Tabella classifications www.xplico.org 
--
CREATE TABLE IF NOT EXISTS classifications (
  id SERIAL NOT NULL PRIMARY KEY,
  level INTEGER NOT NULL UNIQUE,
  description TEXT,
  text_color VARCHAR( 10 ) DEFAULT '#ffffff',
  bg_color VARCHAR( 10 ) DEFAULT '#dddddd'
);

ALTER TABLE classifications OWNER TO capana;
