// define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
//   return declare('hiddenleaders.playertable', null, {
class PlayerTable {
  playerId;

  playerVisibleCards = [];
  playerHiddenCards;
  playerHand;
  playerArtifact;

  constructor(game, player) {
    // Player Table
    this.playerId = Number(player.id);
    
    var currentPlayer = this.playerId == game.getPlayerId();
    
    var leader_id = currentPlayer ? player.leader_id : 0;
    let html = `
    <span id="player-head-${this.playerId}" class="player-head" data-current-player="${currentPlayer}" style="color:#${player.color}">${player.name}</span>
      <div id="player-table-${this.playerId}" class="player-table">
        <div class="player-table-card-wrapper" data-current-player="${currentPlayer}">
          <span class="title">${_("Leader")}</span>
          <div id="player_table_leader_${this.playerId}" class="leader-card" data-leader-id="${leader_id}" style="margin:10px 8px;"></div>
        </div>
    `;
    
    if(player.artifact) {
      html += `<div class="player-table-card-wrapper">
        <span class="title">${_("Artifact")}</span>
        <div id="player_table_artifact_${this.playerId}" style="margin:10px 8px;"></div>
      </div>`;
    }
    
    if(currentPlayer) {
      html += `
        <div class="player-table-card-wrapper" style="flex:auto">
          <span class="title">${_("Hand")}</span>
          <div id="player_table_hand_${this.playerId}" style="margin:10px;"></div>
        </div>
      `
    }
    html+= `
        <div class="break"></div>

        <div id="player_table_hidden_wrapper_${this.playerId}" class="player-table-card-wrapper">
          <div class='card-icon icon icon-${game.HIDDEN}'></div><span class="title">${_("Hidden cards")}</span>
          <div id="player_table_hidden_${this.playerId}" style="margin:10px 8px;"></div>
        </div>

        <div id="player_table_visible_wrapper_${this.playerId}" class="player-table-card-wrapper" style="flex:auto">
          <div class='card-icon icon icon-${game.VISIBLE}'></div><span class="title">${_("Visible cards")}</span>
            <div class='title-faction' data-faction="${game.UNDEAD}">
              ${_(game.gamedatas.factions[game.UNDEAD])} <span class='card-icon faction faction-${game.UNDEAD}'></span>
              <div id="player_table_visible_${game.UNDEAD}_${this.playerId}" style="margin-top:5px;"></div>
            </div>
            <div class='title-faction' data-faction="${game.WATER_FOLK}">
              ${_(game.gamedatas.factions[game.WATER_FOLK])} <span class='card-icon faction faction-${game.WATER_FOLK}'></span>
              <div id="player_table_visible_${game.WATER_FOLK}_${this.playerId}" style="margin-top:5px;"></div>
            </div>
            <div class='title-faction' data-faction="${game.EMPIRE}">
              ${_(game.gamedatas.factions[game.EMPIRE])} <span class='card-icon faction faction-${game.EMPIRE}'></span>
              <div id="player_table_visible_${game.EMPIRE}_${this.playerId}" style="margin-top:5px;"></div>
            </div>
            <div class='title-faction' data-faction="${game.TRIBES}">
              ${_(game.gamedatas.factions[game.TRIBES])} <span class='card-icon faction faction-${game.TRIBES}'></span>
              <div id="player_table_visible_${game.TRIBES}_${this.playerId}" style="margin-top:5px;"></div>
            </div>
      `;

    // if(game.GUARDIAN in game.gamedatas.factions) {
    //     html += `
    //         <div class='title-faction' data-faction="${game.GUARDIAN}">
    //           ${_(game.gamedatas.factions[game.GUARDIAN])} <span class='card-icon faction faction-${game.GUARDIAN}'></span>
    //           <div id="player_table_visible_${game.GUARDIAN}_${this.playerId}" style="margin-top:5px;"></div>
    //         </div>
    //     `
    // }

    html += `
            <div class='title-faction' data-faction="${game.YELLOW}">
              <span style="display: inline-grid;align-items: center; height:var(--iconHeight);">${_("Special Cards")}</span>
              <div id="player_table_visible_${game.YELLOW}_${this.playerId}" style="margin-top:5px;"></div>
            </div>
          </div>
        </div>
    `;

    dojo.place(html, document.getElementById('player_tables'));
    
    html = `<div class="cardToolTip" style="font-size:large">
              <div style="text-align:center">
                <strong>
                ${dojo.string.substitute( _("You can claim the victory if ${faction1} OR ${faction2} is victorious"), {
                  faction1: `<span style="white-space: nowrap"><span class='card-name faction-${player.leader.faction_ids[0]}'>${_(game.gamedatas.factions[player.leader.faction_ids[0]])}</span> <span class='card-icon faction faction-${player.leader.faction_ids[0]}'></span></span>`,
                  faction2: `<span style="white-space: nowrap"><span class='card-name faction-${player.leader.faction_ids[1]}'>${_(game.gamedatas.factions[player.leader.faction_ids[1]])}</span> <span class='card-icon faction faction-${player.leader.faction_ids[1]}'></span></span>`
                })}
                </strong>
              </div>
              <div style="text-align:center">${_(player.leader.description)}</div>
            </div>`;
    game.addTooltipHtml(`player_table_leader_${this.playerId}`, html, 200);

    // Main du joueur
    if(currentPlayer) {
      this.playerHand = new LineStock(game.cardsManager, document.getElementById(`player_table_hand_${this.playerId}`), {center: false});
    }
    else this.playerHand = new HandStock(game.cardsManager, document.getElementById(`hand-${this.playerId}`), {cardOverlap:'5px', cardShift:'2px'});
    this.playerHand.addCards(Object.values(player.hand));
    
    if(player.artifact) {
      this.playerArtifact = new LineStock(game.artifactCardsManager, document.getElementById(`player_table_artifact_${this.playerId}`));
      Object.values(player.artifact).forEach(artifact => {
        this.playerArtifact.addCard(artifact);

        for (let i = 1; i <= artifact.uses; i++) {
          dojo.place(game.createArtifactToken(i), `artifactcard-${artifact.id}-front`);
        }

      });

    }

    if(game.isHorizontal()) {
      ///////////////////
      // INLINE LAYOUT //
      ///////////////////
      
      document.getElementById(`player_table_visible_wrapper_${this.playerId}`).querySelectorAll('[class="title-faction"]').forEach((element) => {
        this.playerVisibleCards[element.dataset.faction] = new AllVisibleDeck(game.cardsManager, document.getElementById(`player_table_visible_${element.dataset.faction}_${this.playerId}`), {
        });
      });

      // for (let i = 1; i <= 5; i++) {
      //   this.playerVisibleCards[i] = new LineStock(game.cardsManager, document.getElementById(`player_table_visible_${i}_${this.playerId}`), {

      //   });
      // }

      this.playerHiddenCards = new AllVisibleDeck(game.cardsManager, document.getElementById(`player_table_hidden_${this.playerId}`), {
        verticalShift: '0px',
        horizontalShift: '40px',
        direction:'horizontal'
      });
      
      Object.values(player.hiddenCards).forEach(card => this.playerHiddenCards.addCard(card));
      Object.values(player.visibleCards).forEach(card => card.isYellow ? this.playerVisibleCards[game.YELLOW].addCard(card) : this.playerVisibleCards[card.type].addCard(card));
      //Object.values(player.visibleCards).forEach(card => this.playerVisibleCards.addCard(card));
    }
    else {
      ////////////////////
      // COLUMNS LAYOUT //
      ////////////////////
      
      document.getElementById(`player_table_visible_wrapper_${this.playerId}`).querySelectorAll('[class="title-faction"]').forEach((element) => {
        this.playerVisibleCards[element.dataset.faction] = new AllVisibleDeck(game.cardsManager, document.getElementById(`player_table_visible_${element.dataset.faction}_${this.playerId}`), {
          verticalShift: '45px',
          horizontalShift: '30px',
        });
      });

      // for (let i = 1; i <= 5; i++) {
      //   this.playerVisibleCards[i] = new AllVisibleDeck(game.cardsManager, document.getElementById(`player_table_visible_${i}_${this.playerId}`), {
      //     verticalShift: '45px',
      //     horizontalShift: '30px',
      //   });
      // }

      this.playerHiddenCards = new AllVisibleDeck(game.cardsManager, document.getElementById(`player_table_hidden_${this.playerId}`), {
        verticalShift: '45px',
        horizontalShift: '30px',
        direction:'horizontal'
      });

      Object.values(player.hiddenCards).forEach(card => this.playerHiddenCards.addCard(card));
      Object.values(player.visibleCards).forEach(card => card.isYellow ? this.playerVisibleCards[game.YELLOW].addCard(card) : this.playerVisibleCards[card.type].addCard(card));
    }
  }

  // getAllCards() {
  //   return [
  //       ...this.playerVisibleCards.getCards(),
  //       ...this.playerHiddenCards.getCards(),
  //   ];
  // }

  // addCardToLineStockWithAnimation(card, fromElement) {
  //   const animationSettings = {
  //       fromElement: fromElement
  //   };

  //   // if (customAnimation) {
  //   //     animationSettings.animation = new BgaAnimation(stockSlideWithDoubleLoopAnimation, {});
  //   // }

  //   this.tableCards.addCard(card, animationSettings);
  // },

  // applyToLeader(element,fn) {
  //   setTimeout(() => fn(element), 200);
  // }
  // slideToScreenCenter(element) {    
  //   this.applyToLeader(element, e => animationManager.attachWithAnimation(
  //     new BgaShowScreenCenterAnimation({ e }),
  //   ));
  // }
};