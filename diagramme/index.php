<?php
$pdo = require __DIR__ . '/../db.php';
$kontoId = 1;
$startMonth = $_GET['start_monat'] ?? date('Y-m', strtotime('-11 months'));
$endMonth = $_GET['end_monat'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $startMonth)) $startMonth = date('Y-m', strtotime('-11 months'));
if (!preg_match('/^\d{4}-\d{2}$/', $endMonth)) $endMonth = date('Y-m');
if ($startMonth > $endMonth) [$startMonth, $endMonth] = [$endMonth, $startMonth];
$from = $startMonth . '-01';
$to = date('Y-m-t', strtotime($endMonth . '-01'));

$stmt = $pdo->prepare("SELECT DATE_FORMAT(datum,'%Y-%m') monat,
SUM(CASE WHEN betrag>0 THEN betrag ELSE 0 END) einnahmen,
ABS(SUM(CASE WHEN betrag<0 THEN betrag ELSE 0 END)) ausgaben,
SUM(betrag) saldo
FROM transaktionen WHERE konto_id=:konto AND datum BETWEEN :von AND :bis
GROUP BY DATE_FORMAT(datum,'%Y-%m') ORDER BY monat");
$stmt->execute(['konto'=>$kontoId,'von'=>$from,'bis'=>$to]);
$raw=[]; foreach($stmt->fetchAll() as $r){$raw[$r['monat']]=$r;}
$labels=$income=$expense=$balance=[];
$c=new DateTimeImmutable($from);$e=new DateTimeImmutable($to);
while($c<=$e){$k=$c->format('Y-m');$labels[]=$c->format('m/Y');$income[]=(float)($raw[$k]['einnahmen']??0);$expense[]=(float)($raw[$k]['ausgaben']??0);$balance[]=(float)($raw[$k]['saldo']??0);$c=$c->modify('+1 month');}

$stmt=$pdo->prepare("SELECT th.name,ABS(SUM(t.betrag)) summe FROM transaktionen t JOIN themen th ON th.id=t.thema_id WHERE t.konto_id=:konto AND t.betrag<0 AND t.datum BETWEEN :von AND :bis GROUP BY th.id,th.name ORDER BY summe DESC");
$stmt->execute(['konto'=>$kontoId,'von'=>$from,'bis'=>$to]);$cats=$stmt->fetchAll();
$catLabels=array_column($cats,'name');$catValues=array_map('floatval',array_column($cats,'summe'));
?>
<!doctype html><html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Diagramme – Haushaltskasse</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="../style.css"><script src="https://cdn.jsdelivr.net/npm/chart.js"></script></head><body>
<header class="topbar"><a class="brand" href="../index.php"><span class="logo"><i class="bi bi-wallet2"></i></span><strong>Haushaltskasse</strong></a><nav class="main-nav"><a href="../index.php"><i class="bi bi-speedometer2"></i> Dashboard</a><a href="#verlauf"><i class="bi bi-graph-up"></i> Verlauf</a><a href="#kategorien"><i class="bi bi-pie-chart"></i> Kategorien</a><a href="../index.php#buchungen"><i class="bi bi-receipt"></i> Buchungen</a></nav><span class="account-name">Diagramme</span></header>
<main class="page-shell"><section class="panel"><div class="panel-heading"><div><p class="eyebrow">Detailauswertung</p><h2>Diagramme</h2></div><form method="get" class="range-form"><label>Anfangsmonat<input type="month" name="start_monat" value="<?=htmlspecialchars($startMonth)?>"></label><label>Endmonat<input type="month" name="end_monat" value="<?=htmlspecialchars($endMonth)?>"></label><button class="button primary">Anzeigen</button></form></div></section>
<section class="panel" id="verlauf" style="margin-top:18px"><div class="panel-heading"><div><p class="eyebrow">Zeitverlauf</p><h2>Einnahmen und Ausgaben pro Monat</h2></div></div><div class="chart-wrap"><canvas id="trend"></canvas></div></section>
<section class="small-charts-grid" style="margin-top:18px"><article class="panel"><div class="panel-heading"><div><p class="eyebrow">Saldo</p><h2>Monatlicher Saldo</h2></div></div><div class="chart-wrap small"><canvas id="saldo"></canvas></div></article><article class="panel" id="kategorien"><div class="panel-heading"><div><p class="eyebrow">Verteilung</p><h2>Ausgaben nach Kategorie</h2></div></div><div class="chart-wrap small"><canvas id="cats"></canvas></div></article></section></main>
<script>const money=v=>new Intl.NumberFormat('de-DE',{style:'currency',currency:'EUR'}).format(v);
new Chart(document.getElementById('trend'),{type:'line',data:{labels:<?=json_encode($labels)?>,datasets:[{label:'Einnahmen',data:<?=json_encode($income)?>,borderColor:'#16a34a',backgroundColor:'rgba(22,163,74,.12)',tension:.25,fill:true},{label:'Ausgaben',data:<?=json_encode($expense)?>,borderColor:'#dc2626',backgroundColor:'rgba(220,38,38,.10)',tension:.25,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{tooltip:{callbacks:{label:c=>c.dataset.label+': '+money(c.raw)}}}}});
new Chart(document.getElementById('saldo'),{type:'bar',data:{labels:<?=json_encode($labels)?>,datasets:[{data:<?=json_encode($balance)?>,backgroundColor:c=>c.raw>=0?'#16a34a':'#dc2626',borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>money(c.raw)}}}}});
new Chart(document.getElementById('cats'),{type:'doughnut',data:{labels:<?=json_encode($catLabels,JSON_UNESCAPED_UNICODE)?>,datasets:[{data:<?=json_encode($catValues)?>}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right'},tooltip:{callbacks:{label:c=>c.label+': '+money(c.raw)}}}}});</script></body></html>
