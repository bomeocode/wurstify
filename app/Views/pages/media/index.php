<?= $this->extend('layouts/main_light') ?>

<?= $this->section('title') ?>EduScreen - Medien<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h2>Medienverwaltung</h2>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">Neue Datei hochladen</h5>

    <div id="uploadStatus"></div>

    <?= form_open_multipart('media/upload', ['id' => 'uploadForm']) ?>
    <div class="mb-3">
      <label for="userfile" class="form-label">Datei auswählen (PDF, JPG, PNG - max. 5MB)</label>
      <input class="form-control" type="file" name="userfile" id="userfile" required>
    </div>
    <button type="submit" id="uploadButton" class="btn btn-primary">Hochladen</button>
    </form>

    <div id="uploadProgress" class="mt-3 d-none">
      <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>
  </div>
</div>

<div class="row mb-3">
  <div class="col-md-8">
    <input type="search" id="media-search" class="form-control" placeholder="Suche nach Dateinamen...">
  </div>
  <div class="col-md-4">
    <select id="media-filter" class="form-select">
      <option value="">Alle Typen</option>
      <option value="image">Bilder (JPG, PNG)</option>
      <option value="application/pdf">PDFs</option>
    </select>
  </div>
</div>

<div class="list-group" id="media-list">
  <?= $this->include('pages/media/list_items_partial', ['media' => $media]) ?>
</div>

<div id="load-more-trigger" class="text-center p-4">
  <div class="spinner-border" role="status">
    <span class="visually-hidden">Lade mehr...</span>
  </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">Vorschau</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>