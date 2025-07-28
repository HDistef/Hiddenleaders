<?php

trait ArgsTrait {
    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */
    function argPlayerSetup(int $playerId)
    {   
        // $data = ['_private' => []];

        // $players = self::loadPlayersBasicInfos();
        // foreach (array_keys($players) as $player_id) {
        //     $data['_private'][$player_id]['discard'] = $this->cards->getCardsInLocation('discard',$player_id);
        //     $data['_private'][$player_id]['hiddenCard'] = $this->cards->getCardsInLocation('hiddenCards',$player_id);
        // }
        // return $data;
        return [
            'discard' => $this->cards->getCardsInLocation('discard',$playerId),
            'hiddenCard' => $this->cards->getCardsInLocation('hiddenCards',$playerId)
        ];
    }

    function argCardEffect()
    {   
        $args['visible'] = VISIBLE;
        $args['hidden'] = HIDDEN;
        $args['wilderness'] = WILDERNESS;
        $args['empire'] = EMPIRE;
        $args['tribes'] = TRIBES;
        $args['undead'] = UNDEAD;
        $args['guardian'] = GUARDIAN;
        $args['player'] = PLAYER;

        $card_id = $this->getGameStateValue(CARD_PLAYED);
        
        //Spread Corruption
        if($this->isCorruption()) {
            $args['canSpread'] = false;

            $nextOnCorruptionCard = $this->corruption_tokens->getCardOnTop( 'onCorruptionCard' );
            //$nbTokenLeft = $this->corruption_tokens->countCardInLocation('onCorruptionCard');
            $nbCardsVisible = $this->cards->countCardsByLocationArgs( 'visibleCards' );

            if($nextOnCorruptionCard != null && $nbCardsVisible > 0) {

                $maxVisibles = max(array_values($nbCardsVisible));
                $nbCardsVisible = $this->SPREAD_CORRUPTION[count($this->getPlayersIds())][$nextOnCorruptionCard['location_arg']];
                
                if($maxVisibles >= $nbCardsVisible) $args['canSpread'] = true;
            }
        }
        //Play Artifact
        if(ArtifactCard::availability($this->getActivePlayerId()) == FAST) $args['artifactAvailable'] = true;

        if($card_id == 0) return $args;
        
        $effect_step = $this->getGameStateValue(EFFECT_STEP);

        $card = $this->getCardInfos($this->cards->getCard($card_id));
        $cardClass = HeroCard::getInstanceOfCard($card);
        
        $args['card'] = $cardClass;
        
        $args['card_name'] = $card->name;
        $args['card_faction_icon'] = $card->type;

        if(!array_key_exists($effect_step, $cardClass->jsactions)) return $args;
        
        $args['card_action'] = $cardClass->jsactions[$effect_step];

        if(array_key_exists($effect_step, $cardClass->jsdescriptions)) $args['card_description'] = '${card_name} - '.$cardClass->jsdescriptions[$effect_step];

        $args['selectable_cards'] = $cardClass->selectableCards;

        $args['selectable_opponents'] = $cardClass->selectableOpponents;

        $args['selectable_factions'] = $this->getFactions();
        
        $args['empireToken'] = intval($this->getGameStateValue(EMPIRE_TOKEN));
        $args['tribesToken'] = intval($this->getGameStateValue(TRIBES_TOKEN));
        $args['tokenAhead'] = $this->getTokenAhead();

        if ($args['card_action'] == 'draw_MiniatureMerman') {
            $args['remainingCardsInGraveyard'] = $this->getRemainingCards('graveyard');
            $args['remainingCardsInDiscard'] = $this->getRemainingCards('discard');
        }
        
        $args['current_player'] = $this->getGameStateValue(CURRENT_PLAYER);
        if ($this->getGameStateValue(SELECTED_PLAYER) != 0) $args['target_id'] = $this->getGameStateValue(SELECTED_PLAYER);

        return $args;
    }

    function argArtifactEffect()
    {   
        $args['visible'] = VISIBLE;
        $args['hidden'] = HIDDEN;
        $args['wilderness'] = WILDERNESS;
        $args['empire'] = EMPIRE;
        $args['tribes'] = TRIBES;
        $args['undead'] = UNDEAD;
        $args['guardian'] = GUARDIAN;
        $args['player'] = PLAYER;

        $artifact_id = $this->getGameStateValue(ARTIFACT_PLAYED);
        
        if($artifact_id == 0) return $args;
        
        $effect_step = $this->getGameStateValue(EFFECT_STEP);

        $artifact = $this->ARTIFACT_CARDS[$artifact_id];
        $artifactClass = ArtifactCard::getInstanceOfArtifact($artifact);
        
        $args['card'] = $artifactClass;
        
        $args['artifact_name'] = $artifact->name;

        if(!array_key_exists($effect_step, $artifactClass->jsactions)) return $args;
        
        $args['card_action'] = $artifactClass->jsactions[$effect_step];

        if(array_key_exists($effect_step, $artifactClass->jsdescriptions)) $args['card_description'] = '${artifact_name} - '.$artifactClass->jsdescriptions[$effect_step];

        $args['selectable_cards'] = $artifactClass->selectableCards;

        $args['selectable_opponents'] = $artifactClass->selectableOpponents;
        
        $args['current_player'] = $this->getGameStateValue(CURRENT_PLAYER);
        
        return $args;
    }
    
    function argDrawCard() {
        $nbCards = 4 - $this->cards->countCardInlocation('hand'.$this->getActivePlayerId());
        return [
            'tavern' => TAVERN,
            'harbor' => HARBOR,
            'nbCards' => '<span style="font-size: x-large">'.$nbCards.'</span>',
            'nbCards_value' => $nbCards
        ];
    }
    
    function argDiscard() {
        $nbCards = $this->cards->countCardInlocation('hand'.$this->getActivePlayerId()) - 3;
        return [
            'wilderness' => WILDERNESS,
            'nbCards' => '<span style="font-size: x-large">'.$nbCards.'</span>'
        ];
    }
    
    function argWillBendingWitch() {
        return [
            'card_name' => $this->CARDS_DATA[HERO_48]['name'],
            'card_faction_icon' => $this->CARDS_DATA[HERO_48]['faction_id'],
        ];
    }
    
    function argPhilantropicPhantom() {
        return [
            'visible' => VISIBLE,
            'hidden' => HIDDEN,
            'card_name' => $this->CARDS_DATA[HERO_109]['name'],
            'card_faction_icon' => $this->CARDS_DATA[HERO_109]['faction_id'],
            //'selectable_cards' => array_merge(array_keys(HiddenLeaders::get()->cards->getCardsInLocation('visibleCards', $this->getCurrentPlayerId())), array_keys(HiddenLeaders::get()->cards->getCardsInLocation('hiddenCards', $this->getCurrentPlayerId()))),
        ];
    }
    
    function argHardShelledTitan() {
        return [
            'card_name' => $this->CARDS_DATA[HERO_113]['name'],
            'card_faction_icon' => $this->CARDS_DATA[HERO_113]['faction_id'],
        ];
    }

    function argFate() {
        return [
            'fateCards' => $this->cards->getCardsInLocation('fateCardInPick')
        ];
    }
    function argSpreadCorruption() {
        return [
            'corruptionToken' => array_values($this->corruption_tokens->getCardsInLocation('hand'.$this->getActivePlayerId())),
            'allCards' => array_keys(self::getCollectionFromDB("SELECT * FROM card WHERE card_location IN ('visibleCards','hiddenCards')"))
        ];
    }

    // function argArtifactSetup() {
    //     foreach ($this->getPlayersIds() as $player_id) {
    //         $data['_private'][$player_id]['artifacts'] = ArtifactCard::getArtifact($player_id);
    //     }
    //     return $data;
    // }

    function argArtifact() {
        $artifact = ArtifactCard::getArtifact($this->getActivePlayerId())[0];
        return [
            'artifact' => $artifact,
            'artifact_name' => $artifact->name
        ];
    }

    function argEndScore() {
        return [
            'cards' => $this->getCardsInfos($this->cards->getCardsInLocation('hiddenCards')),
            'winningFaction' => $this->getWinningFaction(),
        ];
    }
}
