<?php

trait StateTrait {

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    function stPlayerSetup() {
        $this->gamestate->setAllPlayersMultiActive();

        $this->gamestate->initializePrivateStateForAllActivePlayers(); 
    }
    
    // Set a player as First Player
    function stSetupTransition() {
      
      $i = 0; //TODO à tester
      foreach ($this->getPlayersIds() as $playerId) {
        self::DBQuery("UPDATE card set card_location_arg = $i WHERE card_location = 'discard' AND card_location_arg = $playerId"); 
        $i++;
      }

      $firstPlayer = self::activeNextPlayer();
      self::setGameStateInitialValue(FIRST_PLAYER, $firstPlayer);

      $this->giveExtraTime($firstPlayer);
      
      self::incStat( 1, "turns_number", $firstPlayer );
      self::incStat( 1, "turns_number" );

      $this->gamestate->nextState('nextPlayer');
    }

    function stGameEffectManager() {
      $card_id = $this->getGameStateValue(CARD_PLAYED);
      
      $effect_step = $this->getGameStateValue(EFFECT_STEP);
      $card = $this->getCardInfos($this->cards->getCard($card_id));
      $cardClass = HeroCard::getInstanceOfCard($card); 
      
      if(array_key_exists($effect_step, $cardClass->phpactions)) {
        $action = $cardClass->phpactions[$effect_step];
        
        HiddenLeaders::$action();
        return;
      }
      else if (array_key_exists($effect_step, $cardClass->jsactions)) {
        return;
      }
      else {
        if($card->isGuardian) $this->moveGuardianToken();

        if (count($this->getCardsInPlay()) > 0) {
          $cardInPlay_id = $this->getNextCardInPlay();
          
          $this->setGameStateValue(CARD_PLAYED, $cardInPlay_id);
          $this->setGameStateValue(SELECTED_CARD, $this->cards->getCardOnTop( "cardInPlay")["id"]);
          
          $this->setGameStateValue(EFFECT_STEP, 10);
          
          if($this->getGameStateValue(ARTIFACT_PLAYED) <> 0) $this->gamestate->nextState('artifactEffect');
          else $this->gamestate->nextState('nextEffect');
        }
        //Play Artifact
        else if(ArtifactCard::availability($this->getActivePlayerId()) == FAST) {
          $this->setGameStateValue(PREVIOUS_STATE, ST_PLAYER_CARD_EFFECT_MANAGER);
          $this->gamestate->nextState('artifact');
        }
        else $this->gamestate->nextState('nextStep');
      }
    }

    function stArtifactEffectManager() {
      $artifact_id = $this->getGameStateValue(ARTIFACT_PLAYED);
      $artifact = $this->ARTIFACT_CARDS[$artifact_id];
      $artifactClass = ArtifactCard::getInstanceOfArtifact($artifact);
      
      $effect_step = $this->getGameStateValue(EFFECT_STEP);
      
      if(array_key_exists($effect_step, $artifactClass->phpactions)) {
        $action = $artifactClass->phpactions[$effect_step];
        
        HiddenLeaders::$action();
        return;
      }
      else if (array_key_exists($effect_step, $artifactClass->jsactions)) return;
      
      else {
        if($this->getGameStateValue(PREVIOUS_STATE) == ST_PLAYER_ACTION) $this->gamestate->nextState('nextStepFastAction');
        else if($this->getGameStateValue(PREVIOUS_STATE) == ST_PLAYER_CARD_EFFECT_MANAGER) $this->gamestate->nextState('nextStepFastDraw');
        else $this->gamestate->nextState('nextStepSlow');
      }
    }


    function stChangeActivePlayer() {
      $playerTurn = $this->getGameStateValue(CURRENT_PLAYER);
      $activePlayer = intval($this->getActivePlayerId());
      
      $changeActivePlayer = $playerTurn != $activePlayer ? $playerTurn : $this->getGameStateValue(SELECTED_PLAYER);
      
      $this->gamestate->changeActivePlayer( $changeActivePlayer );

      $this->incGameStateValue(EFFECT_STEP, 1);

      $this->gamestate->nextState('changeActivePlayer');
    }
    
    function stDrawCard() {
      if($this->cards->countCardInlocation('hand'.$this->getActivePlayerId()) >= 4) $this->gamestate->nextState('discard');
    }

    function stNextPlayer() {
      //Fill up tavern
      $tavern_cards = $this->cards->getCardsInLocation('tavern');

      for ($i = 1; $i <= 3; $i++) {
        $slotToFill = true;
        foreach ($tavern_cards as $card_id => $card) {
          if($card["location_arg"] == $i) $slotToFill = false;
        }
        if($slotToFill) {
          // if(HiddenLeaders::get()->cards->countCardInLocation('deck') == 1) {
          //   $card = $this->getCardInfos($this->cards->pickCardForLocation('deck', 'tavern', $i));
          //   Notifications::refillDeck();
          // }
          // else $card = $this->getCardInfos($this->cards->pickCardForLocation('deck', 'tavern', $i));
          
          $card = $this->getCardInfos($this->cards->pickCardForLocation('deck', 'tavern', $i));
          Notifications::fillTavern($card, HARBOR);
        }
      }
      
      $this->setGameStateValue(CARD_PLAYED, 0);
      
      //Play Artifact
      if(ArtifactCard::availability($this->getActivePlayerId()) == SLOW) {
        $this->setGameStateValue(PREVIOUS_STATE, ST_NEXT_PLAYER);
        $this->gamestate->nextState('artifact');
        return;
      }
      $this->setGameStateValue(ARTIFACT_PLAYED,0);
      $this->setGameStateValue(PREVIOUS_STATE, ST_PLAYER_ACTION);
      $this->setGameStateValue(EFFECT_STEP, 0);

      if($this->isArtifacts()) {
        $trigger = $this->getMaxCardsEndGame();
        $endGamePlayer = 0;

        foreach ($this->getPlayersIds() as $playerId) {
          $nbVisibles = $this->getRemainingCards('visibleCards', $playerId);
          if($nbVisibles >= $trigger) $endGamePlayer = $playerId;
        }

        if($endGamePlayer != 0) {
          self::notifyAllPlayers('endTrigger', clienttranslate('${player_name} has triggered the end of the game'), [
            'playerId' => $endGamePlayer,
            'player_name' => self::getPlayerName($endGamePlayer),
          ]);
          $this->gamestate->nextState('endScore');
        }
      }

      $playerId = $this->activeNextPlayer();
      
      if($this->getGameStateValue(SKIP_PLAYER) == $playerId) {
        $playerId = $this->activeNextPlayer();
        $this->setGameStateValue(SKIP_PLAYER,0);
        
        // self::notifyAllPlayers('', clienttranslate('${player_name} has to skip his next turn'), [
        //   'playerId' => $playerId,
        //   'player_name' => self::getPlayerName($playerId),
        // ]);
      }

      $this->giveExtraTime($playerId);
      
      self::incStat( 1, "turns_number", $playerId );
      if($playerId == $this->getGameStateValue(FIRST_PLAYER)) self::incStat( 1, "turns_number" );

      $this->gamestate->nextState('nextPlayer');
    }

    function stNextPlayerCorruption() {
      $playerId = $this->activeNextPlayer();
      $this->giveExtraTime($playerId);

      if($playerId == $this->getGameStateValue(CURRENT_PLAYER)) $this->gamestate->nextState('endSpread');
      else $this->gamestate->nextState('nextPlayer');
    }

    function stCheckTriggerEnd() {
      $trigger = $this->getMaxCardsEndGame();
      $endGamePlayer = 0;
      
      foreach ($this->getPlayersIds() as $playerId) {
        $nbVisibles = $this->getRemainingCards('visibleCards', $playerId);
        if($nbVisibles >= $trigger) $endGamePlayer = $playerId;
      }

      if($endGamePlayer != 0) {
        //Notifications::triggerEnd($endGamePlayer);
        
        self::notifyAllPlayers('endTrigger', clienttranslate('${player_name} has triggered the end of the game'), [
          'playerId' => $endGamePlayer,
          'player_name' => self::getPlayerName($endGamePlayer),
        ]);

        $this->gamestate->nextState('endScore');
      }
      else {
        $this->gamestate->nextState('drawCard');
      }
    }

