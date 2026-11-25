// License: Eclipse Public License - v 2.0 (https://www.eclipse.org/org/documents/epl-2.0/EPL-2.0.html)
// Updated on November 7, 2022

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------

var BarrioTheme =
  {
    //----------------------------------------------------------------------------------------------------
    onResizeWrapperNavbar: function ()
    {
      BarrioTheme.adjustNavbarMain();
      BarrioTheme.adjustMainWrapper();

      jQuery(window).resize(function ()
      {
        BarrioTheme.adjustNavbarMain();
        BarrioTheme.adjustMainWrapper();
      });

    },
    //----------------------------------------------------------------------------------------------------
    adjustMainWrapper: function ()
    {
      let loTop = jQuery('#navbar-top');
      let loMain = jQuery('#navbar-main');
      let loWrapper = jQuery('#main-wrapper');

      if ((loTop.length == 0) || (loMain.length == 0) || (loWrapper.length == 0))
      {
        return;
      }

      let lnPaddingTop = 15;
      lnPaddingTop += loTop.height();
      if (loMain.is(':visible'))
      {
        lnPaddingTop += loMain.height();
      }

      loWrapper.css('padding-top', lnPaddingTop + 'px');
    },
    //----------------------------------------------------------------------------------------------------
    adjustNavbarMain: function ()
    {
      let loMainNavBar = jQuery('#navbar-main');

      let loTopNavBar = jQuery('#navbar-top');
      let loToolBarAdmin = jQuery('.toolbar-menu-administration .toolbar-icon-admin-toolbar-tools-help')
      let loToolBarTab = jQuery('.toolbar-tab .toolbar-icon-menu')

      let lnTop = 0;

      if (loMainNavBar.length == 0)
      {
        return;
      }

      // Now includes the admin menu tools when logged in.
      lnTop += (loTopNavBar.length != 0) ? Math.max(loTopNavBar.height(), parseFloat(loTopNavBar.css('height'))) : 0;
      lnTop += (loToolBarAdmin.length != 0) ? Math.max(loToolBarAdmin.height(), parseFloat(loToolBarAdmin.css('height'))) : 0;
      lnTop += (loToolBarTab.length != 0) ? Math.max(loToolBarTab.height(), parseFloat(loToolBarTab.css('height'))) : 0;

      loMainNavBar.css('top', lnTop + 'px');
    }
    //----------------------------------------------------------------------------------------------------
  };

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
