class ExchangeStates {
    game;
    client_args;
    el_card_1 = null;
    el_card_2 = null;

    constructor(game) {
        this.game = game;
    }
  
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
  
    onLeavingState() {
      dojo.empty('customActions');
      
      if(this.el_card_1) this.el_card_1.classList.remove('bga-cards_selected-card');
      if(this.el_card_2) this.el_card_2.classList.remove('bga-cards_selected-card');
      
      this.el_card_1 = null;
      this.el_card_2 = null;

      this.client_args.selectable_cards[0].forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        dojo.removeClass(el, 'bga-cards_selectable-card');
        el.onclick = null;
      });

      if(Array.isArray(this.client_args.selectable_cards[1])) {
        this.client_args.selectable_cards[1].forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 
        dojo.removeClass(el, 'bga-cards_selectable-card');
        el.onclick = null;
      });
    }
    }
  
    onUpdateActionButtons(args) {
      if (!this.game.isCurrentPlayerActive()) return;
      this.client_args = args;

      this.game.addPrimaryActionButton(`exchange_button`, _("Confirm exchange"), () => this.exchange());
      this.game.toggleButton("exchange_button", false);

      if(args.canPass) this.game.addDangerActionButton(`pass_button`, _("Pass"), () => this.game.takeAction('nextEffect',{}));

      const exchange_card_1_Confirm = e => {
        if(this.el_card_1 != null) this.el_card_1.classList.remove('bga-cards_selected-card');

        const el = e.target.parentElement.parentElement;
        el.classList.add('bga-cards_selected-card');
        this.el_card_1 = el;

        if(this.el_card_1 != null && this.el_card_2 != null ) this.game.toggleButton("exchange_button", true);
      };

      args.selectable_cards[0].forEach(card_id => {
          const el = document.getElementById(`herocard-${card_id}`); 
          dojo.addClass(el, 'bga-cards_selectable-card');

          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

          el.onclick = exchange_card_1_Confirm;
      });

      // Specific to MummyMystic
      if(!Array.isArray(args.selectable_cards[1])) {
        var card_id = args.selectable_cards[1];

        const el = document.getElementById(`herocard-${card_id}`); 

        this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

        document.getElementById('graveyard_cards').classList.remove('card-stock', 'deck');
        el.classList.add( 'bga-cards_selected-card' );
        this.el_card_2 = el;
        return;
      }

      const exchange_card_2_Confirm = e => {
        if(this.el_card_2 != null) this.el_card_2.classList.remove('bga-cards_selected-card');

        const el = e.target.parentElement.parentElement;
        el.classList.add('bga-cards_selected-card');
        this.el_card_2 = el;

        if(this.el_card_1 != null && this.el_card_2 != null ) this.game.toggleButton("exchange_button", true);
      };

      args.selectable_cards[1].forEach(card_id => {
        const el = document.getElementById(`herocard-${card_id}`); 

        if(el.parentElement.id == 'graveyard_cards') {
          el.parentElement.classList.remove('card-stock', 'deck');
          el.classList.add( 'bga-cards_selected-card' );
          this.el_card_2 = el;
          return;
        }

        dojo.addClass(el, 'bga-cards_selectable-card');
        
          this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);

        el.onclick = exchange_card_2_Confirm;
      });
    }

    exchange() {
      if(this.el_card_1 == null || this.el_card_2 == null ) return;

      if(this.el_card_2.parentElement.id == 'graveyard_cards') {
        this.el_card_2.parentElement.classList.add('card-stock', 'deck');
      }

      this.game.takeAction('exchange', {
        card_1_id: this.el_card_1.dataset.cardId,
        card_2_id: this.el_card_2.dataset.cardId,
      });
    }

    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }