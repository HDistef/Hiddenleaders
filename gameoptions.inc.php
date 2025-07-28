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
 * gameoptions.inc.php
 *
 * HiddenLeaders game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in hiddenleaders.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

require_once("modules/php/constants.inc.php");

$game_options = array(

    OPTION_QUEENS => [
        'name' => totranslate('Queens & Friend'),
        'values' => [
            1 => [
                'name' => totranslate('Disabled'),
            ],
            2 => [
                'name' => totranslate('Enabled'),
                //'tmdisplay' => totranslate('Queens & Friend'),
                'description' => totranslate('Add the 3 Queens, Emperor\'s Best Friend and Keeper of Discord'),
                'nobeginner' => true,
            ],
        ],
        'default' => 1,
    ],

    OPTION_FORGOTTEN_LEGENDS => [
        'name' => totranslate('Forgotten Legends'),
        
        'values' => [
            1 => [
                'name' => totranslate('No'),
            ],
            2 => [
                'name' => totranslate('Yes'),
                'description' => totranslate('Add the guardian faction'),
            ],
        ],

        'default' => 1,
    ],


    // OPTION_CORRUPTION => [
    //     'name' => totranslate('Corruption'),
        
    //     'values' => [
    //         1 => [
    //             'name' => totranslate('No'),
    //         ],
    //         2 => [
    //             'name' => totranslate('Yes'),
    //             'nobeginner' => true,
    //         ],
    //     ],

    //     'default' => 1,
        
    //     'displaycondition' => [
    //         [
    //         'type' => 'otheroptionisnot',
    //         'id' => OPTION_FORGOTTEN_LEGENDS,
    //         'value' => 1,
    //         ],
    //     ],
    // ],

    // OPTION_ARTIFACTS => [
    //     'name' => totranslate('Artifacts'),
        
    //     'values' => [
    //         1 => [
    //             'name' => totranslate('No'),
    //         ],
    //         2 => [
    //             'name' => totranslate('Yes'),
    //             'nobeginner' => true,
    //         ],
    //     ],

    //     'default' => 1,
        
    //     'displaycondition' => [
    //         [
    //         'type' => 'otheroptionisnot',
    //         'id' => OPTION_FORGOTTEN_LEGENDS,
    //         'value' => 1,
    //         ],
    //     ],
    // ],

);

$game_preferences = array(
    '1' => array(
        'name' => totranslate('Player Board Layout'),
        'needReload' => true,
        'values' => array(
            1 => array('name' => totranslate('Vertical')),
            2 => array('name' => totranslate('Horizontal'), 'cssPref' => 'horizontal_player_board'),
        ),
        'default' => 1,
    ),
    // '2' => array(
    //     'name' => totranslate('Show cards art in tooltips'),
    //     'needReload' => true,
    //     'values' => array(
    //         1 => array('name' => totranslate('Yes')),
    //         2 => array('name' => totranslate('No'), 'cssPref' => 'no_art_cards')
    //     ),
    // ),
);


