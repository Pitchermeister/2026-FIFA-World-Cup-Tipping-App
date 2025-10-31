<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Predictions - Group Stage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    table {
      background-color: white;
    }
    th {
      text-align: center;
    }
    .form-control {
      width: 80px;
      display: inline-block;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <h1 class="mb-4 text-center">Group Stage Predictions</h1>
  <form method="post" action="#">
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-light">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Home Team</th>
            <th scope="col">Away Team</th>
            <th scope="col">Home Scored</th>
            <th scope="col">Away Scored</th>
            <th scope="col">Predicted Result</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Placeholder teams for 72 matches
          $matches = [];
          for ($i = 1; $i <= 72; $i++) {
              $matches[] = [
                  'home' => "Team " . chr(64 + (($i % 26) ?: 26)), // e.g., Team A, B...
                  'away' => "Team " . chr(64 + ((($i + 1) % 26) ?: 26))
              ];
          }

          foreach ($matches as $index => $match) {
              $id = $index + 1;
              echo "
              <tr>
                <td>{$id}</td>
                <td>{$match['home']}</td>
                <td>{$match['away']}</td>
                <td>
                  <label for='home_score_$id' class='visually-hidden'>Home Score</label>
                  <input type='number' min='0' class='form-control score-input' id='home_score_$id' name='home_score_$id' aria-label='Home score for {$match['home']}'>
                </td>
                <td>
                  <label for='away_score_$id' class='visually-hidden'>Away Score</label>
                  <input type='number' min='0' class='form-control score-input' id='away_score_$id' name='away_score_$id' aria-label='Away score for {$match['away']}'>
                </td>
                <td>
                  <span id='result_$id' class='fw-bold text-secondary'>–</span>
                </td>
              </tr>
              ";
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary px-4">Save Predictions</button>
    </div>
  </form>
</div>

<script>
document.querySelectorAll('.score-input').forEach(input => {
  input.addEventListener('input', function() {
    const row = this.closest('tr');
    const home = row.querySelector('[id^="home_score_"]').value;
    const away = row.querySelector('[id^="away_score_"]').value;
    const resultCell = row.querySelector('[id^="result_"]');
    const homeTeam = row.cells[1].innerText;
    const awayTeam = row.cells[2].innerText;

    if (home !== '' && away !== '') {
      if (parseInt(home) > parseInt(away)) {
        resultCell.textContent = homeTeam + ' (Home)';
        resultCell.className = 'fw-bold text-success';
      } else if (parseInt(home) < parseInt(away)) {
        resultCell.textContent = awayTeam + ' (Away)';
        resultCell.className = 'fw-bold text-danger';
      } else {
        resultCell.textContent = 'Draw';
        resultCell.className = 'fw-bold text-warning';
      }
    } else {
      resultCell.textContent = '–';
      resultCell.className = 'fw-bold text-secondary';
    }
  });
});
</script>

</body>
</html>
