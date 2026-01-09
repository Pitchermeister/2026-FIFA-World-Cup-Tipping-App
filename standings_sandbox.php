<?php
session_start();

// === GRUPPEN-DEFINITIONEN ===
$groups = [
    "A" => [
        "teams" => ["Mexico", "Croatia", "Jordan", "Egypt"],
        "matches" => [
            ["home" => "Mexico", "away" => "Croatia"],
            ["home" => "Jordan", "away" => "Egypt"],
            ["home" => "Mexico", "away" => "Jordan"],
            ["home" => "Croatia", "away" => "Egypt"],
            ["home" => "Mexico", "away" => "Egypt"],
            ["home" => "Croatia", "away" => "Jordan"]
        ]
    ],
    "B" => [
        "teams" => ["Canada", "Morocco", "Austria", "Ghana"],
        "matches" => [
            ["home" => "Canada", "away" => "Morocco"],
            ["home" => "Austria", "away" => "Ghana"],
            ["home" => "Canada", "away" => "Austria"],
            ["home" => "Morocco", "away" => "Ghana"],
            ["home" => "Canada", "away" => "Ghana"],
            ["home" => "Morocco", "away" => "Austria"]
        ]
    ],
    // ... (alle anderen Gruppen C-L bleiben gleich wie vorher)
];

// === TABELLEN INITIALISIEREN ===
$standings = [];
foreach ($groups as $group_name => $group_data) {
    foreach ($group_data['teams'] as $team) {
        $standings[$group_name][$team] = [
            'gf' => 0,
            'ga' => 0,
            'gd' => 0,
            'pts' => 0
        ];
    }
}

$third_place_teams = [];

