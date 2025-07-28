class SpreadCorruptionStates {
    game;

    constructor(game) {
       this.game = game;
    }
 
    onEnteringState(args) {
      if (!this.game.isCurrentPlayerActive()) return;
    }
 
    onLeavingState() {
      dojo.empty('customActions');

      document.getElementById('card-pick').dataset.visible = false;
    }
 
    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        console.log(args);
        document.getElementById('card-pick').dataset.visible = true;

        const selectCard = (cardId, tokenId) => {
            this.game.takeAction('moveCorruptionToken', {
                token_id: tokenId,
                card_id: cardId
            });
        };

        const selectToken = (tokenEl, tokenId) => {
            
            //if (args.corruptionToken.length > 1) args.corruptionToken.filter(t => t.id != tokenId).forEach(t => document.getElementById(`corruptionToken_${t.id}`).toggle('bga-cards_selected-card', false));

            tokenEl.classList.toggle('bga-cards_selected-card');
            
            // this.game.playersTables.forEach(playerTable => {
            //     playerTable.playerHiddenCards.setOpened(true);
            // });
            args.allCards.forEach(card_id => {
                const el = document.getElementById(`herocard-${card_id}`); 
                el.classList.toggle('bga-cards_selectable-card');
    
                this.game.cardsManager.getCardStock({id : card_id}).setOpened?.(true);
    
                el.onclick = () => selectCard(card_id, tokenId);
            });
        };

        args.corruptionToken.forEach(token => {
            
            const el = dojo.place(this.game.createCorruptionToken(token.id, token.type), 'card-pick');
            //const el = document.getElementById(`corruptionToken_${token.id}`);
            el.classList.add('bga-cards_selectable-card');
            
            el.onclick = () => selectToken(el, token.id);
        });
    }

    restoreGameState() {
       return new Promise<boolean>((resolve) => resolve(true));
    }
 }