<?php
/**
 * BVN Satellite
 * 
 * WordPress Plugin for interacting with the BVN.ch API
 * 
 * @author Thomas Winter
 * @license https://github.com/atlan79/bvn-satellite/blob/master/LICENSE.md MIT
 * @version 1.3
 */ 
 
/**
 * Parse Ranking
 * @param array with Ranking Data $arRanking
 */
function bvnsat_parseRankingWidget ($objRanking, $highlight) {
		echo '<div style="float:left">';
		echo '<table class="noborder" style="width:260px">';
		echo '<thead>';
		echo '<tr>';
		echo '<td class="nopadding">Team</td>';
		echo '<td class="nopadding">Sp</td>';
		echo '<td class="nopadding">Si</td>';
		echo '<td class="nopadding">Ni</td>';
		echo '<td class="nopadding">Pu</td>';
		echo '<td class="nopadding">Di</td>';
		echo '</tr>';
		echo '</thead>';
		foreach ($objRanking as $team) {
			if($highlight==$team->teamName) {
				echo '<tr>';
				echo '<td class="nopadding" style="font-weight: bold;" title="'.$team->teamName.'">'.$team->team5lc.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->games.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->win.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->lost.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->points.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->difference.'</td>';
				echo '</tr>';
			} else {
				echo '<tr>';
				echo '<td class="nopadding" title="'.$team->teamName.'">'.$team->team5lc.'</td>';
				echo '<td class="nopadding">'.$team->games.'</td>';
				echo '<td class="nopadding">'.$team->win.'</td>';
				echo '<td class="nopadding">'.$team->lost.'</td>';
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
		echo '<table class="noborder" style="width:260px">';
		echo '<thead>';
		echo '<tr>';
		echo '<td class="nopadding">Datum</td>';
		echo '<td colspan="2" class="nopadding">Begegnung</td>';
		echo '<td class="nopadding">Score</td>';
		echo '</tr>';
		echo '</thead>';
		foreach ($objGames as $game) {
			echo '<tr>';
			echo '<td class="nopadding">'.$game->gameDay .'.'. $game->gameMonth.'.</td>';
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
			if($game->homescore != 0 &&  $game->awayscore != 0 ) {
				echo '<td class="nopadding">'.$game->homescore .':'. $game->awayscore.'</td>';
			} else {
				echo '<td class="nopadding">&nbsp;</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';}

?>
