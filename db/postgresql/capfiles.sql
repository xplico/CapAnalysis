--
-- Tabella dei file del dataset (pcap)
--
CREATE TABLE IF NOT EXISTS capfiles (
  id SERIAL NOT NULL PRIMARY KEY,
  dataset_id INTEGER NOT NULL REFERENCES datasets(id) ON DELETE CASCADE ON UPDATE CASCADE,
  data_size BIGINT,
  filename VARCHAR( 255 ),
  md5 VARCHAR( 255 ),
  sha1 VARCHAR( 255 )
);

ALTER TABLE capfiles OWNER TO capana;
