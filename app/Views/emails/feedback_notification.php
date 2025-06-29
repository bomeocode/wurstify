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
    <div class="header">Neues Feedback auf Wurstify!</div>

    <p>Hallo Admin,</p>
    <p>soeben wurde neues Feedback über das Formular auf Wurstify.com eingereicht.</p>

    <div class="meta-info">
      <strong>Eingereicht von:</strong> <?= esc($username) ?><br>
      <strong>Zeitpunkt:</strong> <?= esc($createdAt) ?><br>
      <strong>Browser/Gerät:</strong> <?= esc($userAgent) ?>
    </div>

    <p><strong>Feedback-Text:</strong></p>
    <blockquote>
      <p><?= nl2br(esc($feedbackText)) ?></p>
    </blockquote>

    <hr style="border: 0; border-top: 1px solid #eee; margin-top: 20px;">
    <p style="font-size: 0.8em; color: #aaa;">Diese E-Mail wurde automatisch generiert.</p>
  </div>
</body>

</html>