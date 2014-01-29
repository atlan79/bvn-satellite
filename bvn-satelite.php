<?php
/* 
Plugin Name: BVN Satelite
Plugin URI: http://www.bvn.ch
Description: Liest Daten aus der BVN.ch API und stellt diese in einem Widget dar
Version: 1.0
Author: Thomas Winter
Author URI: http://www.houseofwinter.ch
*/


class BVN_Satelite extends WP_Widget {
	
	private $bvnch;
	/**
	 * Register widget with WordPress.
	 */
    function __construct() {
		parent::__construct(
			'bvn_satelite', // Base ID
			'BVN-Satelite', // Name
			array( 'description' => __( 'Liest Daten aus der BVN.ch API und stellt diese im Widget dar', 'text_domain' ), ) // Args
		);
		
		require_once(dirname(__FILE__).'/lib/lib.bvn-satelite.php');
		require_once(dirname(__FILE__).'/lib/lib.bvn-SDK.php');
		
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
		$api_league_id = apply_filters( 'widget_api_league_id', $instance['api_league_id'] );
		$api_highlight = apply_filters( 'api_highlight', $instance['api_highlight'] );
		
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
		
		if( !empty($api_model) ) {
			
			if($api_model == "ranking") {
				//echo __( 'API Model Ranking', 'text_domain' );
				$objRanking = $this->bvnch->getRanking ($api_league_id);
				
				bvnsat_parseRankingWidget ($objRanking, $highlight);
				//print_r($objRanking);
				
			} elseif($api_model == "matches") {
				//echo __( 'API Model Matches', 'text_domain' );
				
				$objGames = $this->bvnch->getMatches ($api_realm, $api_id, api_view);

				bvnsat_parseGamesWidget ($objGames, $api_realm, $api_id, $highlight);
				//print_r($objGames);
			}
		} else {
			echo __( 'API Path not specified', 'text_domain' );
		}
		
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
															 'api_league_id' => '', 
															 'api_highlight' => '') );
		if ( isset( $instance[ 'api_model' ] ) ) {
			$api_model = $instance[ 'api_model' ];
		} else {
			$api_model = __( 'New api_model', 'text_domain' );
		}
		if ( isset( $instance[ 'api_realm' ] ) ) {
			$api_realm = $instance[ 'api_realm' ];
		} else {
			$api_realm = __( 'New api_realm', 'text_domain' );
		}
		if ( isset( $instance[ 'api_liga' ] ) ) {
			$api_liga = $instance[ 'api_liga' ];
		} else {
			$api_liga = __( 'Liga', 'text_domain' );
		}
		if ( isset( $instance[ 'api_club' ] ) ) {
			$api_club = $instance[ 'api_club' ];
		} else {
			$api_club = __( 'Club', 'text_domain' );
		}
		if ( isset( $instance[ 'api_team' ] ) ) {
			$api_team = $instance[ 'api_team' ];
		} else {
			$api_team = __( 'Team', 'text_domain' );
		}
		if ( isset( $instance[ 'api_view' ] ) ) {
			$api_view = $instance[ 'api_view' ];
		} else {
			$api_view = __( 'New api_view', 'text_domain' );
		}
		if ( isset( $instance[ 'api_league_id' ] ) ) {
			$api_league_id = $instance[ 'api_league_id' ];
		} else {
			$api_league_id = __( 'New league_id', 'text_domain' );
		}
		if ( isset( $instance[ 'api_highlight' ] ) ) {
			$api_highlight = $instance[ 'api_highlight' ];
		} else {
			$api_highlight = __( 'New highlight', 'text_domain' );
		}
		
		$objAPIListRealm = $this->bvnch->getScheduleListRealm();
		$objAPIListLeague = $this->bvnch->getScheduleListLeague();
		$objAPIListClub = $this->bvnch->getScheduleListClub();
		$objAPIListTeam = $this->bvnch->getScheduleListTeam();	
		
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'api_model' ); ?>" style="width:30%"><?php _e( 'API Model:' ); ?></label> 
		<select id="<?php echo $this->get_field_id( 'api_model' ); ?>" name="<?php echo $this->get_field_name( 'api_model' ); ?>" class="widefat" style="width:100%;" 
        	onchange='api_toggle2(<?php echo $this->get_field_id( 'api_model' ); ?>, <?php echo $this->get_field_id( 'api_realm' ); ?>, <?php echo $this->get_field_id( 'api_league_id' ); ?>)'>
			<option value="matches" <?php if ( 'matches' == esc_attr( $instance['api_model'] ) ) echo 'selected="selected"'; ?>>Matches</option>
			<option value="ranking" <?php if ( 'ranking' == esc_attr( $instance['api_model'] ) ) echo 'selected="selected"'; ?>>Ranking</option>
        </select><br />
        
		<label for="<?php echo $this->get_field_id( 'api_realm' ); ?>" style="display: block"><?php _e( 'API Realm:' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'api_realm' ); ?>" name="<?php echo $this->get_field_name( 'api_realm' ); ?>" class="widefat" style="width:100%;display: block">
			<option <?php if ( '' == esc_attr( $instance['api_realm'] ) ) echo 'selected="selected"'; ?>>- select realm -</option>
        	<?php foreach ($objAPIListRealm->error->validRealms as $realm) { ?>
			<option value="<?= $realm ?>" <?php if ( $realm == esc_attr( $instance['api_realm'] ) ) echo 'selected="selected"'; ?>><?= $realm ?></option>
            <?php } ?>
        </select><br />

		<label for="<?php echo $this->get_field_id( 'api_liga' ); ?>" style="display: block"><?php _e( 'Liga Games:' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'api_liga' ); ?>" name="<?php echo $this->get_field_name( 'api_liga' ); ?>" class="widefat" style="width:100%;display: block">
			<option value="" <?php if ( '' == esc_attr( $instance['api_liga'] ) ) echo 'selected="selected"'; ?>>- select Liga -</option>
        	<?php foreach ($objAPIListLeague->error->validLeagues as $arOption) { ?>
			<option value="<?= $arOption->id ?>" <?php if ( $arOption->id == esc_attr( $instance['api_liga'] ) ) echo 'selected="selected"'; ?>><?= $arOption->leagueName ?></option>
            <?php } ?>
        </select><br />

		<label for="<?php echo $this->get_field_id( 'api_club' ); ?>" style="display: block"><?php _e( 'Club Games:' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'api_club' ); ?>" name="<?php echo $this->get_field_name( 'api_club' ); ?>" class="widefat" style="width:100%;display: block">
			<option value="" <?php if ( '' == esc_attr( $instance['api_club'] ) ) echo 'selected="selected"'; ?>>- select Club -</option>
        	<?php foreach ($objAPIListClub->error->validClubs as $arOption) { ?>
			<option value="<?= $arOption->id ?>" <?php if ( $arOption->id == esc_attr( $instance['api_club'] ) ) echo 'selected="selected"'; ?>><?= $arOption->name ?></option>
            <?php } ?>
        </select><br />


		<label for="<?php echo $this->get_field_id( 'api_team' ); ?>" style="display: block"><?php _e( 'Team Games:' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'api_team' ); ?>" name="<?php echo $this->get_field_name( 'api_team' ); ?>" class="widefat" style="width:100%;display: block">
			<option value="" <?php if ( '' == esc_attr( $instance['api_team'] ) ) echo 'selected="selected"'; ?>>- select Team -</option>
        	<?php foreach ($objAPIListTeam->error->validTeams as $arOption) { ?>
			<option value="<?= $arOption->id ?>" <?php if ( $arOption->id == esc_attr( $instance['api_team'] ) ) echo 'selected="selected"'; ?>><?= $arOption->teamName ?> [<?= $arOption->leagueName ?>]</option>
            <?php } ?>
        </select><br />

		<label for="<?php echo $this->get_field_id( 'api_view' ); ?>" style="display: block"><?php _e( 'API View:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'api_view' ); ?>" name="<?php echo $this->get_field_name( 'api_view' ); ?>" type="text" value="<?php echo esc_attr( $api_view ); ?>" style="width:100%;display: block" /><br />
        
		<label for="<?php echo $this->get_field_id( 'api_league_id' ); ?>" style="display: block"><?php _e( 'Liga Ranking:' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'api_league_id' ); ?>" name="<?php echo $this->get_field_name( 'api_league_id' ); ?>" class="widefat" style="width:100%;display: block">
			<option value="" <?php if ( '' == esc_attr( $instance['api_id'] ) ) echo 'selected="selected"'; ?>>- select Liga -</option>
        	<?php foreach ($objAPIListLeague->error->validLeagues as $arOption) { ?>
			<option value="<?= $arOption->id ?>" <?php if ( $arOption->id == esc_attr( $instance['api_league_id'] ) ) echo 'selected="selected"'; ?>><?= $arOption->leagueName ?></option>
            <?php } ?>
        </select><br />
        
        <label for="<?php echo $this->get_field_id( 'api_highlight' ); ?>"><?php _e( 'Highlight:' ); ?></label> 
		<input id="<?php echo $this->get_field_id( 'api_highlight' ); ?>" name="<?php echo $this->get_field_name( 'api_highlight' ); ?>" type="text" value="<?php echo esc_attr( $api_highlight ); ?>" style="width:100%;" /><br />
		</p>
        <script type="text/javascript">
			function api_toggle2(control, opt1, opt2) {
				if (document.getElementById(control).value == 'matches') {
					document.getElementById(opt1).style.display = 'block';
					document.getElementById(opt2).style.display = 'none';
				} else {
					document.getElementById(opt1).style.display = 'none';
					document.getElementById(opt2).style.display = 'block';
				}
			}
			function api_toggle3(control, opt1, opt2, opt3) {
				if (document.getElementById(control).value == 'liga') {
					document.getElementById(opt1).style.display = 'block';
					document.getElementById(opt2).style.display = 'none';
					document.getElementById(opt3).style.display = 'none';
				} else {
					if (document.getElementById(control).value == 'club') {
						document.getElementById(opt1).style.display = 'none';
						document.getElementById(opt2).style.display = 'block';
						document.getElementById(opt3).style.display = 'none';
					} else {
						document.getElementById(opt1).style.display = 'none';
						document.getElementById(opt2).style.display = 'none';
						document.getElementById(opt3).style.display = 'block';
					}
				}
			}
		</script>
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
																	 'api_league_id' => '', 
																	 'api_highlight' => '') );
		$instance['api_model'] = strip_tags($new_instance['api_model']);
		$instance['api_realm'] = strip_tags($new_instance['api_realm']);
		$instance['api_liga'] = strip_tags($new_instance['api_liga']);
		$instance['api_club'] = strip_tags($new_instance['api_club']);
		$instance['api_team'] = strip_tags($new_instance['api_team']);
		$instance['api_view'] = strip_tags($new_instance['api_view']);
		$instance['api_league_id'] = strip_tags($new_instance['api_league_id']);
		$instance['api_highlight'] = strip_tags($new_instance['api_highlight']);

		return $instance;
	}
	
} // class BVN_Satelite

// register BVN_Satelite widget
add_action(
   'widgets_init',
	create_function('','return register_widget("BVN_Satelite");')
);
	
?>
