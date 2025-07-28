class EndScoreStates {
    game;
    client_args;
  
    constructor(game) {
        this.game = game;
    }
  
    onEnteringState(args) {

        //var winningFaction = args ? args.winningFaction : this.game.gamedatas.winningFaction;
        
        var winningFaction = args.winningFaction;
        
        // Reveal hidden cards
        if(args) args.cards.forEach(card => this.game.cardsManager.setCardVisible(card,true));

        this.game.playersTables.forEach(playerTable => {
            playerTable.playerHiddenCards.setOpened(true);

            playerTable.playerHiddenCards.getCards().forEach(card => this.game.incValueFactionCounter(card.type, playerTable.playerId, 1, card.type_arg >= 91));
        });
        
        // Show Table
        document.getElementById(`score-row-player-name`).querySelector("td:first-child").innerHTML = _('Name');
        
        document.getElementById(`score-row-leader`).querySelector("td:first-child").innerHTML = _('Leader');
        
        document.getElementById(`score-row-hero-faction`).querySelector("td:first-child").innerHTML = `<span class='card-icon faction faction-${winningFaction}'></span> : <span class='card-icon icon icon-${  this.game.VISIBLE}'></span> + <span class='card-icon icon icon-${  this.game.HIDDEN}'></span><br>(Most wins)`;
        document.getElementById(`score-row-hero-total`).querySelector("td:first-child").innerHTML = `<span class='card-icon icon icon-${  this.game.VISIBLE}'></span> + <span class='card-icon icon icon-${  this.game.HIDDEN}'></span><br>(Least wins)`;
        document.getElementById(`score-row-leader-value`).querySelector("td:first-child").innerHTML = _('Leader Value<br>(Higher wins)');

        document.getElementById('hiddenleaders-score').style.display = 'flex';
        
        //const players = Object.values(this.game.gamedatas.players);
        //const orderedPlayers = this.game.getOrderedPlayers();

        this.game.gamedatas.playersorder.forEach(playerId => {
            var player = this.game.gamedatas.players[playerId];

            // Reveal leaders from board
            var el = document.getElementById(`player_table_leader_${player.id}`);
            
            el.parentElement.dataset.currentPlayer = 'true';
            el.dataset.leaderId = player.leader_id;

            dojo.place(`<td id="score-${player.id}" class="player-name status-score-${player.id}" style="color: #${player.color}">${player.name}</td>`, 'score-row-player-name');
            
            //if we are a reload of end state, we display values, else we wait for notifications
            
            dojo.place(`<td><div id="row-leader-${player.id}" class="leader-card status-score-${player.id}" data-leader-id="0" style="margin:0 auto;"</div></td>`, 'score-row-leader');
            
            dojo.place(`<td id="row-hero-faction-${player.id}" class="status-score-${player.id}"></td>`, 'score-row-hero-faction');

            dojo.place(`<td id="row-hero-total-${player.id}" class="status-score-${player.id}"></td>`, 'score-row-hero-total');

            dojo.place(`<td id="row-leader-value-${player.id}" class="status-score-${player.id}"></td>`, 'score-row-leader-value');

            // dojo.place(`<td id="row-leader-${player.id}" class="leader-card status-score-${player.id}" data-leader-id="${this.game.gamedatas.endGame ? player.leader_id : 0}"></td>`, 'score-row-leader');
            
            // dojo.place(`<td id="row-hero-faction-${player.id}" class="status-score-${player.id}">${this.game.gamedatas.endGame ? player.heroFaction : ''}</td>`, 'score-row-hero-faction');

            // dojo.place(`<td id="row-hero-total-${player.id}" class="status-score-${player.id}">${this.game.gamedatas.endGame ? player.heroTotal : ''}</td>`, 'score-row-hero-total');

            // dojo.place(`<td id="row-leader-value-${player.id}" class="status-score-${player.id}">${this.game.gamedatas.endGame ? player.leaderValue : ''}</td>`, 'score-row-leader-value');
        });

        // if(this.game.gamedatas.endGame) {
        //     //document.getElementById(`winning-faction`) = _(`${this.game.gamedatas.winningFaction_translated} ${winningFaction}`);

        //     dojo.removeClass(document.getElementById('score-row-hero-faction'),'hide-score');
        //     dojo.removeClass(document.getElementById('score-row-hero-total'),'hide-score');
        //     dojo.removeClass(document.getElementById('score-row-leader-value'),'hide-score');
        // }
    }
  
    onLeavingState() {
    }
  
    onUpdateActionButtons(args) {
    }
}