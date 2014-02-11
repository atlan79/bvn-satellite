<?php
/**
 * BVN Satellite
 * 
 * Admin Panel to optimize performance of widget configuration by minimizing access to the BVN.ch API
 * 
 * @author Thomas Winter
 * @license https://github.com/atlan79/bvn-satellite/blob/master/LICENSE.md MIT
 * @version 1.4
 */
/*
 * Functions start here
 */
/* Filters and actions */
add_action( 'admin_menu', 'bvn_satellite_menu' );



/* Setup global variables from options */
function bvn_satellite_setup_options() {
	bvn_satellite_get_option('bvn_satellite_api_club', 'api_club');
	bvn_satellite_get_option('bvn_satellite_options_club', 'options_club');
	bvn_satellite_get_option('bvn_satellite_options_liga', 'options_liga');
	bvn_satellite_get_option('bvn_satellite_options_team', 'options_team');

	bvn_satellite_sanitize_variables();
}


/* Update options in db from global variables */
function bvn_satellite_update_options() {
	update_option('bvn_satellite_api_club', bvn_satellite_get_option('api_club'));
	update_option('bvn_satellite_options_club', bvn_satellite_get_option('options_club'));
	update_option('bvn_satellite_options_liga', bvn_satellite_get_option('options_liga'));
	update_option('bvn_satellite_options_team', bvn_satellite_get_option('options_team'));
}


/* Add admin options page */
function bvn_satellite_menu() {
	add_options_page( 'bvn-satellite Options',  // Base ID
				'BVN Satellite',                // Display Name
				'manage_options', 'bvn-satellite-panel', 'bvn_satellite_option_page' );
}


/* Show log on admin page */
function bvn_satellite_show_list($arList) {
	if (!is_array($arList) || count($arList) == 0) {
		return;
	}

	echo '<tr><th scope="col">' . __('URL', 'bvn-satellite-panel') . '</th><th scope="col">' . __('Name', 'bvn-satellite-panel') . '</th></tr>';
	foreach ($arList as $key => $value) {
		echo '<tr><td class="bvn-satellite-url">' . $key . '</td><td class="bvn-satellite-right">' . $value . '</td></tr>';
	}
}


