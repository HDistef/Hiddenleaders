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
      dojo.empty('customActions');
      
      this.selected_cards = [];

      this.client_args.selectable_cards.forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        
        if(el) {
          el.classList.toggle('bga-cards_selectable-card', false);
          el.classList.toggle('bga-cards_selected-card', false);
          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(false);

          el.onclick = null;
        }
      });
      //     // if(!Array.isArray(cards)) this.removeSelectableCards(cards);
      //     // else cards.forEach(card_id => this.removeSelectableCards(card_id));
      // });
    }
    // removeSelectableCards(card_id) {
    //   console.log(card_id);
    //   const el = document.getElementById(`herocard-${card_id}`); 
    //   console.log(el);
    //   el.classList.toggle('bga-cards_selectable-card', false);
    //   el.classList.toggle('bga-cards_selected-card', false);
    //   el.onclick = null;
    // }

    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      this.client_args = args;
      
      if(args.pick_card) document.getElementById('card-pick').dataset.visible = true;

      this.game.addPrimaryActionButton(`select_button`, _("Confirm selection"), () => this.selectConfirm());
      this.game.toggleButton("select_button", false);

      document.getElementById(`herocard-${this.client_args.selectable_cards[0]}`).scrollIntoView({ behavior: "smooth", block: "center" });

      this.client_args.selectable_cards.forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        
        el.classList.toggle('bga-cards_selectable-card');
        this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);
        
        el.onclick = () => this.selectCard(el);
      });
      
      if(args.canPass) this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction('nextEffect',{}));
      
      if(args.takeAction == 'discard') this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction("nextEffect", {}));
    }

    selectCard(cardElement) {
      const el = cardElement;
      
      if (this.client_args.nb_cards_to_select == 1) this.selected_cards.filter(c => c.dataset.cardId != el.dataset.cardId).forEach(c => this.unselectCard(c));

      if (this.selected_cards.find(c => c.dataset.cardId == el.dataset.cardId)) {
        this.unselectCard(el);
      }
      else {
        this.selected_cards.push(el);
        el.classList.add('bga-cards_selected-card');
      }
      
      const toggleButton = this.checkConfirm();

      this.game.toggleButton("select_button", toggleButton);
    }

    checkConfirm() {
      const selected_cards_length = this.selected_cards.length;
      return this.client_args.canPass ? selected_cards_length > 0 && selected_cards_length <= this.client_args.nb_cards_to_select
      : selected_cards_length == this.client_args.nb_cards_to_select;
    }
    
    selectConfirm() {
      if(!this.checkConfirm()) return;

      this.game.takeAction(this.client_args.takeAction, {
        ids: this.selected_cards.map(card => card.dataset.cardId).join(";")
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