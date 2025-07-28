class MoveTokenStates {
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
    }
  
    onUpdateActionButtons(args) {
        if (!this.game.isCurrentPlayerActive()) return;
        this.client_args = args;
        this[this.client_args.action]();
    }
    
    moveTokenConfirm(empire_mvt, tribes_mvt) {
        this.game.takeAction('moveToken', {
            empire_mvt,
            tribes_mvt
        });
    }

    pass() {
        this.game.addDangerActionButton(`no_token`, _('No token movement'), () => this.game.takeAction('moveToken',{empire_mvt: 0, tribes_mvt: 0}));
    }

    moveToken() {
        var empire_mvt = this.client_args.token_action[1];
        var tribes_mvt = this.client_args.token_action[2];
        var token_choice = this.client_args.token_action[3];
        
        empire_mvt = this.game.checkTokenLimit(empire_mvt, this.client_args.empireToken);
        tribes_mvt = this.game.checkTokenLimit(tribes_mvt, this.client_args.tribesToken);
        
        if(empire_mvt == 0 && tribes_mvt == 0) {
            this.pass();
        }
        else {
            if(token_choice == 'and') {
                this.game.addBothTokenButton(empire_mvt, tribes_mvt, () => this.moveTokenConfirm(empire_mvt, tribes_mvt));
            }
            else {
                if(empire_mvt != 0) this.game.addEmpireTokenButton('empire_token', empire_mvt, () => this.moveTokenConfirm(empire_mvt, 0));
                if(tribes_mvt != 0) this.game.addTribesTokenButton('tribes_token', tribes_mvt, () => this.moveTokenConfirm(0, tribes_mvt));
                
                if(token_choice == 'and_or' && empire_mvt != 0 && tribes_mvt != 0) {
                    this.game.addBothTokenButton(empire_mvt, tribes_mvt, () => this.moveTokenConfirm(empire_mvt, tribes_mvt));
                }
            }
        }
    }

    moveToken_HairyHermit() {
        var empire_mvt_1 = -1;
        empire_mvt_1 = this.game.checkTokenLimit(empire_mvt_1, this.client_args.empireToken);

        if(empire_mvt_1 != 0) this.game.addEmpireTokenButton('empire_token_1', empire_mvt_1, () => this.moveTokenConfirm(empire_mvt_1, 0));
        else {
            this.pass();
            return;
        }

        if(this.client_args.tokenAhead == this.game.EMPIRE_TOKEN) {
            var empire_mvt_2 = -2;

            empire_mvt_2 = this.game.checkTokenLimit(empire_mvt_2, this.client_args.empireToken);
            if(empire_mvt_2 != 0 && empire_mvt_1 != empire_mvt_2) this.game.addEmpireTokenButton('empire_token_2', empire_mvt_2, () => this.moveTokenConfirm(empire_mvt_2, 0));
        }
    }

    moveToken_AceFighter() {
        var empire_mvt_1 = 1;
        empire_mvt_1 = this.game.checkTokenLimit(empire_mvt_1, this.client_args.empireToken);

        if(empire_mvt_1 != 0) this.game.addEmpireTokenButton('empire_token_1', empire_mvt_1, () => this.moveTokenConfirm(empire_mvt_1, 0));
        else {
            this.pass();
            return;
        }

        if(this.client_args.tokenAhead == this.game.TRIBES_TOKEN) {
            var empire_mvt_2 = 2;
            empire_mvt_2 = this.game.checkTokenLimit(empire_mvt_2, this.client_args.empireToken);
            if(empire_mvt_2 != 0 && empire_mvt_1 != empire_mvt_2) this.game.addEmpireTokenButton('empire_token_2', empire_mvt_2, () => this.moveTokenConfirm(empire_mvt_2, 0));
        }
    }

    moveToken_NaggingNorthman() {
        var empire_mvt = 2;
        empire_mvt = this.game.checkTokenLimit(empire_mvt, this.client_args.empireToken);

        empire_mvt == 0 ? this.pass() : this.moveTokenConfirm(empire_mvt, 0);
    }

    moveToken_AngryPriestess() {
        var tribes_mvt = -2;
        tribes_mvt = this.game.checkTokenLimit(tribes_mvt, this.client_args.tribesToken);
        
        tribes_mvt == 0 ? this.pass() : this.moveTokenConfirm(0, tribes_mvt);
    }
    
    moveToken_SaberToothedTroll() {
        var tribes_mvt_1 = 2;
        tribes_mvt_1 = this.game.checkTokenLimit(tribes_mvt_1, this.client_args.tribesToken);

        var tribes_mvt_2 = -2;
        tribes_mvt_2 = this.game.checkTokenLimit(tribes_mvt_2, this.client_args.tribesToken);
        
        if(tribes_mvt_1 == 0 && tribes_mvt_2 == 0) {
            this.pass();
            return;
        }

        this.game.addTribesTokenButton('tribes_token_1', tribes_mvt_1, () => this.moveTokenConfirm(0, tribes_mvt_1));
        this.game.addTribesTokenButton('tribes_token_2', tribes_mvt_2, () => this.moveTokenConfirm(0, tribes_mvt_2));
    }

    moveToken_DoubtfulPriest() {
        var empire_mvt_1 = 2;
        empire_mvt_1 = this.game.checkTokenLimit(empire_mvt_1, this.client_args.empireToken);

        var empire_mvt_2 = -2;
        empire_mvt_2 = this.game.checkTokenLimit(empire_mvt_2, this.client_args.empireToken);

        if(empire_mvt_1 == 0 && empire_mvt_2 == 0) {
            this.pass();
            return;
        }

        this.game.addEmpireTokenButton('empire_token_1', empire_mvt_1, () => this.moveTokenConfirm(empire_mvt_1, 0));
        this.game.addEmpireTokenButton('empire_token_2', empire_mvt_2, () => this.moveTokenConfirm(empire_mvt_2, 0));
        
    }

    moveToken_DrownedDeserter() {
        if(this.client_args.tokenAhead == 0) {
            this.pass();
            return;
        }

        //   this.game.EMPIRE_TOKEN AHEAD
        var tribes_mvt = 2;
        var empire_mvt = -1;

        //   this.game.TRIBES_TOKEN AHEAD
        if(this.client_args.tokenAhead == this.game.TRIBES_TOKEN) {
            tribes_mvt = -1;
            empire_mvt = +2;
        }
        
        empire_mvt = this.game.checkTokenLimit(empire_mvt, this.client_args.empireToken);
        tribes_mvt = this.game.checkTokenLimit(tribes_mvt, this.client_args.tribesToken);
        
        if(empire_mvt == 0 && tribes_mvt == 0) {
            this.pass();
            return;
        }

        this.game.addEmpireTokenButton('empire_token', empire_mvt, () => this.moveTokenConfirm(empire_mvt, 0));
        this.game.addTribesTokenButton('tribes_token', tribes_mvt, () => this.moveTokenConfirm(0, tribes_mvt));
    }

    moveToken_TripleSwordLizard() {
        if(this.client_args.tokenAhead == 0) {
            this.pass();
            return;
        }

        //   this.game.EMPIRE_TOKEN AHEAD
        if(this.client_args.tokenAhead == this.game.EMPIRE_TOKEN) {
            var empire_mvt_1 = -1;
            var empire_mvt_2 = -3;

            empire_mvt_1 = this.game.checkTokenLimit(empire_mvt_1, this.client_args.empireToken);
            empire_mvt_2 = this.game.checkTokenLimit(empire_mvt_2, this.client_args.empireToken);
            
            if(empire_mvt_1 == 0 && empire_mvt_2 == 0) {
                this.pass();
                return;
            }

            this.game.addEmpireTokenButton('empire_token_1', empire_mvt_1, () => this.moveTokenConfirm(empire_mvt_1, 0));
            if(empire_mvt_2 != 0 && empire_mvt_1 != empire_mvt_2) this.game.addEmpireTokenButton('empire_token_2', empire_mvt_2, () => this.moveTokenConfirm(empire_mvt_2, 0));

        }
        //   this.game.TRIBES_TOKEN AHEAD
        else if(this.client_args.tokenAhead ==   this.game.TRIBES_TOKEN) {
            var tribes_mvt_1 = -1;
            var tribes_mvt_2 = -3;
            tribes_mvt_1 = this.game.checkTokenLimit(tribes_mvt_1, this.client_args.tribesToken);
            tribes_mvt_2 = this.game.checkTokenLimit(tribes_mvt_2, this.client_args.tribesToken);

            if(tribes_mvt_1 == 0 && tribes_mvt_2 == 0) {
                this.pass();
                return;
            }

            this.game.addTribesTokenButton('tribes_token_1', tribes_mvt_1, () => this.moveTokenConfirm(0, tribes_mvt_1));
            if(tribes_mvt_2 != 0 && tribes_mvt_1 != tribes_mvt_2) this.game.addTribesTokenButton('tribes_token_2', tribes_mvt_2, () => this.moveTokenConfirm(0, tribes_mvt_2));
        }
    }

    moveToken_SnappySeaSnake() {
        for (let i = -2; i <= 2; i++) {
            const empire_mvt = this.game.checkTokenLimit(i, this.client_args.empireToken);
            const tribes_mvt = this.game.checkTokenLimit(i, this.client_args.tribesToken);

            if(empire_mvt != 0) this.game.addEmpireTokenButton(`empire_token_${empire_mvt}`, empire_mvt, () => this.moveTokenConfirm(empire_mvt,0));
            if(tribes_mvt != 0) this.game.addTribesTokenButton(`tribes_token_${tribes_mvt}`, tribes_mvt, () => this.moveTokenConfirm(0,tribes_mvt));
        }
    }

    moveToken_OppressedOcean() {
        var empire_mvt = this.client_args.empire_mvt;
        var tribes_mvt = this.client_args.tribes_mvt;

        if(empire_mvt == 0 && tribes_mvt == 0) {
            this.pass();
            return;
        }
        if(empire_mvt != 0) this.game.addEmpireTokenButton('empire_token', empire_mvt, () => this.moveTokenConfirm(empire_mvt, 0));
        if(tribes_mvt != 0) this.game.addTribesTokenButton('tribes_token', tribes_mvt, () => this.moveTokenConfirm(0, tribes_mvt));
        if(empire_mvt != 0 && tribes_mvt != 0) this.game.addBothTokenButton(empire_mvt, tribes_mvt, () => this.moveTokenConfirm(empire_mvt, tribes_mvt));
    }

    restoreGameState() {
        return new Promise<boolean>((resolve) => resolve(true));
    }
  }