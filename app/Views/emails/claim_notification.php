<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <title>Neues Feedback auf Wurstify</title>
  <style>
    body {
      font-family: sans-serif;
      line-height: 1.6;
      color: #333;
    }

    .container {
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 5px;
      max-width: 600px;
      margin: 20px auto;
    }

    .header {
      font-size: 1.5em;
      color: #c71585;
      /* Ein passendes Lila-Rot */
      margin-bottom: 20px;
    }

    .meta-info {
      background-color: #f9f9f9;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 0.9em;
      color: #555;
    }

    blockquote {
      border-left: 4px solid #eee;
      padding-left: 15px;
      margin: 0;
      font-style: italic;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="header">Neuer Inhaber-Anspruch!</div>
    <p>Ein Nutzer hat versucht, die Inhaberschaft für einen Anbieter zu beanspruchen.</p>

    <h3>Details zum Anspruch</h3>
    <ul>
      <li><strong>Anbieter:</strong> <?= esc($vendor['name']) ?> (ID: <?= esc($vendor['id']) ?>)</li>
      <li><strong>Antragsteller:</strong> <?= esc($claimant_name) ?> (User-ID: <?= esc($user_id) ?>)</li>
      <li><strong>Kontakt-E-Mail:</strong> <?= esc($contact_email) ?></li>
    </ul>

    <p><strong>Begründung / Nachweis:</strong></p>
    <blockquote>
      <p><?= nl2br(esc($proof_text)) ?></p>
    </blockquote>

    <hr>
    <h3>Telemetrie-Daten</h3>
    <ul>
      <li><strong>Zeitpunkt:</strong> <?= date('d.m.Y H:i:s') ?></li>
      <li><strong>IP-Adresse:</strong> <?= esc($ip_address) ?></li>
      <li><strong>User Agent:</strong> <?= esc($user_agent) ?></li>
    </ul>

    <p>Bitte prüfen Sie diesen Anspruch im Admin-Bereich und weisen Sie den Anbieter dem Nutzer zu, falls der Anspruch legitim ist.</p>
  </div>
</body>

</html>