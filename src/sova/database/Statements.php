<?php
namespace sova\database;

interface Statements{

	 const PSFS = [
		"sqlite" => "psfs/sqlite.sql"
	];

	const INIT_STATS = "sova.init.stats";
	const FETCH_PLAYER_STATS = "sova.fetch.player.stats";
	const SAVE_PLAYER_STATS = "sova.save.player.stats";
}