// === FORMULAR VERARBEITEN ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    foreach ($groups as $group_name => $group_data) {
        $matches = $group_data['matches'];
        
        foreach ($matches as $match_id => $match) {
            $home_key = "{$group_name}_home_$match_id";
            $away_key = "{$group_name}_away_$match_id";
            
            $home_score = $_POST[$home_key] ?? '';
            $away_score = $_POST[$away_key] ?? '';
            
            if ($home_score !== '' && $away_score !== '') {
                $home_goals = (int)$home_score;
                $away_goals = (int)$away_score;
                
                $standings[$group_name][$match['home']]['gf'] += $home_goals;
                $standings[$group_name][$match['home']]['ga'] += $away_goals;
                $standings[$group_name][$match['away']]['gf'] += $away_goals;
                $standings[$group_name][$match['away']]['ga'] += $home_goals;
                
                if ($home_goals > $away_goals) {
                    $standings[$group_name][$match['home']]['pts'] += 3;
                } elseif ($home_goals < $away_goals) {
                    $standings[$group_name][$match['away']]['pts'] += 3;
                } else {
                    $standings[$group_name][$match['home']]['pts'] += 1;
                    $standings[$group_name][$match['away']]['pts'] += 1;
                }
            }
        }
        
        foreach ($standings[$group_name] as $team => $data) {
            $standings[$group_name][$team]['gd'] = $data['gf'] - $data['ga'];
        }
        
        uasort($standings[$group_name], function($a, $b) {
            if ($a['pts'] != $b['pts']) return $b['pts'] - $a['pts'];
            if ($a['gd'] != $b['gd']) return $b['gd'] - $a['gd'];
            return $b['gf'] - $a['gf'];
        });
        
        $position = 1;
        foreach ($standings[$group_name] as $team => $data) {
            if ($position === 3) {
                $third_place_teams[] = [
                    'group' => $group_name,
                    'team' => $team,
                    'gf' => $data['gf'],
                    'ga' => $data['ga'],
                    'gd' => $data['gd'],
                    'pts' => $data['pts']
                ];
                break;
            }
            $position++;
        }
    }
    
    $group_winners = [];
    $group_runners = [];
    $group_third = [];
    
    foreach ($standings as $group_name => $group_standings) {
        $teams = array_keys($group_standings);
        $group_winners[$group_name] = $teams[0];
        $group_runners[$group_name] = $teams[1];
        $group_third[$group_name] = $teams[2];
    }
    
    $_SESSION['group_winners'] = $group_winners;
    $_SESSION['group_runners'] = $group_runners;
    $_SESSION['group_third'] = $group_third;
    
    usort($third_place_teams, function($a, $b) {
        if ($a['pts'] != $b['pts']) return $b['pts'] - $a['pts'];
        if ($a['gd'] != $b['gd']) return $b['gd'] - $a['gd'];
        return $b['gf'] - $a['gf'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groups Stage Sandbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        table { background-color: #fff; }
        th, td { text-align: center; vertical-align: middle; }
    </style>
</head>
<body>

<div class="container my-5">
    <h1 class="text-center mb-4">Groups Stage Sandbox</h1>

    <form method="post">
        <?php foreach ($groups as $group_name => $group_data) { ?>
            
            <h2 class="mt-4">Group <?php echo htmlspecialchars($group_name); ?></h2>
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Home Team</th>
                            <th>Away Team</th>
                            <th>Home Scored</th>
                            <th>Away Scored</th>
                            <th>Predicted Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $match_number = 0;
                        foreach ($group_data['matches'] as $match) {
                            $date = date("Y-m-d", strtotime("+$match_number days"));
                            $time = sprintf("%02d:00", rand(14, 22));
                            
                            $home_field = "{$group_name}_home_$match_number";
                            $away_field = "{$group_name}_away_$match_number";
                            
                            $home_value = $_POST[$home_field] ?? '';
                            $away_value = $_POST[$away_field] ?? '';
                            
                            $result_text = "–";
                            if ($home_value !== '' && $away_value !== '') {
                                $home_goals = (int)$home_value;
                                $away_goals = (int)$away_value;
                                
                                if ($home_goals > $away_goals) {
                                    $result_text = $match['home'];
                                } elseif ($home_goals < $away_goals) {
                                    $result_text = $match['away'];
                                } else {
                                    $result_text = "Draw";
                                }
                            }
                            
                            echo "<tr>";
                            echo "<td>" . ($match_number + 1) . "</td>";
                            echo "<td>$date $time</td>";
                            echo "<td>{$match['home']}</td>";
                            echo "<td>{$match['away']}</td>";
                            echo "<td><input type='number' class='form-control' name='$home_field' min='0' max='9' value='" . htmlspecialchars($home_value) . "' required></td>";
                            echo "<td><input type='number' class='form-control' name='$away_field' min='0' max='9' value='" . htmlspecialchars($away_value) . "' required></td>";
                            echo "<td><strong>" . htmlspecialchars($result_text) . "</strong></td>";
                            echo "</tr>";
                            
                            $match_number++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary px-4">Save & Show Standings</button>
        </div>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') { ?>
        
        <?php foreach ($standings as $group_name => $group_standings) { ?>
            <div class="mt-5">
                <h3 class="text-center">Group <?php echo htmlspecialchars($group_name); ?> Standings</h3>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Pos</th>
                                <th>Country</th>
                                <th>Goals Scored</th>
                                <th>Goals Conceded</th>
                                <th>Goal Diff</th>
                                <th>Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $position = 1;
                            foreach ($group_standings as $team => $data) {
                                echo "<tr>";
                                echo "<td>$position</td>";
                                echo "<td>" . htmlspecialchars($team) . "</td>";
                                echo "<td>{$data['gf']}</td>";
                                echo "<td>{$data['ga']}</td>";
                                echo "<td>{$data['gd']}</td>";
                                echo "<td>{$data['pts']}</td>";
                                echo "</tr>";
                                $position++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>

        <div class="mt-5">
            <h3 class="text-center">Third Place Teams (All Groups)</h3>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Country</th>
                            <th>Goals Scored</th>
                            <th>Goals Conceded</th>
                            <th>Goal Diff</th>
                            <th>Points</th>
                            <th>Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rank = 1;
                        foreach ($third_place_teams as $entry) {
                            echo "<tr>";
                            echo "<td>$rank</td>";
                            echo "<td>" . htmlspecialchars($entry['team']) . "</td>";
                            echo "<td>{$entry['gf']}</td>";
                            echo "<td>{$entry['ga']}</td>";
                            echo "<td>{$entry['gd']}</td>";
                            echo "<td>{$entry['pts']}</td>";
                            echo "<td>" . htmlspecialchars($entry['group']) . "</td>";
                            echo "</tr>";
                            $rank++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php } ?>

</div>

</body>
</html>