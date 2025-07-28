class WillBendingWitchStates {
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
         
      this.game.makeDiscardSelectable(false);
      document.getElementById('discard').onclick = null;
    }

    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        this.player_table = this.game.getCurrentPlayerTable();
        
        const handleChange = () => {
            const card_selected = this.player_table.playerHand.getSelection().length == 1;

            this.game.makeDiscardSelectable(card_selected);
            this.game.toggleButton("discard_button", card_selected);
        };
        
        this.player_table.playerHand.setSelectionMode('single');
        this.player_table.playerHand.onSelectionChange = handleChange;

        const discardConfirm = () => {
          if(this.player_table.playerHand.getSelection().length != 1) return;
          const selected_card = this.player_table.playerHand.getSelection()[0];

          if(selected_card != null) {
              this.game.takeActionNoLock("discard", {
              ids: selected_card.id,
              });
          }

          dojo.empty('customActions');
    
          this.player_table.playerHand.setSelectionMode("none");
          this.player_table.playerHand.onSelectionChange = null;
             
          this.game.makeDiscardSelectable(false);
          document.getElementById('discard').onclick = null;
        };

        document.getElementById('discard').onclick = discardConfirm;
        if(!document.getElementById('discard_button')) this.game.addDiscardButton(discardConfirm);
        this.game.toggleButton("discard_button", false);
    }

    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }    
}