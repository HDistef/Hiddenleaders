<?php
//////////
//TRIBES//
//////////
class DepressedDruid extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class BlindEyeCollector extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class HairyHermit extends HeroCard {
    //OR -2 empire if the empire marker is the leading marker
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken_HairyHermit'];
    } 
}

class SaberToothedTroll extends HeroCard {
    // take a visible from another opponent into your hand
    // They decide +2 or -2 tribes
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->selectOpponentCards('visibleCards');

        if(count($this->selectableCards) == 0 && $effectStep == 0) return;

        $this->jsactions = [ 0 => 'move_card', 2 => 'moveToken_SaberToothedTroll'];

        $this->phpactions = [1 => 'action_ChangeActivePlayer', 3 => 'action_ChangeActivePlayer'];

        $this->jsdescriptions = [ 0 => 'Take a ${visible} from another ${player}', 1 => 'Move Token :'];
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);

        Notifications::pick($player_id, $args['target_id'], $args['card'], VISIBLE, HAND, false);

        $this->setGameStateValue(SELECTED_PLAYER, $args['target_id']);
    }
}

class LongEaredLoner extends HeroCard {
    // pick another opponent, turn over 1 of their visible or hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('visibleCards');
        $this->selectOpponentCards('hiddenCards');
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Turn over 1 opponent ${visible} or ${hidden}'];
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $flip_from = $args['card']['location'] == 'visibleCards' ?  VISIBLE : HIDDEN;
        $flip_to = $flip_from == VISIBLE ? HIDDEN : VISIBLE;
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], $args['card']['location'] == 'visibleCards' ? 'hiddenCards' : 'visibleCards', $args['target_id']);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class PotatoPrivateer extends HeroCard {
    // pick another opponent, x is the number of visible empire cards in their party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'select_opponent_PotatoPrivateer'];

        $this->jsdescriptions = [ 0 => 'Number of ${visible} ${empire} :'];

        $this->selectableOpponents = $this->getPlayersIds();
    } 
}

class ShakySharpshooter extends HeroCard {
    //Bury any 1 visible empire card
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == EMPIRE || $card['type'] == EMPEROR));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Bury any 1 ${visible} ${empire} :'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        if ($args['card']['type'] != EMPIRE && $args['card']['type'] != EMPEROR) {
            throw new BgaSystemException("Not a empire card");
        }
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class SpiritedShaman extends HeroCard {
    // pick another opponent, turn over of their visible or hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('visibleCards');
        $this->selectOpponentCards('hiddenCards');

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Turn over 1 opponent ${visible} or ${hidden} :'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if (!in_array($args['location'], ['visibleCards','hiddenCards'])) {
            throw new BgaSystemException("Not a visible or hidden card.");
        }

        $flip_from = $args['location'] == 'visibleCards' ?  VISIBLE : HIDDEN;
        $flip_to = $flip_from == VISIBLE ? HIDDEN : VISIBLE;
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], $args['location'] == 'visibleCards' ? 'hiddenCards' : 'visibleCards', $args['target_id']);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class GrumpyGuard extends HeroCard {
    //+2 tribes if you have 1 or more visible water folk card in your party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [ 0 => 'action_GrumpyGuard'];
    } 
}

class OverworkedAmazon extends HeroCard {
    //Pick 1 opponent. He has to bury 1 visible card
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 0 => 'moveToken', 1 => 'switch_player', 2 => 'move_card'];

        $this->jsdescriptions = [ 1 => 'Pick 1 ${player} :', 2 => 'Bury 1 of your ${visible} :'];

        $this->phpactions = [3 => 'action_ChangeActivePlayer'];

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ));
        
        $this->selectableOpponents = array_values(array_filter($this->getPlayersIds(), fn($playerId) => intval(HiddenLeaders::get()->cards->countCardInLocation('visibleCards', $playerId)) > 0));

        if($effectStep == 1 && count($this->selectableOpponents) == 0) {
            $this->jsactions = [];
            $this->jsdescriptions = [];
            $this->phpactions = [];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class HangryBarbarian extends HeroCard {
    //Discard all empire and undead from tavern. x is the number of cards discarded
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [ 0 => 'action_HangryBarbarian'];
    } 
}

class JoylessChief extends HeroCard {
    //Look at the top 2 cards from discard
    //Place 1 of them into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        //$this->selectableCards = $this->getCardsInfos(HiddenLeaders::get()->cards->getCardsOnTop(2, 'discard'));
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));
        
        if(HiddenLeaders::get()->cards->countCardInLocation('discard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];
            $this->jsdescriptions = [ 2 => 'Place 1 card into your party ${hidden}'];
            
            $this->phpactions = [ 1 => 'action_JoylessChief'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], WILDERNESS, HIDDEN, true);

        HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_id']), true, WILDERNESS);
    }
}

class BattlePetMaster extends HeroCard {
    //Place 1 card from your hand into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Place 1 card from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
    }
}

class CuriousTroll extends HeroCard {
    //You may look at any 2 hidden cards
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        $look_card_id_1 = $this->getGameStateValue(SELECTED_CARD);
        $look_card_id_2 = $this->getGameStateValue(SELECTED_CARD_2);

        $this->selectOpponentCards('hiddenCards');
        $nbSelectableCards = count($this->selectableCards);

        if($nbSelectableCards == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'look_card'];

            $this->jsdescriptions = [ 1 => 'You may look at any 2 ${hidden}'];
        }

        if($effectStep == 2 && $look_card_id_1 != 0) {
            $this->jsactions = [ 2 => 'end_look_card'];
            $this->jsdescriptions = [ 2 => 'Stop Looking :'];
            $this->selectableCards = [];
            $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_1));
            
            if($look_card_id_2 != 0) $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_2));
        }
    } 
}

class PigmentedWarPig extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class BoredGoblin extends HeroCard {
    //-2 empire if you have 1 or more visible undead in your party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->phpactions = [ 0 => 'action_BoredGoblin'];
    } 
}

class WatchfulWitch extends HeroCard {
    //You may look at any 2 hidden cards
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        $look_card_id_1 = $this->getGameStateValue(SELECTED_CARD);
        $look_card_id_2 = $this->getGameStateValue(SELECTED_CARD_2);

        $this->selectOpponentCards('hiddenCards');
        $nbSelectableCards = count($this->selectableCards);

        if($nbSelectableCards == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'look_card'];

            $this->jsdescriptions = [ 1 => 'You may look at any 2 ${hidden}'];
        }

        if($effectStep == 2 && $look_card_id_1 != 0) {
            $this->jsactions = [ 2 => 'end_look_card'];
            $this->jsdescriptions = [ 2 => 'Stop Looking :'];
            $this->selectableCards = [];
            $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_1));
            
            if($look_card_id_2 != 0) $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_2));
        }
    }
}

class CuriousCatLover extends HeroCard {
    //Draw 1 card from another player hand. Place it in your party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken', 1 => 'draw_opponent'];

        $this->jsdescriptions = [ 1 => 'Draw 1 card from another ${player} hand into your party ${hidden}'];

        $this->selectableOpponents = $this->getPossibleOpponents();
    } 

    public function drawCardFromOpponent($args)
    {
        $player_id = intval($this->getActivePlayerId());

        $target_id = $args['target_id'];

        HiddenLeaders::get()->cards->shuffle('hand'.$target_id);

        $card = HiddenLeaders::get()->cards->pickCardForLocation('hand'.$target_id, 'temp');

        HiddenLeaders::get()->cards->moveCard($card['id'], 'hiddenCards', $player_id);

        if ($card == null) throw new BgaUserException("Invalid card");
        
        Notifications::pick($player_id, $target_id, $card, HAND, HIDDEN, true);
    }
}

//////////
//EMPIRE//
//////////
class ShortSightedSoldier extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    }
}

