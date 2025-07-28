<?php

trait ActionTrait {

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    //////////// 
    
    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in nicodemus.action.php)
    */

    public function playHiddenCard(int $card_id) {
        //$this->checkAction('playHiddenCard'); 
        
        $playerId = intval($this->getCurrentPlayerId());

        $card = $this->cards->getCard($card_id);

        if ($card == 'null') throw new BgaUserException("Invalid card");

        $this->cards->moveCard($card_id, 'hiddenCards', $playerId);

        $endSetup = $this->cards->countCardInlocation('discard',$playerId) > 0 ? true : false;
        
        Notifications::place($playerId, $card, HAND, HIDDEN, true);

        // if($endSetup) $this->gamestate->setPlayerNonMultiactive($playerId, $action);
        if($endSetup) {
            if($this->isArtifacts()) $this->gamestate->nextPrivateState($playerId, 'artifact');
            else $this->gamestate->setPlayerNonMultiactive($playerId, 'setupFinished');
        }
    }

    public function discard(array $card_ids, bool $anonymize, $from = HAND) {
        //$this->checkAction('discard'); 

        $stateName = $this->getStateName();
        
        $playerId = intval($this->getCurrentPlayerId());
        $cards = $this->cards->getCards($card_ids);

        foreach($cards as $card) {
            if ($card == null) throw new BgaUserException("Invalid card");
        }
        
        if(count($cards) > 0) {
            if($stateName == 'privateSetup') $this->cards->moveCards($card_ids, 'discard', $playerId);
            else {
                foreach($card_ids as $card_id) {
                    $this->cards->moveCard($card_id, 'discard', $this->getLocationArg('discard') + 1);
                }
            }
            $this->setGameStateValue(NB_DISCARDED_CARD, count($card_ids));

            Notifications::discard($playerId, $cards, $anonymize, $from);
            
            if($stateName == 'cardEffect') {

                //temp solution TODO
                $played_card = $this->getCardInfos($this->cards->getCard($this->getGameStateValue(CARD_PLAYED)));
                if($played_card->class == 'AngryPriestess' || $played_card->class == 'NaggingNorthman') $this->nextEffect();
            }
        }

        if($stateName == 'privateSetup') {
            $endSetup = $this->cards->countCardInlocation('hiddenCards',$playerId) > 0 ? true : false;
            
            // if($endSetup) $this->gamestate->setPlayerNonMultiactive($playerId, $action);
            
            if($endSetup) {
                if($this->isArtifacts()) $this->gamestate->nextPrivateState($playerId, 'artifact');
                else $this->gamestate->setPlayerNonMultiactive($playerId, 'setupFinished');
            }
        }

        if($stateName == 'playerAction') {
            if(ArtifactCard::availability($this->getActivePlayerId()) == FAST) $this->gamestate->nextState('artifact');
            else $this->gamestate->nextState('drawCard');
        }

        if($stateName == 'discard') $this->gamestate->nextState('nextPlayer');

        if($stateName == 'willBendingWitch') $this->gamestate->setPlayerNonMultiactive($playerId, 'endEffect');
    }

    public function drawCard(string $location, int $nb_cards, bool $cardInPick = false) {
        //$this->checkAction('drawCard'); 

        $stateName = $this->getStateName();
        
        $playerId = intval($this->getActivePlayerId());
        
        if($location != 'deck') {
            $remainingCards = $this->getRemainingCards($location);
            $nb_cards = $remainingCards >= $nb_cards ? $nb_cards : $remainingCards;
        }

        $to = $cardInPick ? 'cardInPick' : 'hand'.$playerId;

        $cards = [];
        
        for ($i = 1; $i <= $nb_cards; $i++) {
            if($location == 'deck' && $this->getRemainingCards('deck') == 0) $location_arg = $this->getLocationArg('discard');
            else if($cardInPick) $location_arg = $this->getLocationArg($location);
            else $location_arg = 0;

            $card = $this->cards->pickCardForLocation($location, $to, $location_arg);
        
            if ($card == null) throw new BgaUserException("Invalid card");

            array_push($cards, $card);

            Notifications::drawCard($playerId, $card, array_search($location, $this->LOCATIONS), $location, $cardInPick);
        }
        if(count($cards) > 0) Notifications::drawCardNotif($playerId, $cards, array_search($location, $this->LOCATIONS), $cardInPick);

        if($stateName == 'drawCard') {
            $this->getRemainingCards('hand'.$playerId) >= 4 ? $this->gamestate->nextState('discard') : $this->gamestate->nextState('drawCard');
        }
        if($stateName == 'cardEffect') $this->nextEffect();
    }

    // public function drawFate() {
    //     $playerId = intval($this->getActivePlayerId());

    //     $cards = $this->fate_cards->pickCardsForLocation(2,'fate','fateCardInPick');
    // }

    // public function drawCard(string $location, int $nb_cards) {
    //     //$this->checkAction('drawCard'); 

    //     $stateName = $this->getStateName();
        
    //     $playerId = intval($this->getActivePlayerId());
        
    //     $cards = $this->cards->pickCardsForLocation($nb_cards, $location, 'hand'.$playerId);
        
    //     foreach($cards as $card) {
    //         if ($card == null) throw new BgaUserException("Invalid card");
    //     }
        
    //     Notifications::drawCard($playerId, $cards, array_search($location, $this->LOCATIONS));

    //     if($stateName == 'drawCard') {
    //         $this->getRemainingCards('hand'.$playerId) >= 4 ? $this->gamestate->nextState('discard') : $this->gamestate->nextState('drawCard');
    //     }
    //     if($stateName == 'cardEffect') $this->nextEffect();
    // }

    public function drawCardFromTavern(int $card_id) {
        //$this->checkAction('drawCardFromTavern'); 

        $stateName = $this->getStateName();

        $playerId = intval($this->getActivePlayerId());
        
        $card = $this->getCardInfos($this->cards->getCard($card_id));
        
        if ($card == null) throw new BgaUserException("Invalid card");
        if ($card->location != 'tavern') throw new BgaUserException("Card not in tavern");

        $this->cards->moveCard($card_id, 'hand'.$playerId);
        
        Notifications::drawCardFromTavern($playerId, $card);
        
        $this->getRemainingCards('hand'.$playerId) >= 4 ? $this->gamestate->nextState('discard') : $this->gamestate->nextState('drawCard');
    }

    public function playCard(int $card_id) {
        //$this->checkAction('playCard'); 
        
        $playerId = intval($this->getActivePlayerId());

        // Prep for card effect
        $this->setGameStateValue(CURRENT_PLAYER, $playerId);
        $this->setGameStateValue(CARD_PLAYED, $card_id);
        $this->setGameStateValue(EFFECT_STEP, 0);
        $this->setGameStateValue(NB_DISCARDED_CARD, 0);
        $this->setGameStateValue(SELECTED_PLAYER, 0 );
        $this->setGameStateValue(SELECTED_FACTION, 0);
        $this->setGameStateValue(SELECTED_CARD, 0);
        $this->setGameStateValue(SELECTED_CARD_2, 0);
        $this->setGameStateValue(DRAW_FATE, 0);

        $card = $this->cards->getCard($card_id);

        if ($card == 'null') throw new BgaUserException("Invalid card");

        $this->cards->moveCard($card_id, 'visibleCards', $playerId);

        Notifications::playCard($playerId, $card);
        
        $this->gamestate->nextState('cardEffect');
    }

    public function noAction(bool $isArtifact) {
        if($isArtifact) {
            var_dump('xxx');
            $this->setGameStateValue(ARTIFACT_PLAYED, 1);

            if($this->getGameStateValue(PREVIOUS_STATE) == ST_PLAYER_CARD_EFFECT_MANAGER) $this->gamestate->nextState('drawCard');
            else if($this->getGameStateValue(PREVIOUS_STATE) == ST_NEXT_PLAYER) $this->gamestate->nextState('nextPlayer');

            return;
        }

        Notifications::noAction(intval($this->getActivePlayerId()));
        $this->gamestate->nextState('drawCard');
    }

    public function moveToken(int $empire_mvt, int $tribes_mvt) {
        //$this->checkAction('moveToken'); 

        $playerId = intval(self::getActivePlayerId());

        $new_empire_pos = $this->getGameStateValue(EMPIRE_TOKEN) + $empire_mvt;
        $new_tribes_pos = $this->getGameStateValue(TRIBES_TOKEN) + $tribes_mvt;
        
        if($new_empire_pos > 12 || $new_tribes_pos > 12 || $new_empire_pos < 1 || $new_tribes_pos < 1) {
            throw new BgaUserException("Invalid Token position => out of track");
        }

        Notifications::moveToken($playerId, $new_empire_pos, $new_tribes_pos);

        $this->nextEffect();
    }
    public function moveGuardianToken(int $guardian_mvt = 2) {
        //$this->checkAction('moveToken'); 

        $guardian_mvt = $this->checkTokenLimit($guardian_mvt, $this->getGameStateValue(GUARDIAN_TOKEN));

        $playerId = intval(self::getActivePlayerId());

        $new_guardian_pos = $this->getGameStateValue(GUARDIAN_TOKEN) + $guardian_mvt;
        
        if($new_guardian_pos > 12 || $new_guardian_pos < 1) {
            throw new BgaUserException("Invalid Token position => out of track");
        }

        Notifications::moveGuardianToken($playerId, $new_guardian_pos);

        //$this->nextEffect();
    }

    public function moveCard(array $card_ids) {
        //$this->checkAction('moveCard'); 
        
        if(count($card_ids) == 1) {
            $card_id = $card_ids[0];

            $card = $this->cards->getCard($card_id);
            $args['card_id'] = $card_id;
            $args['card'] = $card;
            $args['target_id'] = $card['location_arg'];
            $args['location'] = $card['location'];
        }
        else {
            $args['cards'] = $this->cards->getCards($card_ids);
        }
        if($this->gamestate->state_id() == ST_PLAYER_ARTIFACT_EFFECT_MANAGER) $this->getPlayedArtifactClass()->moveCard($args);
        else {
            $this->getPlayedCardClass()->moveCard($args);

            if($this->getGameStateValue(DRAW_FATE) == 1) {
                for ($i = 1; $i <= 2; $i++) {
                    $card = $this->cards->pickCardForLocation('fate','fateCardInPick');
                    Notifications::drawFate(self::getActivePlayerId(), $card);
                }
                $this->gamestate->nextState('drawFate');
                return;
            }
        }

        $this->nextEffect();
    }

    public function playFateCard(int $card_id) {
        $playerId = intval(self::getActivePlayerId());

        $fate_card = $this->fate_cards->getCard($card_id);
        
        $fate = $this->FATE_CARDS[$fate_card['type_arg']];

        $faction = key($fate);
        $mvt = reset($fate);
        
        Notifications::playFateCard($playerId, $fate);

        switch($faction) {
            case GUARDIAN:
                $this->moveGuardianToken($mvt);
                $this->nextEffect();
            break;
            case EMPIRE:
                $empire_mvt = $this->checkTokenLimit($mvt, $this->getGameStateValue(EMPIRE_TOKEN));
                $this->moveToken($empire_mvt, 0);
            break;
            case TRIBES:
                $tribes_mvt = $this->checkTokenLimit($mvt, $this->getGameStateValue(TRIBES_TOKEN));
                $this->moveToken(0, $tribes_mvt);
            break;
        }
    }

    public function lookCard(array $card_ids) {
        //$this->checkAction('lookCard'); 

        $playerId = intval($this->getActivePlayerId());

        $cards = $this->cards->getCards($card_ids);
        
        foreach($cards as $card) {
            if ($card == null) throw new BgaUserException("Invalid card");
        }
        
        $this->setGameStateValue(SELECTED_CARD, intval($card_ids[0]));
        if(count($card_ids) == 2) $this->setGameStateValue(SELECTED_CARD_2, intval($card_ids[1]));
        
        $args['card_ids'] = $card_ids;
        $args['cards'] = $cards;
        
        Notifications::look($playerId, $cards);

        $this->nextEffect();
    }

    public function drawCardFromOpponent(int $target_id) {
        //$this->checkAction('drawCardFromOpponent'); 

        $args['target_id'] = $target_id;

        $this->getPlayedCardClass()->drawCardFromOpponent($args);

        $this->nextEffect();
    }

    public function selectOpponent(int $target_id) {
        //$this->checkAction('selectOpponent'); 

        $args['target_id'] = $target_id;

        $this->getPlayedCardClass()->moveCard($args);

        $this->nextEffect();
    }

    public function exchange(array $card_1_ids,array $card_2_ids) {
        //$this->checkAction('exchange'); 
        
        for ($i = 0; $i < count($card_1_ids); $i++) {
            $card_1_id = $card_1_ids[$i];
            $card_2_id = $card_2_ids[$i];

            $card_1 = $this->cards->getCard($card_1_id);
            $card_2 = $this->cards->getCard($card_2_id);
            $args['card_1_id'] = $card_1_id;
            $args['card_2_id'] = $card_2_id;
            $args['card_1'] = $card_1;
            $args['card_2'] = $card_2;
            $args['card_1_location'] = $card_1['location'];
            $args['card_2_location'] = $card_2['location'];
            //$args['target_id'] = $card_1['location_arg'];

            if($this->gamestate->state_id() == ST_PLAYER_ARTIFACT_EFFECT_MANAGER) $this->getPlayedArtifactClass()->exchange($args);
            else $this->getPlayedCardClass()->exchange($args);
        }

        // if(count($card_1_ids) == 1) {
        //     $card_1_id = $card_1_ids[0];
        //     $card_2_id = $card_2_ids[0];

        //     $card_1 = $this->cards->getCard($card_1_id);
        //     $card_2 = $this->cards->getCard($card_2_id);
        //     $args['card_1_id'] = $card_1_id;
        //     $args['card_2_id'] = $card_2_id;
        //     $args['card_1'] = $card_1;
        //     $args['card_2'] = $card_2;
        //     $args['card_1_location'] = $card_1['location'];
        //     $args['card_2_location'] = $card_2['location'];
        //     $args['target_id'] = $card_1['location_arg'];
        // }
        // else {
        //     $args['cards_1'] = $this->cards->getCards($card_1_ids);
        //     $args['cards_2'] = $this->cards->getCards($card_2_ids);
        // }
        
        // $this->getPlayedCardClass()->exchange($args);

        $this->nextEffect();
    }

    public function switchPlayer(int $target_id) {
        //$this->checkAction('switchPlayer'); 

        $this->setGameStateValue(SELECTED_PLAYER, $target_id);

        $this->gamestate->nextState('changeActivePlayer');
    }

    public function selectFaction(int $faction_id) {
        //$this->checkAction('selectFaction'); 

        $this->setGameStateValue(SELECTED_FACTION, $faction_id);

        $this->nextEffect();
    }

    public function playNewCard() {
        //$this->checkAction('playNewCard'); 

        $this->gamestate->nextState('playNewCard');
    }

    public function selectCard(int $card_id) {
        //$this->checkAction('selectCard'); 

        $this->setGameStateValue(SELECTED_CARD, $card_id);

        $this->nextEffect();
    }

    public function performEffect() {
        //$this->checkAction('performEffect'); 

        $selectedCard_id = $this->getGameStateValue(SELECTED_CARD);

        $this->setGameStateValue(CARD_PLAYED, $selectedCard_id);
        $this->setGameStateValue(EFFECT_STEP, 0);
        $this->setGameStateValue(NB_DISCARDED_CARD, 0);
        $this->setGameStateValue(SELECTED_PLAYER, 0);
        $this->setGameStateValue(SELECTED_FACTION, 0);
        $this->setGameStateValue(SELECTED_CARD, 0);
        $this->setGameStateValue(DRAW_FATE, 0);
        
        Notifications::cardInPlay($this->getActivePlayerId(), $this->cards->getCard($selectedCard_id), false);

        $this->gamestate->nextState('nextEffect');
    }

    // InsidiousImpaler & ModestMonsterslayer
    public function addCardInPlay() {
        $card_id = $this->getGameStateValue(CARD_PLAYED);
        self::DBQuery("INSERT INTO cardInPlay (card_id) VALUES (".$card_id.")"); 

        $selectedCard_id = $this->getGameStateValue(SELECTED_CARD);

        $this->setGameStateValue(CARD_PLAYED, $selectedCard_id);
        $this->setGameStateValue(EFFECT_STEP, 0);
        $this->setGameStateValue(SELECTED_CARD, 0);

        $this->cards->moveCard($selectedCard_id,'cardInPlay', self::getUniqueValueFromDB("SELECT order_id FROM cardInPlay ORDER BY order_id desc LIMIT 1") );

        Notifications::cardInPlay($this->getActivePlayerId(), $this->cards->getCard($selectedCard_id), true);
        
        if($this->gamestate->state_id() == ST_PLAYER_ARTIFACT_EFFECT_MANAGER) $this->gamestate->nextState('playCardEffect');
        else $this->gamestate->nextState('nextEffect');
    }

    public function nextEffect() {
        $this->incGameStateValue(EFFECT_STEP, 1);
        
        $this->gamestate->nextState('nextEffect');
    }
    
    // cards specific actions
    
    function action_ChangeActivePlayer() {
        $this->gamestate->nextState('changeActivePlayer');
    }

    function action_GrumpyGuard() {
        $tribes_mvt = $this->getCountFactionPlayer( $this->getActivePlayerId(), WATER_FOLK) >= 1 ? 2 : 0;

        $tribes_mvt = $this->checkTokenLimit($tribes_mvt, $this->getGameStateValue(TRIBES_TOKEN));

        $this->moveToken(0,$tribes_mvt);
    }

    function action_ResurrectedRam() {
        $this->discard(array_keys($this->cards->getCardsInLocation('tavern')),false, TAVERN);

        for ($i = 1; $i <= 3; $i++) {

            if($this->getRemainingCards('graveyard') > 0) {
                $card = $this->getCardInfos($this->cards->pickCardForLocation('graveyard', 'tavern', $i));
                
                Notifications::fillTavern($card, GRAVEYARD);
            }
        }

        $this->nextEffect();
    }

    public function action_HangryBarbarian() {
        $ids = $this->getCollectionFromDB('SELECT * FROM card WHERE card_type IN ('.EMPIRE.','.UNDEAD.','.EMPEROR.') AND card_location = "tavern"');

        $this->discard(array_keys($ids), false, TAVERN);

        $empire_mvt = $this->checkTokenLimit(- count($ids), $this->getGameStateValue(EMPIRE_TOKEN));

        $this->moveToken($empire_mvt,0);
    }

    public function action_HeartBendingBard() {
        $ids = $this->getCollectionFromDB('SELECT * FROM card WHERE card_type IN ('.TRIBES.','.WATER_FOLK.','.EMPEROR.') AND card_location = "tavern"');

        $this->discard(array_keys($ids), false, TAVERN);

        $tribes_mvt = $this->checkTokenLimit(- count($ids), $this->getGameStateValue(TRIBES_TOKEN));

        $this->moveToken(0, $tribes_mvt);
    }

    public function action_NaggingNorthman() {
        //$empire_mvt = $this->getGameStateValue(NB_DISCARDED_CARD) > 0 ? 2 : 0;

        $empire_mvt = $this->checkTokenLimit(2, $this->getGameStateValue(EMPIRE_TOKEN));

        $this->moveToken($empire_mvt, 0);
    }

    public function action_AngryPriestess() {
        //$tribes_mvt = $this->getGameStateValue(NB_DISCARDED_CARD) > 0 ? -2 : 0;

        $tribes_mvt = $this->checkTokenLimit(-2, $this->getGameStateValue(TRIBES_TOKEN));
        
        $this->moveToken(0, $tribes_mvt);
    }

    public function action_NaughtyNecromancer() {
        $nbCardsGraveyard = $this->cards->countCardInLocation('graveyard');
        $nbCardsGraveyard = $nbCardsGraveyard > 3 ? 3 : $nbCardsGraveyard;

        $empire_mvt = $this->checkTokenLimit($nbCardsGraveyard, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = $this->checkTokenLimit($nbCardsGraveyard, $this->getGameStateValue(TRIBES_TOKEN));
        
        $this->moveToken($empire_mvt, $tribes_mvt);
    }

    public function action_DoubleShieldedTurtle() {
        $cards = $this->getCollectionFromDB('SELECT * FROM card WHERE card_type IN ('.$this->getGameStateValue(SELECTED_FACTION).','.EMPEROR.') AND card_location = "tavern"');

        $this->discard(array_keys($cards), false, TAVERN);
        
        $this->nextEffect();
    }

    public function action_BoredGoblin() {
        $empire_mvt = $this->getCountFactionPlayer( $this->getActivePlayerId(), UNDEAD) >= 1 ? -2 : 0;

        $empire_mvt = $this->checkTokenLimit($empire_mvt, $this->getGameStateValue(EMPIRE_TOKEN));

        $this->moveToken($empire_mvt,0);
    }

    public function action_SunShySkeleton() {
        $this->setGameStateValue(SELECTED_CARD, $this->cards->getCardOnTop('graveyard') ? $this->getTopCard('graveyard')->id : 0);

        if($this->getGameStateValue(SELECTED_CARD) != 0) $this->performEffect();
        else $this->nextEffect();
    }

    public function action_ModestMonsterslayer() {
        $card_id = $this->getGameStateValue(SELECTED_CARD);

        $this->cards->moveCard($card_id, 'hiddenCards', $this->getActivePlayerId());

        $card = $this->cards->getCard($card_id);
        Notifications::place($this->getActivePlayerId(), $card, TAVERN, HIDDEN, false);

        $this->nextEffect();
    }

    public function action_InsidiousImpaler() {
        $card_id = $this->getGameStateValue(SELECTED_CARD);
        
        $this->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);

        $card = $this->cards->getCard($card_id);
        Notifications::bury($this->getActivePlayerId(), null, $card, TAVERN, false);
        
        if($this->getGameStateValue(DRAW_FATE) == 1) {
            for ($i = 1; $i <= 2; $i++) {
                $card = $this->cards->pickCardForLocation('fate','fateCardInPick');
                Notifications::drawFate(self::getActivePlayerId(), $card);
            }
            $this->gamestate->nextState('drawFate');
            return;
        }
        $this->nextEffect();
    }

    public function action_JoylessChief() {
        if($this->getRemainingCards('discard') == 0) {
            $this->nextEffect();
            return;
        }

        $this->drawCard('discard', 2, true);
    }

    public function action_ShellFishDefender() {
        if($this->getRemainingCards('discard') == 0) {
            $this->nextEffect();
            return;
        }
        $this->drawCard('discard', 4, true);
    }

    public function action_CrowCarrier() {
        if($this->getRemainingCards('graveyard') == 0) {
            $this->nextEffect();
            return;
        }
        
        $this->drawCard('graveyard', 2, true);
    }

    public function action_DrawFromDeck() {
        $this->drawCard('deck', 2, true);
    }
    public function action_AbysmalAutomaton() {
        $this->drawCard('deck', 5, true);
    }

    public function action_SeaweedChopper() {
        if($this->getRemainingCards('discard') == 0) {
            $this->nextEffect();
            return;
        }
        
        $this->drawCard('discard', 2, true);
    }

    public function action_FirmFishmonger() {
        if($this->getRemainingCards('discard') == 0) {
            $this->nextEffect();
            return;
        }
        
        $this->drawCard('discard', 3, true);
    }
    public function action_AllKnowingAntler() {
        $this->cards->shuffle('wilderness');
        $this->drawCard('discard', $this->getRemainingCards('discard'), true);
    }
    public function action_WrappedWarrior() {
        $this->drawCard('graveyard', $this->getRemainingCards('graveyard'), true);
        // //usort($cards, fn($a, $b) => $a['location_arg'] < $b['location_arg']);
    }
    public function action_ObnoxiousNightmare() {
        $this->drawCard('graveyard', $this->getRemainingCards('graveyard'), true);
    }
    public function action_DemonicDarter() {
        $this->drawCard('graveyard', $this->getRemainingCards('graveyard'), true);
    }

    public function action_SurprisedSapling(int $target_id) {
        $playerId = intval($this->getActivePlayerId());

        $this->setGameStateValue(SELECTED_PLAYER, $target_id);

        $remainingCards = $this->getRemainingCards('hand'.$target_id);

        $cards = [];
        
        for ($i = 1; $i <= $remainingCards; $i++) {
            $location_arg = $this->getLocationArg('hand'.$target_id);
            $card = $this->cards->pickCardForLocation('hand'.$target_id, 'cardInPick', $location_arg);

            if ($card == null) throw new BgaUserException("Invalid card");

            array_push($cards, $card);

            Notifications::drawCard($playerId, $card, HAND, 'hand'.$target_id, true);
        }

        $this->nextEffect();
    }

    public function action_KeenKoi() {
        if($this->getTokenAhead() == 0) {
            $this->moveToken(0,0);
            return;
        }

        // EMPIRE_TOKEN AHEAD
        $tribes_mvt = 1;
        $empire_mvt = -1;

        // TRIBES_TOKEN AHEAD
        if($this->getTokenAhead() == 2) {
            $tribes_mvt = -1;
            $empire_mvt = 1;
        }

        $empire_mvt = $this->checkTokenLimit($empire_mvt, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = $this->checkTokenLimit($tribes_mvt, $this->getGameStateValue(TRIBES_TOKEN));

        $this->moveToken($empire_mvt,$tribes_mvt);
    }

    public function action_TentacleOracle() {
        if($this->getRemainingCards('discard') == 0) {
            $this->nextEffect();
            return;
        }

        $empire_mvt = $tribes_mvt = $this->cards->getCardOnTop('discard')['type'] != UNDEAD ? -3 : 0;

        $empire_mvt = $this->checkTokenLimit($empire_mvt, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = $this->checkTokenLimit($tribes_mvt, $this->getGameStateValue(TRIBES_TOKEN));
        
        Notifications::reveal($this->getActivePlayerId(), $this->cards->getCardOnTop('discard'), WILDERNESS);

        $this->moveToken($empire_mvt,$tribes_mvt);
    }

    public function action_WillBendingWitch() {
        $this->gamestate->setPlayersMultiactive( $this->getPossibleOpponents(), 'endEffect');

        $this->incGameStateValue(EFFECT_STEP, 1);

        $this->gamestate->nextState('willBendingWitch');
    }

    public function action_PhilantropicPhantom() {
        $this->gamestate->setPlayersMultiactive( array_values(array_filter($this->getPlayersIds(), fn($playerId) => 
            $playerId != $this->getActivePlayerId() && intval($this->cards->countCardInLocation('visibleCards', $playerId) + $this->cards->countCardInLocation('hiddenCards', $playerId)) > 0
        )), 'endEffect');

        $this->incGameStateValue(EFFECT_STEP, 1);

        $this->gamestate->nextState('philantropicPhantom');
    }

    public function action_HardShelledTitan() {
        $this->gamestate->setPlayersMultiactive( $this->getPossibleOpponents(), 'endEffect');

        $this->incGameStateValue(EFFECT_STEP, 1);

        $this->gamestate->nextState('hardShelledTitan');
    }

    public function bury(int $card_id) {
        $playerId = intval($this->getCurrentPlayerId());

        $card = $this->cards->getCard($card_id);
        $location = $card['location'] == 'visibleCards' ? VISIBLE : HIDDEN;
        
        $this->cards->moveCard($card_id, 'graveyard', $this->getLocationArg('graveyard') + 1);

        Notifications::bury($playerId, $playerId, $card, $location, false);

        $this->gamestate->setPlayerNonMultiactive($playerId, 'endEffect');
    }

    public function handToCardInPick(int $card_id) {
        $playerId = intval($this->getCurrentPlayerId());
        $currentPlayer = $this->getGameStateValue(CURRENT_PLAYER);

        $card = $this->cards->getCard($card_id);

        $this->cards->moveCard($card_id, 'cardinPick');

        Notifications::handToCardInPick($playerId, $currentPlayer, $card);

        $this->gamestate->setPlayerNonMultiactive($playerId, 'endEffect');
    }

    public function action_KindKingSlayer() {
        if($this->getTokenAhead() == 0) {
            $this->moveToken(0,0);
            return;
        }
        $tribes_mvt = 0;
        $empire_mvt = 0;

        // EMPIRE_TOKEN AHEAD ELSE TRIBES_TOKEN AHEAD
        $this->getTokenAhead() == 1 ? $empire_mvt = -2 : $tribes_mvt = -2;

        $empire_mvt = $this->checkTokenLimit($empire_mvt, $this->getGameStateValue(EMPIRE_TOKEN));
        $tribes_mvt = $this->checkTokenLimit($tribes_mvt, $this->getGameStateValue(TRIBES_TOKEN));

        $this->moveToken($empire_mvt,$tribes_mvt);
    }

    public function action_UnderwaterArtist() {
        // $faction_name = '';
        // switch($this->getGameStateValue(SELECTED_FACTION)) {
        //     case UNDEAD:
        //         $faction_name = 'undead';
        //     break;
        //     case WATER_FOLK:
        //         $faction_name = 'water_folk';
        //     break;
        //     case EMPIRE:
        //         $faction_name = 'empire';
        //     break;
        //     case TRIBES:
        //         $faction_name = 'tribes';
        //     break;
        // }

        Notifications::noFaction($this->getGameStateValue(SELECTED_PLAYER), $this->getGameStateValue(SELECTED_FACTION));
        $this->setGameStateValue(SELECTED_FACTION, 0);
        
        $this->gamestate->nextState('changeActivePlayer');
    }

    public function spreadCorruption() {
        //Notification spread corruption + draw tokens 
        
        // $token = $this->corruption_tokens->getCard($token_id);
        // $this->corruption_tokens->moveCard($token_id,'hand'.$this->getActivePlayerId());
        $onCorruptionCardId = $this->corruption_tokens->getCardOnTop( 'onCorruptionCard' )['id'];

        $token = $this->corruption_tokens->pickCardForLocation('onCorruptionCard','hand'.$this->getActivePlayerId());

        Notifications::drawCorruptionToken($this->getActivePlayerId(), $token, $onCorruptionCardId);

        // Add 1 token to every player's hand
        foreach($this->getPlayerIdsInOrder() as $player_id) {
            $token = $this->corruption_tokens->pickCardForLocation('deck','hand'.$player_id);

            Notifications::drawCorruptionToken($player_id, $token, null);
        }

        $this->gamestate->nextState('spreadCorruption');
    }

    public function moveCorruptionToken($token_id, $card_id) {

        $playerId = $this->getActivePlayerId();
        $card = $this->getCardInfos($this->cards->getCard($card_id));
        $targetId = $card->location_arg;
        $anonymize = $card->location == 'hiddenCards';

        $this->corruption_tokens->moveCard($token_id,'onHeroCard',$card_id);

        $this->DbQuery( "UPDATE corruption SET card_type_arg = $playerId WHERE card_id = $token_id" );

        Notifications::moveCorruptionToken($playerId, $this->corruption_tokens->getCard($token_id), $card, $targetId, $anonymize);
        
        if($this->corruption_tokens->countCardInLocation( 'hand'.$playerId ) == 0) $this->gamestate->nextState('nextPlayer');
    }

    public function selectArtifact(int $artifact_id, int $player_id ) {
        $artifact = ArtifactCard::initCard($artifact_id, $player_id);

        Notifications::selectArtifact($artifact, $player_id);

        $this->gamestate->setPlayerNonMultiactive($player_id, 'setupFinished');
    }

    public function playArtifact() {
        $playerId = $this->getActivePlayerId();

        $artifact = ArtifactCard::play($playerId);

        $this->setGameStateValue(ARTIFACT_PLAYED, $artifact->id);

        $this->setGameStateValue(EFFECT_STEP, 0);
        $this->setGameStateValue(SELECTED_PLAYER, 0 );
        $this->setGameStateValue(SELECTED_CARD, 0);
        $this->setGameStateValue(SELECTED_CARD_2, 0);

        Notifications::playArtifact($artifact, $playerId);

        $this->gamestate->nextState('playArtifact');
    }
    
    public function action_ConfusingCrystal(int $card_id, int $target_id ) {
        $card = $this->cards->getCard($card_id);
        $args['card_id'] = $card_id;
        $args['card'] = $card;
        
        $args['target_id'] = $target_id;

        $this->getPlayedCardClass()->moveCard($args);

        $this->nextEffect();
    }
}
