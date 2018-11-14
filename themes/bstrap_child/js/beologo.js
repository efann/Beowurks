//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
var BeoLogo =
  {
    SLOGAN_CREDITS_BLOCK: "#block-slogancredits",
    SLOGAN_CREDITS_DIALOG: "#SloganCredits",

    TITLE_LINK: "#block-header .title a",

    FRACTAL_BLOCK: "#block-header canvas.fractal",

    STEPS: 12000,

    faGoldColors: [],
    fnEdges: 32,

    faSequences: [26, 29, 30],
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
      BeoLogo.initGoldColors();
      BeoLogo.initVariables();

      var loCanvas = BeoLogo.foCanvas;
      var lnRadianIncrements = (2 * Math.PI) / BeoLogo.fnEdges;

      var lnTimerID = setInterval(function ()
      {
        var lnRadians = BeoLogo.faSequences[BeoLogo.fnCurrentSequence] * lnRadianIncrements;

        for (var lnSteps = 0; lnSteps < BeoLogo.STEPS; ++lnSteps)
        {
          // Formula for Hénon's Attractor.
          var back = BeoLogo.fnLorenzX;
          BeoLogo.fnLorenzX = BeoLogo.fnLorenzY + 1.0 - (1.4 * BeoLogo.fnLorenzX * BeoLogo.fnLorenzX);
          BeoLogo.fnLorenzY = 0.3 * back;

          var lnTempX = BeoLogo.fnLorenzX * BeoLogo.fnEnlargeX;
          var lnTempY = BeoLogo.fnLorenzY * BeoLogo.fnEnlargeY;

          var lnCurrentRadian = Math.atan(lnTempY / lnTempX);
          // I could have also used lnTempX / cos(lnCurrentRadian)
          var lnHypotenuse = lnTempY / Math.sin(lnCurrentRadian);

          var lnPlotX = (lnHypotenuse * Math.cos(lnRadians + lnCurrentRadian)) + BeoLogo.fnCenterX;
          var lnPlotY = (lnHypotenuse * Math.sin(lnRadians + lnCurrentRadian)) + BeoLogo.fnCenterY;

          var lnColor = Math.floor(Math.random() * BeoLogo.faGoldColors.length);
          loCanvas.fillStyle = BeoLogo.faGoldColors[lnColor];
          loCanvas.fillRect(lnPlotX, lnPlotY, 1, 1)
        }

        BeoLogo.fnCurrentSequence++;
        if (BeoLogo.fnCurrentSequence >= BeoLogo.faSequences.length)
        {
          clearInterval(lnTimerID);
        }

      }, 250);

    },

    //----------------------------------------------------------------------------------------------------
    initVariables: function ()
    {
      BeoLogo.foBlock = jQuery(BeoLogo.FRACTAL_BLOCK);
      var loBlock = BeoLogo.foBlock;

      BeoLogo.foCanvas = loBlock.get(0).getContext('2d');
      BeoLogo.fnWidth = loBlock.width();
      BeoLogo.fnHeight = loBlock.height();

      BeoLogo.fnCenterX = BeoLogo.fnWidth / 2.0;
      BeoLogo.fnCenterY = BeoLogo.fnHeight / 2.0;

      BeoLogo.fnEnlargeX = BeoLogo.fnWidth * 0.35;
      BeoLogo.fnEnlargeY = BeoLogo.fnHeight * 0.75;

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
      var lcHex = Number(tnDigit).toString(16);
      if (lcHex.length < 2)
      {
        lcHex = "0" + lcHex;
      }

      return (lcHex);
    },
    //----------------------------------------------------------------------------------------------------
    rgbToHex: function (tnRed, tnGreen, tnBlue)
    {
      return ('#' + BeoLogo.toHex(tnRed) + BeoLogo.toHex(tnGreen) + BeoLogo.toHex(tnBlue));
    },

    //----------------------------------------------------------------------------------------------------
    // Only change the default behaviour of the logo if on the front page where you
    // should find the slogan.
    // And the slogan is in a block: #block-block-3
    setupLogo: function ()
    {
      // Should only exist on the front page.
      var loText = jQuery(BeoLogo.SLOGAN_CREDITS_BLOCK);
      if (loText.length == 0)
      {
        return;
      }

      jQuery(BeoLogo.TITLE_LINK).click(function (toEvent)
      {
        toEvent.preventDefault();

        var lcDialog = BeoLogo.SLOGAN_CREDITS_DIALOG;
        if (jQuery(lcDialog).length == 0)
        {
          jQuery('body').append('<div id="' + lcDialog.substring(1) + '">' + loText.html() + '</div>');
        }

        jQuery(lcDialog).dialog(
          {
            title: 'Slogan',
            width: '90%',
            height: 'auto',
            modal: true,
            autoOpen: true,
            show: {
              effect: 'fade',
              duration: 300
            },
            hide: {
              effect: 'fade',
              duration: 300
            },
            create: function (toEvent, toUI)
            {
              // The maxWidth property doesn't really work.
              // From http://stackoverflow.com/questions/16471890/responsive-jquery-ui-dialog-and-a-fix-for-maxwidth-bug
              // And id="ShowTellQuote" gets enclosed in a ui-dialog wrapper. So. . . .
              jQuery(this).parent().css("maxWidth", "800px");
            }
          });
      });
    },
    //----------------------------------------------------------------------------------------------------

  };
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