class DoubtfulPriest extends HeroCard {
    //next player decides +2 or -2 empire. You may play another card that is not empire
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 1 => 'moveToken_DoubtfulPriest'];

        $this->setGameStateValue(SELECTED_PLAYER, HiddenLeaders::get()->getPlayerAfter( $this->getActivePlayerId() ));

        $this->phpactions = [ 0 => 'action_ChangeActivePlayer', 2 => 'action_ChangeActivePlayer', 3 => 'playNewCard'];

        if($effectStep == 3 && count(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('hand'.$this->getActivePlayerId()), fn($card) => $card['type'] != EMPIRE)) == 0) {
            $this->phpactions = [];
        }
    }
}

class UnderestimatedSquire extends HeroCard {
    // You may exchange 1 of your hidden with 1 card from your hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'You may exchange 1 of your ${hidden} with 1 card from your hand'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'hiddenCards' || $args['card_2_location'] != 'hand'.$this->getActivePlayerId()) {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hand'.$player_id);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'hiddenCards', $player_id);

        Notifications::exchange($player_id, $player_id, $args['card_1'], $args['card_2'], HIDDEN, HAND, true);
    }
}

class FlailingKnight extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    }
}

class UnderpaidMercenary extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    }
}

class HeartBendingBard extends HeroCard {
    // discard all wf and tribes from tavern => -x tribes
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [ 0 => 'action_HeartBendingBard'];
    }
}

class ModestMonsterslayer extends HeroCard {
    //take 1 card from tavern, perform it then go to hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('tavern'));

        if(count($this->selectableCards) > 0) {
            $this->jsactions = [0 => 'select_card'];
            $this->jsdescriptions = [ 0 => 'Take 1 card from ${tavern} and perform it'];
        }
        $this->phpactions = [ 1 => 'addCardInPlay', 10 => 'action_ModestMonsterslayer'];
    }
}

class AceFighter extends HeroCard {
    //+1 or +2 empire if the empire is the leading marker
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken_AceFighter'];
    }
}

class BattleConnoisseur extends HeroCard {
    // take all from tavern. Place 1 of them hidden, discard other
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('tavern'));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Place a card from ${tavern} into your party ${hidden}'];
        }
    }
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
        Notifications::place($player_id, $args['card'], TAVERN, HIDDEN, false);

        HiddenLeaders::get()->discard(array_filter($this->selectableCards, fn($card_id) => $card_id != $args['card_id']), false, TAVERN);
    }
}

class CannedChampion extends HeroCard {
    // bury any visible tribes card
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == TRIBES || $card['type'] == EMPEROR));

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Bury any ${visible} ${tribes}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        if ($args['card']['type'] != TRIBES && $args['card']['type'] != EMPEROR) {
            throw new BgaSystemException("Not a tribes card");
        }
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class AlmostEvilScholar extends HeroCard {
    // pick another opponent, x is the number of visible tribes cards in their party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'select_opponent_AlmostEvilScholar'];

        $this->jsdescriptions = [ 0 => 'Number of ${visible} ${tribes} :'];

        $this->selectableOpponents = $this->getPlayersIds();
    } 
}

class WellAgedWarrior extends HeroCard {
    //Place 1 card from your hand into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Place 1 card from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
    }
}

class AndrogynousAssassin extends HeroCard {
    // guess 1 faction. Turn over 1 hidden of another player. Bury that hidden if right faction
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('hiddenCards');

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'select_faction', 2 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Select a faction :', 2 => 'Turn over 1 ${hidden} of another ${player}'];
        }
    }

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if($args['card']['type'] == $this->getGameStateValue(SELECTED_FACTION) || $args['card']['type'] == EMPEROR) {
            // bury
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            
            Notifications::bury($player_id, $args['target_id'], $args['card'], HIDDEN, false);
        }
        else {
            // flip
            $flip_from = HIDDEN;
            $flip_to = VISIBLE;
            
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'visibleCards', $args['target_id']);

            Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
        }
    }
}

class GroggyPreacher extends HeroCard {
    // exchange 1 card from tavern with 1 of your visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'visibleCards', $this->getActivePlayerId() )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'tavern' ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 1 card from ${tavern} with 1 of your ${visible}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'tavern') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'tavern', $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);

        Notifications::exchange($player_id, null, $args['card_1'], $args['card_2'], VISIBLE, TAVERN, false);
    }
}

class QueerQuartermaster extends HeroCard {
    // you may exchange 1 of your visible that is not empire with 1 card in your hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = [
            0 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ), fn($card) => $card['type'] != EMPIRE)),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'You may exchange 1 card of your ${visible} that is not ${empire} with 1 card in your hand'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'hand'.$this->getActivePlayerId()) {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hand'.$player_id);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);

        Notifications::exchange($player_id, $player_id, $args['card_1'], $args['card_2'], VISIBLE, HAND, false);
    }
}

class NaggingNorthman extends HeroCard {
    // +2 empire if you discard a tribes from tavern
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effect_step = $this->getGameStateValue(EFFECT_STEP);
        
        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('tavern'), fn($card) => $card['type'] == TRIBES || $card['type'] == EMPEROR));

        if(count($this->selectableCards) == 0 && $effect_step == 0) return;

        $this->jsactions = [ 0 => 'discard'];

        $this->jsdescriptions = [ 0 => '+2 ${empire_token} if you discard a ${tribes} from ${tavern}'];

        if($this->getGameStateValue(NB_DISCARDED_CARD) > 0) $this->phpactions = [1 => 'action_NaggingNorthman'];
    }
}

class AngryPriestess extends HeroCard {
    // -2 tribes if you discard a undead from tavern
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effect_step = $this->getGameStateValue(EFFECT_STEP);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('tavern'), fn($card) => $card['type'] == UNDEAD || $card['type'] == EMPEROR));

        if(count($this->selectableCards) == 0 && $effect_step == 0) return;

        $this->jsactions = [ 0 => 'discard'];

        $this->jsdescriptions = [ 0 => '-2 ${tribes_token} if you discard a ${undead} from ${tavern}'];

        if($this->getGameStateValue(NB_DISCARDED_CARD) > 0) $this->phpactions = [1 => 'action_AngryPriestess'];
    }
}

class ResilientRearguard extends HeroCard {
    // draw 1 card from another player hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken', 1 => 'draw_opponent'];

        $this->jsdescriptions = [ 1 => 'Draw 1 card from another ${player}'];

        $this->selectableOpponents = $this->getPossibleOpponents();
    } 

    public function drawCardFromOpponent($args)
    {
        $player_id = intval($this->getActivePlayerId());

        $target_id = $args['target_id'];

        HiddenLeaders::get()->cards->shuffle('hand'.$target_id);

        $card = HiddenLeaders::get()->cards->pickCardForLocation('hand'.$target_id, 'hand'.$player_id);
        
        if ($card == null) throw new BgaUserException("Invalid card");
        
        Notifications::pick($player_id, $target_id, $card, HAND, HAND, true);
    }
}
//////////
//UNDEAD//
//////////
class ArrowgantSkeleton extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class UnconfidentExecutioner extends HeroCard {
    // bury any 1 visible card
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'));

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Bury any 1 ${visible} card'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class GhastlyGranny extends HeroCard {
    //pick 1 of your visible. Perform that card's abilities as if you played it 
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ), fn($card) => $card['type_arg'] != HERO_39));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [0 => 'select_card'];

            $this->jsdescriptions = [ 0 => 'Perform effects from 1 of your ${visible}'];
        }
        $this->phpactions = [ 1 => 'performEffect'];
    }
}

