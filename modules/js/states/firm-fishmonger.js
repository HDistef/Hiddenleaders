class FirmFishmongerStates {
    game;
    client_args;
    player_table;
  
    constructor(game) {
        this.game = game;
    }
  
    onEnteringState(args) {
        if (!this.game.isCurrentPlayerActive()) return;
    }
  
    onLeavingState() {
        dojo.empty('customActions');
        
        this.player_table.playerHiddenCards.setSelectionMode('none');
        this.game.decks.pick_cards.setSelectionMode('none');
        this.player_table.playerHiddenCards.setOpened(false);

        document.getElementById('card-pick').dataset.visible = false;
    }
  
    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        this.client_args = args;

        this.player_table = this.game.getCurrentPlayerTable();
        
        this.game.addPrimaryActionButton(`exchange_button`, _("Confirm exchange"), () => this.exchange());
        this.game.toggleButton("exchange_button", false);

        document.getElementById('card-pick').dataset.visible = true;
        
        const handleChange = () => {
            const hiddenCardSelected = this.player_table.playerHiddenCards.getSelection().length;
            const cardInPickSelected = this.game.decks.pick_cards.getSelection().length;
            const exchange = hiddenCardSelected == cardInPickSelected == 1;

            this.game.toggleButton("exchange_button", exchange);
        }

        this.game.decks.pick_cards.setSelectionMode('single');
        this.game.decks.pick_cards.onSelectionChange = handleChange;

        this.player_table.playerHiddenCards.setSelectionMode('single');
        this.player_table.playerHiddenCards.onSelectionChange = handleChange;
        this.player_table.playerHiddenCards.setOpened(true);
    }

    exchange() {
        if(this.player_table.playerHiddenCards.getSelection().length == 0 || this.game.decks.pick_cards.getSelection().length == 0 ) return;

        this.game.takeAction('exchange', {
            card_1_id: this.player_table.playerHiddenCards.getSelection()[0].id,
            card_2_id: this.game.decks.pick_cards.getSelection()[0].id,
        });
    }
    
    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }