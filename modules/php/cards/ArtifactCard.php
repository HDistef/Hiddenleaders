<?php

class AllKnowingAntler extends ArtifactCard {
    //Shuffle the ${wilderness}. Search the ${wilderness} and take any 1 card into your hand. Then shuffle the ${wilderness} again
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        if(HiddenLeaders::get()->cards->countCardInLocation('discard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') > 0) {

            $this->jsactions = [ 1 => 'pick_card'];

            $this->jsdescriptions = [ 1 => 'Take any 1 card from ${wilderness} into your hand'];

            $this->phpactions = [ 0 => 'action_AllKnowingAntler'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);

        HiddenLeaders::get()->cards->shuffle('discard');

        Notifications::place($player_id, $args['card'], WILDERNESS, HAND, true);
        
        Notifications::replaceCardInPick_AllKnowingAntler();
    }
}

class BloomingBag extends ArtifactCard {
    //Draw a card from ${tavern} OR from ${graveyard} TODO ASK publisher
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        if(HiddenLeaders::get()->getRemainingCards('tavern') == 0 && HiddenLeaders::get()->getRemainingCards('graveyard') == 0) return;

        $this->jsactions = [ 0 => 'draw_BloomingBag'];

        $this->jsdescriptions = [ 0 => 'Draw a card from ${tavern} OR from ${graveyard}'];
    } 
}

class TwoShotCrossBow extends ArtifactCard {
    //Bury any 1 ${visible} OR ${hidden}
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $this->selectableCards = array_keys(HiddenLeaders::get()->getCollection("SELECT * FROM card WHERE card_location IN ('visibleCards','hiddenCards')"));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'move_card'];
            $this->jsdescriptions = [ 0 => 'Bury any 1 ${visible} OR ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $location = $args['location'] == 'visibleCards' ? VISIBLE : HIDDEN;
        
        if($location == VISIBLE && $args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', HiddenLeaders::get()->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], $location, false);
        }
    }
}

class SedativeShell extends ArtifactCard {
    //Exchange 1 ${hidden} in your party with 1 card in your hand
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $array = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ))
        ];
        $this->selectableCards = $array;
        
        if(count($this->selectableCards[0]) > 0 && count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'exchange'];
            $this->jsdescriptions = [ 0 => 'Exchange 1 ${hidden} in your party with 1 card in your hand'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_2_location'] != 'hand'.$player_id || $args['card_1_location'] != 'hiddenCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hand'.$player_id);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'hiddenCards', $player_id);

        Notifications::exchange($player_id, $player_id, $args['card_1'], $args['card_2'], HIDDEN, HAND, true);
    }
}

class OverchargedTrident extends ArtifactCard {
    //Pick 1 of your ${visible}. Perform that ${visible} abilities as if you played it. Then bury it
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ));

        if(count($this->selectableCards) > 0) {
            $this->jsactions = [0 => 'select_card'];
            $this->jsdescriptions = [ 0 => 'Pick 1 of your ${visible} and perform it'];
        }
        $this->phpactions = [ 1 => 'addCardInPlay', 10 => 'action_InsidiousImpaler'];
    } 
}

class TrappingTreasure extends ArtifactCard {
    //Exchange 1 card from ${tavern} with any 1 ${visible}
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $this->selectOpponentCards('visibleCards');
        $array = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'visibleCards' )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'tavern' ))
        ];
        $this->selectableCards = $array;
        
        if(count($this->selectableCards[0]) > 0 && count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0  => 'exchange'];

            $this->jsdescriptions = [ 0 => 'Exchange 1 card from ${tavern} with any 1 ${visible}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'tavern') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'tavern', $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $args['target_id']);

        Notifications::exchange($player_id, $args['target_id'], $args['card_1'], $args['card_2'], VISIBLE, TAVERN, false );
    }
}

class SuggestivePuppet extends ArtifactCard {
    //Pick 1 card in ${tavern}. Perform that card\'s abilities as if you played it. Then place it ${hidden} in another ${player} party
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        if($effectStep == 0) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('tavern')) ;
            $this->jsactions = [0 => 'select_card'];
            $this->jsdescriptions = [ 0 => 'Take 1 card from ${tavern} and perform it'];
        }
        else {
            $this->getPossibleOpponents('hiddenCards');
            $this->jsactions = [10 => 'select_opponent_GoblinCrytographer'];
            $this->jsdescriptions = [ 10 => 'Place it ${hidden} in another ${player} party :'];

        }
        $this->phpactions = [ 1 => 'addCardInPlay'];
    }

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        $card = HiddenLeaders::get()->cards->getCard($this->getGameStateValue(SELECTED_CARD));

        HiddenLeaders::get()->cards->moveCard($card['id'], 'hiddenCards', $args['target_id']);

        Notifications::place($player_id, $card, TAVERN, HIDDEN, false, $args['target_id']);
    }
}

class SoundlessShoes extends ArtifactCard {
    //Place a card from your hand into your party ${hidden}
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0  => 'move_card'];
            $this->jsdescriptions = [ 0 => 'Place 1 card from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
    }
}

class ConfusingCrystal extends ArtifactCard {
    //Look at 2 ${hidden}. You may place 1 of them ${hidden} into the party of a ${player} other than you TODO ASK publisher
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $look_card_id_1 = $this->getGameStateValue(SELECTED_CARD);
        $look_card_id_2 = $this->getGameStateValue(SELECTED_CARD_2);
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards' ));
        if( count($this->selectableCards) == 0 ) return;
        
        if($effectStep == 0) {
            $this->jsactions = [ 0 => 'look_card'];
            $this->jsdescriptions = [ 0 => 'Look at any 2 ${hidden}'];
        }

        if($effectStep == 1 && $look_card_id_1 != 0) {
            $this->selectableCards = [];
            $this->selectableCards[] = HiddenLeaders::get()->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_1));
            
            if($look_card_id_2 != 0) $this->selectableCards[] = HiddenLeaders::get()->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_2));

            $this->jsactions = [ 1 => 'action_ConfusingCrystal'];
            $this->jsdescriptions = [ 1 => 'Select 1 card'];

        }

        if($effectStep == 2) {
            $this->selectableOpponents = $this->getPossibleOpponents();

            $this->jsactions = [ 2 => 'select_opponent'];
            $this->jsdescriptions = [ 2 => 'Place 1 of them ${hidden} into the party of a ${player} other than you :'];
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        $card_id = $this->getGameStateValue(SELECTED_CARD);
        $card = HiddenLeaders::get()->cards->getCard($card_id);

        HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $args['target_id']);
        
        Notifications::place($player_id, $card, HIDDEN, HIDDEN, true, $args['target_id']);
    }
}

class GuardingGoblet extends ArtifactCard {
    //Take any 1 ${visible} ${guardian} into your hand
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => HiddenLeaders::get()->isGuardian($card['type_arg'])));

        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'move_card'];
            $this->jsdescriptions = [ 0 => 'Take any 1 ${visible} ${guardian} into your hand'];
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);

        Notifications::pick($player_id, $args['target_id'], $args['card'], VISIBLE, HAND, false);
    }
}

class NutritiousStew extends ArtifactCard {
    //Take 1 ${corruption} from the bag and place it onto any 1 ${visible} OR ${hidden} TODO
    public function __construct($id, $name) {
        parent::__constructClass($id, $name);
        
    } 
}
