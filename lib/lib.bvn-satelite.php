<?php
/**
 * BVN Satellite
 * 
 * WordPress Plugin for interacting with the BVN.ch API
 * 
 * @author Thomas Winter
 * @license https://github.com/atlan79/bvn-satellite/blob/master/LICENSE.md MIT
 * @version 1.7
 * @date 21.10.2014
 */ 
 
/**
 * Parse Ranking
 * @param array with Ranking Data $arRanking
 */
function bvnsat_parseRankingWidget ($objRanking, $highlight) {
		echo '<div style="float:left">';
		echo '<table class="noborder" style="width:100%">';
		echo '<thead>';
		echo '<tr>';
		echo '<td class="nopadding">Team</td>';
		echo '<td class="nopadding">Sp</td>';
		echo '<td class="nopadding">Si</td>';
		echo '<td class="nopadding">Ni</td>';
		echo '<td class="nopadding">Erz</td>';
		echo '<td class="nopadding">Erh</td>';
		echo '<td class="nopadding">Pu</td>';
		echo '<td class="nopadding">KV</td>';
		echo '</tr>';
		echo '</thead>';
		foreach ($objRanking as $team) {
			if($highlight==$team->teamName) {
				echo '<tr>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->teamName.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->games.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->win.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->lost.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->scored.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->received.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->points.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->difference.'</td>';
				echo '</tr>';
			} else {
				echo '<tr>';
				echo '<td class="nopadding">'.$team->teamName.'</td>';
				echo '<td class="nopadding">'.$team->games.'</td>';
				echo '<td class="nopadding">'.$team->win.'</td>';
				echo '<td class="nopadding">'.$team->lost.'</td>';
				echo '<td class="nopadding">'.$team->scored.'</td>';
				echo '<td class="nopadding">'.$team->received.'</td>';
				echo '<td class="nopadding">'.$team->points.'</td>';
				echo '<td class="nopadding">'.$team->difference.'</td>';
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '</div>';
}


/**
 * Parse Games
 * @param array with Schedule Data $arGames
  */
function bvnsat_parseGamesWidget ($objGames, $api_realm, $api_id, $api_view, $api_limit, $highlight) {
		echo '<div style="float:left">';
		echo '<table class="noborder" style="width:100%">';
		echo '<thead>';
		echo '<tr>';
		echo '<td class="nopadding">Datum</td>';
		if($api_view != "last") {
			echo '<td class="nopadding">Zeit</td>';	
		}
		if($api_realm != "league" && $api_realm != "team") {
			echo '<td class="nopadding">Liga</td>';	
		}
		echo '<td colspan="2" class="nopadding">Begegnung</td>';
		if($api_realm == "league" || $api_realm == "team") {
			echo '<td class="nopadding">Halle</td>';
		}
		if($api_view != "next") {
			echo '<td class="nopadding">Score</td>';
		}
		echo '</tr>';
		echo '</thead>';
		/*
		echo '<tr><td>'. gettype ( $objGames ) .'</td></tr>';
		if (is_object($objGames)) {
			echo '<tr><td>Object</td></tr>';
		}
		if (is_array($objGames)) {
			echo '<tr><td>Array</td></tr>';
		}		
		*/
		if (!is_array($objGames)) {
			// no output
		} else {
			foreach ($objGames as $game) {
				echo '<tr>';
				echo '<td class="nopadding" title="'.$game->gymName.'">'.$game->gameDay.'.'. $game->gameMonth.'.</td>';
				if($api_view != "last") {
					echo '<td class="nopadding" title="'.$game->gymName.'">'.$game->gameHour.':'. $game->gameMin.'.</td>';
				}
				if($api_realm != "league" && $api_realm != "team") {
					echo '<td class="nopadding" title="'.$game->leagueName.'">'.$game->idLeague.'</td>';
				}
				if($highlight==$game->homeName) {
					echo '<td class="nopadding" style="font-weight: bold;" title="'.$game->homeName.'">'.$game->homeTeam5lc.'</td>';
				} else {
					echo '<td class="nopadding" title="'.$game->homeName.'">'.$game->homeTeam5lc.'</td>';
				}
				if($highlight==$game->awayName) {
					echo '<td class="nopadding" style="font-weight: bold;" title="'.$game->awayName.'">'.$game->awayTeam5lc.'</td>';
				} else {
					echo '<td class="nopadding" title="'.$game->awayName.'">'.$game->awayTeam5lc.'</td>';
				}
				if($api_realm == "league" || $api_realm == "team") {
					echo '<td class="nopadding">'.$game->gymName.'</td>';
				}
				if($api_view != "next") {
					if($game->homescore != 0 || $game->awayscore != 0 ) {
						echo '<td class="nopadding">'.$game->homescore .':'. $game->awayscore.'</td>';
					} else {
						echo '<td class="nopadding">&nbsp;</td>';
					}
				}
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '</div>';}

?>
