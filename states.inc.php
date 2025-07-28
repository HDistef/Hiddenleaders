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
 * states.inc.php
 *
 * HiddenLeaders game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

$machinestates = array(

    // The initial state. Please do not modify.
    ST_BGA_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => ST_SETUP_TRANSITION )
    ),
    
    ST_PRIVATE_SETUP => array (
        "name" => "privateSetup",
        "description" => clienttranslate('Wait for everyone'),
        "descriptionmyturn" => clienttranslate('${you} must put a card face-down and discard a card'),
        "descriptionmyturnfacedown" => clienttranslate('${you} must put a card face-down'),
        "descriptionmyturndiscard" => clienttranslate('${you} must discard a card'),
        "type" => "multipleactiveplayer",
        "initialprivate" => ST_PLAYER_SETUP,
        "action" => "stPlayerSetup",
        //"possibleactions" => ["changeMind"], //this action is possible if player is not in any private state which usually happens when they are inactive
        "transitions" => ["setupFinished" => ST_SETUP_TRANSITION] // this is normal next transition which will happen after all players finish their turns 
    ),

    // Initial starting hand
    ST_PLAYER_SETUP => array(
        "name" => "playerSetup",
        //"description" => clienttranslate('Wait for everyone'),
        "descriptionmyturn" => clienttranslate('${you} must put a card face-down and discard a card'),
        "descriptionmyturnfacedown" => clienttranslate('${you} must put a card face-down'),
        "descriptionmyturndiscard" => clienttranslate('${you} must discard a card'),
        "type" => "private",
        //"action" => "stPlayerSetup",
        "args" => "argPlayerSetup",  
        "possibleactions" => array( "playHiddenCard", "discard" ),
        "transitions" => array("artifact" => ST_PLAYER_ARTIFACT_SETUP)
    ),

    ST_SETUP_TRANSITION => array(
        "name" => "setupTransition",
        "description" => "",
        "type" => "game",
        "action" => "stSetupTransition", 
        "transitions" => array( "nextPlayer" => ST_PLAYER_ACTION )
    ),

    ST_NEXT_PLAYER => array(
        "name" => "nextPlayer",
        "description" => "",
        "type" => "game",
        "action" => "stNextPlayer", // fill up tavern
        "updateGameProgression" => true,
        "transitions" => array( "nextPlayer" => ST_PLAYER_ACTION, 'artifact' => ST_PLAYER_ARTIFACT, "endScore" => ST_END_SCORE )
    ),

    // Player turn
    // Step 1/3
    ST_PLAYER_ACTION => array(
        "name" => "playerAction",
        "description" => clienttranslate('${actplayer} must play a card face-up or discard up to 3 cards'),
        "descriptionmyturn" => clienttranslate('${you} must play a card face-up or discard up to 3 cards'),
        "descriptionmyturnreplay" => clienttranslate('${you} can play a card face-up'),
        "descriptionreplay" => clienttranslate('${actplayer} can play a card face-up'),
        "type" => "activeplayer",
        "args" => "argCardEffect",
        "possibleactions" => array( "playCard","discard","spreadCorruption","playArtifact" ),
        "transitions" => array( "cardEffect" => ST_PLAYER_CARD_EFFECT_MANAGER, "drawCard" => ST_PLAYER_DRAW_CARD, "spreadCorruption" => ST_PLAYER_SPREAD_CORRUPTION, "artifact" => ST_PLAYER_ARTIFACT, "playArtifact" => ST_PLAYER_ARTIFACT_EFFECT_MANAGER, "zombiePass" => ST_NEXT_PLAYER)
    ),

    ST_PLAYER_CARD_EFFECT_MANAGER => array(
        "name" => "cardEffect",
        "description" => clienttranslate('${actplayer} plays ${card_name}'),
        "descriptionmyturn" => clienttranslate('${you} play ${card_name}'),
        "type" => "activeplayer",
        "args" => "argCardEffect",
        "action" => "stGameEffectManager",
        "possibleactions" => array( "moveToken", "moveCard", "discard", "drawCard", "lookCard", "drawCardFromOpponent", "selectOpponent", "exchange", "performEffect",
    "switchPlayer", "selectFaction", "playNewCard", "selectCard", "drawFate" ),
    	"transitions" => array( 
            "nextEffect" => ST_PLAYER_CARD_EFFECT_MANAGER, "nextStep" => ST_TRIGGER_END, "playNewCard" => ST_PLAYER_ACTION,
            "changeActivePlayer" => ST_GAME_CHANGE_ACTIVE_PLAYER,
            "willBendingWitch" => ST_PLAYER_CARD_EFFECT_WILLBENDINGWITCH,
            "philantropicPhantom" => ST_PLAYER_CARD_EFFECT_PHILANTROPICPHANTOM,
            "hardShelledTitan" => ST_PLAYER_CARD_EFFECT_HARDSHELLEDTITAN,
            'drawFate' => ST_PLAYER_FATE,
            'artifact' => ST_PLAYER_ARTIFACT, 'artifactEffect' => ST_PLAYER_ARTIFACT_EFFECT_MANAGER,
            "zombiePass" => ST_NEXT_PLAYER)
    ),
    
    ST_GAME_CHANGE_ACTIVE_PLAYER => array(
        "name" => "changeActivePlayer",
        "description" => "",
        "type" => "game",
        "action" => "stChangeActivePlayer",  
        "transitions" => array( "changeActivePlayer" => ST_PLAYER_CARD_EFFECT_MANAGER)
    ),

    ST_PLAYER_CARD_EFFECT_WILLBENDINGWITCH => array(
        "name" => "willBendingWitch",
        "description" => clienttranslate('${card_name} - All other players have to discard 1 card from their hand'),
        "descriptionmyturn" => clienttranslate('${card_name} - ${you} have to discard 1 card from your hand'),
        "type" => "multipleactiveplayer",
        "args" => "argWillBendingWitch",
        "possibleactions" => array( "discard"),
        "transitions" => array( "endEffect" => ST_PLAYER_CARD_EFFECT_MANAGER)
    ),

    ST_PLAYER_CARD_EFFECT_PHILANTROPICPHANTOM => array(
        "name" => "philantropicPhantom",
        "description" => clienttranslate('${card_name} - All other players have to bury 1 of their ${visible} OR ${hidden}'),
        "descriptionmyturn" => clienttranslate('${card_name} - ${you} have to bury 1 of your ${visible} OR ${hidden}'),
        "type" => "multipleactiveplayer",
        "args" => "argPhilantropicPhantom",
        "possibleactions" => array( "bury"),
        "transitions" => array( "endEffect" => ST_PLAYER_CARD_EFFECT_MANAGER)
    ),

    ST_PLAYER_CARD_EFFECT_HARDSHELLEDTITAN => array(
        "name" => "hardShelledTitan",
        "description" => clienttranslate('${card_name} - All other players have to give you 1 card from their hand'),
        "descriptionmyturn" => clienttranslate('${card_name} - ${you} have to give you 1 card from your hand'),
        "type" => "multipleactiveplayer",
        "args" => "argHardShelledTitan",
        "possibleactions" => array( "moveCard"),
        "transitions" => array( "endEffect" => ST_PLAYER_CARD_EFFECT_MANAGER)
    ),

    ST_TRIGGER_END => array(
        "name" => "triggerEnd",
        "description" => "",
        "type" => "game",
        "action" => "stCheckTriggerEnd",
        "transitions" => array( "drawCard" => ST_PLAYER_DRAW_CARD, "endScore" => ST_END_SCORE)
    ),

    // Step 3/4
    ST_PLAYER_DRAW_CARD => array(
        "name" => "drawCard",
        "description" => clienttranslate('${actplayer} must draw from ${tavern} and/or ${harbor}'),
        "descriptionmyturn" => clienttranslate('${you} must draw ${nbCards} from ${tavern} and/or ${harbor}'),
        "type" => "activeplayer",
        "action" => "stDrawCard",
        "args" => "argDrawCard",
        "possibleactions" => array( "drawCard", "drawCardFromTavern" ),
        "transitions" => array( "drawCard" => ST_PLAYER_DRAW_CARD, "discard" => ST_PLAYER_DISCARD, "zombiePass" => ST_NEXT_PLAYER )
    ),

    // Step 4/4
    ST_PLAYER_DISCARD => array(
        "name" => "discard",
        "description" => clienttranslate('${actplayer} must discard down to 3 cards'),
        "descriptionmyturn" => clienttranslate('${you} must discard ${nbCards} card(s)'),
        "type" => "activeplayer",
        "args" => "argDiscard",
        "possibleactions" => array( "discard" ),
        "transitions" => array( "nextPlayer" => ST_NEXT_PLAYER, "zombiePass" => ST_NEXT_PLAYER )
    ),
    
    // Fate draw
    ST_PLAYER_FATE => array(
        "name" => "fate",
        "description" => clienttranslate('${actplayer} must choose a fate card'),
        "descriptionmyturn" => clienttranslate('${you} must choose a fate card'),
        "type" => "activeplayer",
        "args" => "argFate",
        "possibleactions" => array( "drawFate" ),
        "transitions" => array( "nextEffect" => ST_PLAYER_CARD_EFFECT_MANAGER, "zombiePass" => ST_NEXT_PLAYER )
    ),

    // Spread Corruption
    ST_PLAYER_SPREAD_CORRUPTION => array(
        "name" => "spreadCorruption",
        "description" => clienttranslate('${actplayer} must spread the Corruption'),
        "descriptionmyturn" => clienttranslate('${you} must spread the Corruption'),
        "type" => "activeplayer",
        //"action" => "stSpreadCorruption",
        "args" => "argSpreadCorruption",
        "possibleactions" => array( "spreadCorruption" ),
        "transitions" => array( "nextPlayer" => ST_NEXT_PLAYER_CORRUPTION, "zombiePass" => ST_NEXT_PLAYER )
    ),
    
    ST_NEXT_PLAYER_CORRUPTION => array(
        "name" => "nextPlayerCorruption",
        "description" => "",
        "type" => "game",
        "action" => "stNextPlayerCorruption",
        "transitions" => array( "nextPlayer" => ST_PLAYER_SPREAD_CORRUPTION, "endSpread" => ST_PLAYER_ACTION )
    ),
    
    // Artifact
    ST_PLAYER_ARTIFACT_SETUP => array(
        "name" => "artifactSetup",
        //"description" => clienttranslate('Wait for everyone'),
        "descriptionmyturn" => clienttranslate('${you} have to choose a artifact'),
        "type" => "private",
        //"args" => "argArtifactSetup",
        "possibleactions" => array( "selectArtifact")
    ),
    
    ST_PLAYER_ARTIFACT => array(
        "name" => "artifact",
        "description" => clienttranslate('${actplayer} can play his artifact'),
        "descriptionmyturn" => clienttranslate('${you} can play your artifact'),
        "type" => "activeplayer",
        "args" => "argArtifact",
        "possibleactions" => array("playArtifact"),
    	"transitions" => array( 
            "playArtifact" => ST_PLAYER_ARTIFACT_EFFECT_MANAGER,
            "drawCard" => ST_PLAYER_DRAW_CARD,
            "nextPlayer" => ST_NEXT_PLAYER,
            "zombiePass" => ST_NEXT_PLAYER)
    ),
    

    ST_PLAYER_ARTIFACT_EFFECT_MANAGER => array(
        "name" => "cardEffect",
        "description" => clienttranslate('${actplayer} plays ${artifact_name}'),
        "descriptionmyturn" => clienttranslate('${you} play ${artifact_name}'),
        "type" => "activeplayer",
        "args" => "argArtifactEffect",
        "action" => "stArtifactEffectManager",
        "possibleactions" => array( ),
    	"transitions" => array( 
            "nextEffect" => ST_PLAYER_ARTIFACT_EFFECT_MANAGER, "nextStepFastAction" => ST_PLAYER_ACTION, "nextStepFastDraw" => ST_PLAYER_DRAW_CARD, "nextStepSlow" => ST_NEXT_PLAYER, "playCardEffect" => ST_PLAYER_CARD_EFFECT_MANAGER, "drawFate" => ST_PLAYER_FATE, "zombiePass" => ST_NEXT_PLAYER)
    ),

    // END
    ST_END_SCORE => [
        "name" => "endScore",
        "description" => "",
        "type" => "game",
        "action" => "stEndScore",
        "args" => "argEndScore",
        "transitions" => [
            "endGame" => ST_END_GAME,
        ],
    ],
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ),
);