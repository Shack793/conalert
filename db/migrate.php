<?php
// Usage: php db/migrate.php --path=db/migration_v2_footer_number_links.sql
$path = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--path=')) $path = substr($a, 7);
}
if (!$path) $path = 'db/migration_v2_footer_number_links.sql';
if (!file_exists($path) && file_exists(__DIR__ . '/' . basename($path))) $path = __DIR__ . '/' . basename($path);
if (!file_exists($path)) { fwrite(STDERR, "File not found: $path\n"); exit(1); }
require __DIR__ . '/../db.php';
$pdo = get_db();
$sql = file_get_contents($path);
// split by ; but handle simple case — use PDO exec per statement
$stmts = array_filter(array_map('trim', explode(';', $sql)));
$ok=0; foreach($stmts as $s){ if($s==='') continue; try{ $pdo->exec($s); $ok++; } catch(Throwable $e){ echo "SKIP: ".$e->getMessage()."\n"; } }
echo "Done $ok statements from $path\n";
foreach(['cases case_number','settings','social_links'] as $t){
  try{ $c=$pdo->query("SELECT COUNT(*) FROM ".explode(' ',$t)[0])->fetchColumn(); echo "$t: $c rows\n"; }catch(Throwable $e){}
}