class RottingOrangutan extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class HalfEatenBull extends HeroCard {
    //Bury any water folk visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == WATER_FOLK || $card['type'] == EMPEROR));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Bury any 1 ${visible} ${water_folk}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class NightmarishNorthman extends HeroCard {
    //Bury 1 of your visible and 1 visible of another player
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken'];

        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId())),
            1 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['location_arg'] != $this->getActivePlayerId()))
        ];

        if(count($this->selectableCards[0]) > 0 || count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $description = 'Bury 1 of your ${visible} and 1 ${visible} of another ${player}';
            if(count($this->selectableCards[1]) == 0) $description = 'Bury 1 of your ${visible}';

            $this->jsdescriptions = [ 1 => $description];
        }

        // $effect_step = $this->getGameStateValue(EFFECT_STEP);

        // $cardsActivePlayer = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId()));
        // $cardsOpponents = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['location_arg'] != $this->getActivePlayerId()));

        // if($effect_step == 1) {
        //     if(count($cardsActivePlayer) > 0) {
        //         $this->selectableCards = $cardsActivePlayer;

        //         $this->jsactions = [ 1 => 'move_card'];
        //         $this->jsdescriptions = [ 1 => 'Bury 1 of your ${visible}'];
        //     }
        //     else if (count($cardsOpponents) > 0) {
        //         $this->selectOpponentCards('visibleCards');

        //         $this->jsactions = [ 1 => 'move_card'];
        //         $this->jsdescriptions = [ 1 => 'Bury 1 ${visible} of another ${player}'];
        //     }
        // }

        // if($effect_step == 2 && count($cardsOpponents) > 0) {
        //     $this->selectOpponentCards('visibleCards');

        //     $this->jsactions = [ 2 => 'move_card'];
        //     $this->jsdescriptions = [ 2 => 'Bury 1 ${visible} of another ${player}'];
        // }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
                Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
            }
            else {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
                Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
            }
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                if($card['location_arg'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $card['type_arg'] != KeeperOfDiscord) {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $card['location_arg']);
                    Notifications::flip($player_id, $card['location_arg'], $card, VISIBLE, HIDDEN);
                }
                else {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);
                    Notifications::bury($player_id, $card['location_arg'], $card, VISIBLE, false);
                }
            }
        }
    }
}

class MummyMystic extends HeroCard {
    //you may exchange the top card from graveyard with 1 your visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        if(HiddenLeaders::get()->getRemainingCards('graveyard') == 0) return;

        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'visibleCards', $this->getActivePlayerId() )),
            1 => intval(HiddenLeaders::get()->cards->getCardOnTop( 'graveyard' )['id'])
        ];
        
        if(count($this->selectableCards[0]) > 0 && $this->selectableCards[1] != null) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];

            $this->jsdescriptions = [ 1 => 'You may select 1 of your ${visible} to exchange with the top card from ${graveyard}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'graveyard') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);

        Notifications::exchange($player_id, null, $args['card_1'], $args['card_2'], VISIBLE, GRAVEYARD, false);

        self::incStat( 1, "takenFromGraveyard", $player_id );
    }
}

class InsidiousImpaler extends HeroCard {
    //pick 1 in tavern. Perform that card's abilities as if you played it then bury it
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('tavern'));

        if(count($this->selectableCards) > 0) {
            $this->jsactions = [0 => 'select_card'];
            $this->jsdescriptions = [ 0 => 'Take 1 card from ${tavern} and perform it'];
        }
        $this->phpactions = [ 1 => 'addCardInPlay', 10 => 'action_InsidiousImpaler'];
    }
}

class GorgeousGorgon extends HeroCard {
    //pick 1 player. Bury 1 of their visible at random
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableOpponents = array_values(array_filter($this->getPlayersIds(), fn($playerId) => intval(HiddenLeaders::get()->cards->countCardInLocation('visibleCards', $playerId)) > 0));

        if(count($this->selectableOpponents) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'draw_opponent'];
            $this->jsdescriptions = [ 1 => 'Bury 1 of the selected opponent ${visible} at random'];
        }
    } 

    public function drawCardFromOpponent($args)
    {
        $player_id = intval($this->getActivePlayerId());

        $target_id = $args['target_id'];

        $card_ids = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $target_id));

        $rand_index = bga_rand(0, count($card_ids) - 1);

        $card_id = $card_ids[$rand_index];
        if ($card_id == null) throw new BgaUserException("Invalid card");

        $card = HiddenLeaders::get()->cards->getCard($card_id);
        if ($card == null) throw new BgaUserException("Invalid card");
        
        if($target_id == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $card['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $target_id);
            Notifications::flip($player_id, $target_id, $card, VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $target_id, $card, VISIBLE, false);
        }
    }
}

class CrowCarrier extends HeroCard {
    //draw 2 cards from graveyard. Place 1 of them into your party hidden. Discard the other
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        if(HiddenLeaders::get()->cards->countCardInLocation('graveyard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];
            $this->jsdescriptions = [ 2 => 'Place 1 card into your party ${hidden}'];
            
            $this->phpactions = [ 1 => 'action_CrowCarrier'];
        }
    }
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
        Notifications::place($player_id, $args['card'], GRAVEYARD, HIDDEN, true);

        HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_id']), true, GRAVEYARD);

        self::incStat( 1, "takenFromGraveyard", $player_id );
    }
}

class ResurrectedRam extends HeroCard {
    //Discard all cards from tavern. Then refill tavern with the top cards from graveyard
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        $this->phpactions = [ 1 => 'action_ResurrectedRam'];
    }
}

class HalfHeadedWizard extends HeroCard {
    //Exchange 1 card from tavern with 1 visible of another player
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('visibleCards');
        $array = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'tavern' )),
            1 => $this->selectableCards
        ];
        $this->selectableCards = $array;
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];

            $this->jsdescriptions = [ 1 => 'Exchange 1 card from ${tavern} with 1 ${visible} of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'tavern' || $args['card_2_location'] != 'visibleCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'visibleCards', $args['card_2']['location_arg'] );
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'tavern', $args['card_1']['location_arg']);

        Notifications::exchange($player_id, $args['card_2']['location_arg'], $args['card_1'], $args['card_2'], TAVERN, VISIBLE, false);
    }
}

class NaughtyNecromancer extends HeroCard {
    //X is the number of cards in graveyard
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [0 => 'action_NaughtyNecromancer'];
    } 
}

class LethargicLeech extends HeroCard {
    //Place 1 card from your hand into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Place 1 card from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
    }
}

class SunShySkeleton extends HeroCard {
    //Perform the abilities of top card from graveyard as if you played it
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = ['action_SunShySkeleton'];
    } 
}

class WillBendingWitch extends HeroCard {
    //All other players have to discard 1 card from their hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        $this->phpactions = [ 1 => 'action_WillBendingWitch'];
    } 
}

class WrappedWarrior extends HeroCard {
    //Take any 1 card from graveyard into your hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        if(HiddenLeaders::get()->cards->countCardInLocation('graveyard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];

            $this->jsdescriptions = [ 2 => 'Take any 1 card from ${graveyard} into your hand'];

            $this->phpactions = [ 1 => 'action_WrappedWarrior'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);

        Notifications::place($player_id, $args['card'], GRAVEYARD, HAND, true);
        
        self::incStat( 1, "takenFromGraveyard", $player_id );

        Notifications::replaceCardInPick_WrappedWarrior();
    }
}

class SlaughteredSlime extends HeroCard {
    //Bury any hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards'));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Bury any 1 ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);

        Notifications::bury($player_id, $args['target_id'], $args['card'], HIDDEN, false);
    }
}
/////////////
//WATERFOLK//
/////////////
class PessimisticWhaleman extends HeroCard {
    //Draw 2 cards from harbor. Place 1 of them into your party hidden. Discard the other
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];

        $this->jsdescriptions = [ 2 => ' Place 1 into your party ${hidden}'];
            
        $this->phpactions = [ 1 => 'action_DrawFromDeck'];
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
        Notifications::place($player_id, $args['card'], HARBOR, HIDDEN, true);

        HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_id']), true, HARBOR);

    }
}

