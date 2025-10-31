<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Knockout Phase Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
  <h1 class="mb-4 text-center">Knockout Phase Predictions</h1>
  <form method="post" action="#">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Match</th>
            <th>Team 1</th>
            <th>Team 2</th>
            <th>Who Advances?</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Example placeholders until backend logic exists
          $matches = [
            ['match' => 'A1 vs B2', 'team1' => 'Team A', 'team2' => 'Team F'],
            ['match' => 'C1 vs D2', 'team1' => 'Team C', 'team2' => 'Team D']
          ];
          foreach ($matches as $index => $m) {
              $id = $index + 1;
              echo "
              <tr>
                <td>$id</td>
                <td>{$m['match']}</td>
                <td>{$m['team1']}</td>
                <td>{$m['team2']}</td>
                <td>
                  <label for='winner_$id' class='visually-hidden'>Who Advances?</label>
                  <select id='winner_$id' name='winner_$id' class='form-select' required>
                    <option value=''>Select...</option>
                    <option value='{$m['team1']}'>{$m['team1']}</option>
                    <option value='{$m['team2']}'>{$m['team2']}</option>
                  </select>
                </td>
              </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary px-4">Save KO Predictions</button>
    </div>
  </form>
</div>
</body>
</html>
