{OVERALL_GAME_HEADER}

<div id="all_area">
    
    <div id="hiddenleaders-score">
        <table id="score-table">
            <caption id="winning-faction"></caption>
            <tr id="score-row-player-name" class="row-player-name">
                <td class="first-column"></td>
            </tr>
            <tr id="score-row-leader" class="row-leader">
                <td class="first-column"></td>
            </tr>
            <tr id="score-row-hero-faction" class="row-hero-faction hide-score">
                <td class="first-column"></td>
            </tr>
            <tr id="score-row-hero-total" class="row-hero-total hide-score">
                <td class="first-column"></td>
            </tr>
            <tr id="score-row-leader-value" class="row-leader-value hide-score">
                <td class="first-column"></td>
            </tr>
        </table>
    </div>
    
    <div id="central_area">
        <div id="cardInPlay"></div>
        <div id="discard" class="cards-stack rotate"></div>
        <div id="deck" class="cards-stack"></div>
        <div id="board"> 
            <div id ="power-track">
                <div id="empire-token" class="power-token transition-token"></div>
                <div id="tribes-token" class="power-token transition-token"></div>
            </div>
        </div>
        <div id="graveyard_cards" class="cards-stack"></div>
        <div id="tavern_stock"></div>

        <div id="tabs-container">
            <div class="hd-tab" id="tab-icon-overview">{ICON OVERVIEW}</div>
            <div class="hd-tab" id="tab-win-conditions">{WIN CONDITIONS}</div>
        </div>
    </div>

    <div id="player_tables"></div>
</div>

{OVERALL_GAME_FOOTER}

<script type="text/javascript">
    var jstpl_corruption = '<div id="corruptionToken_${tokenId}" data-type="${type}" class="card-icon icons-corruption ${onHeroCard}"></div>';
    var jstpl_artifact = '<span class="icon-artifactToken" data-number="${number}"></span>';

    var jstpl_helpModal = `
      <div id="help-container">
        <div class="helper-classes">
          <div class="helper-classes-row">
            <div class="helper-classes-symbol player"></div>
            <div class="helper-classes-desc">
              <strong>\${player}</strong>
              <p>\${playerText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol faceup"></div>
            <div class="helper-classes-desc">
              <strong>\${faceup}</strong>
              <p>\${faceupText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol facedown"></div>
            <div class="helper-classes-desc">
              <strong>\${facedown}</strong>
              <p>\${facedownText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol harbor"></div>
            <div class="helper-classes-desc">
              <strong>\${harbor}</strong>
              <p>\${harborText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol wilderness"></div>
            <div class="helper-classes-desc">
              <strong>\${wilderness}</strong>
              <p>\${wildernessText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol tavern"></div>
            <div class="helper-classes-desc">
              <strong>\${tavern}</strong>
              <p>\${tavernText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-symbol graveyard"></div>
            <div class="helper-classes-desc">
              <strong>\${graveyard}</strong>
              <p>\${graveyardText}</p>
            </div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="1">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="helper-classes-column" data-class="2">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="helper-classes-column" data-class="3">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="helper-classes-column" data-class="4">
              <div class="helper-classes-symbol"></div>
            </div>
            
            <div class="helper-classes-column" data-class="5" data-guardian="0">
              <div class="helper-classes-symbol"></div>
            </div>
            
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="1">
              <strong>\${undead}</strong>
            </div>
            <div class="helper-classes-column" data-class="2">
              <strong>\${waterfolk}</strong>
            </div>
            <div class="helper-classes-column" data-class="3">
              <strong>\${empire}</strong>
            </div>
            <div class="helper-classes-column" data-class="4">
              <strong>\${tribes}</strong>
            </div>

            <div class="helper-classes-column" data-class="5" data-guardian="0">
              <strong>\${guardian}</strong>
            </div>
            
          </div>
        </div>
      </div>
    `;
    
    var jstpl_conditionsModal = `
    <div id="help-container">
        <div class="helper-classes">
          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="1">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="wincon-classes-image" id="wincon-1"></div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-desc">
              <p>\${wincon_undeadText}</p>
              <i style="color:blue;font-size: smaller;">\${wincon_undeadText2}</i>
            </div>
          </div>

          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="2">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="wincon-classes-image" id="wincon-2"></div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-desc">
              <p>\${wincon_waterfolkText}</p>
            </div>
          </div>
          
          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="3">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="wincon-classes-image" id="wincon-3"></div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-desc">
              <p>\${wincon_empireText}</p>
            </div>
          </div>
          
          <div class="helper-classes-row">
            <div class="helper-classes-column" data-class="4">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="wincon-classes-image" id="wincon-4"></div>
          </div>
          <div class="helper-classes-row">
            <div class="helper-classes-desc">
              <p>\${wincon_tribesText}</p>
            </div>
          </div>
          
          <div class="helper-classes-row" data-guardian="0">
            <div class="helper-classes-column" data-class="5">
              <div class="helper-classes-symbol"></div>
            </div>
            <div class="wincon-classes-image" id="wincon-5"></div>
          </div>
          <div class="helper-classes-row" data-guardian="0">
            <div class="helper-classes-desc">
              <p>\${wincon_guardianText}</p>
              <i style="color:blue;font-size: smaller;">\${wincon_guardianText2}</i>
            </div>
          </div>
          
        </div>
      </div>`;
</script>