class TentacleOracle extends HeroCard {
    //Reveal the top card from wilderness. -3 and -3 if not undead
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [ 0 => 'action_TentacleOracle'];
    } 
}

class DrownedDeserter extends HeroCard {
    //if there is a leading marker, -1 leading marker or +2 trailing marker
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken_DrownedDeserter'];
    } 
}

class DeepSeaSquire extends HeroCard {
    //Draw 2 cards from harbor. Place 1 of them into your party hidden. Keep 1 in hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];

        $this->jsdescriptions = [ 2 => ' Place 1 into your party ${hidden} (the other will go in your hand)'];

        $this->phpactions = [ 1 => 'action_DrawFromDeck'];
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
        Notifications::place($player_id, $args['card'], HARBOR, HIDDEN, true);

        foreach(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick') as $card_id => $card) {
            HiddenLeaders::get()->cards->moveCard($card_id, 'hand'.$player_id);
            Notifications::place($player_id, $card, HARBOR, HAND, true);
        }
    }
}

class VegetarianSharkguard extends HeroCard {
    //Discard 1 visible of another player
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('visibleCards');

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Discard 1 ${visible} of another ${player}'];
        }

    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'discard', $this->getLocationArg('discard') + 1);

        Notifications::place($player_id, $args['card'], VISIBLE, WILDERNESS, false, $args['target_id']);
    }
}

class DoubleShieldedTurtle extends HeroCard {
    // Pick 1 faction, Discard all cards of this faction in tavern. -x and/or -x is the number of cards discarded
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        if(HiddenLeaders::get()->cards->countCardInLocation('tavern') == 0 && $effectStep == 0) return;
        
        $this->token_action[1] = - $this->getGameStateValue(NB_DISCARDED_CARD);
        $this->token_action[2] = - $this->getGameStateValue(NB_DISCARDED_CARD);

        $this->jsactions = [ 0 => 'select_faction', 2 => 'moveToken'];

        $this->jsdescriptions = [ 0 => 'Select a faction :'];

        $this->phpactions = [1 => 'action_DoubleShieldedTurtle'];
    }
}

class LeeryLizard extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class FuriousFrog extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class ApatheticWaterpriest extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class HopefulSalamander extends HeroCard {
    //Take 1 card from tavern. Place it in your party visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('tavern'));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Place 1 card from ${tavern} into your party ${visible}'];
        }
    }
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'visibleCards', $player_id);

        Notifications::place($player_id, $args['card'], TAVERN, VISIBLE, false);
    }
}

class KeenKoi extends HeroCard {
    //if there is a leading marker, -1 leading marker and +1 trailing marker
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [ 0 => 'action_KeenKoi'];
    } 
}

class SaltwaterSage extends HeroCard {
    // Turn over 1 of your hidden. Perform that cards as if you played it
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() ));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'move_card'];
            $this->jsdescriptions = [ 0 => 'Turn over 1 of your ${hidden} and perform its effects'];
        }
        $this->phpactions = [ 1 => 'performEffect'];
    }

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $this->setGameStateValue(SELECTED_CARD, $args['card_id']);

        $flip_from = HIDDEN;
        $flip_to = VISIBLE;
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'visibleCards', $player_id);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class MiniatureMerman extends HeroCard {
    // Draw 2 cards either from wilderness or graveyard
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken', 1 => 'draw_MiniatureMerman'];

        $this->jsdescriptions = [ 1 => 'Draw 2 cards :'];

        if(HiddenLeaders::get()->getRemainingCards('graveyard') == 0 && HiddenLeaders::get()->getRemainingCards('discard') == 0) $this->jsactions = [ 0 => 'moveToken'];
    }
}

class TripleSwordLizard extends HeroCard {
    //if there is a leading marker, -1 leading marker or -3 leading marker
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken_TripleSwordLizard'];
    } 
}

class KrillKeeper extends HeroCard {
    // pick another opponent, -x is the number of visible undead cards in their party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'select_opponent_KrillKeeper'];

        $this->jsdescriptions = [ 0 => 'Number of ${visible} ${undead} :'];

        $this->selectableOpponents = $this->getPlayersIds();
    } 
}

class AimlessEel extends HeroCard {
    // bury any visible undead card
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == UNDEAD || $card['type'] == EMPEROR));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Bury any ${visible} ${undead}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card']['type'] != UNDEAD && $args['card']['type'] != EMPEROR) {
            throw new BgaSystemException("Not a undead card");
        }
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class BludgeoningBlowfish extends HeroCard {
    // turn over 1 hidden of another player or look at any 1 hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        $look_card_id = $this->getGameStateValue(SELECTED_CARD);
        
        $this->selectOpponentCards('hiddenCards');

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'action_BludgeoningBlowfish', 2 => 'end_look_card'];

            $this->jsdescriptions = [ 1 => 'Turn over or look at 1 ${hidden} of another ${player}', 2 => 'Stop Looking'];

            if($effectStep == 2) {
                $this->selectableCards = [];
                $look_card_id == 0 ? $this->jsactions = [] : $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id));
            }
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $flip_from = HIDDEN;
        $flip_to = VISIBLE;
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'visibleCards', $args['target_id']);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class FriendlyFrogmage extends HeroCard {
    //Place 1 card from your hand into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Place 1 card from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
    }
}

class BuriedEmperor extends HeroCard {
    //this card is 1 Hero representing all factions at any time.
}

class KindKingSlayer extends HeroCard {
    //IF there is a leading marker,-2 leading marker, Bury 1 hidden or visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->phpactions = [0 => 'action_KindKingSlayer'];

        $this->selectableCards = array_merge(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards')), array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards')));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Bury 1 ${hidden} or ${visible}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);

        $location = $args['location'] == 'visibleCards' ? VISIBLE : HIDDEN;
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord && $location == VISIBLE) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], $location, false);
        }
    }
}

class HalfSlicedGhoul extends HeroCard {
    //Turn over 1 hidden of another player IF this card is empire or tribes : +2 empire AND/OR +2 tribes
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->selectOpponentCards('hiddenCards');

        if($effectStep == 0 && count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'move_card'];
            $this->jsdescriptions = [ 0 => 'Turn over 1 ${hidden} of another ${player}'];
        }
        if($effectStep == 1 && in_array($this->getGameStateValue(SELECTED_FACTION), [EMPIRE, TRIBES, EMPEROR])) $this->jsactions = [ 1 => 'moveToken'];
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $flip_from = HIDDEN;
        $flip_to = VISIBLE;
        
        $this->setGameStateValue(SELECTED_FACTION, $args['card']['type']);

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'visibleCards', $args['target_id']);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class CarelessCartographer extends HeroCard {
    //+1 empire AND/OR +1 tribes. Exchange 1 hidden of another player with 1 of your hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectOpponentCards('hiddenCards');
        $array = [
            0 => $this->selectableCards,
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() ))
        ];
        $this->selectableCards = $array;
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'You may exchange 1 of your ${hidden} with 1 ${hidden} card of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'hiddenCards' || $args['card_2_location'] != 'hiddenCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hiddenCards', $player_id);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'hiddenCards', $args['card_1']['location_arg']);

        Notifications::exchange($player_id, $args['card_1']['location_arg'], $args['card_1'], $args['card_2'], HIDDEN, HIDDEN, true, 'CarelessCartographer');
    }
}

class WellShavedWizard extends HeroCard {
    //+X empire OR -X tribes. X is the number of factions other than empire in your party
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $player_id = intval($this->getActivePlayerId());

        $emperor = HiddenLeaders::get()->cards->getCard(HiddenLeaders::get()->getEmperor());

        if($emperor['location'] == 'visibleCards' && $emperor['location_arg'] == $player_id) $nbFactions = 3;
        else {
            $visibleCards = array_column(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $player_id), 'type');
            $nbFactions = count(array_unique(array_filter($visibleCards, fn($card_type) => $card_type != EMPIRE && $card_type != EMPEROR)));
        }

        if($nbFactions == 0) return;
        
        $empire_mvt = HiddenLeaders::get()->checkTokenLimit($nbFactions, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = HiddenLeaders::get()->checkTokenLimit(- $nbFactions, $this->getGameStateValue(TRIBES_TOKEN));

        $this->token_action[1] = intval($empire_mvt);
        $this->token_action[2] = intval($tribes_mvt);
        
        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class SanguineScholar extends HeroCard {
    //Choose 1 visible from the player to your left Perform this card as if you played it
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards',  HiddenLeaders::get()->getPlayerAfter( $this->getActivePlayerId() ) ));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [0 => 'select_card'];

            $this->jsdescriptions = [ 0 => 'Perform effects from 1 ${visible} of the next ${player}'];
        }
        $this->phpactions = [ 1 => 'performEffect'];
    }
}

class GoblinCrytographer extends HeroCard {
    //-1 empire AND/OR -1 tribes. You may take 1 visible and place it in the party of a player other than you
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        if(count(HiddenLeaders::get()->getPlayersIds()) == 2) $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ));
        else $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'));
        
        if($effectStep == 0) $this->jsactions = [ 0 => 'moveToken'];

        if($effectStep == 1 && count($this->selectableCards) > 0) {
            $this->jsactions = [ 1 => 'select_card'];
            $this->jsdescriptions = [ 1 => 'Take any 1 ${visible} card'];

        } 
        if($effectStep == 2 && $this->getGameStateValue(SELECTED_CARD) != 0) {
            $selectedCard_Player = HiddenLeaders::get()->cards->getCard($this->getGameStateValue(SELECTED_CARD))['location_arg'];
            $this->selectableOpponents = array_values(array_filter(HiddenLeaders::get()->getPlayersIds(), fn($playerId) => $playerId != $this->getActivePlayerId() && $playerId != $selectedCard_Player));
            
            $this->jsactions = [ 2 => 'select_opponent_GoblinCrytographer'];
            $this->jsdescriptions = [ 2 => 'Place it in the party of a ${player} other than you'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        $card = HiddenLeaders::get()->cards->getCard($this->getGameStateValue(SELECTED_CARD));
        $player_from = $card['location_arg'];
        $player_to = $args['target_id'];
        HiddenLeaders::get()->cards->moveCard($card['id'], 'visibleCards', $player_to);

        Notifications::placeGoblinCrytographer($player_id, $card, $player_from, $player_to);
    }
}

class FirmFishmonger extends HeroCard {
    //+1 empire AND/OR +1 tribes. Look at the top 3 cards from wilderness. Exchange 1 of them with 1 of your hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
        
        if(HiddenLeaders::get()->cards->countCardInLocation('hiddenCards', $this->getActivePlayerId()) > 0 && HiddenLeaders::get()->cards->countCardInLocation('discard') > 0) $this->phpactions = [ 1 => 'action_FirmFishmonger'];

        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'cardInPick' )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) return;
        
        $this->jsactions = [ 0 => 'moveToken', 2 => 'exchange'];
        $this->jsdescriptions = [ 2 => 'Exchange 1 of them with 1 of your ${hidden}'];

    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'cardInPick' || $args['card_2_location'] != 'hiddenCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_1_id']), true, WILDERNESS);
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hiddenCards', $player_id);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'discard', $this->getLocationArg('discard') + 1);

        Notifications::exchange($player_id, $player_id, $args['card_1'], $args['card_2'], WILDERNESS, HIDDEN, true);

    }
}

class PolarProtector extends HeroCard {
    //-X empire OR +X tribes. X is the number of hidden in your party (max +/-3)
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $nbHiddens = HiddenLeaders::get()->cards->countCardInLocation('hiddenCards', $this->getActivePlayerId());
        
        if($nbHiddens == 0) return;

        if($nbHiddens > 3) $nbHiddens = 3;

        $empire_mvt = HiddenLeaders::get()->checkTokenLimit(-$nbHiddens, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = HiddenLeaders::get()->checkTokenLimit($nbHiddens, $this->getGameStateValue(TRIBES_TOKEN));

        $this->token_action[1] = intval($empire_mvt);
        $this->token_action[2] = intval($tribes_mvt);
        
        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class GamblingOverseer extends HeroCard {
    //-1 empire AND -1 tribes. Bury 1 of your hidden. Then place 2 cards from your hand into your party hidden
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 0 => 'moveToken'];

        if($effectStep == 1) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards', $this->getActivePlayerId() ));

            if(count($this->selectableCards) > 0) {
                $this->jsactions = [ 1 => 'move_card_GamblingOverseer_bury'];
                $this->jsdescriptions = [ 1 => 'Bury 1 of your ${hidden} :'];
            }
            else {
                $this->phpactions = [ 1 => 'nextEffect'];
            }
        }

        if($effectStep == 2) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hand'.$this->getActivePlayerId() ));

            if(count($this->selectableCards) > 0) { 
                $this->jsactions = [ 2 => 'move_card_GamblingOverseer_hand'];
                $this->jsdescriptions = [ 2 => 'Place 2 cards from your hand into your party ${hidden}'];
            }
        }
    } 
    
    public function moveCard($args) {
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $player_id = intval($this->getActivePlayerId());
        
        if($effectStep == 1) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);

            Notifications::bury($player_id, $args['target_id'], $args['card'], HIDDEN, false);
        }
        else {
            if(array_key_exists('card_id', $args)) {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

                Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
            }
            else {
                foreach($args['cards'] as $card_id => $card) {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $player_id);

                    Notifications::place($player_id, $card, HAND, HIDDEN, true);
                }
            }
        }
    }
}

class CarefulChameleon extends HeroCard {
    //+1 empire OR +1 tribes. Exchange 1 card from your hand with 1 visible of another player
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectOpponentCards('visibleCards');
        $array = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() )),
            1 => $this->selectableCards
        ];
        $this->selectableCards = $array;
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 1 from your hand with 1 ${visible} of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'hand'.$this->getActivePlayerId() || $args['card_2_location'] != 'visibleCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'visibleCards', $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'hand'.$player_id);

        Notifications::exchange($player_id, $args['card_2']['location_arg'], $args['card_1'], $args['card_2'], HAND, VISIBLE, false);
    }
}

class SeaweedChopper extends HeroCard {
    //-1 empire OR -1 tribes. Draw 2 cards from wilderness. Put 1 of them into your party hidden. Keep 1 in hand
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        if(HiddenLeaders::get()->cards->countCardInLocation('discard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];

            $description = HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 2 ? 'Place 1 into your party ${hidden}. Keep the other in hand' : 'Place into your party ${hidden}';
            $this->jsdescriptions = [ 2 => $description];
            
            $this->phpactions = [ 1 => 'action_SeaweedChopper'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
        Notifications::place($player_id, $args['card'], WILDERNESS, HIDDEN, true);

        foreach(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick') as $card_id => $card) {
            HiddenLeaders::get()->cards->moveCard($card_id, 'hand'.$player_id);
            Notifications::place($player_id, $card, WILDERNESS, HAND, true);   
        }
    }
}

class UnderwaterArtist extends HeroCard {
    //+1 empire AND/OR +1 tribes. Choose 1 faction and 1 player. They must give you 1 card from their hand of this faction
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->selectableOpponents = $this->getPossibleOpponents();

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('hand'.$this->getGameStateValue(SELECTED_PLAYER)), fn($card) => $card['type'] == $this->getGameStateValue(SELECTED_FACTION) || $card['type'] == EMPEROR));
        
        $faction_name = '';
        switch($this->getGameStateValue(SELECTED_FACTION)) {
            case UNDEAD:
                $faction_name = 'undead';
            break;
            case WATER_FOLK:
                $faction_name = 'water_folk';
            break;
            case EMPIRE:
                $faction_name = 'empire';
            break;
            case TRIBES:
                $faction_name = 'tribes';
            break;
        }

        if($effectStep == 3 && count($this->selectableCards) == 0) {
            $this->phpactions = [3 => 'action_UnderwaterArtist'];
            return;
        }

        $this->jsactions = [ 0 => 'moveToken', 1 => 'select_faction', 2 => 'switch_player', 3 => 'move_card'];

        $this->jsdescriptions = [ 1 => 'Select a faction :', 2 => 'Select 1 player :', 3 => 'You must give 1 ${'.$faction_name.'} card from your hand to ${player_ids}'];

        if($this->getGameStateValue(SELECTED_FACTION) != 0) $this->phpactions = [4 => 'action_ChangeActivePlayer'];
    }

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$this->getGameStateValue(CURRENT_PLAYER));

        Notifications::pick($this->getGameStateValue(CURRENT_PLAYER), $player_id, $args['card'], HAND, HAND, false);
    }
}

class WellFundedQueen extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class QueenOfTheWild extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class QueenOfTheStreets extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class EmperorBestFriend extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class KeeperOfDiscord extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

//FORGOTTEN LEGENDS
class ObligingOgre extends HeroCard {
    //Turn over 2 of your visible
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() ));

        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];

            $this->jsdescriptions = [ 1 => 'Turn over 2 of your ${visible}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        $flip_from = VISIBLE;
        $flip_to = HIDDEN;
        
        if(array_key_exists('card_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            
            Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $player_id);
                
                Notifications::flip($player_id, $card['location_arg'], $card, $flip_from, $flip_to);
            }
        }
    }
}

class SleepyHopGoblin extends HeroCard {
    //Take 1 ${hidden} from another {player}. Place it in your party ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectOpponentCards('hiddenCards');
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Take 1 ${hidden} from another ${player}'];
        }
    }
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::pick($player_id, $args['target_id'], $args['card'], HIDDEN, HIDDEN, true);
    }
}

class GiganticDuo extends HeroCard {
    //You may bury any 1 ${visible} ${empire} AND/OR bury any 1 ${visible} {water_folk}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken'];
        
        $this->selectableCards = [
            0 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == EMPIRE || $card['type'] == EMPEROR)),
            1 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['type'] == WATER_FOLK || $card['type'] == EMPEROR))
        ];

        if(count($this->selectableCards[0]) > 0 || count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Bury any 1 ${visible} ${empire} AND/OR ${water_folk} :'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
                Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
            }
            else {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
                Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
            }
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                if($card['location_arg'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $card['type_arg'] != KeeperOfDiscord) {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $card['location_arg']);
                    Notifications::flip($player_id, $card['location_arg'], $card, VISIBLE, HIDDEN);
                }
                else {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);
                    Notifications::bury($player_id, $card['location_arg'], $card, VISIBLE, false);
                }
            }
        }
    }
}

class SurprisedSapling extends HeroCard {
    //Look at the hand of another ${player}. Take 1 of their cards. Place it into your party ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        $this->jsactions = [ 0 => 'moveToken'];

        $target_id = $this->getGameStateValue(SELECTED_PLAYER);

        $this->selectableOpponents = array_values(array_filter($this->getPlayersIds(), fn($playerId) => $playerId != $this->getActivePlayerId() && intval(HiddenLeaders::get()->cards->countCardInLocation('hand'.$playerId)) > 0));
        
        if($effectStep == 1 && count($this->selectableOpponents) > 0) {
            $this->jsactions = [ 1 => 'select_opponent_SurprisedSapling'];
            $this->jsdescriptions = [ 1 => 'Pick 1 ${player}:'];
        }

        if($effectStep == 2 && HiddenLeaders::get()->cards->countCardInLocation('hand'.$target_id) + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') > 0) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

            $this->jsactions = [ 2 => 'pick_card'];

            $this->jsdescriptions = [ 2 => 'Take 1 of their cards'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        $target_id = $this->getGameStateValue(SELECTED_PLAYER);

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        //Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
        Notifications::pick($player_id, $target_id, $args['card'], HAND, HIDDEN, true);

        Notifications::replaceCardInPick_SurprisedSapling($target_id);
    }
}

class FungifiedTroll extends HeroCard {
    //Exchange 1 of your ${visible} with 1 ${undead} {visible} OR 1 ${empire} {visible} of another ${player}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() )),
            1 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['location_arg'] != $this->getActivePlayerId() && ($card['type'] == EMPIRE || $card['type'] == UNDEAD || $card['type'] == EMPEROR)))
        ];
        
        if(count($this->selectableCards[0]) > 0 && count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 1 of your ${visible} with 1 ${undead} ${visible} OR 1 ${empire} ${visible} of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'visibleCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'visibleCards', $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);

        Notifications::exchange($player_id, $args['card_2']['location_arg'], $args['card_1'], $args['card_2'], VISIBLE, VISIBLE, false);
    }
}

class TwiggyTreeKeeper extends HeroCard {
    //Look at any 2 ${hidden}. Then bury any 1 ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 0 => 'moveToken'];

        $look_card_id_1 = $this->getGameStateValue(SELECTED_CARD);
        $look_card_id_2 = $this->getGameStateValue(SELECTED_CARD_2);

        $this->selectOpponentCards('hiddenCards');
        $nbSelectableCards = count($this->selectableCards);

        if( count( array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards')) ) == 0 ) return;

        if($effectStep == 1) {
            if($nbSelectableCards == 0) {
                $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards'));
                
                $this->jsactions = [ 1 => 'move_card'];

                $this->jsdescriptions = [ 1 => 'Bury any 1 ${hidden}'];
            }
            else {
                $this->jsactions = [ 1 => 'look_card'];

                $this->jsdescriptions = [ 1 => 'Look at any 2 ${hidden}'];
            }
        }

        if($effectStep == 2 && $look_card_id_1 != 0) {
            $this->jsactions = [ 2 => 'action_TwiggyTreeKeeper'];

            $this->jsdescriptions = [ 2 => 'Bury any 1 ${hidden}'];

            $this->selectableCards = [];
            $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_1));
            
            if($look_card_id_2 != 0) $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_2));
        }

        if($effectStep == 3) {
            $this->jsactions = [ 3 => 'action_end_TwiggyTreeKeeper'];

            $this->selectableCards = [];
            $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_1));
            
            if($look_card_id_2 != 0) $this->selectableCards[] = $this->getCardInfos(HiddenLeaders::get()->cards->getCard($look_card_id_2));
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);

        Notifications::bury($player_id, $args['target_id'], $args['card'], HIDDEN, false);
    }
}
// TO DO
class NecklessCharlatan extends HeroCard {
    //Look at any 1 ${corruption} AND/OR perform the ability of any 1 ${artifact} as if it was your ${artifact}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class PatientProtector extends HeroCard {
    //Exchange 2 cards from ${tavern} with 2 of your ${visible}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'visibleCards', $this->getActivePlayerId() )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'tavern' ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 2 cards from ${tavern} with 2 of your ${visible}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_1_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'tavern', $args['card_2']['location_arg']);
            HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);
    
            Notifications::exchange($player_id, null, $args['card_1'], $args['card_2'], VISIBLE, TAVERN, false);
        }
        else {
            for ($i = 0; $i <= count($args['cards_1']); $i++) {
                HiddenLeaders::get()->cards->moveCard($args['cards_1'][i]['id'], 'tavern', $args['cards_2'][i]['location_arg']);
                HiddenLeaders::get()->cards->moveCard($args['cards_2'][i]['id'], 'visibleCards', $player_id);
        
                Notifications::exchange($player_id, null, $args['cards_1'][i], $args['cards_2'][i], VISIBLE, TAVERN, false);
            }
        }
    }
}