    function stEndScore() {

      //notify winning faction
      $winningFaction = $this->getWinningFaction();
      self::notifyAllPlayers('endWinningFaction', clienttranslate('${winningFaction} is the winning faction'), [
        'winningFaction_translated' => $this->FACTIONS[$winningFaction],
        'i18n' => array( 'winningFaction_translated' ),
        'winningFaction' => $winningFaction //$this->getFactionName($winningFaction)
      ]);

		  self::setStat( $winningFaction, 'winning_faction');

      //get players with aligned leader
      $alignedLeaders = [];
      foreach ($this->getPlayerIdsInOrder() as $playerId) {
        $leader = $this->getLeader($playerId);

        if(in_array($winningFaction, $leader->faction_ids)) {
          $nbCardsFaction = $winningFaction == GUARDIAN ? $this->getCountGuardianPlayer($playerId, $winningFaction, false) : $this->getCountFactionPlayer($playerId, $winningFaction, false);

          $nbCardsFaction += $this->checkGameEndSpecialCards($playerId, $winningFaction);

          $alignedLeaders[$playerId] = $nbCardsFaction;
          
          self::DbQuery("UPDATE player SET `player_score` = $nbCardsFaction WHERE player_id = $playerId");
          //Notifications::setPlayerScore($playerId, $nbCardsFaction, $winningFaction);
        }
        
        self::setStat( intval($this->cards->countCardInLocation('visibleCards', $playerId)), 'visibleCards', $playerId);
        self::setStat( intval($this->cards->countCardInLocation('hiddenCards', $playerId)), 'hiddenCards', $playerId);
      }

      $winner = 0;
      
      $args['visible'] = VISIBLE;
      $args['hidden'] = HIDDEN;
      
      $args['leaders'] = HiddenLeaders::get()->getLeaders();
      $args['winningFaction'] = $winningFaction;
      $args['nbCardsFaction'] = $alignedLeaders;
      $args['aligned_player_ids'] = array_keys($alignedLeaders);

      //no contender => no winner
      if(count($alignedLeaders) == 0) {
        $description = clienttranslate('No player is aligned with the winning faction !');
      }
      // one contender => winner
      else if(count($alignedLeaders) == 1) {
        $winner = array_keys($alignedLeaders)[0];

        if(array_values($alignedLeaders)[0] == 0) {
          self::DbQuery("UPDATE player SET `player_score` = 1 WHERE player_id = $winner");
        }

        $description = clienttranslate('${player_name} is the only player aligned with the winning faction');
        $args['winner_id'] = $winner;
        $args['nbCardsFaction'] = array_values($alignedLeaders)[0];
        $args['player_name'] = $this->getPlayerName($winner);
      }
      //multiple contenders for the victory
      else {
        $player_ids = [];
        foreach($alignedLeaders as $playerId => $value) {
          array_push($player_ids, $playerId);
        }
        
        $description = clienttranslate('${player_ids} are aligned with the winning faction');
        $args['player_ids'] = $player_ids;
      }
      
      $leaders = HiddenLeaders::get()->getLeaders();
      foreach ($this->getPlayerIdsInOrder() as $playerId) {
        self::notifyAllPlayers('scoreAlignedLeaders', '', [
          'playerId' => $playerId,
          'leaderId' => $leaders[$playerId]
        ]);
      }
      // foreach (HiddenLeaders::get()->getLeaders() as $playerId => $leader) {
      //   self::notifyAllPlayers('scoreAlignedLeaders', '', [
      //       'playerId' => $playerId,
      //       'leaderId' => $leader
      //   ]);
      // }

      self::notifyAllPlayers('endAlignedLeaders', $description, $args);

      if(count($alignedLeaders) <= 1) {
        $this->gamestate->nextState('endGame');
        return;
      }

      //winner is the player with the most heroes of the winning faction
      $mostHeroesFaction = array_keys($alignedLeaders, max($alignedLeaders));
      $args['mostHeroesFaction'] = $mostHeroesFaction;
      $args['mostHeroesFactionValue'] = max($alignedLeaders);
      

      if(count($mostHeroesFaction) == 1) {
        $winner = array_values($mostHeroesFaction)[0];
        
        $description = clienttranslate('${player_name} is the player with the most heroes of the winning faction (${mostHeroesFactionValue})');
        $args['winner_id'] = $winner;
        $args['player_name'] = $this->getPlayerName($winner);
      }
      else {
        //tie : the player with the lower total number of heroes
        $player_ids = [];
        $totalHeroes = [];

        foreach($mostHeroesFaction as $nbHeroes => $playerId) {
          array_push($player_ids, $playerId);

          $nbTotalCards = intval($this->cards->countCardInlocation('visibleCards', $playerId)) + intval($this->cards->countCardInlocation('hiddenCards', $playerId));
          $totalHeroes[$playerId] = $nbTotalCards;

          self::DbQuery("UPDATE player SET `player_score_aux` = - $nbTotalCards WHERE player_id = $playerId");
        }

        $description = clienttranslate('${player_ids} are tied for the most heroes of the winning faction (${mostHeroesFactionValue})');
        $args['player_ids'] = $player_ids;
      }

      foreach ($alignedLeaders as $playerId => $nbCardsFaction) {
        self::notifyAllPlayers('scoreMostHeroesFaction', '', [
            'playerId' => $playerId,
            'nbCardsFaction' => $nbCardsFaction,
            'winningFaction' => $winningFaction
        ]);
      }

      self::notifyAllPlayers('endMostHeroesFaction', $description, $args);

      if(count($mostHeroesFaction) == 1) {
        $this->gamestate->nextState('endGame');
        return;
      }

      $lowestTotalHeroes = array_keys($totalHeroes, min($totalHeroes));
      $args['lowestTotalHeroes'] = $lowestTotalHeroes;
      $args['totalHeroes'] = $totalHeroes;
      $args['lowestTotalHeroesValue'] = min($totalHeroes);

      if(count($lowestTotalHeroes) == 1) {
        $winner = array_values($lowestTotalHeroes)[0];

        $description = clienttranslate('${player_name} is the player with the lowest total number of heroes (${lowestTotalHeroesValue})');
        $args['winner_id'] = $winner;
        $args['player_name'] = $this->getPlayerName($winner);

        //self::DbQuery("UPDATE player SET `player_score` = `player_score` + 1,  `player_score_aux` = `player_score_aux` + 1 WHERE player_id = $winner");
        //Notifications::incPlayerScore($winner, 1);
      }
      else {
        //in case of tie, the leader with the higher number wins
        $player_ids = [];
        $leaderValues = [];
        $higherNumber = 0;

        foreach($lowestTotalHeroes as $nbTotal => $playerId) {
          array_push($player_ids, $playerId);

          $leader_number = $this->getLeader($playerId)->card_value;

          $leaderValues[$playerId] = $leader_number;

          if($leader_number > $higherNumber) {
            $higherNumber = $leader_number;
            $winner = $playerId;
          }
          
          self::DbQuery("UPDATE player SET `player_score_aux` = $leader_number WHERE player_id = $playerId");
        }
        $description = clienttranslate('${player_ids} are tied for for the lower total number of heroes (${lowestTotalHeroesValue})');
        $args['player_ids'] = $player_ids;
      }
      
      foreach ($totalHeroes as $playerId => $totalHero) {
        self::notifyAllPlayers('scoreLowestTotalHeroes', '', [
            'playerId' => $playerId,
            'totalHeroes' => $totalHero
        ]);
      }

      self::notifyAllPlayers('endLowestTotalHeroes', $description, $args);

      if(count($lowestTotalHeroes) == 1) {
        $this->gamestate->nextState('endGame');
        return;
      }
      
      foreach ($leaderValues as $playerId => $leaderValue) {
        self::notifyAllPlayers('scoreLeaderNumber', '', [
            'playerId' => $playerId,
            'leaderValue' => $leaderValue
        ]);
      }

      self::notifyAllPlayers('endLeaderNumber', clienttranslate('${player_name} has the leader with the higher number (${leaderValue})'), [
        'winner_id' => $winner,
        'player_name' => $this->getPlayerName($winner),
        'leaderValue' => $higherNumber
      ]);

      $this->gamestate->nextState('endGame');
    }
}
