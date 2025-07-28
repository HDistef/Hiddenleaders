<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HiddenLeaders implementation : © Hervé DI STEFANO hdistef7@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * hiddenleaders.action.php
 *
 * HiddenLeaders main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/hiddenleaders/hiddenleaders/myAction.html", ...)
 *
 */
  
  
  class action_hiddenleaders extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "hiddenleaders_hiddenleaders";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 

    public function playCard()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $id = self::getArg( "id", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->playCard( $id );

        self::ajaxResponse();
    }
    
    public function cardEffect()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $id = self::getArg( "id", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->playCard( $id );

        self::ajaxResponse();
    }

    public function playHiddenCard()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->playHiddenCard( $id );

        self::ajaxResponse();
    }

    public function setup_discard()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->setup_discard( $id );

        self::ajaxResponse();
    }

    public function discard()
    {
        self::setAjaxMode();     

        $card_ids_raw = self::getArg( "ids", AT_numberlist, true );

        if( substr( $card_ids_raw, -1 ) == ';' )
            $card_ids_raw = substr( $card_ids_raw, 0, -1 );
        if( $card_ids_raw == '' )
            $ids = array();
        else
            $ids = explode( ';', $card_ids_raw );

        $anonymize = self::getArg( "anonymize", AT_bool, false, true );
        
        $this->game->discard( $ids, $anonymize );

        self::ajaxResponse();
    }

    public function drawCard()
    {
        self::setAjaxMode();    

        $location = self::getArg( "location", AT_alphanum, true );

        $nb_cards = self::getArg( "nb_cards", AT_posint, false, 1 );

        $this->game->drawCard( $location, $nb_cards );

        self::ajaxResponse();
    }
    
    public function drawCardFromTavern()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->drawCardFromTavern( $id );

        self::ajaxResponse();
    }
    
    public function drawCardFromOpponent()
    {
        self::setAjaxMode();     

        $target_id = self::getArg( "target_id", AT_int, true );
        
        $this->game->drawCardFromOpponent( $target_id );

        self::ajaxResponse();
    }
    
    public function moveToken()
    {
        self::setAjaxMode();    

        $empire_mvt = self::getArg( "empire_mvt", AT_int, true );
        $tribes_mvt = self::getArg( "tribes_mvt", AT_int, true );

        $this->game->moveToken( $empire_mvt, $tribes_mvt );

        self::ajaxResponse();
    }
    
    public function moveCard()
    {
        self::setAjaxMode();     

        $card_ids_raw = self::getArg( "ids", AT_numberlist, true );

        if( substr( $card_ids_raw, -1 ) == ';' )
            $card_ids_raw = substr( $card_ids_raw, 0, -1 );
        if( $card_ids_raw == '' )
            $ids = array();
        else
            $ids = explode( ';', $card_ids_raw );
        
        $this->game->moveCard( $ids );

        self::ajaxResponse();
    }

    public function selectCard()
    {
        self::setAjaxMode();     

        $card_ids_raw = self::getArg( "ids", AT_numberlist, true );

        if( substr( $card_ids_raw, -1 ) == ';' )
            $card_ids_raw = substr( $card_ids_raw, 0, -1 );
        if( $card_ids_raw == '' )
            $ids = array();
        else
            $ids = explode( ';', $card_ids_raw );

        $this->game->selectCard( $ids[0] );

        self::ajaxResponse();
    }
    
    public function lookCard()
    {
        self::setAjaxMode();     

        $card_ids_raw = self::getArg( "ids", AT_numberlist, true );
        
        if( substr( $card_ids_raw, -1 ) == ';' )
            $card_ids_raw = substr( $card_ids_raw, 0, -1 );
        if( $card_ids_raw == '' )
            $ids = array();
        else
            $ids = explode( ';', $card_ids_raw );
        
        $this->game->lookCard( $ids );

        self::ajaxResponse();
    }
    
    public function exchange()
    {
        self::setAjaxMode();    

        $card_1_ids_raw = self::getArg( "card_1_ids", AT_numberlist, true );
        $card_2_ids_raw = self::getArg( "card_2_ids", AT_numberlist, true );
        
        if( substr( $card_1_ids_raw, -1 ) == ';' )
            $card_1_ids_raw = substr( $card_1_ids_raw, 0, -1 );
        if( $card_1_ids_raw == '' )
            $card_1_ids = array();
        else
            $card_1_ids = explode( ';', $card_1_ids_raw );
        
        if( substr( $card_2_ids_raw, -1 ) == ';' )
            $card_2_ids_raw = substr( $card_2_ids_raw, 0, -1 );
        if( $card_2_ids_raw == '' )
            $card_2_ids = array();
        else
            $card_2_ids = explode( ';', $card_2_ids_raw );

        $this->game->exchange( $card_1_ids, $card_2_ids );

        self::ajaxResponse();
    }
    
    public function selectOpponent()
    {
        self::setAjaxMode();     

        $target_id = self::getArg( "target_id", AT_int, true );
        
        $this->game->selectOpponent( $target_id );

        self::ajaxResponse();
    }

    public function surprisedSapling()
    {
        self::setAjaxMode();     

        $target_id = self::getArg( "target_id", AT_int, true );
        
        $this->game->action_SurprisedSapling( $target_id );

        self::ajaxResponse();
    }
    
    public function selectFaction()
    {
        self::setAjaxMode();     

        $faction_id = self::getArg( "faction_id", AT_int, true );
        
        $this->game->selectFaction( $faction_id );

        self::ajaxResponse();
    }
    
    public function switchPlayer()
    {
        self::setAjaxMode();     

        $target_id = self::getArg( "target_id", AT_int, true );
        
        $this->game->switchPlayer( $target_id );

        self::ajaxResponse();
    }
    
    public function playNewCard() {
        self::setAjaxMode();     

        $this->game->playNewCard();
        
        self::ajaxResponse();
    }

    public function performEffect() {
        self::setAjaxMode();     

        $this->game->performEffect();
        
        self::ajaxResponse();
    }

    public function nextEffect()
    {
        self::setAjaxMode();     
        
        $this->game->nextEffect();

        self::ajaxResponse();
    }

    public function noAction()
    {
        self::setAjaxMode();     

        $isArtifact = self::getArg( "isArtifact", AT_bool, false, false );
        
        $this->game->noAction($isArtifact);

        self::ajaxResponse();
    }

    public function playFateCard()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->playFateCard( $id );

        self::ajaxResponse();
    }

    public function bury()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->bury( $id );

        self::ajaxResponse();
    }

    public function handToCardInPick()
    {
        self::setAjaxMode();     

        $id = self::getArg( "id", AT_posint, true );

        $this->game->handToCardInPick( $id );

        self::ajaxResponse();
    }

    public function spreadCorruption()
    {
        self::setAjaxMode();     
        
        $this->game->spreadCorruption();

        self::ajaxResponse();
    }

    public function moveCorruptionToken()
    {
        self::setAjaxMode();     

        $token_id = self::getArg( "token_id", AT_posint, true );
        $card_id = self::getArg( "card_id", AT_posint, true );

        $this->game->moveCorruptionToken($token_id, $card_id);

        self::ajaxResponse();
    }

    public function selectArtifact() {
        self::setAjaxMode();    

        $artifact_id = self::getArg( "artifact_id", AT_int, true );
        $player_id = self::getArg( "player_id", AT_int, true );

        $this->game->selectArtifact( $artifact_id, $player_id );

        self::ajaxResponse();
    }

    public function playArtifact() {
        self::setAjaxMode();    
        
        $this->game->playArtifact();

        self::ajaxResponse();

    }

    public function confusingCrystal() {
        self::setAjaxMode();    

        $card_id = self::getArg( "card_id", AT_int, true );
        $target_id = self::getArg( "target_id", AT_int, true );

        $this->game->action_ConfusingCrystal( $card_id, $target_id );

        self::ajaxResponse();

    }
}
  

