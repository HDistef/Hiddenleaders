class FateStates {
    game;

    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
 
    onLeavingState() {
      dojo.empty('customActions');
      
      this.game.decks.fatePick_cards.setSelectionMode("none");
      this.game.decks.fatePick_cards.onSelectionChange = null;

      document.getElementById('card-pick').dataset.visible = false;
    }
 
    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;

        document.getElementById('card-pick').dataset.visible = true;

        const selectConfirm = () => {
            if(this.game.decks.fatePick_cards.getSelection().length != 1) return;

            const played_fateCard = this.game.decks.fatePick_cards.getSelection()[0];
            console.log(played_fateCard);
            if(played_fateCard !== null) {
                this.game.decks.fatePick_cards.setSelectionMode("none");
                
                this.game.takeAction("playFateCard", {
                    id: played_fateCard.id,
                });
            }
        };

        this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), selectConfirm);
        this.game.toggleButton("select_button", false);

        const handleChange = () => {
            this.game.toggleButton("select_button", this.game.decks.fatePick_cards.getSelection().length == 1);
        };

        this.game.decks.fatePick_cards.setSelectionMode('single');
        this.game.decks.fatePick_cards.onSelectionChange = handleChange;
    }

    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }