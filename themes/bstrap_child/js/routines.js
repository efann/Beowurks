// Updated on March 12, 2017
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------

var Routines =
  {
    CONTACT_BLOCK: "#contact-message-feedback-form",

    //----------------------------------------------------------------------------------------------------
    initializeRoutines: function ()
    {
      Beo.initializeBrowserFixes();

    },

    //----------------------------------------------------------------------------------------------------
    setupProjectsFlexSlider: function ()
    {
      var loSliderImages = jQuery("#projects_block");

      if (loSliderImages.length == 0)
      {
        return;
      }

      // FlexSlide setup should come before Beo.setupImageDialogBox. Otherwise, there appears
      // to be some issues with the first image linking to the image and not Beo.setupImageDialogBox.
      // Strange. . . .
      if (loSliderImages.find("a.dialogbox-image").length > 0)
      {
        alert("Routines.setupFlexSlider must be run before Beo.setupImageDialogBox.");
        return;
      }

      // From https://github.com/woocommerce/FlexSlider/issues/292
      // If you use animation: slide, the first image shown will be the last in
      // the queue. animation: fade fixes this issue, and it looks better anyway.
      loSliderImages.find('.flexslider').flexslider(
        {
          directionNav: (jQuery(window).width() >= 768),
          controlNav: true,
          prevText: "",
          nextText: "",
          animation: "fade",
          slideshow: false
        });

      loSliderImages.find('img').click(function (toEvent)
      {
        toEvent.preventDefault();

        // The ID is in the form of #_project. Note the following with parseInt.
        //   Only the first numbers in the string are returned.
        //   If the first character cannot be converted to a number, parseInt() returns NaN.
        var lnNodeID = parseInt(jQuery(this).attr("id"));
        Routines.loadProjectByAJAX(lnNodeID);

        return (false);
      });

      loSliderImages.fadeIn('slow');
    },

    //----------------------------------------------------------------------------------------------------
    loadProjectByAJAX: function (tnNodeID)
    {
      Routines.showAJAX(true);

      // To determine the URL. From http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/
      jQuery.ajax({
        url: window.location.protocol + "//" + window.location.host + "/ajax/node/" + tnNodeID
      }).done(function (toData)
      {
        var lcDialog = "#ProjectInformation";
        if (jQuery(lcDialog).length == 0)
        {
          jQuery('body').append('<div id="' + lcDialog.substring(1) + '"></div>');
        }

        var loData = jQuery(toData);
        jQuery(lcDialog).html(loData.find('nodeinfo').find('body').text());

        jQuery(lcDialog).find("a").each(function ()
        {
          jQuery(this).attr("target", "_blank");
          jQuery(this).attr("title", "This link will open in a new browser window");
        });

        jQuery(lcDialog).dialog(
          {
            title: 'Information',
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

        Routines.showAJAX(false);

      });

    },

    //----------------------------------------------------------------------------------------------------
    setupWatermarks: function ()
    {
      var lcForm = Routines.CONTACT_BLOCK;
      if (jQuery(lcForm).length == 0)
      {
        return;
      }

      Beo.setupWatermark(lcForm + " #edit-name", "Your Name");
      Beo.setupWatermark(lcForm + " #edit-mail", "Your@E-mail.com");
      Beo.setupWatermark(lcForm + " #edit-subject-0-value", "Subject");
      Beo.setupWatermark(lcForm + " #edit-message-0-value", "Message");

    },

    //----------------------------------------------------------------------------------------------------
    setupTaxonomyTabsForAjax: function (tcTabBlock)
    {
      var loTabs = jQuery(tcTabBlock);

      if (loTabs.length == 0)
      {
        return;
      }

      loTabs.tabs({
        show: {effect: "slide", direction: "up"},
        hide: {effect: "fadeOut", duration: 400},
        beforeLoad: function (event, ui)
        {
          Routines.showAJAX(true);
        },
        load: function (event, ui)
        {
          Routines.showAJAX(false);
        }
      });


      Beo.adjustTabsAlignment(loTabs);
      jQuery(window).resize(function ()
      {
        Beo.adjustTabsAlignment(loTabs);
      });

      loTabs.fadeIn('slow');
    },

    //----------------------------------------------------------------------------------------------------
    // From https://stackoverflow.com/questions/5250630/difference-between-load-and-ajax-functions-in-jquery
    // $.get(), $.post(), .load() are all just wrappers for $.ajax() as it's called internally.
    getJEquityVersionInfo: function ()
    {
      jQuery("#version-info").load("/ajax/version/2 #jequity-version", function ()
      {
        var lcVersion = jQuery('#app_version').html();
        jQuery('#installation span.version').html(lcVersion);

        var lcFolder = jQuery('#app_folder').html();

        var loLink = jQuery('#installation a.folder');
        loLink.attr('href', lcFolder);
        loLink.html(lcFolder);
      });

    },

    //----------------------------------------------------------------------------------------------------
    setupChapterTree: function ()
    {
      Routines.showAJAX(true);

      jQuery.ajax({
        url: '/ajax/chaptertree/2',
        type: 'GET',
        success: function (tcData)
        {
          var loData = JSON.parse(tcData);

          var loTree = jQuery("#jqtree_list");
          loTree.tree({
            data: loData,
            autoOpen: 1,
            autoEscape: false,
            saveState: 'help-documentation-tree',
          });

          loTree.on(
            'tree.click',
            function (toEvent)
            {
              // The clicked node is 'event.node'
              var loNode = toEvent.node;
              Routines.loadHelpContent(loNode.id);
            }
          );

          // Load the first element after initialization.
          if (typeof loData[0].id !== 'undefined')
          {
            Routines.loadHelpContent(loData[0].id);
          }

          Routines.showAJAX(false);
        },
        error: function (loErr)
        {
          alert(loErr);
          Routines.showAJAX(false);
        }
      });

    },

    //----------------------------------------------------------------------------------------------------
    // From https://stackoverflow.com/questions/5250630/difference-between-load-and-ajax-functions-in-jquery
    // $.get(), $.post(), .load() are all just wrappers for $.ajax() as it's called internally.
    loadHelpContent: function (tnID)
    {
      Routines.showAJAX(true);
      var lcPath = "/ajax/node/" + tnID;
      var loContent = jQuery("#jqtree_content");

      loContent.fadeOut('fast', function ()
      {
        // Wait till fading completes, then load content.
        loContent.load(lcPath, function ()
        {
          loContent.fadeIn('fast', function ()
          {
            // Otherwise, images will not appear. Unless you specifically set display: block for
            // images in #jqtree_content.
            Beo.setupImageDialogBox(20, 'explode', true, '#jqtree_content');
            Routines.showAJAX(false);
          });
        });
      });
    },
    //----------------------------------------------------------------------------------------------------
    showAJAX: function (tlShow)
    {
      var lcAJAX = "#ajax-loading";
      var loAJAX = jQuery(lcAJAX);
      if (loAJAX.length == 0)
      {
        alert("The HTML element " + lcAJAX + " does not exist!");
        return;
      }

      if (tlShow)
      {
        loAJAX.show();
      }
      else
      {
        loAJAX.hide();
      }

    },

    //----------------------------------------------------------------------------------------------------
  };

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
