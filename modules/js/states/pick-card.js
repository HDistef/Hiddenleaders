class PickCardStates {
    game;
    client_args;
  
    constructor(game) {
        this.game = game;
    }
  
    onEnteringState(args) {
        if (!this.game.isCurrentPlayerActive()) return;
    }
  
    onLeavingState() {
        dojo.empty('customActions');

        this.game.decks.pick_cards.setSelectionMode('none');
        this.game.decks.pick_cards.onCardClick = null;

        document.getElementById('card-pick').dataset.visible = false;
    }
  
    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        this.client_args = args;

        document.getElementById('card-pick').dataset.visible = true;
        
        this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => this.selectConfirm());
        this.game.toggleButton("select_button", false);

        const handleChange = () => {
            const selectButtonToggle = args.nb_cards_to_select == this.game.decks.pick_cards.getSelection().length;
            this.game.toggleButton("select_button", selectButtonToggle);
        };

        // this.client_args.selectable_cards.forEach(card => {
        //     this.game.decks.pick_cards.addCard(card);
        // });
        
        // switch(this.client_args.selectable_cards[0].location) {
        //     case 'deck':
        //         this.game.decks.deck.setCardNumber(0,null);
        //     break;
        //     case 'discard':
        //         this.game.decks.discard_cards.setCardNumber(0,null);
        //     break;
        //     case 'graveyard':
        //         this.game.decks.graveyard_cards.setCardNumber(0,null);
        //     break;
        // }

        this.game.decks.pick_cards.setSelectionMode( args.nb_cards_to_select > 1 ?'multiple' : 'single');
        this.game.decks.pick_cards.onCardClick = handleChange;
    }

    selectConfirm() {
        //if(this.client_args.nb_cards_to_select == this.game.decks.pick_cards.getSelection().length) return;
        
        const selected_card_ids = this.game.decks.pick_cards.getSelection().map((x) => x.id);
        
        this.game.takeAction("moveCard", {
            ids: selected_card_ids.join(";"),
        });
    }
    
    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }