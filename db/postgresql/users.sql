--
-- Tabella degli utenti
--
CREATE TABLE IF NOT EXISTS users (
  id SERIAL NOT NULL PRIMARY KEY,
  username VARCHAR( 40 ) NOT NULL UNIQUE,
  password VARCHAR( 40 ) NOT NULL,
  email VARCHAR( 255 ) NOT NULL UNIQUE,
  em_checked BOOL DEFAULT FALSE,
  em_key VARCHAR( 40 ) NOT NULL,
  first_name VARCHAR( 40 ) NOT NULL,
  last_name VARCHAR( 40 ) NOT NULL,
  last_login TIMESTAMP,
  login_num INTEGER NOT NULL DEFAULT 0,
  user_type VARCHAR( 40 ) DEFAULT 'NORMAL',
  enabled BOOLEAN DEFAULT TRUE,
  accept_notes BOOLEAN DEFAULT TRUE,
  group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
  quota_used INTEGER
);

ALTER TABLE users OWNER TO capana;
