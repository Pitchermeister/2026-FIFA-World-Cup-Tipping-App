<?php
$matches = [
    "2026-06-11|18:00|USA|Kanada|Gruppenphase",
    "2026-06-12|21:00|Mexiko|Brasilien|Gruppenphase",
    "2026-06-13|18:00|Deutschland|Frankreich|Gruppenphase"
];

file_put_contents("matches.txt", implode("\n", $matches) . "\n");

echo "Matches erfolgreich erzeugt ✅";
?>