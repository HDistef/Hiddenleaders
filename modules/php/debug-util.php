<?php

trait DebugUtilTrait {

//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    function debugSetup() {
        if ($this->getBgaEnvironment() != 'studio') { 
            return;
        } 

        $this->debugFillHands();
        $this->debugFillTable();
        //$this->debugSetToken();

        //$this->gamestate->changeActivePlayer(2343492);
    }

    function debugFillHands() {
        //$playersId = $this->getPlayersIds();
        //return self::getUniqueValueFromDB("SELECT * FROM card WHERE card_type_arg = ".HERO_73);

        // for ($i = 104; $i <= 111; $i++) {
        //     $card = $this->cards->getCardsOfType(1,$i);
        //     $this->cards->moveCard(array_key_first($card), 'hand2385354');
        // }
        $this->cards->moveCard(self::getUniqueValueFromDB("SELECT * FROM card WHERE card_type_arg = ".HERO_71), 'hand2385354');
        
    }

    function debugFillTable() {
        $number = 3;
        $playersIds = $this->getPlayersIds();
        foreach($playersIds as $playerId) {
            $this->cards->pickCardsForLocation(3, 'deck', 'visibleCards', $playerId);
            $this->cards->pickCardsForLocation(3, 'deck', 'hiddenCards', $playerId);
        }
       //$this->cards->moveCard(self::getUniqueValueFromDB("SELECT * FROM card WHERE card_type_arg = ".HERO_71), 'visibleCards', '2385355');
        
        for ($i = 1; $i <= 5; $i++) {
            $this->cards->pickCardForLocation('deck','graveyard', $i);
        }
        for ($i = 1; $i <= 5; $i++) {
            $this->cards->pickCardForLocation('deck','discard', $i);
        }
        //$this->cards->pickCardsForLocation(5,'deck','graveyard');

        //$this->cards->pickCardsForLocation(32,'deck','discard');
    }

    function debugSetToken() {
        $this->setGameStateValue( EMPIRE_TOKEN, 12 );
        $this->setGameStateValue( TRIBES_TOKEN, 12);
    }

    function debug($debugData) {
        if ($this->getBgaEnvironment() != 'studio') { 
            return;
        }die('debug data : '.json_encode($debugData));
    }
}
