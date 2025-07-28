class SelectCardStates {
    game;
    client_args;
    selected_cards = [];

    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
 
    onLeavingState() {
      if (!this.game.isCurrentPlayerActive()) return;

      dojo.empty('customActions');
      
      this.selected_cards = [];

      this.client_args.selectable_cards.forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          el.classList.toggle('bga-cards_selectable-card', false);
          el.classList.toggle('bga-cards_selected-card', false);
          el.onclick = null;
      });
    }
 
    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      this.client_args = args;

      const selectCard = e => {
        const el = e.target.parentElement.parentElement;

        if (args.nb_cards_to_select == 1) this.selected_cards.filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(c));
        
        if (this.selected_cards.find(c => c.dataset.cardId == el.dataset.cardId)) {
          this.unselectCard(el);
        }
        else {
          this.selected_cards.push(el);
          el.classList.add('bga-cards_selected-card');
        }

        args.nb_cards_to_select == this.selected_cards.length ? this.game.toggleButton("select_button", true) : this.game.toggleButton("select_button", false);
      };

      args.selectable_cards.forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          el.classList.toggle('bga-cards_selectable-card');

          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

          el.onclick = selectCard;
      });
      
      this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => this.selectConfirm());
      this.game.toggleButton("select_button", false);
      
      if(args.canPass) this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction('nextEffect',{}));
      
      if(args.takeAction == 'discard') this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction("nextEffect", {}));
    }
    
    selectConfirm() {
      if(this.selected_cards.length < 1 || this.selected_cards.length > this.client_args.nb_cards_to_select ) return;
      
      //this.game.actionManager.addClientArgument(this.selected_cards.map(card => card.dataset.cardId));
      
      this.game.takeAction(this.client_args.takeAction, {
        ids: this.selected_cards.map(card => card.dataset.cardId).join(";"),
        //target_id: el.parentElement.dataset?.playerId
      });

      if(this.client_args.nextEffect) this.game.takeAction('nextEffect', {});
    }
    
    unselectCard(element) {
      element === null || element === void 0 ? void 0 : element.classList.remove('bga-cards_selected-card');
      var index = this.selected_cards.findIndex(c => c.dataset.cardId == element.dataset.cardId);
      if (index !== -1) {
          this.selected_cards.splice(index, 1);
      }
    }

    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }