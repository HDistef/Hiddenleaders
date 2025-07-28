const states = {
    client: {
      moveToken: "client_moveToken",
      selectCard: "client_selectCard",
      multiSelectCard: "client_multiSelectCard",
      //selectOpponent: "client_selectOpponent",
      pickCard: "client_pickCard",
      exchange: "client_exchange",
      bludgeoningBlowfish: "client_bludgeoningBlowfish",
      firmFishmonger: "client_firmFishmonger",
      confusingCrystal: "client_confusingCrystal"
    },
    server: {
      playerSetup: "playerSetup",
      playerAction: "playerAction",
      cardEffect: "cardEffect",
      drawCard: "drawCard",
      discard: "discard",
      willBendingWitch: "willBendingWitch",
      philantropicPhantom: "philantropicPhantom",
      hardShelledTitan: "hardShelledTitan",
      fate: 'fate',
      spreadCorruption: 'spreadCorruption',
      artifactSetup: 'artifactSetup',
      artifact: 'artifact',
      endScore: "endScore",
    },
 };

 class StateManager {
   states;
   client_states = [];
   game;
   
    constructor(game) {
      this.game = game;
      
       this.states = {
            [states.client.moveToken]: new MoveTokenStates(game),
            [states.client.selectCard]: new SelectCardStates(game),
            [states.client.multiSelectCard]: new MultiSelectCardStates(game),
            [states.client.pickCard]: new PickCardStates(game),
            [states.client.exchange]: new ExchangeStates(game),
            [states.client.bludgeoningBlowfish]: new BludgeoningBlowfishStates(game),
            [states.client.firmFishmonger]: new FirmFishmongerStates(game),
            [states.client.confusingCrystal]: new ConfusingCrystalStates(game),

            [states.server.playerSetup]: new PlayerSetupStates(game),
            [states.server.playerAction]: new PlayerActionStates(game),
            [states.server.cardEffect]: new CardEffectStates(game),
            [states.server.drawCard]: new DrawCardStates(game),
            [states.server.discard]: new DiscardStates(game),
            [states.server.willBendingWitch]: new WillBendingWitchStates(game),
            [states.server.philantropicPhantom]: new PhilantropicPhantomStates(game),
            [states.server.hardShelledTitan]: new HardShelledTitanStates(game),
            [states.server.fate]: new FateStates(game),
            [states.server.spreadCorruption]: new SpreadCorruptionStates(game),
            [states.server.artifactSetup]: new ArtifactSetupStates(game),
            [states.server.artifact]: new ArtifactStates(game),
            [states.server.endScore]: new EndScoreStates(game),
       };
    }
 
    onEnteringState(stateName, args) {
       console.log("Entering state: " + stateName);
       
       if (this.states[stateName] !== undefined) {
          this.states[stateName].onEnteringState(args.args);
          if (stateName.startsWith("client_")) {
             this.client_states.push(this.states[stateName]);
          } else {
             this.client_states.splice(0);
          }
       } else {
          this.client_states.splice(0);
       }
    }
 
    onLeavingState(stateName) {
      console.log("Leaving state: " + stateName);
 
       if (this.states[stateName] !== undefined) {
         dojo.empty('customActions');
         
          if (this.game.isCurrentPlayerActive()) {
             this.states[stateName].onLeavingState();
          }
       }
    }
 
    onUpdateActionButtons(stateName, args) {
       if (this.states[stateName] !== undefined) {
          if (this.game.isCurrentPlayerActive()) {
             this.states[stateName].onUpdateActionButtons(args);
          }
       }
    }
 
   //  async restoreGameState() {
   //     return new Promise(async (resolve) => {
   //        while (this.client_states.length > 0) {
   //           const state = this.client_states.pop();
   //           await state.restoreGameState();
   //        }
   //        resolve(true);
   //     });
   //  }
 }