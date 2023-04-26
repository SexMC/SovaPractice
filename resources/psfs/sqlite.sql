-- #!sqlite
-- #{ sova

-- # { init.stats
CREATE TABLE IF NOT EXISTS sovaPlayers(
	player VARCHAR(36) NOT NULL,
    kills INT DEFAULT 0,
    deaths INT DEFAULT 0,
	rankID INT DEFAULT 0,
	PRIMARY KEY (player));
-- # }

-- # { fetch.player.stats
-- #  :player string
SELECT * FROM sovaPlayers WHERE player = :player;
-- # }


-- # { save.player.stats
-- #  :player string
-- #  :rankID int
-- #  :kills int
-- #  :deaths int
REPLACE INTO sovaPlayers(player, rankID, kills, deaths) VALUES(:player, :rankID, :kills, :deaths);
-- # }

-- #}