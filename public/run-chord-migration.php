<?php
// JEDNORAZOVÝ SKRIPT – po spustení ZMAŽ cez FTP!

$env = [];
foreach (file(__DIR__ . '/../.env') as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, '"\'');
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8mb4",
        $env['DB_USERNAME'],
        $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    die('<p style="color:red">❌ DB spojenie zlyhalo: ' . $e->getMessage() . '</p>');
}

echo '<pre style="background:#111;color:#eee;padding:16px;font-size:13px;">';

// ── 1. Vytvor tabuľku chord_diagrams ─────────────────────────────────────────
$pdo->exec("
CREATE TABLE IF NOT EXISTS `chord_diagrams` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `song_id`            BIGINT UNSIGNED NULL DEFAULT NULL,
  `name`               VARCHAR(20) NOT NULL,
  `frets`              JSON NOT NULL,
  `fingers`            JSON NOT NULL,
  `starting_fret`      TINYINT NOT NULL DEFAULT 1,
  `barre_fret`         TINYINT NULL DEFAULT NULL,
  `barre_from_string`  TINYINT NULL DEFAULT NULL,
  `barre_to_string`    TINYINT NULL DEFAULT NULL,
  `created_at`         TIMESTAMP NULL DEFAULT NULL,
  `updated_at`         TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chord_name_song_idx` (`name`, `song_id`),
  CONSTRAINT `chord_diagrams_song_id_foreign`
    FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✅ Tabuľka chord_diagrams vytvorená (alebo už existuje)\n";

// Zaregistruj migrácie v Laravel migrations tabuľke
$existing = $pdo->query("SELECT migration FROM migrations WHERE migration LIKE '%chord_diagrams%'")->fetchAll(PDO::FETCH_COLUMN);
$migrations = [
    '2026_06_10_100000_create_chord_diagrams_table',
    '2026_06_10_110000_add_song_id_to_chord_diagrams_table',
];
$batchRow = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
$batch = ($batchRow ?: 0) + 1;
$ins = $pdo->prepare("INSERT IGNORE INTO migrations (migration, batch) VALUES (?, ?)");
foreach ($migrations as $m) {
    if (!in_array($m, $existing)) { $ins->execute([$m, $batch]); echo "✅ Migrácia zaregistrovaná: $m\n"; }
    else echo "⏭ Migrácia už existuje: $m\n";
}

// ── 2. Seeder – vloženie akordov ──────────────────────────────────────────────
$chords = [
    ['A',    [-1,0,2,2,2,0],   [0,0,1,2,3,0],   1, null, null, null],
    ['A#',   [-1,1,3,3,3,1],   [0,1,2,3,4,1],   1, 1,    1,    5],
    ['H',    [-1,2,4,4,4,2],   [0,1,2,3,4,1],   2, 2,    1,    5],
    ['C',    [-1,3,2,0,1,0],   [0,3,2,0,1,0],   1, null, null, null],
    ['C#',   [-1,4,6,6,6,4],   [0,1,2,3,4,1],   4, 4,    1,    5],
    ['D',    [-1,-1,0,2,3,2],  [0,0,0,1,3,2],   1, null, null, null],
    ['D#',   [-1,6,8,8,8,6],   [0,1,2,3,4,1],   6, 6,    1,    5],
    ['E',    [0,2,2,1,0,0],    [0,2,3,1,0,0],   1, null, null, null],
    ['F',    [1,3,3,2,1,1],    [1,3,4,2,1,1],   1, 1,    0,    5],
    ['F#',   [2,4,4,3,2,2],    [1,3,4,2,1,1],   2, 2,    0,    5],
    ['G',    [3,2,0,0,0,3],    [2,1,0,0,0,3],   1, null, null, null],
    ['G#',   [4,6,6,5,4,4],    [1,3,4,2,1,1],   4, 4,    0,    5],
    ['Am',   [-1,0,2,2,1,0],   [0,0,2,3,1,0],   1, null, null, null],
    ['A#m',  [-1,1,3,3,2,1],   [0,1,3,4,2,1],   1, 1,    1,    5],
    ['Hm',   [-1,2,4,4,3,2],   [0,1,3,4,2,1],   2, 2,    1,    5],
    ['Cm',   [-1,3,5,5,4,3],   [0,1,3,4,2,1],   3, 3,    1,    5],
    ['C#m',  [-1,4,6,6,5,4],   [0,1,3,4,2,1],   4, 4,    1,    5],
    ['Dm',   [-1,-1,0,2,3,1],  [0,0,0,2,3,1],   1, null, null, null],
    ['D#m',  [-1,6,8,8,7,6],   [0,1,3,4,2,1],   6, 6,    1,    5],
    ['Em',   [0,2,2,0,0,0],    [0,2,3,0,0,0],   1, null, null, null],
    ['Fm',   [1,3,3,1,1,1],    [1,3,4,1,1,1],   1, 1,    0,    5],
    ['F#m',  [2,4,4,2,2,2],    [1,3,4,1,1,1],   2, 2,    0,    5],
    ['Gm',   [3,5,5,3,3,3],    [1,3,4,1,1,1],   3, 3,    0,    5],
    ['G#m',  [4,6,6,4,4,4],    [1,3,4,1,1,1],   4, 4,    0,    5],
    ['A7',   [-1,0,2,0,2,0],   [0,0,2,0,3,0],   1, null, null, null],
    ['H7',   [-1,2,1,2,0,2],   [0,3,1,4,0,2],   1, null, null, null],
    ['C7',   [-1,3,2,3,1,0],   [0,3,2,4,1,0],   1, null, null, null],
    ['D7',   [-1,-1,0,2,1,2],  [0,0,0,2,1,3],   1, null, null, null],
    ['E7',   [0,2,0,1,0,0],    [0,2,0,1,0,0],   1, null, null, null],
    ['F7',   [1,3,1,2,1,1],    [1,3,1,2,1,1],   1, 1,    0,    5],
    ['F#7',  [2,4,2,3,2,2],    [1,3,1,2,1,1],   2, 2,    0,    5],
    ['G7',   [3,2,0,0,0,1],    [3,2,0,0,0,1],   1, null, null, null],
    ['G#7',  [4,6,4,5,4,4],    [1,3,1,2,1,1],   4, 4,    0,    5],
    ['A#7',  [-1,1,3,1,3,1],   [0,1,3,1,4,1],   1, 1,    1,    5],
    ['Am7',  [-1,0,2,0,1,0],   [0,0,2,0,1,0],   1, null, null, null],
    ['Em7',  [0,2,0,0,0,0],    [0,1,0,0,0,0],   1, null, null, null],
    ['Dm7',  [-1,-1,0,2,1,1],  [0,0,0,2,1,1],   1, null, null, null],
    ['Hm7',  [-1,2,4,2,3,2],   [0,1,3,1,2,1],   2, 2,    1,    5],
    ['Cm7',  [-1,3,5,3,4,3],   [0,1,3,1,2,1],   3, 3,    1,    5],
    ['Gm7',  [3,5,3,3,3,3],    [1,3,1,1,1,1],   3, 3,    0,    5],
    ['F#m7', [2,4,2,2,2,2],    [1,3,1,1,1,1],   2, 2,    0,    5],
    ['Amaj7',[-1,0,2,1,2,0],   [0,0,2,1,3,0],   1, null, null, null],
    ['Cmaj7',[-1,3,2,0,0,0],   [0,3,2,0,0,0],   1, null, null, null],
    ['Dmaj7',[-1,-1,0,2,2,2],  [0,0,0,1,2,3],   1, null, null, null],
    ['Emaj7',[0,2,1,1,0,0],    [0,3,1,2,0,0],   1, null, null, null],
    ['Fmaj7',[-1,-1,3,2,1,0],  [0,0,3,2,1,0],   1, null, null, null],
    ['Gmaj7',[3,2,0,0,0,2],    [3,2,0,0,0,1],   1, null, null, null],
    ['Hmaj7',[-1,2,4,3,4,2],   [0,1,3,2,4,1],   2, 2,    1,    5],
    ['Asus2',[-1,0,2,2,0,0],   [0,0,1,2,0,0],   1, null, null, null],
    ['Dsus2',[-1,-1,0,2,3,0],  [0,0,0,1,3,0],   1, null, null, null],
    ['Esus2',[0,2,2,1,0,0],    [0,2,3,1,0,0],   1, null, null, null],
    ['Gsus2',[3,0,0,2,3,3],    [2,0,0,1,3,4],   1, null, null, null],
    ['Asus4',[-1,0,2,2,3,0],   [0,0,1,2,4,0],   1, null, null, null],
    ['Dsus4',[-1,-1,0,2,3,3],  [0,0,0,1,3,4],   1, null, null, null],
    ['Esus4',[0,2,2,2,0,0],    [0,1,2,3,0,0],   1, null, null, null],
    ['Gsus4',[3,3,0,0,1,3],    [2,3,0,0,1,4],   1, null, null, null],
    ['G7sus4',[3,3,0,0,1,1],   [3,4,0,0,1,2],   1, null, null, null],
    ['E7sus4',[0,2,0,2,0,0],   [0,1,0,2,0,0],   1, null, null, null],
    ['A7sus4',[-1,0,2,0,3,0],  [0,0,2,0,3,0],   1, null, null, null],
    ['D7sus4',[-1,-1,0,2,1,3], [0,0,0,2,1,3],   1, null, null, null],
    ['C7sus4',[-1,3,5,3,4,3],  [0,1,3,1,2,1],   3, 3,    1,    5],
    ['Adim',  [-1,0,1,2,1,-1], [0,0,1,3,2,0],   1, null, null, null],
    ['Edim',  [0,1,2,0,-1,-1], [0,1,2,0,0,0],   1, null, null, null],
    ['Hdim',  [-1,2,3,4,3,-1], [0,1,2,4,3,0],   2, null, null, null],
    ['Aaug',  [-1,0,3,2,2,1],  [0,0,4,3,2,1],   1, null, null, null],
    ['Caug',  [-1,3,2,1,1,0],  [0,4,3,1,2,0],   1, null, null, null],
    ['Eaug',  [0,3,2,1,1,0],   [0,3,2,1,1,0],   1, null, null, null],
];

$stmt = $pdo->prepare("
    INSERT INTO chord_diagrams (song_id, name, frets, fingers, starting_fret, barre_fret, barre_from_string, barre_to_string, created_at, updated_at)
    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ON DUPLICATE KEY UPDATE
      frets=VALUES(frets), fingers=VALUES(fingers), starting_fret=VALUES(starting_fret),
      barre_fret=VALUES(barre_fret), barre_from_string=VALUES(barre_from_string),
      barre_to_string=VALUES(barre_to_string), updated_at=NOW()
");

// ON DUPLICATE nefunguje bez unique indexu pre (name, NULL) – použijeme manuálny upsert
$check = $pdo->prepare("SELECT id FROM chord_diagrams WHERE name=? AND song_id IS NULL LIMIT 1");
$update = $pdo->prepare("UPDATE chord_diagrams SET frets=?,fingers=?,starting_fret=?,barre_fret=?,barre_from_string=?,barre_to_string=?,updated_at=NOW() WHERE id=?");
$insert = $pdo->prepare("INSERT INTO chord_diagrams (song_id,name,frets,fingers,starting_fret,barre_fret,barre_from_string,barre_to_string,created_at,updated_at) VALUES (NULL,?,?,?,?,?,?,?,NOW(),NOW())");

$inserted = 0; $updated = 0;
foreach ($chords as $c) {
    [$name, $frets, $fingers, $sf, $bf, $bfs, $bts] = $c;
    $fretsJ   = json_encode($frets);
    $fingersJ = json_encode($fingers);
    $check->execute([$name]);
    $row = $check->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $update->execute([$fretsJ, $fingersJ, $sf, $bf, $bfs, $bts, $row['id']]);
        $updated++;
    } else {
        $insert->execute([$name, $fretsJ, $fingersJ, $sf, $bf, $bfs, $bts]);
        $inserted++;
    }
}

echo "✅ Akordy: $inserted nových, $updated aktualizovaných\n";
echo '</pre>';
echo '<p style="color:green;font-weight:bold;font-size:1.1rem">✅ Hotovo! ZMAŽ súbor run-chord-migration.php cez FTP!</p>';
