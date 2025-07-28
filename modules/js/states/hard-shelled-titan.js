class HardShelledTitanStates {
    player_table;
    game;

    constructor(game) {
      this.game = game;
    }

    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }

    onLeavingState() {
      dojo.empty('customActions');

      this.player_table.playerHand.setSelectionMode("none");
      this.player_table.playerHand.onSelectionChange = null;
    }

    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        this.player_table = this.game.getCurrentPlayerTable();
        
        const handleChange = () => {
            const card_selected = this.player_table.playerHand.getSelection().length == 1;

            this.game.toggleButton("select_button", card_selected);
        };
        
        this.player_table.playerHand.setSelectionMode('single');
        this.player_table.playerHand.onSelectionChange = handleChange;

        const discardConfirm = () => {
          if(this.player_table.playerHand.getSelection().length != 1) return;
          const selected_card = this.player_table.playerHand.getSelection()[0];

          if(selected_card != null) {
              this.game.takeActionNoLock("handToCardInPick", {
              id: selected_card.id,
              });
          }

          dojo.empty('customActions');
    
          this.player_table.playerHand.setSelectionMode("none");
          this.player_table.playerHand.onSelectionChange = null;
        };

        this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => discardConfirm());
        this.game.toggleButton("select_button", false);
    }

    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }    
}