/* Actual admin page */
function bvn_satellite_option_page() {
	
	require_once(dirname(__FILE__).'/lib/lib.bvn-SDK.php');
	$bvnch = new BvnchSDK();
	
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	/* Make sure post was from this page */
	if (count($_POST) > 0) {
		check_admin_referer('bvn-satellite-panel-options');
	}
		
	/* Should we clear club options list? */
	if (isset($_POST['clear_clubs'])) {
		delete_option('bvn_satellite_options_club');
		echo '<div id="message" class="updated fade"><p>'
			. __('Cleared Club List', 'bvn-satellite-panel')
			. '</p></div>';
	}
	
	/* Should we clear league options log? */
	if (isset($_POST['clear_leagues'])) {
		delete_option('bvn_satellite_options_liga');
		echo '<div id="message" class="updated fade"><p>'
			. __('Cleared League List', 'bvn-satellite-panel')
			. '</p></div>';
	}
	
	/* Should we clear team options list? */
	if (isset($_POST['clear_teams'])) {
		delete_option('bvn_satellite_options_team');
		echo '<div id="message" class="updated fade"><p>'
			. __('Cleared Team List', 'bvn-satellite-panel')
			. '</p></div>';
	}
		

	/* Should we update options? */
	if (isset($_POST['update_options'])) {
		global $bvn_satellite_options;

		update_option('bvn_satellite_api_club', $_POST['api_club']);
		
		$objAPITeamListClub = $bvnch->getTeamListClub(get_option('bvn_satellite_api_club'));

		$arClubOptions = array();
		$arLigaOptions = array();
		$arTeamOptions = array();
		foreach ($objAPITeamListClub as $objClubTeams) {
			$arClubOptions[$objClubTeams->canonical_club] = $objClubTeams->clubName;
			$arLigaOptions[$objClubTeams->idLeague] = $objClubTeams->leagueName;
			$arTeamOptions[$objClubTeams->canonical_team] = $objClubTeams->teamName."[".$objClubTeams->idLeague."]";
		}
		update_option('bvn_satellite_options_club', serialize( $arClubOptions ) );
		update_option('bvn_satellite_options_liga', serialize( $arLigaOptions ) );
		update_option('bvn_satellite_options_team', serialize( $arTeamOptions ) );

		echo '<div id="message" class="updated fade"><p>'
			. __('Options changed', 'bvn-satellite-panel')
			. '</p></div>';
	}
	
	$objAPIListClub = $bvnch->getClubList();

	$api_club = get_option('bvn_satellite_api_club');

	?>
    
  <style type="text/css" media="screen">
    .bvn-satellite-log th {
        font-weight: bold;
    }
    .bvn-satellite-log td, .bvn-satellite-log th {
        padding: 1px 5px 1px 5px;
    }
    td.bvn-satellite-url {
        font-family:  "Courier New", Courier, monospace;
        vertical-align: top;
    }
    td.bvn-satellite-max {
        width: 100%;
    }
  </style>
    
	<div class="wrap">
	  <h2><?php echo __('BVN Satellite Settings','bvn-satellite-panel'); ?></h2>
	  <h3><?php echo __('Dropdown Options Selection','bvn-satellite-panel'); ?></h3>
	  <form action="options-general.php?page=bvn-satellite-panel" method="post">
		<?php wp_nonce_field('bvn-satellite-panel-options'); ?>
	    <table class="form-table">
		  <tr>
			<th scope="row" valign="top"><?php echo __('Select Club','bvn-satellite-panel'); ?></th>
			<td>
              <select name="api_club" >
                <option value="" <?php if ( '' == $api_club ) echo 'selected="selected"'; ?>>- select Club -</option>
            <?php foreach ($objAPIListClub as $objOption) { ?>
                <option value="<?= $objOption->id ?>" <?php if ( $objOption->id == $api_club ) echo 'selected="selected"'; ?>><?= $objOption->name ?></option>
            <?php } ?>
              </select>
			</td>
		  </tr>
		</table>
		<p class="submit">
		  <input name="update_options" value="<?php echo __('Add Club to Dropdown Options','bvn-satellite-panel'); ?>" type="submit" />
		</p>
	  </form>

      <h3><?php echo __('Selected Club Options','bvn-satellite-panel'); ?></h3>
      <?php
		$arClubOptionsList = unserialize( get_option('bvn_satellite_options_club') );

		if (is_array($arClubOptionsList) && count($arClubOptionsList) > 0) {
	  ?>      
	  <form action="options-general.php?page=bvn-satellite-panel" method="post">
		<?php wp_nonce_field('bvn-satellite-panel-options'); ?>
		<input type="hidden" value="true" name="clear_clubs" />
		<p class="submit">
		  <input name="submit" value="<?php echo __('Clear Clubs','bvn-satellite-panel'); ?>" type="submit" />
		</p>
	  </form>

	  <div class="bvn-satellite-log">
		<table class="form-table ">
		  <?php bvn_satellite_show_list($arClubOptionsList); ?>
		</table>
	  </div>
	  <?php
		} /* if showing $arClubOptionsList */
	  ?>
      
      <h3><?php echo __('Selected League Options','bvn-satellite-panel'); ?></h3>
      <?php
		$arLigaOptionsList = unserialize( get_option('bvn_satellite_options_liga') );

		if (is_array($arLigaOptionsList) && count($arLigaOptionsList) > 0) {
	  ?>      
	  <form action="options-general.php?page=bvn-satellite-panel" method="post">
		<?php wp_nonce_field('bvn-satellite-panel-options'); ?>
		<input type="hidden" value="true" name="clear_leagues" />
		<p class="submit">
		  <input name="submit" value="<?php echo __('Clear Leagues','bvn-satellite-panel'); ?>" type="submit" />
		</p>
	  </form>

	  <div class="bvn-satellite-log">
		<table class="form-table">
		  <?php bvn_satellite_show_list($arLigaOptionsList); ?>
		</table>
	  </div>
	  <?php
		} /* if showing $arLigaOptionsList */
	  ?>
      
      <h3><?php echo __('Selected Team Options','bvn-satellite-panel'); ?></h3>
      <?php
		$arTeamOptionsList = unserialize( get_option('bvn_satellite_options_team') );

		if (is_array($arTeamOptionsList) && count($arTeamOptionsList) > 0) {
	  ?>      
	  <form action="options-general.php?page=bvn-satellite-panel" method="post">
		<?php wp_nonce_field('bvn-satellite-panel-options'); ?>
		<input type="hidden" value="true" name="clear_teams" />
		<p class="submit">
		  <input name="submit" value="<?php echo __('Clear Teams','bvn-satellite-panel'); ?>" type="submit" />
		</p>
	  </form>

	  <div class="bvn-satellite-log">
		<table class="form-table">
		  <?php bvn_satellite_show_list($arTeamOptionsList); ?>
		</table>
	  </div>
	  <?php
		} /* if showing $arTeamOptionsList */
	  ?>
       
	</div>	
	<?php		
}	
?>
