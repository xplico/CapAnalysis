--
-- Tabella dei dataset
--
CREATE TABLE IF NOT EXISTS datasets (
  id SERIAL NOT NULL PRIMARY KEY,
  name VARCHAR( 40 ) NOT NULL,
  group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
  share VARCHAR( 200 ),
  depth VARCHAR( 200 ) DEFAULT '-'
);

ALTER TABLE datasets OWNER TO capana;