class MisinformedMechatron extends HeroCard {
    //Place up to 2 cards from your hand into your party ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ));
        
        if(count($this->selectableCards) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Place 2 cards from your hand into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
    
            Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $player_id);
        
                Notifications::place($player_id, $card, HAND, HIDDEN, true);
            }
        }
    }
}

class MendedColossus extends HeroCard {
    //Bury 1 ${visible} from the next AND previous ${player}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 0 => 'moveToken'];
        
        if($effectStep == 1) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards',  HiddenLeaders::get()->getPlayerAfter( $this->getActivePlayerId() ) ));
            
            if(count($this->selectableCards) > 0) {
                $this->jsactions = [ 1 => 'move_card'];
                $this->jsdescriptions = [ 1 => 'Bury 1 ${visible} from the next ${player}'];
            }
            else $this->phpactions = [ 1 => 'nextEffect'];
        }

        if($effectStep == 2) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards',  HiddenLeaders::get()->getPlayerBefore( $this->getActivePlayerId() ) ));
            
            if(count($this->selectableCards) > 0) {
                $this->jsactions = [ 2 => 'move_card'];
                $this->jsdescriptions = [ 2 => 'Bury 1 ${visible} from the previous ${player}'];
            }
        }
        
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class HuggableHulk extends HeroCard {
    //Bury 1 of your ${visible} that is not ${guardian}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken'];

        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId()), fn($card) => !HiddenLeaders::get()->isGuardian($card['type_arg']) ));

        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'Bury 1 of your ${visible} that is not ${guardian}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
            Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
        }
        else {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
            Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
        }
    }
}

class ValuableVindicator extends HeroCard {
    //Exchange 1 of your ${visible} with 1 ${visible} {water_folk} OR ${visible} {tribes} of another ${player}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId() )),
            1 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['location_arg'] != $this->getActivePlayerId() && ($card['type'] == WATER_FOLK || $card['type'] == TRIBES || $card['type'] == EMPEROR)))
           
        ];
        
        if(count($this->selectableCards[0]) > 0 && count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 1 of your ${visible} with 1 ${water_folk} ${visible} OR 1 ${tribes} ${visible} of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'visibleCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'visibleCards',  $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $args['card_1']['location_arg']);

        Notifications::exchange($player_id, $args['card_2']['location_arg'], $args['card_1'], $args['card_2'], VISIBLE, VISIBLE, false);
    }
}

class GracefulGriffin extends HeroCard {
    //Exchange up to 2 cards of your ${hidden} with cards from your hand.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = [
            0 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hiddenCards', $this->getActivePlayerId() )),
            1 => array_keys(HiddenLeaders::get()->cards->getCardsInLocation( 'hand'.$this->getActivePlayerId() ))
        ];
        
        if(count($this->selectableCards[0]) == 0 || count($this->selectableCards[1]) == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange up to 2 cards of your ${hidden} with your hand'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_1_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'hand'.$player_id);
            HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'hiddenCards', $player_id);
    
            Notifications::exchange($player_id, $player_id, $args['card_1'], $args['card_2'], HIDDEN, HAND, true);
        }
        else {
            for ($i = 0; $i <= count($args['cards_1']); $i++) {
                HiddenLeaders::get()->cards->moveCard($args['cards_1'][i]['id'], 'hand'.$player_id);
                HiddenLeaders::get()->cards->moveCard($args['cards_2'][i]['id'], 'hiddenCards', $player_id);
        
                Notifications::exchange($player_id, $player_id, $args['cards_1'][i], $args['cards_2'][i], HIDDEN, HAND, true);
            }
        }
    }
}
// TO DO
class SelfMadeWidow extends HeroCard {
    //Exchange any 2 ${corruption} OR any 2 ${artifact}. Every ${player} keeps their ${artifact} tokens.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class SeveredSeraph extends HeroCard {
    //Exchange 1 of your ${visible} ${guardian} with 1 ${visible} ${guardian} of another ${player}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];

        $this->selectableCards = [
            0 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getActivePlayerId()), fn($card) => HiddenLeaders::get()->isGuardian($card['type_arg']))),
            1 => array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => $card['location_arg'] != $this->getActivePlayerId() && HiddenLeaders::get()->isGuardian($card['type_arg'])))
        ];
        
        if(count($this->selectableCards[0]) > 0 && count($this->selectableCards[1]) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'exchange'];
            $this->jsdescriptions = [ 1 => 'Exchange 1 of your ${visible} ${guardian} with 1 ${visible} ${guardian} of another ${player}'];
        }
    }

    public function exchange($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if ($args['card_1_location'] != 'visibleCards' || $args['card_2_location'] != 'visibleCards') {
            throw new BgaSystemException("Locations are invalid");
        }
        
        HiddenLeaders::get()->cards->moveCard($args['card_1_id'], 'visibleCards', $args['card_2']['location_arg']);
        HiddenLeaders::get()->cards->moveCard($args['card_2_id'], 'visibleCards', $player_id);

        Notifications::exchange($player_id, $args['card_2']['location_arg'], $args['card_1'], $args['card_2'], VISIBLE, VISIBLE, false);
    }
}

class BanefulBeacon extends HeroCard {
    //You may bury up to any 2 ${visible} ${guardian}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->jsactions = [ 0 => 'moveToken'];
        
        $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards'), fn($card) => HiddenLeaders::get()->isGuardian($card['type_arg']) ));
        
        if(count($this->selectableCards) > 0) {
            $this->jsactions = [ 0 => 'moveToken', 1 => 'move_card'];
            $this->jsdescriptions = [ 1 => 'You may bury up to any 2 ${visible} ${guardian} :'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            if($args['target_id'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $args['card']['type_arg'] != KeeperOfDiscord) {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $args['target_id']);
                Notifications::flip($player_id, $args['target_id'], $args['card'], VISIBLE, HIDDEN);
            }
            else {
                HiddenLeaders::get()->cards->moveCard($args['card_id'], 'graveyard', $this->getLocationArg('graveyard') + 1);
                Notifications::bury($player_id, $args['target_id'], $args['card'], VISIBLE, false);
            }
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                if($card['location_arg'] == HiddenLeaders::get()->getPlayerKeeperOfDiscord() && $card['type_arg'] != KeeperOfDiscord) {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $card['location_arg']);
                    Notifications::flip($player_id, $card['location_arg'], $card, VISIBLE, HIDDEN);
                }
                else {
                    HiddenLeaders::get()->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);
                    Notifications::bury($player_id, $card['location_arg'], $card, VISIBLE, false);
                }
            }
        }
    }
}

class DemonicDarter extends HeroCard {
    //Take up to 3 ${guardian} from ${graveyard} into your hand.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        if($effectStep == 0) $this->jsactions = [ 0 => 'moveToken'];

        if($effectStep == 1 && count(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('graveyard'), fn($card) => HiddenLeaders::get()->isGuardian($card['type_arg']))) > 0) {
            $this->phpactions = [ 1 => 'action_DemonicDarter'];
        }

        if($effectStep == 2) {
            $this->selectableCards = array_keys(array_filter(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'), fn($card) => HiddenLeaders::get()->isGuardian($card['type_arg']) ));
            
            $this->jsactions = [ 2 => 'pick_card'];

            $this->jsdescriptions = [ 2 => 'Take up to 3 ${guardian} from ${graveyard} into your hand'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);
    
            Notifications::place($player_id, $args['card'], GRAVEYARD, HAND, true);

            self::incStat( 1, "takenFromGraveyard", $player_id );
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                HiddenLeaders::get()->cards->moveCard($card_id, 'hiddenCards', $player_id);
        
                Notifications::place($player_id, $card, GRAVEYARD, HAND, true);

                self::incStat( 1, "takenFromGraveyard", $player_id );
            }
        }
        
        Notifications::replaceCardInPick_WrappedWarrior();
    }
}

