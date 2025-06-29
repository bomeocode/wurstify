<div class="alert alert-info">
  <h4 class="alert-heading">Wurstify ist in der Beta-Phase!</h4>
  <p>Wir arbeiten ständig an Verbesserungen. Wenn Dir ein Fehler auffällt oder Du einen Vorschlag hast, wie wir Wurstify noch besser machen können, teile es uns bitte mit. Jedes Feedback ist wertvoll!</p>
</div>

<?= form_open(route_to('feedback_create'), ['id' => 'feedback-form']) ?>
<div class="mb-3">
  <label for="feedback_text" class="form-label">Dein Feedback:</label>
  <textarea name="feedback_text" id="feedback_text" class="form-control" rows="5" required minlength="10"></textarea>
</div>
<div class="d-grid">
  <button type="submit" class="btn btn-primary">Feedback absenden</button>
</div>
<?= form_close() ?>