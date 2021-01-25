<?php

namespace App\Repositories;

class Ranking 
{ 
    function goalDifference(int $goalFor, int $goalAgainst): int 
    {
       return $goalFor - $goalAgainst;
    }

    function points(int $wonMatchCount, int $drawMatchCount): int
    {
        return  $wonMatchCount *3 + $drawMatchCount *1;
    }

    function teamWinsMatch(int $teamId, array $match): bool
    {
        return ($match["team0"]==$teamId && $match["score0"]>$match["score1"])
            || ($match["team1"]==$teamId && $match["score0"]< $match["score1"]);
    }

    function teamLosesMatch(int $teamId, array $match): bool
    {
        return ($match["team0"]==$teamId && $match["score0"]<$match["score1"])
            || ($match["team1"]==$teamId && $match["score0"]>$match["score1"]);
    }

    function teamMakesADraw(int $teamId, array $match): bool
    {
        return ($match["team0"]==$teamId && $match["score0"]==$match["score1"])
        || ($match["team1"]==$teamId && $match["score0"]==$match["score1"]);
    }

    function goalForCountDuringAMatch(int $teamId, array $match)
    {
        return ($match["team0"]==$teamId) ? ($match["score0"]) : (($match["team1"]==$teamId) ? $match["score1"] : 0);
    }
    
    function goalAgainstCountDuringAMatch(int $teamId, array $match) 
    {
        return ($match["team0"]==$teamId) ? ($match["score1"]) : (($match["team1"]==$teamId) ? $match["score0"] : 0);
    }
    function goalForCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $row) {
            if ($teamId == $row["team0"])
                $sum += $row["score0"];

            if ($teamId == $row["team1"])
                $sum += $row["score1"];
        }
        return $sum;
    }

    function goalAgainstCount(int $teamId, array $matches): int
    {
        $sum = 0;
        foreach ($matches as $row) {
            if ($teamId == $row["team0"])
                $sum += $row["score1"];

            if ($teamId == $row["team1"])
                $sum += $row["score0"];
        }
        return $sum;
    }
    function wonMatchCount(int $teamId, array $matches): int
    {
        $count = 0;
        foreach ($matches as $row) {
            if ($this->teamWinsMatch($teamId, $row)) {
                $count++;
            }
        }
        return $count;

    }

    function lostMatchCount(int $teamId, array $matches): int
    {
        $count = 0;
        foreach ($matches as $row) {
            if ($this->teamLosesMatch($teamId, $row)) {
                $count++;
            }
        }
        return $count;
    }

    function drawMatchCount(int $teamId, array $matches): int
    {
        $count = 0;
        foreach ($matches as $row) {
            if ($this->teamMakesADraw($teamId, $row)) {
                $count++;
            }
        }
        return $count;
    }

    function rankingRow(int $teamId, array $matches): array
    {
        $goalAgainstCount = $this->goalAgainstCount($teamId,  $matches);
        $goalForCount = $this->goalForCount($teamId, $matches);
        $goalDifference = $this->goalDifference($goalForCount, $goalAgainstCount);
        $drawMatchCount = $this->drawMatchCount($teamId, $matches);
        $lostMatchCount = $this->lostMatchCount($teamId, $matches);
        $wonMatchCount = $this->wonMatchCount($teamId, $matches);
        $matchPlayedCount = $drawMatchCount + $lostMatchCount + $wonMatchCount;
        $points = $this->points($wonMatchCount, $drawMatchCount);

        $teamInfo = [
            'team_id'            => $teamId,
            'match_played_count' => $matchPlayedCount,
            'won_match_count'    => $wonMatchCount,
            'lost_match_count'   => $lostMatchCount,
            'draw_match_count'   => $drawMatchCount,
            'goal_for_count'     => $goalForCount,
            'goal_against_count' => $goalAgainstCount,
            'goal_difference'    => $goalDifference,
            'points'             => $points
        ];

        return $teamInfo;
    }

    function unsortedRanking(array $teams, array $matches): array
    {
        $result = [];
        foreach ($teams as $row) {
            $result[] = $this->rankingRow($row['id'], $matches);
        }
        return $result;
    }


        static function compareRankingRow(array $row1, array $row2): int
    {   

        if($row1['points'] != $row2['points']){

            return $row2['points'] - $row1['points'];
        }

        if($row1['goal_difference'] != $row2['goal_difference']){

            return $row2['goal_difference'] - $row1['goal_difference'];
        }

        if($row1['goal_for_count'] != $row2['goal_for_count']){

            return $row2['goal_for_count'] - $row1['goal_for_count'];
        }


        return 0;
    }


        function sortedRanking(array $teams, array $matches): array
        
        {
            $result = $this->unsortedRanking($teams, $matches);
            usort($result, ['App\Repositories\Ranking', 'compareRankingRow']);

            for ($rank = 1; $rank <= count($teams); $rank++) {
                $result[$rank - 1]['rank']=$rank ;
            }
            return $result;
        }


    }   