<?php
/**
 * BVN Satelite
 * 
 * WordPress Plugin for interacting with the BVN.ch API
 * 
 * @author Thomas Winter
 * @license https://github.com/atlan79/bvn-satelite/blob/master/LICENSE.md MIT
 * @version 0.6 beta
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
		echo '<td class="nopadding">Mannschaft</td>';
		echo '<td class="nopadding">S</td>';
		echo '<td class="nopadding">N</td>';
		echo '</tr>';
		echo '</thead>';
		foreach ($objRanking as $team) {
			if($highlight==$team->teamName) {
				echo '<tr>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->teamName.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->win.'</td>';
				echo '<td class="nopadding" style="font-weight: bold;">'.$team->lost.'</td>';
				echo '</tr>';
			} else {
				echo '<tr>';
				echo '<td class="nopadding">'.$team->teamName.'</td>';
				echo '<td class="nopadding">'.$team->win.'</td>';
				echo '<td class="nopadding">'.$team->lost.'</td>';
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
function bvnsat_parseGamesWidget ($objGames, $api_realm, $api_id, $highlight) {
		echo '<div style="float:left">';
		echo '<table class="noborder" style="width:260px">';
		echo '<thead>';
		echo '<tr>';
		echo '<td class="nopadding">Datum</td>';
		echo '<td class="nopadding">Mannschaft</td>';
		echo '<td class="nopadding">Score</td>';
		echo '</tr>';
		echo '</thead>';
		foreach ($objGames as $game) {
			if($api_id==$game->homeTeamUrl) {
				echo '<tr>';
				echo '<td class="nopadding">'.$game->gameDay .'.'. $game->gameMonth.'.</td>';
				echo '<td class="nopadding">'.$game->awayName.'</td>';
				if($game->homescore != 0 &&  $game->awayscore != 0 ) {
					echo '<td class="nopadding">'.$game->homescore .':'. $game->awayscore.'</td>';
				} else {
					echo '<td class="nopadding">&nbsp;</td>';
				}
				echo '</tr>';
			} else {
				echo '<tr>';
				echo '<td class="nopadding">'.$game->gameDay .'.'. $game->gameMonth.'.</td>';
				echo '<td class="nopadding">@'.$game->homeName.'</td>';
				if($game->homescore != 0 &&  $game->awayscore != 0 ) {
					echo '<td class="nopadding">'.$game->homescore .':'. $game->awayscore.'</td>';
				} else {
					echo '<td class="nopadding">&nbsp;</td>';
				}
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '</div>';}

?>
