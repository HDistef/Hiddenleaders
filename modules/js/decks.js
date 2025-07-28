class Decks {
  deck;
  discard_cards;
  graveyard_cards;
  tavern_cards;

  constructor(game, gamedatas) {
    this.deck = new Deck(game.cardsManager,document.getElementById('deck'), {
      autoUpdateCardNumber:true,
      topCard: gamedatas.deckTopCard,
      cardNumber: gamedatas.remainingCardsInDeck,
      counter: {
          //extraClasses: 'pile-counter',
      },
      thicknesses: [0, 2, 5, 10, 20, 30]
    });
    //this.deck.setCardVisible(gamedatas.deckTopCard, false);

    this.discard_cards = new Deck(game.cardsManager,document.getElementById('discard'), {
      //autoRemovePreviousCards:false,
      autoUpdateCardNumber:true,
      topCard: gamedatas.discardTopCard,
      cardNumber: gamedatas.remainingCardsInDiscard,
      counter: {},
      thicknesses: [0, 2, 5, 10, 20, 30]
    });
    //if (gamedatas.discardTopCard != null) this.discard_cards.setCardVisible(gamedatas.discardTopCard, false);

    this.graveyard_cards = new Deck(game.cardsManager,document.getElementById('graveyard_cards'), {
      //autoRemovePreviousCards:false,
      autoUpdateCardNumber:true,
      topCard: gamedatas.graveyardTopCard,
      cardNumber: gamedatas.remainingCardsInGraveyard,
      counter: {
          //extraClasses: 'pile-counter',
      },
      thicknesses: [0, 2, 5, 10, 20, 30]
    });

    this.tavern_cards = new SlotStock(game.cardsManager, document.getElementById(`tavern_stock`), {
        gap: '92px',
        slotsIds: [1,2,3],
        mapCardToSlot: card => card.location_arg,
    });
    for (var herocard_id in gamedatas.tavern) {
        var herocard = gamedatas.tavern[herocard_id];
        this.tavern_cards.addCard(herocard);
    }

    this.pick_cards = new LineStock(game.cardsManager, document.getElementById(`card-pick`), {center: false});
    for (var herocard_id in gamedatas.cardInPick) {
      var herocard = gamedatas.cardInPick[herocard_id];
      this.pick_cards.addCard(herocard);
    }

    this.inPlay_cards = new LineStock(game.cardsManager,document.getElementById('cardInPlay'));
    for (var herocard_id in gamedatas.cardInPlay) {
        var herocard = gamedatas.cardInPlay[herocard_id];
        this.inPlay_cards.addCard(herocard);
    }

    if(gamedatas.fateTopCard) {
      this.fate_cards = new Deck(game.fateCardsManager,document.getElementById('fateCards'), {
        topCard: gamedatas.fateTopCard
      });

      this.fatePick_cards = new LineStock(game.fateCardsManager, document.getElementById(`card-pick`), {center: false});
      for (var fate_id in gamedatas.fateCardInPick) {
        var fate = gamedatas.fateCardInPick[fate_id];
        this.fatePick_cards.addCard(fate);
      }
    }
  }
}