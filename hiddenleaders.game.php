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
  * hiddenleaders.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

require_once('modules/php/objects/card.php');
require_once('modules/php/cards/HeroCard.php');
require_once('modules/php/cards/ArtifactCard.php');
require_once('modules/php/Notifications.php');
require_once('modules/php/constants.inc.php');
require_once('modules/php/actions.php');
require_once('modules/php/args.php');
require_once('modules/php/debug-util.php');
require_once('modules/php/states.php');
require_once('modules/php/utils.php');

class HiddenLeaders extends Table
{
    /** @var HiddenLeaders */
    public static $instance = null;

    use UtilTrait;
    use ActionTrait;
    use StateTrait;
    use ArgsTrait;
    use DebugUtilTrait;

	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        $this->bSelectGlobalsForUpdate = true;

        self::initGameStateLabels( array( 
            FIRST_PLAYER => FIRST_PLAYER,
            EMPIRE_TOKEN => EMPIRE_TOKEN,
            TRIBES_TOKEN => TRIBES_TOKEN,
            CARD_PLAYED => CARD_PLAYED,
            EFFECT_STEP => EFFECT_STEP,
            NB_DISCARDED_CARD => NB_DISCARDED_CARD,
            SELECTED_CARD => SELECTED_CARD,
            SELECTED_CARD_2 => SELECTED_CARD_2,
            CURRENT_PLAYER => CURRENT_PLAYER,
            SELECTED_PLAYER => SELECTED_PLAYER,
            SELECTED_FACTION => SELECTED_FACTION,
            OPTION_QUEENS => OPTION_QUEENS,
            OPTION_FORGOTTEN_LEGENDS => OPTION_FORGOTTEN_LEGENDS,
            OPTION_CORRUPTION => OPTION_CORRUPTION,
            OPTION_ARTIFACTS => OPTION_ARTIFACTS,
            GUARDIAN_TOKEN => GUARDIAN_TOKEN,
            DRAW_FATE => DRAW_FATE,
            SKIP_PLAYER => SKIP_PLAYER,
            ARTIFACT_PLAYED => ARTIFACT_PLAYED,
            PREVIOUS_STATE => PREVIOUS_STATE
        ) );        

        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" );
        $this->cards->autoreshuffle = true;

        $this->fate_cards = self::getNew( "module.common.deck" );
        $this->fate_cards->init( "card" );

        $this->corruption_tokens = self::getNew( "module.common.deck" );
        $this->corruption_tokens->init( "corruption" );

        self::$instance = $this;
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "hiddenleaders";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        $sql .= implode( ',', $values );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( EMPIRE_TOKEN, 4 );
        self::setGameStateInitialValue( TRIBES_TOKEN, 4 );
        self::setGameStateInitialValue( GUARDIAN_TOKEN, 1 );
        self::setGameStateInitialValue( CARD_PLAYED, 0 );
        self::setGameStateInitialValue( EFFECT_STEP, 0 );
        self::setGameStateInitialValue( NB_DISCARDED_CARD, 0 );
        self::setGameStateInitialValue( SELECTED_CARD, 0 );
        self::setGameStateInitialValue( SELECTED_CARD_2, 0 );
        self::setGameStateInitialValue( CURRENT_PLAYER, 0 );
        self::setGameStateInitialValue( SELECTED_PLAYER, 0 );
        self::setGameStateInitialValue( SELECTED_FACTION, 0 );
        self::setGameStateInitialValue( DRAW_FATE, 0 );
        self::setGameStateInitialValue( SKIP_PLAYER, 0 );
        self::setGameStateInitialValue( ARTIFACT_PLAYED, 0 );
        self::setGameStateInitialValue( PREVIOUS_STATE, ST_PLAYER_ACTION );

        // if (!$isFLExpansion) { TO DO
        //     self::dontPreloadImage("fl_cards.jpg");
        //     self::dontPreloadImage("fl_leaders.jpg");
        //     self::dontPreloadImage("loots.jpg");
        // }
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
		self::initStat( 'table', "turns_number", 0 );
		self::initStat( 'table', "winning_faction", 0 );
		self::initStat( 'player', "leader", 0 );
		self::initStat( 'player', "hiddenCards", 0 );
		self::initStat( 'player', "visibleCards", 0 );
		self::initStat( 'player', "turns_number", 0 );
		self::initStat( 'player', "playedUndead", 0 );
		self::initStat( 'player', "playedWaterfolk", 0 );
		self::initStat( 'player', "playedEmpire", 0 );
		self::initStat( 'player', "playedTribes", 0 );
		self::initStat( 'player', "buriedCards", 0 );
		self::initStat( 'player', "discardedCards", 0 );
		self::initStat( 'player', "takenFromGraveyard", 0 );
		self::initStat( 'player', "moveEmpireToken", 0 );
		self::initStat( 'player', "moveTribesToken", 0 );

        // Guardians
        if ($this->isFLExpansion()) {
            self::initStat( 'player', "playedGuardian", 0 );
            self::initStat( 'player', "moveGuardianToken", 0 );
            $this->setupFate();
        }

        // Give one leader to each player
        $this->drawLeader();

        // Create deck
        $this->setupCards();

        // Corruption
        if($this->isCorruption()) $this->setupCorruption();

        // Artifact
        if($this->isArtifacts()) ArtifactCard::setupArtifacts();
        
        /************ End of the game initialization *****/
    }

    public static function get() {
        return self::$instance;
    }

	public static function Query( $sql ) { return self::DBQuery( $sql ); }
	public function getObject( $sql ) { return self::getObjectFromDB( $sql ); }
	public function getObjectList( $sql ) { return self::getObjectListFromDB( $sql ); }
	public function getUniqueValue( $sql ) { return self::getUniqueValueFromDB( $sql ); }
	public function getCollection( $sql ) { return self::getCollectionFromDB( $sql ); }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $endGame = $this->gamestate->state_id() == ST_END_GAME;

        $result = array();
        
        $winningFaction = $this->getWinningFaction();
        $result['factions'] = $this->getFactions();
        $result['winningFaction'] = $winningFaction;
        $result['winningFaction_translated'] = $this->FACTIONS[$winningFaction];

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_name name, player_color color, player_score score, player_leader_id leader_id FROM player ";
        $result['players'] = self::getCollectionFromDB( $sql );
        
        $result['playersorder'] = $this->getPlayerIdsInOrder();

        foreach ($result['players'] as $player_id => &$player) {
            //tableau de chaque joueur
            
            $hand = $this->cards->getCardsInLocation('hand'.$player_id);
            $player['hand'] = $player_id == $current_player_id ? $this->getCardsInfos($hand) : HeroCard::onlyIds($hand);

            $player['visibleCards'] = $this->getCardsInfos($this->cards->getCardsInLocation('visibleCards', $player_id)); 

            $hiddenCards = $this->cards->getCardsInLocation('hiddenCards', $player_id);
            $player['hiddenCards'] = $player_id == $current_player_id || $endGame ? $this->getCardsInfos($hiddenCards) : HeroCard::onlyIds($hiddenCards);
            
            $player['counterFactions'] = $this->getCountAllFactionsPlayer($player_id);

            $player['leader'] = $player['leader_id'] <= 6 ? $this->LEADER_CARDS[$player['leader_id']] : $this->FL_LEADER_CARDS[$player['leader_id']];
            
            if($this->isArtifacts()) $player['artifact'] = ArtifactCard::getArtifact($player_id);
            
            // if($endGame) {
            //     $player['heroFaction'] = $this->getCountFactionPlayer($player_id, $winningFaction, false) + $this->checkGameEndSpecialCards($player_id, $winningFaction);
            //     $player['heroTotal'] = intval($this->cards->countCardInlocation('visibleCards', $player_id)) + intval($this->cards->countCardInlocation('hiddenCards', $player_id));
            //     $player['leaderValue'] = $this->LEADER_CARDS[$player['leader_id']]->card_value;
            // }
        }
        
        $result['tavern'] = $this->getCardsInfos($this->cards->getCardsInLocation('tavern'));
        
        $result['cardInPlay'] = $this->getCardsInfos($this->cards->getCardsInLocation('cardInPlay'));
        $result['cardInPick'] = $this->getCardsInfos($this->cards->getCardsInLocation('cardInPick'));
        
        $result['deckTopCard'] = $this->getTopCard('deck');
        $result['discardTopCard'] = $this->getTopCard('discard'); 
        $result['graveyardTopCard'] = $this->getCardInfos($this->cards->getCardOnTop('graveyard'));

        $result['remainingCardsInDeck'] = $this->getRemainingCards('deck');
        $result['remainingCardsInGraveyard'] = $this->getRemainingCards('graveyard');
        $result['remainingCardsInDiscard'] = $this->getRemainingCards('discard');
        
        $result['nbPlayers'] = count($this->getPlayersIds());
        $result['getMaxCardsEndGame'] = $this->getMaxCardsEndGame();

        $result['empireToken'] = intval($this->getGameStateValue(EMPIRE_TOKEN));
        $result['tribesToken'] = intval($this->getGameStateValue(TRIBES_TOKEN));
        
        $result['args'] = [
            'undead' => UNDEAD,
            'water_folk'=> WATER_FOLK,
            'empire'=> EMPIRE,
            'tribes'=> TRIBES,
            'visible'=> VISIBLE,
            'hidden'=> HIDDEN,
            'wilderness'=> WILDERNESS,
            'graveyard'=> GRAVEYARD,
            'harbor'=> HARBOR,
            'tavern'=> TAVERN,
            'player'=> PLAYER,
            'guardian'=> GUARDIAN,
            'artifact'=> ARTIFACT,
            'corruption' => CORRUPTION,
            'slow' => SLOW,
            'fast' => FAST,
            'artifactToken' => ARTIFACTTOKEN
        ];

        $result['endGame'] = $endGame;
        
        //FORGOTTEN LEGENDS
        if($this->isFLExpansion()) {
        $result['guardianToken'] = intval($this->getGameStateValue(GUARDIAN_TOKEN));
        
        $result['fateTopCard'] = $this->getTopFateCard();
        $result['fateCardInPick'] = $this->getCardsInfos($this->cards->getCardsInLocation('fateCardInPick'));
        }
        if($this->isCorruption()) {
            $result['onCorruptionCardTokens']= array_values($this->corruption_tokens->getCardsInLocation('onCorruptionCard'));
            
            $onHeroCardTokens = array_values($this->corruption_tokens->getCardsInLocation('onHeroCard'));
            $result['onHeroCardTokens'] = array_map(fn($onHeroCardToken) => new CorruptionToken($onHeroCardToken, !$endGame && $current_player_id != $onHeroCardToken['type_arg'] && $current_player_id != $this->cards->getCard($onHeroCardToken['location_arg'])['location_arg']), $onHeroCardTokens);
            //$result['corruptionTopToken'] = $this->getTopCorruptionToken();
        }

        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $maxCards = $this->getMaxCardsEndGame();

        $maxVisibles = [];
        foreach ($this->getPlayersIds() as $playerId) {
          $maxVisibles[$playerId] = intval($this->cards->countCardInlocation('visibleCards', $playerId));
        }
        
        return 100 * max(array_values($maxVisibles)) / $maxCards;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
