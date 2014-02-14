<?php
/* 
Plugin Name: BVN Satellite
Plugin URI: http://www.bvn.ch
Description: Reads Data from the BVN.ch API and displays them in a WordPress Widget
Version: 1.4.1
Author: Thomas Winter
Author URI: http://www.houseofwinter.ch
@license https://github.com/atlan79/bvn-satellite/blob/master/LICENSE.md MIT
*/


class bvn_satellite extends WP_Widget {
	
	private $bvnch;
	/**
	 * Register widget with WordPress.
	 */
    function __construct() {
		parent::__construct(
			'bvn_satellite', // Base ID
			'BVN Satellite', // Name
			array( 'description' => __( 'Liest Daten aus der BVN.ch API und stellt diese im Widget dar', 'text_domain' ), ) // Args
		);
		
		require_once(dirname(__FILE__).'/lib/lib.bvn-satellite.php');
		require_once(dirname(__FILE__).'/lib/lib.bvn-SDK.php');
		require_once(dirname(__FILE__).'/bvn-satellite-panel.php');
		
		$this->bvnch = new BvnchSDK();
    }

    /**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);
		$api_model = apply_filters( 'widget_api_model', $instance['api_model'] );
		$api_realm = apply_filters( 'widget_api_realm', $instance['api_realm'] );
		$api_liga = apply_filters( 'widget_api_liga', $instance['api_liga'] );
		$api_club = apply_filters( 'widget_api_club', $instance['api_club'] );
		$api_team = apply_filters( 'widget_api_team', $instance['api_team'] );
		$api_view = apply_filters( 'widget_api_view', $instance['api_view'] );
		$api_limit = apply_filters( 'widget_api_limit', $instance['api_limit'] );
		$api_league_id = apply_filters( 'widget_api_league_id', $instance['api_league_id'] );
		$api_highlight = apply_filters( 'widget_api_highlight', $instance['api_highlight'] );
		$api_title = apply_filters( 'widget_api_title', $instance['api_title'] );
		
		switch ($api_realm) {
            case 'liga':
                $api_id = $api_liga;
                break;
            case 'club':
                $api_id = $api_club;
                break;
			case 'team':
                $api_id = $api_team;
                break;
        }
		
		echo $before_widget; // pre-widget code from theme
		
		echo '<div>'."\n";
		echo '	<div class="widget-title">' . $api_title . '</div>'."\n";
		
		if( !empty($api_model) ) {
			
			if($api_model == "ranking") {
				//echo __( 'API Model Ranking', 'text_domain' );
				$objRanking = $this->bvnch->getRanking ($api_league_id);
				
				bvnsat_parseRankingWidget ($objRanking, $api_highlight);
				//print_r($objRanking);
				
			} elseif($api_model == "matches") {
				//echo __( 'API Model Matches', 'text_domain' );
				
				$objGames = $this->bvnch->getMatches ($api_realm, $api_id, $api_view, $api_limit);

				bvnsat_parseGamesWidget ($objGames, $api_realm, $api_id, $api_view, $api_limit, $api_highlight);
				//print_r($objGames);
			}
		} else {
			echo __( 'API Model not specified', 'text_domain' );
		}
		echo '</div>'."\n";
		
		echo $after_widget; // post-widget code from theme
	}
	 
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'api_model' => '', 
															 'api_realm' => '', 
															 'api_liga' => '', 
															 'api_club' => '', 
															 'api_team' => '', 
															 'api_view' => '', 
															 'api_limit' => '', 
															 'api_league_id' => '', 
															 'api_highlight' => '',
															 'api_title') );
		if ( isset( $instance[ 'api_model' ] ) )     { $api_model = $instance[ 'api_model' ]; } else { $api_model = __( 'New api_model', 'text_domain' ); }
		if ( isset( $instance[ 'api_realm' ] ) )     { $api_realm = $instance[ 'api_realm' ]; } else { $api_realm = __( 'New api_realm', 'text_domain' ); }
		if ( isset( $instance[ 'api_liga' ] ) )      { $api_liga = $instance[ 'api_liga' ];   } else { $api_liga = __( 'Liga', 'text_domain' ); }
		if ( isset( $instance[ 'api_club' ] ) )      { $api_club = $instance[ 'api_club' ];   } else { $api_club = __( 'Club', 'text_domain' ); }
		if ( isset( $instance[ 'api_team' ] ) )      { $api_team = $instance[ 'api_team' ];   } else { $api_team = __( 'Team', 'text_domain' ); }
		if ( isset( $instance[ 'api_view' ] ) )      { $api_view = $instance[ 'api_view' ];   } else { $api_view = __( 'New api_view', 'text_domain' );	}
		if ( isset( $instance[ 'api_limit' ] ) )     { $api_limit = $instance[ 'api_limit' ]; } else { $api_limit = __( 'New api_limit', 'text_domain' ); }
		if ( isset( $instance[ 'api_league_id' ] ) ) { $api_league_id = $instance[ 'api_league_id' ]; } else { $api_league_id = __( 'New league_id', 'text_domain' ); }
		if ( isset( $instance[ 'api_highlight' ] ) ) { $api_highlight = $instance[ 'api_highlight' ]; } else { $api_highlight = __( 'New highlight', 'text_domain' ); }
		if ( isset( $instance[ 'api_title' ] ) )     { $api_title = $instance[ 'api_title' ]; } else { $api_title = __( 'New title', 'text_domain' ); }
		
		//$objAPIListRealm = $this->bvnch->getRealmList();
		$arRealmOptionsList = array ("liga","club","team");
		
		$arClubOptionsList = unserialize( get_option('bvn_satellite_options_club') );
		$arApiClubList = array();
		if (is_array($arClubOptionsList) && count($arClubOptionsList) > 0) {
			foreach ($arClubOptionsList as $key => $value) {
				$arApiClubList[$key] = $value;
			}
		} else {
			$objAPIListClub = $this->bvnch->getClubList();
			foreach ($objAPIListClub as $objOptions) {
				$arApiClubList[$objOptions->id] = $objOptions->name;
			}
		}
		
		$arLigaOptionsList = unserialize( get_option('bvn_satellite_options_liga') );
		$arApiLigaList = array();
		if (is_array($arLigaOptionsList) && count($arLigaOptionsList) > 0) {
			foreach ($arLigaOptionsList as $key => $value) {
				$arApiLigaList[$key] = $value;
			}
		} else {
			$objAPIListLiga = $this->bvnch->getLeagueList();
			foreach ($objAPIListLiga as $objOptions) {
				$arApiLigaList[$objOptions->id] = $objOptions->leagueName;
			}
		}
		
		$arTeamOptionsList = unserialize( get_option('bvn_satellite_options_team') );
		$arApiTeamList = array();
		if (is_array($arTeamOptionsList) && count($arTeamOptionsList) > 0) {
			foreach ($arTeamOptionsList as $key => $value) {
				$arApiTeamList[$key] = $value;
			}
		} else {
			$objAPIListTeam = $this->bvnch->getLeagueList();
			foreach ($objAPIListTeam as $objOptions) {
				$arApiTeamList[$objOptions->id] = $objOptions->name;
			}
		}
		
		?>
       
		<p>
        	<label for="<?php echo $this->get_field_id( 'api_title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'api_title' ); ?>" name="<?php echo $this->get_field_name( 'api_title' ); ?>" type="text" value="<?php echo esc_attr( $api_title ); ?>" class="widefat" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'api_model' ); ?>" style="width:30%"><?php _e( 'API Model:' ); ?></label> 
            <select id="<?php echo $this->get_field_id( 'api_model' ); ?>" name="<?php echo $this->get_field_name( 'api_model' ); ?>" class="widefat">
            	<option value="matches" <?php selected( $instance['api_model'], 'matches' ); ?>><?php _e('Spiele'); ?></option>
                <option value="ranking" <?php selected( $instance['api_model'], 'ranking' ); ?>><?php _e('Rangliste'); ?></option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('api_realm'); ?>"><?php _e( 'Select Games Realm:' ); ?></label>
            <select id="<?php echo $this->get_field_id('api_realm'); ?>" name="<?php echo $this->get_field_name('api_realm'); ?>" class="widefat">
            	<option value=""><?php _e('- select realm -', 'text_domain'); ?></option>
            <?php foreach ( $arRealmOptionsList as $realm ) { ?>
            	<option value="<?= $realm ?>" <?php selected( $instance['api_realm'], $realm ); ?>><?= $realm ?></option>
            <?php } ?>
            </select><br />

			<label for="<?php echo $this->get_field_id( 'api_club' ); ?>"><?php _e( 'Select Club:' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'api_club' ); ?>" name="<?php echo $this->get_field_name( 'api_club' ); ?>" class="widefat">
				<option value=""><?php _e('- select club -', 'text_domain'); ?></option>
            <?php foreach ($arApiClubList as $value => $option) { ?>
				<option value="<?= $value ?>" <?php selected( $instance['api_club'], $value ); ?>><?= $option ?></option>
            <?php } ?>
	        </select><br />

			<label for="<?php echo $this->get_field_id( 'api_liga' ); ?>"><?php _e( 'Select League:' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'api_liga' ); ?>" name="<?php echo $this->get_field_name( 'api_liga' ); ?>" class="widefat">
                <option value=""><?php _e('- select league -', 'text_domain'); ?></option>
            <?php foreach ($arApiLigaList as $value => $option) { ?>
                <option value="<?= $value ?>" <?php selected( $instance['api_liga'], $value ); ?>><?= $option ?></option>
            <?php } ?>
            </select><br />

			<label for="<?php echo $this->get_field_id( 'api_team' ); ?>"><?php _e( 'Select Team:' ); ?></label>
	        <select id="<?php echo $this->get_field_id( 'api_team' ); ?>" name="<?php echo $this->get_field_name( 'api_team' ); ?>" class="widefat">
                <option value=""><?php _e('- select team -', 'text_domain'); ?></option>
        	<?php foreach ($arApiTeamList as $value => $option) { ?>
                <option value="<?= $value ?>" <?php selected( $instance['api_team'], $value ); ?>><?= $option ?></option>
            <?php } ?>
	        </select><br />
		        
	        <label for="<?php echo $this->get_field_id('api_view'); ?>"><?php _e( 'View:' ); ?></label>
			<select name="<?php echo $this->get_field_name('api_view'); ?>" id="<?php echo $this->get_field_id('api_view'); ?>" class="widefat">
				<option value="all"<?php selected( $instance['api_view'], 'all' ); ?>><?php _e( 'Normal View' ); ?></option>
				<option value="last"<?php selected( $instance['api_view'], 'last' ); ?>><?php _e( 'Last Games' ); ?></option>
				<option value="next"<?php selected( $instance['api_view'], 'next' ); ?>><?php _e( 'Next Games' ); ?></option>
			</select>
			<label for="<?php echo $this->get_field_id('api_limit'); ?>"><?php _e( 'Number of games to show:' ); ?></label>
			<input id="<?php echo $this->get_field_id('api_limit'); ?>" name="<?php echo $this->get_field_name('api_limit'); ?>" type="text" value="<?php echo $api_limit == -1 ? '' : intval( $api_limit ); ?>" size="3" />
		</p>
        <p>
        	<label for="<?php echo $this->get_field_id( 'api_league_id' ); ?>"><?php _e( 'Select League Ranking:' ); ?></label>
	        <select id="<?php echo $this->get_field_id( 'api_league_id' ); ?>" name="<?php echo $this->get_field_name( 'api_league_id' ); ?>" class="widefat">
                <option value=""><?php _e('- select league -', 'text_domain'); ?></option>
        	<?php foreach ($arApiLigaList as $value => $option) { ?>
                <option value="<?= $value ?>" <?php selected( $instance['api_league_id'], $value ); ?>><?= $option ?></option>
            <?php } ?>
	        </select>
		</p>
        <p>
	        <label for="<?php echo $this->get_field_id( 'api_highlight' ); ?>"><?php _e( 'Highlight:' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'api_highlight' ); ?>" name="<?php echo $this->get_field_name( 'api_highlight' ); ?>" type="text" value="<?php echo esc_attr( $api_highlight ); ?>" class="widefat" />
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'api_model' => '', 
																	 'api_realm' => '', 
																	 'api_liga' => '', 
																	 'api_club' => '', 
																	 'api_team' => '', 
																	 'api_view' => '', 
																	 'api_limit' => '',
																	 'api_league_id' => '',
																	 'api_highlight' => '', 
																	 'api_title' => '') );
		$instance['api_model'] = strip_tags($new_instance['api_model']);
		$instance['api_realm'] = strip_tags($new_instance['api_realm']);
		$instance['api_liga'] = strip_tags($new_instance['api_liga']);
		$instance['api_club'] = strip_tags($new_instance['api_club']);
		$instance['api_team'] = strip_tags($new_instance['api_team']);
		$instance['api_view'] = strip_tags($new_instance['api_view']);
		$instance['api_limit'] = strip_tags($new_instance['api_limit']);
		$instance['api_league_id'] = strip_tags($new_instance['api_league_id']);
		$instance['api_highlight'] = strip_tags($new_instance['api_highlight']);
		$instance['api_title'] = strip_tags($new_instance['api_title']);

		return $instance;
	}
	
} // class bvn_satellite

// register bvn_satellite widget
add_action(
   'widgets_init',
	create_function('','return register_widget("bvn_satellite");')
);
	
?>
