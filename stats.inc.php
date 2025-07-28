<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HiddenLeaders implementation : © Hervé DI STEFANO hdistef7@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * HiddenLeaders game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = [

    // Statistics global to table
    "table" => [

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
        "winning_faction" => array("id"=> 31,
                    "name" => totranslate("Winning Faction"),
                    "type" => "int" ),
    ],
    
    // Statistics existing for each player
    "player" => [

        "turns_number" => array("id"=> 10,
                    "name" => totranslate("Number of turns"),
                    "type" => "int" ),
        "visibleCards" => array("id"=> 22,
                    "name" => totranslate("Number of cards visible"),
                    "type" => "int" ),
        "hiddenCards" => array("id"=> 15,
                    "name" => totranslate("Number of cards face-down"),
                    "type" => "int" ),
        "moveEmpireToken" => array("id"=> 19,
                    "name" => totranslate("Total Imperial Army Token Movements"),
                    "type" => "int" ),
        "moveTribesToken" => array("id"=> 20,
                    "name" => totranslate("Total Hill Tribes Token Movements"),
                    "type" => "int" ),
        "moveGuardianToken" => array("id"=> 23,
                    "name" => totranslate("Total Guardian Token Movements"),
                    "type" => "int" ),
        "playedUndead" => array("id"=> 11,
                    "name" => totranslate("Played Undead cards"),
                    "type" => "int" ),
        "playedWaterfolk" => array("id"=> 12,
                    "name" => totranslate("Played Water-Folk cards"),
                    "type" => "int" ),
        "playedEmpire" => array("id"=> 13,
                    "name" => totranslate("Played Imperial Army cards"),
                    "type" => "int" ),
        "playedTribes" => array("id"=> 14,
                    "name" => totranslate("Played Hill Tribes cards"),
                    "type" => "int" ),
        "playedGuardian" => array("id"=> 24,
                    "name" => totranslate("Played Guardians cards"),
                    "type" => "int" ),
        "buriedCards" => array("id"=> 16,
                    "name" => totranslate("Buried cards"),
                    "type" => "int" ),
        "discardedCards" => array("id"=> 17,
                    "name" => totranslate("Discarded cards"),
                    "type" => "int" ),
        "takenFromGraveyard" => array("id"=> 18,
                    "name" => totranslate("Cards taken from Graveyard"),
                    "type" => "int" ),
        "leader" => array("id"=> 21,
                    "name" => totranslate("Leader"),
                    "type" => "int" ),
    ],

    "value_labels" => [
        21 => [
            1 => totranslate('Lemron - The Wise'), 
            2 => totranslate('Cyra - The Righteous'), 
            3 => totranslate('Myrad - The Banished'), 
            4 => totranslate('Xiadul - The Cunning'), 
            5 => totranslate('Pavyr - The Opportunist'), 
            6 => totranslate('Enned - The Innocent'),

            7 => totranslate('Tissa - The Inventive'), 
            8 => totranslate('Ulc - The Rooted'), 
            9 => totranslate('Irafel - The Lyrical'), 
            10 => totranslate('Vyma - The Deep'),
        ],
		31 => [
			1 => totranslate("Undead"),
			2 => totranslate("Water Folk"), 
			3 => totranslate("Imperial Army"), 
			4 => totranslate("Hill Tribes"),
			5 => totranslate("Guardians"),
        ],
	]

];
