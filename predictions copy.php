<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Group Stage Predictions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    h2 {
      margin-top: 2rem;
      margin-bottom: 1rem;
    }
    .form-control {
      width: 80px;
      text-align: center;
      display: inline-block;
    }
    table {
      background-color: #fff;
    }
    th, td {
      text-align: center;
    }
  </style>
</head>
<body>

<div class="container my-5">
  <h1 class="text-center mb-5">Group Stage Predictions</h1>

  <form id="predictionsForm" method="post" action="#">
    <?php
    $groups = range('A', 'L'); // Groups A–L
    $match_id = 1;

    foreach ($groups as $group) {
        echo "<h2>Group $group</h2>";
        echo '<div class="table-responsive mb-4">';
        echo '<table class="table table-bordered align-middle text-center">';
        echo '<thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Date & Time</th>
                  <th>Home Team</th>
                  <th>Away Team</th>
                  <th>Home Scored</th>
                  <th>Away Scored</th>
                  <th>Predicted Result</th>
                </tr>
              </thead><tbody>';

        for ($m = 1; $m <= 6; $m++) {
            $home = "Team " . chr(64 + (($match_id % 26) ?: 26));
            $away = "Team " . chr(64 + ((($match_id + 1) % 26) ?: 26));
            $date = date("Y-m-d", strtotime("+$m days"));
            $time = sprintf("%02d:%02d", rand(12, 22), rand(0, 59));

            echo "
            <tr>
              <td>$match_id</td>
              <td>$date $time</td>
              <td>$home</td>
              <td>$away</td>
              <td>
                <label for='home_score_$match_id' class='visually-hidden'>Home Score</label>
                <input type='number' min='0' max='3' class='form-control score-input' id='home_score_$match_id' name='home_score_$match_id' aria-label='Home score for $home'>
              </td>
              <td>
                <label for='away_score_$match_id' class='visually-hidden'>Away Score</label>
                <input type='number' min='0' max='3' class='form-control score-input' id='away_score_$match_id' name='away_score_$match_id' aria-label='Away score for $away'>
              </td>
              <td><span id='result_$match_id' class='fw-bold'>–</span></td>
            </tr>";
            $match_id++;
        }
        echo '</tbody></table></div>';
    }
    ?>

    <div class="text-center mt-5">
      <button type="button" id="fillRandom" class="btn btn-secondary me-3">Fill Out with Random Scores</button>
      <button type="submit" id="saveBtn" class="btn btn-primary px-4" disabled>Save Predictions</button>
    </div>
  </form>
</div>

<script>
const inputs = document.querySelectorAll('.score-input');
const saveBtn = document.getElementById('saveBtn');
const randomBtn = document.getElementById('fillRandom');

// Update predicted result when both scores entered
inputs.forEach(input => {
  input.addEventListener('input', () => {
    const row = input.closest('tr');
    const home = row.querySelector('[id^="home_score_"]').value;
    const away = row.querySelector('[id^="away_score_"]').value;
    const resultCell = row.querySelector('[id^="result_"]');
    const homeTeam = row.cells[2].innerText;
    const awayTeam = row.cells[3].innerText;

    if (home !== '' && away !== '') {
      if (parseInt(home) > parseInt(away)) {
        resultCell.textContent = homeTeam + ' (Home)';
      } else if (parseInt(home) < parseInt(away)) {
        resultCell.textContent = awayTeam + ' (Away)';
      } else {
        resultCell.textContent = 'Draw';
      }
    } else {
      resultCell.textContent = '–';
    }

    checkAllFilled();
  });
});

// Random fill function
randomBtn.addEventListener('click', () => {
  inputs.forEach(input => {
    if (input.value === '') {
      input.value = Math.floor(Math.random() * 4);
      input.dispatchEvent(new Event('input'));
    }
  });
});

// Check if all fields filled → enable Save button
function checkAllFilled() {
  const allFilled = Array.from(inputs).every(i => i.value !== '');
  saveBtn.disabled = !allFilled;
}

// Redirect after "saving" (for testing)
document.getElementById('predictionsForm').addEventListener('submit', e => {
  e.preventDefault();
  window.location.href = 'ko_phase.php';
});
</script>

</body>
</html>
