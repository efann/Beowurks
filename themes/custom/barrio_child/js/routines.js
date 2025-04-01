//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------

var Routines =
  {
    CONTACT_BLOCK: '#contact-message-feedback-form',
    CHAPTER_TREE_LIST: '#jqtree_list',

    //----------------------------------------------------------------------------------------------------
    initializeRoutines: function ()
    {
      Beo.initializeBrowserFixes();

    },

    //----------------------------------------------------------------------------------------------------
    setupProjectsCarousel: function ()
    {
      let loCarousel = jQuery('#block-barrio-child-projectsblock');

      if (loCarousel.length === 0)
      {
        return;
      }

      loCarousel.find('img').click(function (toEvent)
      {
        toEvent.preventDefault();

        // The ID is in the form of #_project. Note the following with parseInt.
        //   Only the first numbers in the string are returned.
        //   If the first character cannot be converted to a number, parseInt() returns NaN.
        let lnNodeID = parseInt(jQuery(this).attr('id'));
        Routines.loadProjectByAJAX(lnNodeID);

        return (false);
      });

      loCarousel.carousel();
      loCarousel.fadeIn('slow');
    },

    //----------------------------------------------------------------------------------------------------
    loadProjectByAJAX: function (tnNodeID)
    {
      Routines.showAJAX(true);

      // To determine the URL. From http://css-tricks.com/snippets/javascript/get-url-and-url-parts-in-javascript/
      jQuery.ajax({
        url: window.location.protocol + '//' + window.location.host + '/ajax/node/' + tnNodeID
      }).done(function (toData)
      {
        let loModal = jQuery('#ProjectInformation');
        if (loModal.length !== 0)
        {
          let loData = jQuery(toData);
          loModal.find('.modal-body').html(loData.find('nodeinfo').find('body').text());

          loModal.find('a').each(function ()
          {
            jQuery(this).attr('target', '_blank');
            jQuery(this).attr('title', 'This link will open in a new browser window');
          });

          loModal.modal('show');
        }

        Routines.showAJAX(false);
      });

    },

    //----------------------------------------------------------------------------------------------------
    setupWatermarks: function ()
    {
      let lcForm = Routines.CONTACT_BLOCK;
      if (jQuery(lcForm).length === 0)
      {
        return;
      }

      Beo.setupWatermark(lcForm + ' #edit-name', 'Your Name');
      Beo.setupWatermark(lcForm + ' #edit-mail', 'Your@E-mail.com');
      Beo.setupWatermark(lcForm + ' #edit-subject-0-value', 'Subject');
      Beo.setupWatermark(lcForm + ' #edit-message-0-value', 'Message');

    },

    //----------------------------------------------------------------------------------------------------
    setupTaxonomyTabsForAjax: function (tcTabBlock)
    {
      let loTabBlock = jQuery(tcTabBlock);

      if (loTabBlock.length === 0)
      {
        return;
      }

      let loTabs = loTabBlock.find('a');
      let loPanel = loTabBlock.find('.content-panel');

      // After converting from jQuery UI to Bootstrap Navs, I had to some things programmatically, like
      // the Ajax, the active selection, the fadeIn/fadeOut, and load the first tab content.
      loTabs.on('click', function (toEvent)
      {
        toEvent.preventDefault();

        Routines.showAJAX(true);

        let loThis = jQuery(this);
        loTabs.removeClass('active');
        loThis.addClass('active');

        let lcHref = window.location.protocol + '//' + window.location.host + loThis.attr('href');

        loPanel.fadeOut('slow', function ()
        {
          jQuery.ajax({
            url: lcHref
          }).done(function (toData)
          {
            loPanel.html(jQuery(toData).html());

            loPanel.find('a').each(function ()
            {
              jQuery(this).attr('target', '_blank');
              jQuery(this).attr('title', 'This link will open in a new browser window');
            });

            // Otherwise, images will not appear. Unless you specifically set display: block for
            // images in #jqtree_content.
            Beo.setupLightbox(true, '#main-wrapper');
            Routines.tweakAjaxImages(tcTabBlock);

            loPanel.fadeIn('slow');
            Routines.showAJAX(false);
          });
        });

      });

      // Now the first tab content.
      loTabs.first().trigger('click');

    },

    //----------------------------------------------------------------------------------------------------
    // From https://stackoverflow.com/questions/5250630/difference-between-load-and-ajax-functions-in-jquery
    // $.get(), $.post(), .load() are all just wrappers for $.ajax() as it's called internally.
    getJEquityVersionInfo: function ()
    {
      jQuery('#version-info').load('/ajax/version/2 #jequity-version', function ()
      {
        let lcVersion = jQuery('#app_version').html();
        jQuery('#installation span.version').html(lcVersion);

        let lcFolder = jQuery('#app_folder').html();

        let loLink = jQuery('#installation a.folder');
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
          let loData = JSON.parse(tcData);

          let loTree = jQuery(Routines.CHAPTER_TREE_LIST);
          loTree.tree({
            data: loData,
            autoOpen: 1,
            autoEscape: false,
          });

          loTree.on(
            'tree.click',
            function (toEvent)
            {
              // The clicked node is 'event.node'
              let loNode = toEvent.node;
              Routines.loadHelpContent(loNode);
            }
          );

          let lcHash = window.location.hash;
          let lnOpenID = (lcHash.length > 1) ? parseInt(lcHash.substr(1), 10) : -1;

          let lnInitID = ((lnOpenID === -1) && (typeof loData[0].id !== 'undefined')) ? loData[0].id : lnOpenID;

          let loNode = loTree.tree('getNodeById', lnInitID);
          loTree.tree('selectNode', loNode);
          // The act of programmatically selecting does not fire the click event.
          Routines.loadHelpContent(loNode);

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
    loadHelpContent: function (toNode)
    {
      if (typeof toNode === 'undefined')
      {
        return;
      }

      Routines.showAJAX(true);
      let lcPath = toNode.href;

      let loContent = jQuery('#jqtree_content');

      loContent.fadeOut('fast', function ()
      {
        // Wait till fading completes, then load content.
        loContent.load(lcPath, function ()
        {
          Routines.updateHelpContentHashLinks(loContent);

          loContent.fadeIn('fast', function ()
          {
            // Otherwise, images will not appear. Unless you specifically set display: block for
            // images in #jqtree_content.
            Beo.setupLightbox(true, '#main-wrapper');
            Routines.tweakAjaxImages('#jqtree_content');
            Routines.showAJAX(false);
          });
        });
      });
    },
    //----------------------------------------------------------------------------------------------------
    updateHelpContentHashLinks: function (toContent)
    {
      toContent.find('a').each(function ()
      {
        let loThis = jQuery(this);
        let lcHref = loThis.attr('href');

        if (lcHref.startsWith('#'))
        {
          let lnID = (lcHref.length > 1) ? parseInt(lcHref.substr(1), 10) : -1;
          if (lnID !== -1)
          {
            loThis.click(function (toEvent)
            {
              toEvent.preventDefault();

              let loTree = jQuery(Routines.CHAPTER_TREE_LIST);

              let loNode = loTree.tree('getNodeById', lnID);
              loTree.tree('selectNode', loNode);
              // The act of programmatically selecting does not fire the click event.
              Routines.loadHelpContent(loNode);
            });
          }
        }
      });

    },

    //----------------------------------------------------------------------------------------------------
    tweakAjaxImages: function (tcBlock)
    {
      jQuery(tcBlock + ' img').each(function ()
      {
        let loImage = jQuery(this);

        // If an link, then it's a.img type element.
        let loParent = loImage.parent();
        if (loParent.is('a'))
        {
          loParent.addClass('ajax_content_images');
          loParent.wrap('<div><div class="col-sm-12" style="text-align: center;"></div></div>');
        }
      });

    },

    //----------------------------------------------------------------------------------------------------
    showAJAX: function (tlShow)
    {
      let lcAJAX = '#ajax-loading';
      let loAJAX = jQuery(lcAJAX);
      if (loAJAX.length === 0)
      {
        alert('The HTML element ' + lcAJAX + ' does not exist!');
        return;
      }

      if (tlShow)
      {
        loAJAX.show();
      }
      else
      {
        loAJAX.fadeOut(750);
      }

    },

    //----------------------------------------------------------------------------------------------------
  };

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
