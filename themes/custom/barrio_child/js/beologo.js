//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
var BeoLogo =
  {
    SLOGAN_CREDITS_BLOCK: '#block-barrio-child-slogancredits',
    SLOGAN_CREDITS_DIALOG: '#SloganCredits',

    FRACTAL_DESC_BLOCK: '#block-barrio-child-fractaldescription',
    FRACTAL_DESC_DIALOG: '#FractalDescription',

    TITLE_LINK: '#block-barrio-child-headerblock .title a',

    FRACTAL_CANVAS_HEADER: '#block-barrio-child-headerblock canvas.fractal',
    FRACTAL_CANVAS_DESCRIPTION: '#FractalDescription canvas.fractal',

    STEPS: 12000,

    faGoldColors: [],
    fnEdges: 32,

    /*
      In the C code, the array sequence was 1-based, but the calculations
      were 0-based. Not sure why. Now all values are 0-based.
    */
    faSequences: [25, 28, 29],
    fnCurrentSequence: 0,

    fnWidth: 0,
    fnHeight: 0,
    foBlock: null,
    foCanvas: null,

    fnLorenzX: 0.9,
    fnLorenzY: 0.1,

    fnCenterX: 0.0,
    fnCenterY: 0.0,

    fnEnlargeX: 0.0,
    fnEnlargeY: 0.0,

    //----------------------------------------------------------------------------------------------------
    startFractal: function ()
    {
      BeoLogo.setupLogoOnClick();

      BeoLogo.initGoldColors();

      BeoLogo.initVariables(BeoLogo.FRACTAL_CANVAS_HEADER);
      BeoLogo.drawFractal();
    },

    //----------------------------------------------------------------------------------------------------
    drawFractal: function ()
    {
      Routines.showAJAX(true);

      let loCanvas = BeoLogo.foCanvas;
      let lnRadianIncrements = (2 * Math.PI) / BeoLogo.fnEdges;

      // This timer creates a loop with a delay of 250 milliseconds between loops.
      let lnTimerID = setInterval(function ()
      {
        let lnRadians = BeoLogo.faSequences[BeoLogo.fnCurrentSequence] * lnRadianIncrements;

        for (let lnSteps = 0; lnSteps < BeoLogo.STEPS; ++lnSteps)
        {
          // Formula for Hénon's Attractor.
          let back = BeoLogo.fnLorenzX;
          BeoLogo.fnLorenzX = BeoLogo.fnLorenzY + 1.0 - (1.4 * BeoLogo.fnLorenzX * BeoLogo.fnLorenzX);
          BeoLogo.fnLorenzY = 0.3 * back;

          let lnTempX = BeoLogo.fnLorenzX * BeoLogo.fnEnlargeX;
          let lnTempY = BeoLogo.fnLorenzY * BeoLogo.fnEnlargeY;

          let lnCurrentRadian = Math.atan(lnTempY / lnTempX);
          // I could have also used lnTempX / cos(lnCurrentRadian)
          let lnHypotenuse = lnTempY / Math.sin(lnCurrentRadian);

          let lnPlotX = (lnHypotenuse * Math.cos(lnRadians + lnCurrentRadian)) + BeoLogo.fnCenterX;
          let lnPlotY = (lnHypotenuse * Math.sin(lnRadians + lnCurrentRadian)) + BeoLogo.fnCenterY;

          let lnColor = Math.floor(Math.random() * BeoLogo.faGoldColors.length);
          loCanvas.fillStyle = BeoLogo.faGoldColors[lnColor];
          loCanvas.fillRect(lnPlotX, lnPlotY, 1, 1)
        }

        BeoLogo.fnCurrentSequence++;
        if (BeoLogo.fnCurrentSequence >= BeoLogo.faSequences.length)
        {
          clearInterval(lnTimerID);
          Routines.showAJAX(false);
        }

      }, 250);

    },
    //----------------------------------------------------------------------------------------------------
    initVariables: function (tcBlock)
    {
      BeoLogo.foBlock = jQuery(tcBlock);
      let loBlock = BeoLogo.foBlock;

      BeoLogo.foCanvas = loBlock.get(0).getContext('2d');
      BeoLogo.fnWidth = loBlock.width();
      BeoLogo.fnHeight = loBlock.height();

      // Ensure that the canvas is blank.
      BeoLogo.foCanvas.clearRect(0, 0, BeoLogo.fnWidth, BeoLogo.fnHeight);

      // Move towards the right.
      BeoLogo.fnCenterX = BeoLogo.fnWidth / 1.8;
      // Move towards the top.
      BeoLogo.fnCenterY = BeoLogo.fnHeight / 2.3;

      BeoLogo.fnEnlargeX = BeoLogo.fnWidth * 0.3;
      BeoLogo.fnEnlargeY = BeoLogo.fnHeight * 0.9;

      BeoLogo.fnCurrentSequence = 0;
    },
    //----------------------------------------------------------------------------------------------------
    initGoldColors: function ()
    {
      // Copied from the C code.
      // For soome reason, I can't initialize when declaring faGoldColors. Oh well. . . .
      BeoLogo.faGoldColors.push(BeoLogo.rgbToHex(255, 235, 199),
        BeoLogo.rgbToHex(251, 223, 159),
        BeoLogo.rgbToHex(251, 215, 143),
        BeoLogo.rgbToHex(243, 199, 107),
        BeoLogo.rgbToHex(243, 199, 99),
        BeoLogo.rgbToHex(243, 195, 91),
        BeoLogo.rgbToHex(243, 195, 83),
        BeoLogo.rgbToHex(239, 215, 151),
        BeoLogo.rgbToHex(219, 191, 119),
        BeoLogo.rgbToHex(215, 187, 115),
        BeoLogo.rgbToHex(211, 147, 51),
        BeoLogo.rgbToHex(207, 179, 107),
        BeoLogo.rgbToHex(199, 135, 47),
        BeoLogo.rgbToHex(175, 143, 67),
        BeoLogo.rgbToHex(171, 131, 35),
        BeoLogo.rgbToHex(163, 135, 55),
        BeoLogo.rgbToHex(131, 99, 19),
        BeoLogo.rgbToHex(103, 71, 7),
        BeoLogo.rgbToHex(99, 67, 0),
        BeoLogo.rgbToHex(83, 67, 0),
        BeoLogo.rgbToHex(83, 51, 0),
        BeoLogo.rgbToHex(67, 51, 0),
        BeoLogo.rgbToHex(51, 35, 0)
      );

    },

    //----------------------------------------------------------------------------------------------------
    toHex: function (tnDigit)
    {
      let lcHex = Number(tnDigit).toString(16);
      if (lcHex.length < 2)
      {
        lcHex = '0' + lcHex;
      }

      return (lcHex);
    },
    //----------------------------------------------------------------------------------------------------
    rgbToHex: function (tnRed, tnGreen, tnBlue)
    {
      return ('#' + BeoLogo.toHex(tnRed) + BeoLogo.toHex(tnGreen) + BeoLogo.toHex(tnBlue));
    },

    //----------------------------------------------------------------------------------------------------
    setupLogoOnClick: function ()
    {
      if (jQuery(BeoLogo.FRACTAL_DESC_BLOCK).length == 0)
      {
        return;
      }

      let loModal = jQuery(BeoLogo.FRACTAL_DESC_DIALOG);
      loModal.on('shown.bs.modal', function (toEvent)
      {
        BeoLogo.initVariables(BeoLogo.FRACTAL_CANVAS_DESCRIPTION);
        BeoLogo.drawFractal();
      })

      jQuery(BeoLogo.FRACTAL_CANVAS_HEADER).click(function (toEvent)
      {
        toEvent.preventDefault();

        // From https://stackoverflow.com/questions/45621755/add-bootstrap-modal-using-javascript-and-cloned-node
        loModal.modal('show')

      });
    },
    //----------------------------------------------------------------------------------------------------
    // Only change the default behaviour of the logo if on the front page where you
    // should find the slogan.
    // And the slogan is in a block: #block-block-3
    setupTitle: function ()
    {
      // Should only exist on the front page.
      if (jQuery(BeoLogo.SLOGAN_CREDITS_BLOCK).length == 0)
      {
        return;
      }

      // Overriding the URL link for the title.
      jQuery(BeoLogo.TITLE_LINK).click(function (toEvent)
      {
        toEvent.preventDefault();

        // From https://stackoverflow.com/questions/45621755/add-bootstrap-modal-using-javascript-and-cloned-node
        jQuery(BeoLogo.SLOGAN_CREDITS_DIALOG).modal('show')
      });
    },
    //----------------------------------------------------------------------------------------------------

  };
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