class PossessedPoodle extends HeroCard {
    //Pick 1 ${player}. They have to skip their next turn.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableOpponents = $this->getPossibleOpponents();

        $this->jsactions = [ 0 => 'moveToken', 1 => 'select_opponent'];

        $this->jsdescriptions = [ 1 => 'Pick 1 ${player}. They have to skip their next turn'];
    } 
    
    public function moveCard($args) {
        $this->setGameStateValue(SKIP_PLAYER, $args['target_id']);

        Notifications::notifyAll('skip', clienttranslate('${player_name} has to skip his next turn'), [
            'playerId' => $args['target_id'],
            'player_name' => HiddenLeaders::get()->getPlayerName($args['target_id']),
        ]);
    }
}

class PhilantropicPhantom extends HeroCard {
    //All other ${player} have to bury 1 of their ${visible} OR ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
        
        $this->phpactions = [ 1 => 'action_PhilantropicPhantom'];
    } 
}

class ObnoxiousNightmare extends HeroCard {
    //Place any 1 card from ${graveyard} into your party ${hidden}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

        if(HiddenLeaders::get()->cards->countCardInLocation('graveyard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];

            $this->jsdescriptions = [ 2 => 'Place any 1 card from ${graveyard} into your party ${hidden}'];

            $this->phpactions = [ 1 => 'action_ObnoxiousNightmare'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], GRAVEYARD, HIDDEN, true);
        
        self::incStat( 1, "takenFromGraveyard", $player_id );

        Notifications::replaceCardInPick_WrappedWarrior();
    }
}
//TO DO
class OpenMindedMentalist extends HeroCard {
    //Draw 1 ${corruption} and place it on any 1 ${visible} OR Take 1 ${artifact} token from the box and place it on any 1 ${artifact}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class OppressedOcean extends HeroCard {
    //Pick 1 ${player}. X is the number of ${visible} {guardian} in their party (max -4)
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'select_opponent_OppressedOcean'];

        $this->jsdescriptions = [ 0 => 'Number of ${visible} ${guardian} :'];

        $this->selectableOpponents = $this->getPlayersIds();
    } 
}

class HardShelledTitan extends HeroCard {
    //All other ${player} have to give you 1 card from their hand. Place 1 of them into your party ${hidden}. Discard the others.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
        
        $this->phpactions = [ 1 => 'action_HardShelledTitan'];
        
        if(HiddenLeaders::get()->cards->countCardInLocation('cardInPick') > 0) {
            $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));

            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];
            $this->jsdescriptions = [ 2 => 'Place 1 of them into your party ${hidden}'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

        Notifications::place($player_id, $args['card'], HAND, HIDDEN, true);

        HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_id']), true);
    }
}

class AbysmalAutomaton extends HeroCard {
    //Look at the top 5 cards from ${harbor}. Take up to 3 of them into your hand. Discard the others.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        //$this->selectableCards = $this->getCardsInfos(HiddenLeaders::get()->cards->getCardsOnTop(2, 'discard'));
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));
        
        if(HiddenLeaders::get()->cards->countCardInLocation('deck') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card'];
            $this->jsdescriptions = [ 2 => 'Take up to 3 cards of them into your hand'];
            
            $this->phpactions = [ 1 => 'action_AbysmalAutomaton'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());
        
        if(array_key_exists('card_id', $args)) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);
    
            Notifications::place($player_id, $args['card'], HARBOR, HAND, true);
        }
        else {
            foreach($args['cards'] as $card_id => $card) {
                HiddenLeaders::get()->cards->moveCard($card_id, 'hand'.$player_id);
        
                Notifications::place($player_id, $card, HARBOR, HAND, true);
            }
        }

        HiddenLeaders::get()->discard(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), true, WILDERNESS);
    }
}

class CrabbyKnight extends HeroCard {
    //Turn over 1 ${visible} OR ${hidden} of every other ${player}
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);
        
        $effectStep = $this->getGameStateValue(EFFECT_STEP);

        $this->jsactions = [ 0 => 'moveToken'];

        $numPlayer = 1;
        foreach($this->getPossibleOpponentsLocation() as $target_id) {
            $this->selectOneOpponentCards($target_id);

            if(count($this->selectableCards) > 0 && $effectStep == $numPlayer) {
                $this->setGameStateValue(SELECTED_PLAYER, $target_id);

                $this->jsactions = [ 0 => 'moveToken', $effectStep => 'move_card'];
    
                $this->jsdescriptions = [ $effectStep => 'Turn over 1 ${visible} or ${hidden} of ${target_id}'];

                return;
            }
            $numPlayer++;
        }
    } 

    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $flip_from = $args['card']['location'] == 'visibleCards' ?  VISIBLE : HIDDEN;
        $flip_to = $flip_from == VISIBLE ? HIDDEN : VISIBLE;
        
        HiddenLeaders::get()->cards->moveCard($args['card_id'], $args['card']['location'] == 'visibleCards' ? 'hiddenCards' : 'visibleCards', $args['target_id']);

        Notifications::flip($player_id, $args['target_id'], $args['card'], $flip_from, $flip_to);
    }
}

class SnappySeaSnake extends HeroCard {
    //Move the ${tribes} OR ${empire} marker 1 OR 2 steps in any direction.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken_SnappySeaSnake'];
    } 
}

class ShellFishDefender extends HeroCard {
    //Look at the top 4 cards from ${wilderness}. Place 1 of them into your party ${hidden} AND Take 1 of them into your hand.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        //$this->selectableCards = $this->getCardsInfos(HiddenLeaders::get()->cards->getCardsOnTop(4, 'discard'));
        $this->selectableCards = array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick'));
        
        if(HiddenLeaders::get()->cards->countCardInLocation('discard') + HiddenLeaders::get()->cards->countCardInLocation('cardInPick') == 0) $this->jsactions = [ 0 => 'moveToken'];
        else {
            $this->jsactions = [ 0 => 'moveToken', 2 => 'pick_card', 3 => 'pick_card'];
            $this->jsdescriptions = [ 2 => 'Place 1 card into your party ${hidden}', 3 => 'Take 1 card into your hand'];
            
            $this->phpactions = [ 1 => 'action_ShellFishDefender'];
        }
    } 
    
    public function moveCard($args) {
        $player_id = intval($this->getActivePlayerId());

        $effectStep = $this->getGameStateValue(EFFECT_STEP);
        
        if($effectStep == 2) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hiddenCards', $player_id);

            Notifications::place($player_id, $args['card'], WILDERNESS, HIDDEN, true);
        }
        if($effectStep == 3) {
            HiddenLeaders::get()->cards->moveCard($args['card_id'], 'hand'.$player_id);

            Notifications::place($player_id, $args['card'], WILDERNESS, HAND, true);

            HiddenLeaders::get()->discard(array_filter(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('cardInPick')), fn($card_id) => $card_id != $args['card_id']), true, WILDERNESS);
        }
    }
}
//TO DO
class PiranhaPriestess extends HeroCard {
    //Discard any 1 ${artifact} token OR look at any 1 ${corruption}. You may discard it.
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}

class MotherOfGuardians extends HeroCard {
    public function __construct($dbCard, $cards_data) {
        parent::__construct($dbCard, $cards_data);

        $this->jsactions = [ 0 => 'moveToken'];
    } 
}
