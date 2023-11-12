-- #! mysql
-- #{ table
-- #    { init
CREATE TABLE IF NOT EXISTS giftcode (
      player_name TEXT UNIQUE,
      code TEXT,
      used_code TEXT
);
-- #    }
-- #    { insert
-- #      :player_name string
-- #      :code string
-- #      :used_code string
INSERT INTO giftcode (player_name, code, used_code) VALUES (:player_name, :code, :used_code);
-- #    }
-- #    { select
-- #      :player_name string
SELECT * FROM giftcode WHERE player_name = :player_name;
-- #    }
-- #    { update
-- #      :player_name string
-- #      :code string
-- #      :used_code string
UPDATE giftcode SET used_code = :used_code, code = :code WHERE player_name = :player_name;
-- #    }
-- # }